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
class Session implements \ArrayAccess {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this session is active or not.
   *
   * @var boolean
   */
  public $active = false;

  /**
   * Timestamp this session was first authenticated.
   *
   * @var integer
   */
  public $authentication = 0;

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
  protected $name;

  /**
   * The session's user ID.
   *
   * @var integer
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
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Kernel $kernel
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct() {
    global $db, $kernel;

    // Export the session's name to class scope.
    $this->name = session_name();

    // Only attempt to load the session if a non-empty session ID is present. Anonymous user's don't get any session to
    // ensure that HTTP proxies are able to cache anonymous pageviews.
    if (!empty($_COOKIE[$this->name])) {
      // Try to resume the session with the ID from the cookie.
      $this->start();
      $this->id = session_id();

      // Try to load the session from the persistent session storage for registered users if we just generated a new
      // session ID and have no data stored for it.
      if ($_COOKIE[$this->name] != $this->id && empty($_SESSION)) {
        // Load session data from session storage.
        $stmt = $db->query("SELECT UNIX_TIMESTAMP(`authentication`), `user_id` FROM `sessions` WHERE `session_id` = ? LIMIT 1", "s", [ $_COOKIE[$this->name ]]);
        $stmt->bind_result($this->authentication, $this->userId);

        // We couldn't find a valid session and we have no data, invalid session.
        if (!$stmt->fetch()) {
          $this->destroy();
        }
        $stmt->close();
        $stmt = $db->query("SELECT `name`, `time_zone_identifier` FROM `users` WHERE `id` = ? LIMIT 1", "d", [ $this->userId ]);
        $stmt->bind_result($this->userName, $this->userTimeZoneId);

        // Well, this is akward, we have a valid session but no valid user, destroy session and log this error.
        if (!$stmt->fetch()) {
          $this->destroy();
          error_log("Non-existent user ID from persistent session storage, IP was: {$kernel->remoteAddress} (Session ID: {$_COOKIE[$this->name]})");
        }
        $stmt->close();

        // Everything looks good, valid session and valid user, export and update persistent storage.
        $_SESSION["auth"] = $this->authentication;
        $_SESSION["id"]   = $this->userId;
        $_SESSION["name"] = $this->userName;
        $_SESSION["tz"]   = $this->userTimeZoneId;
        $kernel->delayMethodCall([ $this, "update" ], [ $_COOKIE[$this->name] ]);
      }
      // Session data was loaded from Memcached.
      elseif (!empty($_SESSION)) {
        // This is a regular user if we stored an ID along with this session in Memcached.
        if (!empty($_SESSION["id"])) {
          $this->authentication  = $_SESSION["auth"];
          $this->userId          = $_SESSION["id"];
          $this->userName        = $_SESSION["name"];
          $this->userTimeZoneId  = $_SESSION["tz"];
          $this->isAuthenticated = true;

          // Regenerate the session ID at least every 20 minutes (OWASP recommendation).
          if ($this->authentication + 1200 < $_SERVER["REQUEST_TIME"]) {
            $this->regenerate();
          }
        }
        // This is a anonymous user if we didn't store an ID along with this session in Memcached.
        else {
          $this->userName       = $kernel->remoteAddress;
          $this->userTimeZoneId = ini_get("date.timezone");
        }
      }
      // If we have no data for this session ID directly destroy it.
      else {
        $this->destroy();
      }
    }
    // Export dynamic values to class scope.
    else {
      $this->userName       = $kernel->remoteAddress;
      $this->userTimeZoneId = ini_get("date.timezone");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Authenticate a user.
   *
   * @global \MovLib\Data\Database $db
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
    global $db, $kernel;

    // Load necessary user data from storage (if we have any).
    if (!($result = $db->query("SELECT `id`, `name`, `password`, `time_zone_identifier` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ])->get_result()->fetch_assoc())) {
      throw new SessionException("Couldn't find user with email '{$email}'.");
    }

    // Validate the submitted password.
    if (password_verify($rawPassword, $result["password"]) === false) {
      throw new SessionException("Invalid password for user with email '{$email}'.");
    }

    // Maybe the user was doing some work as anonymous user and already has a session active. If so generate new session
    // ID and if not generate a completely new session.
    session_status() === PHP_SESSION_ACTIVE ? $this->regenerate() : $this->start();
    $_SESSION["auth"]     = $this->authentication = $_SERVER["REQUEST_TIME"];
    $_SESSION["id"]       = $this->userId         = $result["id"];
    $_SESSION["name"]     = $this->userName       = $result["name"];
    $_SESSION["tz"]       = $this->userTimeZoneId = $result["time_zone_identifier"];
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
   * @global \MovLib\Data\Database $db
   * @param string|array $sessionId [optional]
   *   The unique session ID(s) that should be deleted. If no ID is passed along the current session ID of this instance
   *   will be used. If a numeric array is passed all values are treated as session IDs and deleted.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete($sessionId = null) {
    global $db;

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
        $db->query("DELETE FROM `sessions` WHERE `session_id` IN ({$clause})", str_repeat("s", $c), $sessionId);
        for ($i = 0; $i < $c; ++$i) {
          $sessionId[$i] = "{$sessionPrefix}{$sessionId[$i]}";
        }
        $memcached->deleteMulti($sessionId);
      }
      else {
        $db->query("DELETE FROM `sessions` WHERE `session_id` = ?", "s", [ $sessionId ]);
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

    // The user is no longer authenticated, keep this outside of the if for PHPUnit tests.
    $this->active          = false;
    $this->authentication  = 0;
    $this->isAuthenticated = false;
    $this->userId          = 0;
    $this->userName        = $kernel->remoteAddress;
    $this->userTimeZoneId  = ini_get("date.timezone");

    // Only execute the following if this request was made through nginx.
    if (isset($_SERVER["FCGI_ROLE"])) {
      // Remove all data associated with this session.
      if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
        session_write_close();
      }
      // Remove the cookie.
      $cookie = session_get_cookie_params();
      setcookie($this->name, "", 1, $cookie["path"], $cookie["domain"], $cookie["secure"], $cookie["httponly"]);
      // Remove the session ID from our database.
      $kernel->delayMethodCall([ $this, "delete" ], [ $this->id ]);
    }

    return $this;
  }

  /**
   * Retrieve a list of all active sessions.
   *
   * @global \MovLib\Data\Database $db
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
    global $db;
    return $db->query(
      "SELECT `session_id`, UNIX_TIMESTAMP(`authentication`) AS `authentication`, `ip_address`, `user_agent` FROM `sessions` WHERE `user_id` = ?",
      "d",
      [ $this->userId ]
    )->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Get the user's rating for the given movie ID.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $movieId
   *   The unique movie's identifier.
   * @return null|integer
   *   The user's rating if available, otherwise <code>NULL</code>.
   */
  public function getMovieRating($movieId) {
    global $db;
    $result = $db->query("SELECT `rating` FROM `ratings` WHERE `user_id` = ? AND `movie_id` = ? LIMIT 1", "dd", [ $this->userId, $movieId ])->get_result()->fetch_row();
    if (isset($result[0])) {
      return $result[0];
    }
  }

