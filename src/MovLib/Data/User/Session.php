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

use \MovLib\Data\User\Full as FullUser;
use \MovLib\Data\User\User;
use \MovLib\Presentation\Error\Forbidden;
use \MovLib\Presentation\Error\Unauthorized;

/**
 * The session model loads the basic user information, creates, updates and deletes sessions.
 *
 * The following attributes are always available:
 * <ul>
 *   <li><code>Session::$authentication</code> contains the timestamp of the time when this session was initialized</li>
 *   <li><code>Session::$isAuthenticated</code> is a flag indicating if this is a known user</li>
 *   <li><code>Session::$userId</code> is zero for anonymous users, otherwise it contains the user's unique ID</li>
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
   * The user's absolute avatar image URL for the header.
   *
   * @var string
   */
  public $userAvatar;

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

      // Try to load the session from the persistent session storage for known users if we just generated a new
      // session ID and have no data stored for it.
      if (empty($_SESSION)) {
        // Load session data from persistent session storage.
        $stmt = $db->query("SELECT UNIX_TIMESTAMP(`authentication`), `user_id` FROM `sessions` WHERE `id` = ? LIMIT 1", "s", [ $_COOKIE[$this->name ]]);
        $stmt->bind_result($this->authentication, $this->userId);

        // We couldn't find a valid session and we have no data, invalid session.
        if (!$stmt->fetch()) {
          $this->destroy();
        }
        else {
          $stmt->close();
          try {
            $user = new User(User::FROM_ID, $this->userId);

            // Everything looks good, valid session and valid user, export and update persistent storage.
            $_SESSION["auth"]      = $this->authentication;
            $_SESSION["id"]        = $this->userId;
            $_SESSION["avatar"]    = $this->userAvatar     = $user->getStyle(User::STYLE_HEADER_USER_NAVIGATION);
            $_SESSION["name"]      = $this->userName       = $user->name;
            $_SESSION["tz"]        = $this->userTimeZoneId = $user->timeZoneIdentifier;
            $this->isAuthenticated = true;

            $this->regenerate();
          }
          // Well, this is akward, we have a valid session but no valid user, destroy session and log this error.
          catch (\OutOfBoundsException $e) {
            $this->destroy();
            error_log("Non-existent user ID from persistent session storage, IP was: {$kernel->remoteAddress} (Session ID: {$_COOKIE[$this->name]})");
          }
        }
      }
      // Session data was loaded from Memcached.
      elseif (!empty($_SESSION["id"])) {
        $this->authentication  = $_SESSION["auth"];
        $this->userAvatar      = $_SESSION["avatar"];
        $this->userId          = $_SESSION["id"];
        $this->userName        = $_SESSION["name"];
        $this->userTimeZoneId  = $_SESSION["tz"];
        $this->isAuthenticated = true;

        // Regenerate the session ID at least every 20 minutes (OWASP recommendation).
        if ($this->authentication + 1200 < $_SERVER["REQUEST_TIME"]) {
          $this->regenerate();
        }
      }
    }

    // This is an anonymous user if we have no user name at this point.
    if (!$this->userName) {
      $this->userName       = $kernel->remoteAddress;
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
   * @throws \OutOfBoundsException
   * @throws \UnexpectedValue
   */
  public function authenticate($email, $rawPassword) {
    global $kernel;

    // Load necessary user data from storage (if we have any).
    $user = new FullUser(User::FROM_EMAIL, $email);

    // Validate the submitted password.
    if ($user->verifyPassword($rawPassword) === false) {
      throw new \UnexpectedValueException("Invalid password for user with email '{$email}'");
    }

    $_SESSION["auth"]   = $this->authentication = $_SERVER["REQUEST_TIME"];
    $_SESSION["avatar"] = $this->userAvatar     = $user->getStyle(User::STYLE_HEADER_USER_NAVIGATION);
    $_SESSION["id"]     = $this->userId         = $user->id;
    $_SESSION["name"]   = $this->userName       = $user->name;
    $_SESSION["tz"]     = $this->userTimeZoneId = $user->timeZoneIdentifier;

    // Maybe the user was doing some work as anonymous user and already has a session active. If so generate new session
    // ID and if not generate a completely new session.
    if (session_status() === PHP_SESSION_ACTIVE) {
      $this->regenerate();
    }
    else {
      $this->start();
      $kernel->delayMethodCall([ $this, "insert" ]);
    }

    // @todo Is this unnecessary overhead or a good protection? If PHP updates the default password this would be the
    //       only way to update the password's of all users. We execute it delayed, so there's only the server load we
    //       have to worry about. Maybe introduce a configuration option for this?
    $kernel->delayMethodCall([ $this, "passwordNeedsRehash" ], [ $user->password, $rawPassword ]);

    return $this;
  }

  /**
   * Check if the user is authenticated and if not throw an unauthorized exception.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function checkAuthorization($message) {
    if ($this->isAuthenticated === false) {
      throw new Unauthorized($message);
    }
    return $this;
  }

  /**
   * Check whether the user is an admin and if not throw a forbidden exception.
   *
   * @global \MovLib\Data\Database $db
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 403.
   * @return this
   * @throws \MovLib\Presentation\Error\Forbidden
   */
  public function checkAuthorizationAdmin($message) {
    global $db;
    if ($this->userId > 0) {
      $result = $db->query("SELECT `admin` FROM `users` WHERE `id` = ? LIMIT 1", "d", [ $this->userId ])->get_result()->fetch_row();
      if (!empty($result[0]) && (boolean) $result[0] === true) {
        return $this;
      }
    }
    throw new Forbidden($message);
  }

  /**
   * Check if the user is authenticated and if the she or he authenticated recently.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function checkAuthorizationTimestamp($message) {
    if ($this->isAuthenticated === false || $this->authentication + 3600 < $_SERVER["REQUEST_TIME"]) {
      throw new Unauthorized($message);
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
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete($sessionId = null) {
    global $db;

    if (!$sessionId) {
      $sessionId = $db->query("SELECT `id` FROM `sessions` WHERE `user_id` = ?", "d", [ $this->userId ])->get_result()->fetch_all();
      if (empty($sessionId)) {
        return $this;
      }
      $sessionId = array_column($sessionId, 0);
    }

    // Fetch all configured Memcached servers from the PHP configuration and split them by the delimiter.
    $sessionPrefix = ini_get("memcached.sess_prefix");
    $servers       = explode(",", ini_get("session.save_path"));

    // Build the array as expected by Memcached::addServers().
    $c = count($servers);
    for ($i = 0; $i < $c; ++$i) {
      $servers[$i] = explode(":", $servers[$i]);
      // The port is mandatory!
      if (!isset($servers[$i][1])) {
        $servers[$i][1] = 0;
      }
    }

    $memcached = new \Memcached();
    $memcached->addServers($servers);
    if (is_array($sessionId) && ($c = count($sessionId)) > 0) {
      $clause = rtrim(str_repeat("?,", $c), ",");
      $db->query("DELETE FROM `sessions` WHERE `id` IN ({$clause})", str_repeat("s", $c), $sessionId);
      for ($i = 0; $i < $c; ++$i) {
        $sessionId[$i] = "{$sessionPrefix}{$sessionId[$i]}";
      }
      $memcached->deleteMulti($sessionId);
    }
    else {
      $db->query("DELETE FROM `sessions` WHERE `id` = ?", "s", [ $sessionId ]);
      $memcached->delete("{$sessionPrefix}{$sessionId}");
    }

    return $this;
  }

  /**
   * Destroy this session and optionally all sessions.
   *
   * Removes this session (or all sessions) from our Memcached and persistent storage, additionally a delete cookie
   * is sent, requesting the user's user agent to delete this session cookie. As you know, this is something that is
   * up to the user, that's why it's important for us to delete this session (or all sessions) from all our storage devices.
   *
   * @global \MovLib\Kernel $kernel
   * @param boolean $deleteAllSessions [optional]
   *   <code>TRUE</code> to delete all sessions. Defaults to <code>FALSE</code>
   * @return this
   */
  public function destroy($deleteAllSessions = false) {
    global $kernel;

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

      // Delete all sessions if the flag is set.
      if ($deleteAllSessions === true) {
        $this->delete();
      }
      // Otherwise only delete the current session.
      else {
        $this->delete($this->id);
      }
    }

    // The user is no longer authenticated, keep this outside of the if for PHPUnit tests.
    $this->active          = false;
    $this->authentication  = 0;
    $this->isAuthenticated = false;
    $this->userAvatar      = null;
    $this->userId          = 0;
    $this->userName        = $kernel->remoteAddress;
    $this->userTimeZoneId  = ini_get("date.timezone");

    return $this;
  }

  /**
   * Retrieve a list of all active sessions.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   Mysqli result ontaining all sessions currently stored in the persistent session storage for the currently signed
   *   in user. Each entry in the result set contains the following names in the projection:
   *   <ul>
   *     <li><code>"id"</code> is the session's unique ID</li>
   *     <li><code>"authentication"</code> is the timestamp when this session was initially created</li>
   *     <li><code>"ip_address"</code> is the IP address stored during authentication or regeneration</li>
   *     <li><code>"user_agent"</code> is the user agent string submitted during authentication or regeneration</li>
   *   </ul>
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getActiveSessions() {
    global $db;
    return $db->query(
      "SELECT `id`, UNIX_TIMESTAMP(`authentication`) AS `authentication`, `ip_address`, `user_agent` FROM `sessions` WHERE `user_id` = ?",
      "d",
      [ $this->userId ]
    )->get_result();
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
      "INSERT INTO `sessions` (`id`, `user_id`, `user_agent`, `ip_address`, `authentication`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
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
   * @throws \DomainException
   */
  public function passwordNeedsRehash($password, $rawPassword) {
    global $kernel;
    if (password_needs_rehash($password, PASSWORD_DEFAULT, $kernel->passwordOptions) === true) {
      $user     = new FullUser(FullUser::FROM_ID, $this->userId);
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
      if ($this->userId > 0) {
        $kernel->delayMethodCall([ $this, "update" ], [ $this->id ]);
      }
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
   * @throws \DomainException
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
        $_SESSION["auth"]   = $this->authentication;
        $_SESSION["id"]     = $this->userId;
        $_SESSION["avatar"] = $this->userAvatar;
        $_SESSION["name"]   = $this->userName;
        $_SESSION["tz"]     = $this->userTimeZoneId;
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
    global $kernel;

    // Create backup of existing session data (if any).
    $sessionData = isset($_SESSION) ? $_SESSION : null;

    // Start new session (if exeution was started by nginx).
    if (isset($_SERVER["FCGI_ROLE"]) && ($this->active = session_start()) === false) {
      throw new \MemcachedException("Couldn't start session (may be Memcached is down?)");
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
   * @param string $oldId
   *   The old session ID that should be updated.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function update($oldId) {
    global $db, $kernel;
    $db->query(
      "UPDATE `sessions` SET `id` = ?, `ip_address` = ?, `user_agent` = ? WHERE `id` = ? AND `user_id` = ?",
      "ssssd",
      [ $this->id, inet_pton($kernel->remoteAddress), $kernel->userAgent, $oldId, $this->userId ]
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
