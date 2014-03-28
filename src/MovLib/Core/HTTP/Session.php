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
namespace MovLib\Core\HTTP;

use \MovLib\Data\User;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\UnauthorizedException;

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
final class Session extends \MovLib\Core\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Session array key for the authentication (sing in) time of the session.
   *
   * @var integer
   */
  const AUTHENTICATION = 0;

  /**
   * Session array key for the time the session identifier was last regenerated.
   *
   * @var integer
   */
  const INIT_TIME = 1;

  /**
   * Time after which a user cannot access any protected pages (e.g. change password).
   *
   * @var integer
   */
  const PROTECTION_GRACE_TIME = 3600; // 3600 seconds = 60 minutes

  /**
   * Time after which a session's identifier has to be regenerated.
   *
   * @var integer
   */
  const REGENERATION_GRACE_TIME = 1200; // 1200 seconds = 20 minutes

  /**
   * Session array key for the user's avatar.
   *
   * @var integer
   */
  const USER_AVATAR = 2;

  /**
   * Session array key for the user's identifier.
   *
   * @var integer
   */
  const USER_ID = 3;

  /**
   * Session array key for the user's name.
   *
   * @var integer
   */
  const USER_NAME = 4;

  /**
   * Session array key for the user's time zone.
   *
   * @var integer
   */
  const USER_TIME_ZONE = 5;



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
   * The session's data.
   *
   * @var array
   */
  public $data = [];

  /**
   * The session's ID.
   *
   * @var string
   */
  public $id;

  /**
   * The time this session's identifier was last regenerated.
   *
   * @var integer
   */
  protected $initTime;

  /**
   * The user's authentication status.
   *
   * @var boolean
   */
  public $isAuthenticated = false;

  /**
   * Active log instance.
   *
   * @var \MovLib\Core\Log
   */
  protected $log;

  /**
   * The session's name.
   *
   * @var string
   */
  protected $name = "MOVSID";

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
   * @var string
   */
  public $userTimeZone;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTTP session object.
   *
   * @param \MovLib\Core\Log $log
   *   Active log instance.
   * @param \MovLib\Core\HTTP\Response $response
   *   Active response instance.
   * @param string $remoteAddress
   *   The client's remote address.
   * @param string $timeZone
   *   The default time zone.
   */
  public function __construct(\MovLib\Core\Log $log, \MovLib\Core\HTTP\Response $response, $remoteAddress, $timeZone) {
    $this->data         =& $_SESSION;
    $this->log          = $log;
    $this->response     = $response;
    $this->userName     = $remoteAddress;
    $this->userTimeZone = $timeZone;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Authenticate a user.
   *
   * @param string $email
   *   The user submitted email address.
   * @param string $rawPassword
   *   The user submitted raw password.
   * @return boolean
   *   <code>TRUE</code> if the credentials could be verified, otherwise <code>FALSE</code>.
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function authenticate($email, $rawPassword) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "email", "rawPassword" ] as $param) {
      if (empty($email) || !is_string($email)) {
        throw new \InvalidArgumentException("\${$param} cannot be empty and must be of type string.");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      // Load necessary user data from storage (if we have any).
      $user = new User($this->diContainer);
      $user->init($rawPassword, $user);

      // Validate the submitted password.
      if ($user->verifyPassword($rawPassword) === false) {
        return false;
      }

      // Only create a session if we're serving this request via nginx.
      if ($kernel->fastCGI === true) {
        // Export to class scope.
        $this->authentication           = $this->initTime = $_SERVER["REQUEST_TIME"];
        $this->userAvatar               = $user->getStyle(User::STYLE_HEADER_USER_NAVIGATION);
        $this->userId                   = $user->id;
        $this->userName                 = $user->name;
        $this->userTimeZone           = $user->timeZoneIdentifier;

        // Export to session storage.
        $this->data[self::AUTHENTICATION] =& $this->authentication;
        $this->data[self::INIT_TIME]      =& $this->initTime;
        $this->data[self::USER_AVATAR]    =& $this->userAvatar;
        $this->data[self::USER_ID]        =& $this->userId;
        $this->data[self::USER_NAME]      =& $this->userName;
        $this->data[self::USER_TIME_ZONE] =& $this->userTimeZone;

        // Maybe the user was doing some work as anonymous user and already has a session active. If so generate new session
        // ID and if not generate a completely new session.
        if (session_status() === PHP_SESSION_ACTIVE) {
          $this->regenerate();
        }
        else {
          $this->start();
          $kernel->delayMethodCall([ $this, "insert" ]);
        }

        // Set cookie for preferred system language for nginx. We set the cookie expire time to January 2038. This is the
        // maximum value that is possible.
        $kernel->cookieCreate("lang", $user->systemLanguageCode, 2147483647);
      }

      // @todo Is this unnecessary overhead or a good protection? If PHP updates the default password this would be the
      //       only way to update the password's of all users. We execute it delayed, so there's only the server load we
      //       have to worry about. Maybe introduce a configuration option for this?
      $kernel->delayMethodCall([ $this, "passwordNeedsRehash" ], [ $user->password, $rawPassword ]);

      return true;
    }
    catch (\Exception $e) {
      return false;
    }
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
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 403.
   * @return this
   * @throws \MovLib\Presentation\Error\Forbidden
   */
  public function checkAuthorizationAdmin($message) {
    if ($this->isAdmin() === false) {
      throw new Forbidden($message);
    }
    return $this;
  }

  /**
   * Check if the user is authenticated and if she or he authenticated recently.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function checkAuthorizationTimestamp($message) {
    if ($this->isAuthenticated === false || ($this->authentication + self::PROTECTION_GRACE_TIME) < $_SERVER["REQUEST_TIME"]) {
      throw new Unauthorized($message);
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
   */
  public function delete($sessionId = null) {
    try {
      if (!isset($sessionId)) {
        $result = $db
          ->query("SELECT `id` FROM `sessions` WHERE `user_id` = ?", "d", [ $this->userId ])
          ->get_result()
          ->fetch_all()
        ;
        if (!isset($result[0])) {
          return $this;
        }
        $sessionId = $result[0];
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
    }
    catch (\Exception $e) {
      Log::critical($e);
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
   * @param boolean $deleteAllSessions [optional]
   *   <code>TRUE</code> to delete all sessions. Defaults to <code>FALSE</code>
   * @return this
   */
  public function destroy($deleteAllSessions = false) {
    // Only execute the following if this request was made through nginx.
    if ($kernel->fastCGI === true) {
      // Remove all data associated with this session.
      if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
        session_write_close();
      }

      // Remove the session and language cookie.
      $kernel->cookieDelete([ $this->name, "lang" ]);

      // Delete all sessions if the flag is set.
      if ($deleteAllSessions === true) {
        // @devStart
        // @codeCoverageIgnoreStart
        Log::debug("Deleting all Sessions");
        // @codeCoverageIgnoreEnd
        // @devEnd
        $this->delete();
      }
      // Otherwise only delete the current session.
      else {
        // @devStart
        // @codeCoverageIgnoreStart
        Log::debug("Deleting Session", [ "id" => $this->id ]);
        // @codeCoverageIgnoreEnd
        // @devEnd
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
    $this->userTimeZone    = date_default_timezone_get();

    return $this;
  }

  /**
   * Retrieve a list of all active sessions.
   *
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
   * @return this
   */
  public function insert() {
    try {
      $db->query(
        "INSERT INTO `sessions` (`id`, `user_id`, `user_agent`, `ip_address`, `authentication`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
        "sdssi",
        [ $this->id, $this->userId, $kernel->userAgent, inet_pton($kernel->remoteAddress), $this->authentication ]
      );
    }
    catch (DatabaseException $e) {
      Log::critical($e);
    }
    return $this;
  }

  /**
   * Check if the currently authenticated user is an administrator.
   *
   * @staticvar boolean $isAdmin
   *   Used to cache the result.
   * @return boolean
   *   <code>TRUE</code> if the current user is an administrator, otherwise <code>FALSE</code>.
   */
  public function isAdmin() {
    static $isAdmin = null;
    if ($this->isAuthenticated === true) {
      if (!$isAdmin) {
        try {
          $result = $db
            ->query("SELECT `admin` FROM `users` WHERE `id` = ? LIMIT 1", "d", [ $this->userId ])
            ->get_result()
            ->fetch_row()
          ;
          $isAdmin = (!empty($result[0]) && $result[0] === 1);
        }
        catch (DatabaseException $e) {
          Log::critical($e);
          $isAdmin = false;
        }
      }
      return $isAdmin;
    }
    return false;
  }

  /**
   * Test after every authentication if the password needs to be rehashed.
   *
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
    if (password_needs_rehash($password, PASSWORD_DEFAULT, $kernel->passwordOptions) === true) {
      try {
        $user     = new FullUser(FullUser::FROM_ID, $this->userId);
        $password = $user->hashPassword($rawPassword);
        $user->updatePassword($password);
      }
      catch (\Exception $e) {
        Log::critical($e);
      }
    }
    return $this;
  }

  /**
   * Regenerate session ID and update persistent storage.
   *
   * @return this
   */
  protected function regenerate() {
    // @devStart
    // @codeCoverageIgnoreStart
    Log::debug("Regenerating Session ID");
    // @codeCoverageIgnoreEnd
    // @devEnd
    if ($kernel->fastCGI === true) {
      if (session_regenerate_id(true) === true) {
        if ($this->userId > 0) {
          $kernel->delayMethodCall([ $this, "update" ], [ $this->id ]);
        }
        $this->id       = session_id();
        $this->initTime = $_SERVER["REQUEST_TIME"];
      }
      else {
        Log::critical("Couldn't regenerate session identifier", [ "session" => $this ]);
        $this->destroy();
      }
    }
    return $this;
  }

  /**
   * Resume existing HTTP session.
   *
   * @return this
   * @throws \MemcachedException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function resume() {
    // Add all alerts that are stored in a cookie to the current presentation. The page is automatically not cacheable
    // anymore because we're displaying alert messages, we also remove the cookie directly after displaying the alerts
    // to ensure that subsequent requests can be cached.
//    if (isset($_COOKIE["alerts"])) {
//      $cache->cacheable = false;
//      $this->alerts    .= $_COOKIE["alerts"];
//      $this->kernel->cookieDelete("alerts");
//    }

    // Only attempt to load the session if a non-empty session ID is present. Anonymous user's don't get any session to
    // ensure that HTTP proxies are able to cache anonymous pageviews.
    if (!empty($request->cookies[$this->name])) {
      // Try to resume the session with the ID from the cookie.
      $this->start();

      // Try to load the session from the persistent session storage for known users if we just generated a new
      // session ID and have no data stored for it.
      if (empty($this->data)) {
        // Load session data from persistent session storage.
        /* @var $stmt \mysqli_stmt */
        $stmt = $db->query(
          "SELECT UNIX_TIMESTAMP(`authentication`), `user_id` FROM `sessions` WHERE `id` = ? LIMIT 1",
          "s",
          [ $request->cookies[$this->name] ]
        );
        $stmt->bind_result($this->authentication, $this->userId);

        // We couldn't find a valid session and we have no data, invalid session.
        if ($stmt->fetch()) {
          try {
            // Try to load the user and directly regenerate the session's identifier.
            $user = new User(User::FROM_ID, $this->userId);
            $this->regenerate();

            // Export database result to class scope.
            $this->userAvatar   = $user->getStyle(User::STYLE_HEADER_USER_NAVIGATION);
            $this->userName     = $user->name;
            $this->userTimeZone = $user->timeZoneIdentifier;

            // Export properties to session scope.
            $this->data[self::AUTHENTICATION] =& $this->authentication;
            $this->data[self::INIT_TIME]      =& $this->initTime;
            $this->data[self::USER_ID]        =& $this->userId;
            $this->data[self::USER_AVATAR]    =& $this->userAvatar;
            $this->data[self::USER_NAME]      =& $this->userName;
            $this->data[self::USER_TIME_ZONE] =& $this->userTimeZone;

            $this->isAuthenticated = true;
            // @devStart
            // @codeCoverageIgnoreStart
            Log::debug("Loaded Session from Database");
            // @codeCoverageIgnoreEnd
            // @devEnd
          }
          // Well, this is akward, we have a valid session but no valid user, destroy session and log this error.
          catch (\Exception $e) {
            Log::error(
              new \RuntimeException("Non-existent user ID from persistent session storage", null, $e),
              [ "remote address" => $request->remoteAddress, "session ID" => $request->cookies[$this->name] ]
            );
            $this->destroy();
          }
        }
        else {
          // @devStart
          // @codeCoverageIgnoreStart
          Log::debug("Couldn't Restore Session from Database");
          // @codeCoverageIgnoreEnd
          // @devEnd
          $this->destroy();
        }
        $stmt->close();
      }
      // Session data was loaded from Memcached.
      elseif (!empty($this->data[self::USER_ID])) {
        $this->authentication  =& $this->data[self::AUTHENTICATION];
        $this->initTime        =& $this->data[self::INIT_TIME];
        $this->userAvatar      =& $this->data[self::USER_AVATAR];
        $this->userId          =& $this->data[self::USER_ID];
        $this->userName        =& $this->data[self::USER_NAME];
        $this->userTimeZone    =& $this->data[self::USER_TIME_ZONE];
        $this->isAuthenticated = true;
        // @devStart
        // @codeCoverageIgnoreStart
        Log::debug("Loaded Session from Memcached");
        // @codeCoverageIgnoreEnd
        // @devEnd
        if (($this->initTime + self::REGENERATION_GRACE_TIME) < $request->time) {
          $this->regenerate();
        }
      }
    }

    return $this;
  }

  /**
   * Shutdown the currently active session and start one for anonymous users if we have to.
   *
   * @return this
   * @throws \MemcachedException
   * @throws \DomainException
   */
  public function shutdown() {
    // Absolutely no session data is present (default state).
    if (empty($this->data)) {
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
    // Only attempt to start a new session if we're operating via php-fpm.
    if ($kernel->fastCGI === true) {
      // Create backup of existing session data (if any).
      $sessionData = isset($this->data) ? $this->data : null;

      // Start new session (if exeution was started by nginx).
      if (($this->active = session_start()) === false) {
        $e = new \MemcachedException("Couldn't start session (may be Memcached is down?)");
        Log::critical($e);
        throw $e;
      }

      $this->id = session_id();

      // @devStart
      // @codeCoverageIgnoreStart
      Log::debug("Started Session");
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Restore session data.
      if ($sessionData) {
        $this->data += $sessionData;
      }
    }
    // @devStart
    // @codeCoverageIgnoreStart
    else {
      Log::debug("Not starting new session, not in cookie supporting environment");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this;
  }

  /**
   * Update the ID of a session in our persistent session store.
   *
   * @delayed
   * @param string $oldId
   *   The old session ID that should be updated.
   * @return this
   */
  public function update($oldId) {
    try {
      $db->query(
        "UPDATE `sessions` SET `id` = ?, `ip_address` = ?, `user_agent` = ? WHERE `id` = ? AND `user_id` = ?",
        "ssssd",
        [ $this->id, inet_pton($kernel->remoteAddress), $kernel->userAgent, $oldId, $this->userId ]
      );
    }
    catch (DatabaseException $e) {
      Log::critical($e);
    }
    return $this;
  }

  /**
   * Update the user's access time.
   *
   * @return this
   */
  public function updateUserAccess() {
    try {
      $db->query("UPDATE `users` SET `access` = CURRENT_TIMESTAMP WHERE `id` = ?", "d", [ $this->userId ]);
    }
    catch (DatabaseException $e) {
      Log::critical($e);
    }
    return $this;
  }

}
