<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Data;

use \Memcached;
use \MemcachedException;
use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Data\User;
use \MovLib\Exception\SessionException;
use \MovLib\Exception\UserException;
use \MovLib\Exception\UnauthorizedException;

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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Session extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Alert messages that should be displayed to the user on the next pageview.
   *
   * @var string
   */
  public $alerts = "";

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
   * The session's IP address.
   *
   * This value is only available if this session was newly initialized!
   *
   * @var string
   */
  private $ipAddress;

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
   * The session's user agent string.
   *
   * This value is only available if this session was newly initialized!
   *
   * @var string
   */
  private $userAgent;

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
   * The session's user timezone.
   *
   * @var string
   */
  public $userTimezone;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Resume existing session if any.
   *
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct() {
    // Export the session's name to class scope.
    $this->name = session_name();

    // Only attempt to load the session if a non-empty session ID is present. Anonymous user's don't get any session to
    // ensure that HTTP proxies are able to cache anonymous pageviews.
    if (!empty($_COOKIE[$this->name])) {
      // Try to resume the session with the ID from the cookie.
      if (session_start() === false) {
        throw new MemcachedException("Could not resume session (may be Memcached is down).");
      }
      $this->id = session_id();

      // We have to try loading the session from our persistent session storage if the session IDs don't match.
      if ($_COOKIE[$this->name] != $this->id) {
        $result = $this->select("SELECT `user_id`, UNIX_TIMESTAMP(`authentication`) AS `authentication` FROM `sessions` WHERE `session_id` = ? LIMIT 1", "s", $_COOKIE[$this->name]);

        // This is an old session that requires sign in and it's expired for anonymous users.
        if (empty($result[0])) {
          $this->destroy();
        }
        // Otherwise we have to initialize this new session with fresh data and update the record in our persistent
        // session storage.
        else {
          $this->init($result[0]["user_id"], $result[0]["authentication"]);
          DelayedMethodCalls::stack($this, "update", [ $_COOKIE[$this->name] ]);
        }
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
        $this->userTimezone   = $_SESSION["user_timezone"];
        if ($this->authentication + 86400 < time()) {
          $this->regenerate();
        }
        if ($this->userId > 0) {
          $this->isAuthenticated = true;
        }
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Authenticate a user.
   *
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
    // Load necessary user data from storage.
    $result = $this->select("SELECT `user_id`, `name`, `password`, `deactivated` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ]);

    // We couldn't find a user for the given email address if above query's result is empty.
    if (empty($result[0])) {
      throw new SessionException("Could not find user with email {$email}");
    }

    // Validate the submitted password.
    if (password_verify($rawPassword, $result[0]["password"]) === false) {
      throw new SessionException("Invalid password for user with email {$email}");
    }

    // My be the user was doing some work as anonymous user and already has a session active. If so generate new session
    // ID and if not generate a completely new session.
    if (session_status() === PHP_SESSION_ACTIVE) {
      $this->regenerate();
    }
    else {
      $this->start();
    }
    $this->init($result[0]["user_id"]);
    DelayedMethodCalls::stack($this, "insert");

    // @todo Is this unnecessary overhead or a good protection? If PHP updates the default password this would be the
    //       only way to update the password's of all users. We execute it delayed, so there's only the server load we
    //       have to worry about. Maybe introduce a configuration option for this?
    DelayedMethodCalls::stack($this, "passwordNeedsRehash", [ $result[0]["password"], $rawPassword ]);

    if ((bool) $result[0]["deactivated"] === true) {
      throw new UserException("Account is deactivated!");
    }

    return $this;
  }

  /**
   * Check if the user is authenticated and if not throw an unauthorized exception.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Exception\UnauthorizedException
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
   * @throws \MovLib\Exception\UnauthorizedException
   */
  public function checkAuthorizationTimestamp($message) {
    if ($this->isAuthenticated === false || $this->authentication + 3600 < time()) {
      throw new UnauthorizedException($message);
    }
    return $this;
  }

  /**
   * Deletes this session from our session database.
   *
   * Must be public for delayed execution.
   *
   * @param string|array $sessionId [optional]
   *   The unique session ID(s) that should be deleted. If no ID is passed along the current session ID of this instance
   *   will be used. If a numeric array is passed all values are treated as session IDs and deleted.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete($sessionId = null) {
    $sessionPrefix = ini_get("memcached.sess_prefix");
    $sessionId = $sessionId ?: $this->id;
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
      $memcached = new Memcached();
      $memcached->addServers($servers);
      if (is_array($sessionId)) {
        $c = count($sessionId);
        $clause = implode(", ", array_fill(0, $c, "?"));
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
    catch (MemcachedException $e) {
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
   * @return this
   */
  public function destroy() {
    if (session_status() === PHP_SESSION_ACTIVE) {
      // The user is no longer authenticated!
      $this->isAuthenticated = false;
      // Remove all data associated with this session.
      session_destroy();
      session_unset();
      // Remove the cookie.
      $cookieParams = session_get_cookie_params();
      setcookie(session_name(), "", time() - 42000, $cookieParams["path"], $cookieParams["domain"], $cookieParams["secure"], $cookieParams["httponly"]);
      // Remove the session ID from our database.
      DelayedMethodCalls::stack($this, "delete");
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
    return $this->select(
      "SELECT `session_id`, UNIX_TIMESTAMP(`authentication`) AS `authentication`, `ip_address`, `user_agent` FROM `sessions` WHERE `user_id` = ?",
      "d",
      [ $_SESSION["user_id"] ]
    );
  }

  /**
   * Initialize session with default data.
   *
   * @param int $userId [optional]
   *   The ID of the user for wish we should initialize a session. Zero is used if no value is passed, this will
   *   initialize the session for an anonymous user.
   * @param int $signIn [optional]
   *   The timestamp of the last time this user signed in. The current timestamp is used if no value is given.
   * @return this
   * @throws \MovLib\Exception\SessionException
   */
  private function init($userId = 0, $signIn = null) {
    // IP address and user agent string are only used within the danger zone settings to display them to the user. We
    // don't validate them and the session isn't bound to them.
    //
    // @todo If we're ever going to use proxy servers this code has to be changed!
    //       https://github.com/komola/ZendFramework/blob/master/Controller/Request/Http.php#L1054
    $this->id             = session_id();
    $this->csrfToken      = $_SESSION["csrf_token"]     = hash("sha512", openssl_random_pseudo_bytes(1024));
    $this->ipAddress      = $_SESSION["ip_address"]     = filter_input(INPUT_SERVER, "REMOTE_ADDR", FILTER_SANITIZE_STRING);
    $this->userAgent      = $_SESSION["user_agent"]     = filter_input(INPUT_SERVER, "HTTP_USER_AGENT", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    $this->authentication = $_SESSION["authentication"] = $signIn ? : time();

    // We are initializing this session for a registered user.
    if ($userId > 0) {
      $result = $this->select("SELECT `name`, `timezone` FROM `users` WHERE `user_id` = ? LIMIT 1", "d", [ $userId]);
      if (empty($result[0]["name"])) {
        throw new SessionException("Could not fetch user name for user ID {$userId}.");
      }
      $this->userId          = $_SESSION["user_id"]       = $userId;
      $this->userName        = $_SESSION["user_name"]     = $result[0]["name"];
      $this->userTimezone    = $_SESSION["user_timezone"] = $result[0]["timezone"];
      $this->isAuthenticated = true;
    }
    // Initialize this session for an anonymous user.
    else {
      // At this stage we validate the IP address because we'll use it as global display name for this anonymous user.
      // This will be stored in our database as the name for this anonymous user, therefor we have to have a valid IP
      // address!
      //
      // http://stackoverflow.com/a/5092951/1251219
      if (filter_var($this->ipAddress, FILTER_VALIDATE_IP, FILTER_REQUIRE_SCALAR) === false) {
        throw new SessionException("Empty or invalid IP address (this is more or less impossible, check web server and if behind a proxy check implementation).");
      }
      $this->userId          = $_SESSION["user_id"]       = $userId;
      $this->userName        = $_SESSION["user_name"]     = $this->ipAddress;
      // @todo Guess timezone with JavaScript: https://bitbucket.org/pellepim/jstimezonedetect
      $this->userTimezone    = $_SESSION["user_timezone"] = "UTC";
      $this->isAuthenticated = false; // Just making sure
    }

    return $this;
  }

  /**
   * Insert newly created session into persistent session storage.
   *
   * Must be public for delayed execution.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insert() {
    return $this->query(
      "INSERT INTO `sessions` (`session_id`, `user_id`, `user_agent`, `ip_address`, `authentication`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
      "sdssi",
      [ $this->id, $this->userId, $this->userAgent, inet_pton($this->ipAddress), $this->authentication ]
    );
  }

  /**
   * Test after every authentication if the password needs to be rehashed.
   *
   * This method must be public for delayed execution.
   *
   * @param string $password
   *   The hashed password.
   * @param string $rawPassword
   *   The unhashed password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\UserException
   */
  public function passwordNeedsRehash($password, $rawPassword) {
    if (password_needs_rehash($password, PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"]]) === true) {
      $user = new User(User::FROM_ID, $this->userId);
      $user->updatePassword($rawPassword);
    }
    return $this;
  }

  /**
   * Regenerate session ID and update persistent storage.
   *
   * @return this
   */
  private function regenerate() {
    session_regenerate_id(true);
    DelayedMethodCalls::stack($this, "update", [ $this->id ]);
    $this->id = session_id();
    return $this;
  }

  /**
   * Shutdown the currently active session and start one for anonymous users if we have to.
   *
   * @return this
   * @throws \MemcachedException
   * @throws \MovLib\Exception\SessionException
   */
  public function shutdown() {
    // Store alert messages as cookie instead of storing them on our server. This will only increase network traffic by
    // a few bytes instead of wasting our RAM. Plus the alert message is stored until the user closes the user agent,
    // instead of the Memcached lifetime for session entries.
    if (!empty($this->alerts)) {
      setcookie("alerts", $this->alerts, 0, "/", ini_get("session.cookie_domain"));
    }

    // Only start a session for this anonymous user if there is any data that we need to remember and if no session is
    // already active (which is the case if this request was made by an authenticated user).
    if (session_status() === PHP_SESSION_NONE && !empty($_SESSION)) {
      // Tell the user agent to delete this cookie on it's own shutdown (e.g. closing browser).
      session_set_cookie_params(0);
      $this->start()->init();
    }

    // Save session data to Memcached before sending the response to the user. No matter if we just started as session
    // in the code above or we already have an active user session. This ensures that the session lock is released for
    // this session and the next request can resume this session.
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_write_close();
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
    $sessionData = isset($_SESSION) ? $_SESSION : null;
    if (session_start() === false) {
      throw new MemcachedException("Could not start session (may be Memcached is down?).");
    }
    if ($sessionData) {
      $_SESSION = $sessionData;
    }
    return $this;
  }

  /**
   * Update the ID of a session in our persistent session store.
   *
   * Must be public for delayed execution.
   *
   * @param string $oldSessionId
   *   The old session ID that should be updated.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function update($oldSessionId) {
    return $this->query(
      "UPDATE `sessions` SET `session_id` = ?, `ip_address` = ?, `user_agent` = ? WHERE `session_id` = ? AND `user_id` = ?",
      "sissd",
      [ $this->id, inet_pton($this->ipAddress), $this->userAgent, $oldSessionId, $this->userId ]
    );
  }

  /**
   * Validate session's CSRF token.
   *
   * @return boolean
   *   <code>TRUE</code> if the token is valid, otherwise <code>FALSE</code>.
   */
  public function validateCsrfToken() {
    if (session_status() === PHP_SESSION_ACTIVE && (!isset($_POST["csrf"]) || $this->csrfToken != $_POST["csrf"])) {
      $this->regenerate();
      return false;
    }
    return true;
  }

}
