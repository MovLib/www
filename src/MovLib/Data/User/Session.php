<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Data\User;

use \MovLib\Data\User\Full as UserFull;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Exception\SessionException;

/**
 * The session model loads the basic user information, creates, updates and deletes sessions.
 *
 * The following attributes are always available:
 * <ul>
 *   <li><code>Session::$authentication</code> contains the timestamp of the time when this session was initialized</li>
 *   <li><code>Session::$csrfToken</code> contains this session's CSRF token used to validate forms</li>
 *   <li><code>Session::$isAuthenticated</code> is a flag indicating if this is a known user</li>
 *   <li><code>Session::$userId</code> is zero for anonymous users, otherwise it contains the unique user's ID</li>
 *   <li><code>Session::$userName</code> contains the IP address for anonymous users, otherwise the user's unique name</li>
 * </ul>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Session extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Timestamp this session was first authenticated.
   *
   * @var int
   */
  public $authentication;

  /**
   * The session's CSRF token.
   *
   * @var string
   */
  public $csrfToken;

  /**
   * The session's ID.
   *
   * @var string
   */
  public $id;

  /**
   * The user's authentication status.
   *
   * @var boolean
   */
  public $isAuthenticated = false;

  /**
   * The session's name.
   *
   * @var string
   */
  private $name;

  /**
   * The session's user ID.
   *
   * @var int
   */
  public $userId = 0;

  /**
   * The session's user name.
   *
   * @var string
   */
  public $userName;

  /**
   * The session's user time zone ID.
   *
   * Defaults to global PHP INI default timezone.
   *
   * @see Session::__construct()
   * @var string
   */
  public $userTimeZoneId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Resume existing session if any.
   *
   * @global \MovLib\Kernel $kernel
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct() {
    global $kernel;

    // Export the session's name to class scope.
    $this->name = session_name();

    // Only attempt to load the session if a non-empty session ID is present. Anonymous user's don't get any session to
    // ensure that HTTP proxies are able to cache anonymous pageviews.
    if (!empty($_COOKIE[$this->name])) {
      // Try to resume the session with the ID from the cookie.
      if (session_start() === false) {
        throw new \MemcachedException("Could not resume session (maybe Memcached is down).");
      }
      $this->id = session_id();

      // We have to try loading the session from our persistent session storage if the session IDs don't match.
      if ($_COOKIE[$this->name] != $this->id || !($result = $this->query("SELECT `user_id`, UNIX_TIMESTAMP(`authentication`) AS `authentication` FROM `sessions` WHERE `session_id` = ? LIMIT 1", "s", [ $_COOKIE[$this->name] ])->get_result()->fetch_assoc())) {
        $this->init($result["user_id"], $result["authentication"]);
        $kernel->delayMethodCall([ $this, "update" ], [ $_COOKIE[$this->name] ]);
      }
      // Maybe somebody is trying with a random session ID to get a session?
      elseif (!isset($_SESSION["user_id"])) {
        $this->destroy();
      }
      // If we have a user ID everything should be fine, export default session data to class scope.
      else {
        $this->csrfToken      = $_SESSION["csrf_token"];
        $this->authentication = $_SESSION["authentication"];
        $this->userId         = $_SESSION["user_id"];
        $this->userName       = $_SESSION["user_name"];
        $this->userTimeZoneId = $_SESSION["user_time_zone_id"];

        // Regenerate the session ID at least every 20 minutes (OWASP recommendation).
        if ($this->authentication + 1200 < $_SERVER["REQUEST_TIME"]) {
          $this->regenerate();
        }

        // If the stored user ID is greather than 0 it's a known user.
        if ($this->userId > 0) {
          $this->isAuthenticated = true;
        }
      }
    }
    else {
      $this->userTimeZoneId = ini_get("date.timezone");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Authenticate a user.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $email
   *   The user submitted email address.
   * @param string $rawPassword
   *   The user submitted raw password.
   * @return this
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\SessionException
   */
  public function authenticate($email, $rawPassword) {
    global $kernel;

    // Load necessary user data from storage (if we have any).
    $result = $this->query("SELECT `id`, `password` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ])->get_result()->fetch_assoc();
    if (!$result) {
      throw new SessionException("Couldn't find user with email '{$email}'!");
    }

    // Validate the submitted password.
    if (password_verify($rawPassword, $result["password"]) === false) {
      throw new SessionException("Invalid password for user with email {$email}!");
    }

    // My be the user was doing some work as anonymous user and already has a session active. If so generate new session
    // ID and if not generate a completely new session.
    session_status() === PHP_SESSION_ACTIVE ? $this->regenerate() : $this->start();
    $this->init($result["id"]);
    $kernel->delayMethodCall([ $this, "insert" ]);

    // @todo Is this unnecessary overhead or a good protection? If PHP updates the default password this would be the
    //       only way to update the password's of all users. We execute it delayed, so there's only the server load we
    //       have to worry about. Maybe introduce a configuration option for this?
    $kernel->delayMethodCall([ $this, "passwordNeedsRehash" ], [ $result["password"], $rawPassword ]);

    return $this;
  }

  /**
   * Check if the user is authenticated and if not throw an unauthorized exception.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function checkAuthorization($message) {
    if ($this->isAuthenticated === false) {
      throw new UnauthorizedException($message);
    }
    return $this;
  }

  /**
   * Check if the user is authenticated and if the she or he authenticated recently.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function checkAuthorizationTimestamp($message) {
    if ($this->isAuthenticated === false || $this->authentication + 3600 < $_SERVER["REQUEST_TIME"]) {
      throw new UnauthorizedException($message);
    }
    return $this;
  }

  /**
   * Deletes this session from our session database.
   *
   * @delayed
   * @param string|array $sessionId [optional]
   *   The unique session ID(s) that should be deleted. If no ID is passed along the current session ID of this instance
   *   will be used. If a numeric array is passed all values are treated as session IDs and deleted.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete($sessionId = null) {
    $sessionPrefix = ini_get("memcached.sess_prefix");
    if (!$sessionId) {
      $sessionId = $this->id;
    }

    // Fetch all configured Memcached servers from the PHP configuration and split them by the delimiter.
    $servers = explode(",", ini_get("session.save_path"));

    // Build the array as expected by Memcached::addServers().
    $c = count($servers);
    for ($i = 0; $i < $c; ++$i) {
      $servers[$i] = explode(":", $servers[$i]);
      // The port is mandatory!
      if (!isset($servers[$i][1])) {
        $servers[$i][1] = 0;
      }
    }

    try {
      $memcached = new \Memcached();
      $memcached->addServers($servers);
      if (is_array($sessionId)) {
        $c      = count($sessionId);
        $clause = rtrim(str_repeat("?,", $c), ",");
        $this->query("DELETE FROM `sessions` WHERE `session_id` IN ({$clause})", str_repeat("s", $c), $sessionId);
        for ($i = 0; $i < $c; ++$i) {
          $sessionId[$i] = "{$sessionPrefix}{$sessionId[$i]}";
        }
        $memcached->deleteMulti($sessionId);
      }
      else {
        $this->query("DELETE FROM `sessions` WHERE `session_id` = ?", "s", [ $sessionId ]);
        $memcached->delete("{$sessionPrefix}{$sessionId}");
      }
    }
    catch (\MemcachedException $e) {
      throw new DatabaseException($e->getMessage(), $e);
    }

    return $this;
  }

  /**
   * Destroy this session.
   *
   * Removes this session from our Memcached and persistent storage, additionally a delete cookie is sent, requesting
   * the user's user agent to delete this session cookie. As you know, this is something that is up to the user, that's
   * why it's important for us to delete this session from all our storage devices.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  public function destroy() {
    global $kernel;

    // The user is no longer authenticated, keep this line outside of the if for PHPUnit tests.
    $this->isAuthenticated = false;

    // If no session is active, nothing has to be done.
    if (session_status() === PHP_SESSION_ACTIVE) {
      // Remove all data associated with this session.
      session_unset();
      session_destroy();
      // Remove the cookie.
      $cookie = session_get_cookie_params();
      setcookie($this->name, "", 1, $cookie["path"], $cookie["domain"], $cookie["secure"], $cookie["httponly"]);
      session_write_close();
      // Remove the session ID from our database.
      $kernel->delayMethodCall([ $this, "delete" ], [ $this->id ]);
    }

    return $this;
  }

  /**
   * Retrieve a list of all active sessions.
   *
   * @return array
   *   Numeric array ontaining all sessions currently stored in the persistent session storage for the currently signed
   *   in user. Each entry in the numeric array is an associative array with the following entries:
   *   <ul>
   *     <li><code>"session_id"</code> is the session's unique ID</li>
   *     <li><code>"authentication"</code> is the timestamp when this session was initially created</li>
   *     <li><code>"ip_address"</code> is the IP address stored during authentication or regeneration</li>
   *     <li><code>"user_agent"</code> is the user agent string submitted during authentication or regeneration</li>
   *   </ul>
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getActiveSessions() {
    return $this->query(
      "SELECT `session_id`, UNIX_TIMESTAMP(`authentication`) AS `authentication`, `ip_address`, `user_agent` FROM `sessions` WHERE `user_id` = ?",
      "d",
      [ $_SESSION["user_id"] ]
    )->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Initialize session with default data.
   *
   * @global \MovLib\Kernel $kernel
   * @param int $userId [optional]
   *   The ID of the user for wish we should initialize a session. Zero is used if no value is passed, this will
   *   initialize the session for an anonymous user.
   * @param int $signIn [optional]
   *   The timestamp of the last time this user signed in. The current timestamp is used if no value is given.
   * @return this
   * @throws \MovLib\Exception\SessionException
   */
  private function init($userId = 0, $signIn = null) {
    global $kernel;

    // We might be changing from an anonymous session to a signed in session while a form is submitted (e.g. the login
    // form is visited by an anonymous user with an active anonymous session and the form is submitted with the CSRF
    // token) therefore we have to validate the CSRF token. If the token is invalid at this point, reject creation of
    // a new session (which might result in elevated privileges).
    if ($this->validateCsrfToken() === false) {
      return $this;
    }

    $this->id             = session_id();
    $this->csrfToken      = $_SESSION["csrf_token"]     = hash("sha512", openssl_random_pseudo_bytes(1024));
    $this->authentication = $_SESSION["authentication"] = $signIn ? : $_SERVER["REQUEST_TIME"];

    // Update the (already validated) post submitted CSRF token to the newly generated one. This is important, because
    // any submitted form will validate the token automatically again, but this would result in an invalid form because
    // we just created a new session with a new CSRF token for this client.
    if (isset($_POST["csrf"])) {
      $_POST["csrf"] = $this->csrfToken;
    }

    // We are initializing this session for a registered user.
    if ($userId > 0) {
      if (!($result = $this->query("SELECT `name`, `timeZoneId` FROM `users` WHERE `id` = ? LIMIT 1", "d", [ $userId ])->get_result()->fetch_assoc())) {
        throw new SessionException("Could not fetch user data for user ID {$userId}.");
      }
      $this->userId          = $_SESSION["user_id"]           = $userId;
      $this->userName        = $_SESSION["user_name"]         = $result["name"];
      $this->userTimeZoneId  = $_SESSION["user_time_zone_id"] = $result["timeZoneId"];
      $this->isAuthenticated = true;
    }
    // Initialize this session for an anonymous user.
    else {
      $this->userId          = $_SESSION["user_id"]           = $userId;
      $this->userName        = $_SESSION["user_name"]         = $kernel->remoteAddress;
      // @todo Guess timezone with JavaScript: https://bitbucket.org/pellepim/jstimezonedetect
      $this->userTimeZoneId  = $_SESSION["user_time_zone_id"] = ini_get("date.timezone");
      $this->isAuthenticated = false; // Just making sure
    }

    return $this;
  }

  /**
   * Insert newly created session into persistent session storage.
   *
   * @delayed
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insert() {
    global $kernel;
    return $this->query(
      "INSERT INTO `sessions` (`session_id`, `user_id`, `user_agent`, `ip_address`, `authentication`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
      "sdssi",
      [ $this->id, $this->userId, $kernel->userAgent, inet_pton($kernel->remoteAddress), $this->authentication ]
    );
  }

  /**
   * Test after every authentication if the password needs to be rehashed.
   *
   * @global \MovLib\Kernel $kernel
   * @delayed
   * @param string $password
   *   The hashed password.
   * @param string $rawPassword
   *   The unhashed password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\UserException
   */
  public function passwordNeedsRehash($password, $rawPassword) {
    global $kernel;
    if (password_needs_rehash($password, PASSWORD_DEFAULT, $kernel->passwordOptions) === true) {
      $user     = new UserFull(UserFull::FROM_ID, $this->userId);
      $password = $user->hashPassword($rawPassword);
      $user->updatePassword($password);
    }
    return $this;
  }

  /**
   * Regenerate session ID and update persistent storage.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  private function regenerate() {
    global $kernel;

    // Do nothing if this method isn't called via nginx!
    if (isset($_SERVER["FCGI_ROLE"])) {
      session_regenerate_id(true);
      $kernel->delayMethodCall([ $this, "update" ], [ $this->id ]);
      $this->id = session_id();
    }

    return $this;
  }

  /**
   * Shutdown the currently active session and start one for anonymous users if we have to.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \MemcachedException
   * @throws \MovLib\Exception\SessionException
   */
  public function shutdown() {
    global $kernel;
    $status = session_status();

    // Only start a session for this anonymous user if there is any data that we need to remember and if no session is
    // already active (which is the case if this request was made by an authenticated user).
    if ($status === PHP_SESSION_NONE && !empty($_SESSION)) {
      error_log(print_r($_SESSION, true));
      // Tell the user agent to delete this cookie on it's own shutdown (e.g. closing browser).
      session_set_cookie_params(0);
      $this->start()->init();
    }

    // Save session data to Memcached before sending the response to the user. No matter if we just started a session
    // in the code above or we already have an active user session. This ensures that the session lock is released for
    // this session and the next request can resume this session.
    if ($status === PHP_SESSION_ACTIVE) {
      session_write_close();
      if ($this->userId > 0) {
        $kernel->delayMethodCall([ $this, "updateUserAccess" ]);
      }
    }

    return $this;
  }

  /**
   * Forcefully start new or resume session and keep previously set session data (if any).
   *
   * @return this
   * @throws \MemcachedException
   */
  private function start() {
    // Create backup of existing session data (if any).
    $sessionData = isset($_SESSION) ? $_SESSION : null;

    // Start new session (if exeution was started by nginx).
    if (isset($_SERVER["FCGI_ROLE"]) && session_start() === false) {
      throw new \MemcachedException("Could not start session (may be Memcached is down?).");
    }

    // Restore session data.
    if ($sessionData) {
      $_SESSION += $sessionData;
    }

    return $this;
  }

  /**
   * Update the ID of a session in our persistent session store.
   *
   * @delayed
   * @global \MovLib\Kernel $kernel
   * @param string $oldSessionId
   *   The old session ID that should be updated.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function update($oldSessionId) {
    global $kernel;
    return $this->query(
      "UPDATE `sessions` SET `session_id` = ?, `ip_address` = ?, `user_agent` = ? WHERE `session_id` = ? AND `user_id` = ?",
      "ssssd",
      [ $this->id, inet_pton($kernel->remoteAddress), $kernel->userAgent, $oldSessionId, $this->userId ]
    );
  }

  /**
   * Update the user's access time.
   *
   * @return this
   */
  public function updateUserAccess() {
    return $this->query("UPDATE `users` SET `access` = CURRENT_TIMESTAMP WHERE `id` = ?", "d", [ $this->userId ]);
  }

  /**
   * Validate session's CSRF token.
   *
   * @return boolean
   *   <code>TRUE</code> if the token is valid, otherwise <code>FALSE</code>.
   */
  public function validateCsrfToken() {
    if ($this->csrfToken && (empty($_POST["csrf"]) || $this->csrfToken != $_POST["csrf"])) {
      $this->regenerate();
      return false;
    }
    return true;
  }

}