  /**
   * Insert newly created session into persistent session storage.
   *
   * @delayed
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insert() {
    global $db, $kernel;
    $db->query(
      "INSERT INTO `sessions` (`session_id`, `user_id`, `user_agent`, `ip_address`, `authentication`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
      "sdssi",
      [ $this->id, $this->userId, $kernel->userAgent, inet_pton($kernel->remoteAddress), $this->authentication ]
    );
    return $this;
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
  protected function regenerate() {
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

    // Absolutely no session data is present (default state).
    if (empty($_SESSION)) {
      // Destroy this session if one is active without any data associated to it.
      if (session_status() === PHP_SESSION_ACTIVE) {
        $this->destroy();
      }
    }
    // We have session data and we have an active session.
    elseif (session_status() === PHP_SESSION_ACTIVE) {
      // If this session belongs to an authenticated user, update the last access time.
      if ($this->isAuthenticated === true) {
        $kernel->delayMethodCall([ $this, "updateUserAccess" ]);
      }

      // Commit session to memcached and release session lock.
      session_write_close();
    }
    // We have session data but no active session, this means that we have to start a new session for an anonymous user.
    else {
      session_set_cookie_params(0);
      $this->start();
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
  protected function start() {
    // Create backup of existing session data (if any).
    $sessionData = isset($_SESSION) ? $_SESSION : null;

    // Start new session (if exeution was started by nginx).
    if (isset($_SERVER["FCGI_ROLE"]) && ($this->active = session_start()) === false) {
      throw new \MemcachedException("Could not start session (may be Memcached is down?).");
    }

    $this->id = session_id();

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
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Kernel $kernel
   * @param string $oldSessionId
   *   The old session ID that should be updated.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function update($oldSessionId) {
    global $db, $kernel;
    $db->query(
      "UPDATE `sessions` SET `session_id` = ?, `ip_address` = ?, `user_agent` = ? WHERE `session_id` = ? AND `user_id` = ?",
      "ssssd",
      [ $this->id, inet_pton($kernel->remoteAddress), $kernel->userAgent, $oldSessionId, $this->userId ]
    );
    return $this;
  }

  /**
   * Update the user's access time.
   *
   * @global \MovLib\Data\Database $db
   * @return this
   */
  public function updateUserAccess() {
    global $db;
    $db->query("UPDATE `users` SET `access` = CURRENT_TIMESTAMP WHERE `id` = ?", "d", [ $this->userId ]);
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($_SESSION[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function &offsetGet($offset) {
    return $_SESSION[$offset];
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $_SESSION[$offset] = $value;
    $this->active = true;
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($_SESSION[$offset]);
    if (empty($_SESSION)) {
      $this->destroy();
    }
  }

}
