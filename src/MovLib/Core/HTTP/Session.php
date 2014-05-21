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

use \MovLib\Data\DateTime;
use \MovLib\Data\Image\ImageStyle;
use \MovLib\Data\Image\ImageStylePlaceholder;
use \MovLib\Exception\ClientException\ForbiddenException;
use \MovLib\Exception\ClientException\UnauthorizedException;

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
final class Session extends \MovLib\Core\AbstractDatabase {


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
   * Session array key for the user's identifier.
   *
   * @var integer
   */
  const USER_ID = 2;

  /**
   * Session array key for the user's MD5 image cache buster string.
   *
   * @var integer
   */
  const USER_IMAGE_CACHE_BUSTER = 3;

  /**
   * Session array key for the user's imag extension.
   *
   * @var integer
   */
  const USER_IMAGE_EXTENSION = 4;

  /**
   * Session array key for the user's name.
   *
   * @var integer
   */
  const USER_NAME = 5;

  /**
   * Session array key for the user's time zone.
   *
   * @var integer
   */
  const USER_TIMEZONE = 6;



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
  public $authentication;

  /**
   * The session's ID.
   *
   * @var string
   */
  public $ssid;

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
   * The active request instance.
   *
   * @var \MovLib\Core\HTTP\Request
   */
  protected $request;

  /**
   * The active response instance.
   *
   * @var \MovLib\Core\HTTP\Response
   */
  protected $response;

  /**
   * The session user's unique identifier.
   *
   * @var integer
   */
  public $userId = 0;

  /**
   * The session user's image cachge buster.
   *
   * @var null|string
   */
  public $userImageCacheBuster;

  /**
   * The session user's image extension.
   *
   * @var null|string
   */
  public $userImageExtension;

  /**
   * The session user's unique name.
   *
   * @var string
   */
  public $userName;

  /**
   * The session user's timezone.
   *
   * @var \DateTimeZone
   */
  public $userTimezone;

  /**
   * The session user's timezone identifier.
   *
   * @var string
   */
  public $userTimezoneId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    parent::__construct($diContainerHTTP);
  }
  // @codeCoverageIgnoreEnd
  // @devEnd


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
    $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT `id`, HEX(`image_cache_buster`), `image_extension`, `language_code`, `name`, `password`, `timezone` FROM `users` WHERE `email` = ? LIMIT 1
SQL
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($this->userId, $this->userImageCacheBuster, $this->userImageExtension, $languageCode, $this->userName, $passwordHash, $this->userTimezoneId);
    $found = $stmt->fetch();
    if (!$found) {
      return false;
    }

    if (password_verify($rawPassword, $passwordHash) === false) {
      return false;
    }

    $this->authentication = $this->initTime = $this->request->time;
    $_SESSION[self::AUTHENTICATION]          =& $this->authentication;
    $_SESSION[self::INIT_TIME]               =& $this->initTime;
    $_SESSION[self::USER_ID]                 =& $this->userId;
    $_SESSION[self::USER_IMAGE_CACHE_BUSTER] =& $this->userImageCacheBuster;
    $_SESSION[self::USER_IMAGE_EXTENSION]    =& $this->userImageExtension;
    $_SESSION[self::USER_NAME]               =& $this->userName;
    $_SESSION[self::USER_TIMEZONE]           =& $this->userTimezoneId;

    // Maybe the user was doing some work as anonymous user and already has a session active. If so generate new session
    // identifier and if not generate a completely new session.
    if (session_status() === PHP_SESSION_ACTIVE) {
      $this->regenerate();
    }
    else {
      $this->start();
      $this->kernel->delayMethodCall([ $this, "insert" ]);
    }

    // Set cookie for preferred system language for nginx. We set the cookie expire time to January 2038. This is the
    // maximum value that is possible.
    $this->response->createCookie("lang", $languageCode, 2147483647);

    // @todo Is this unnecessary overhead or a good protection? If PHP updates the default password this would be the
    //       only way to update the password's of all users. We execute it delayed, so there's only the server load we
    //       have to worry about. Maybe introduce a configuration option for this?
    $this->kernel->delayMethodCall([ $this, "rehashPassword" ], [ $this->userId, $rawPassword, $passwordHash ]);

    return true;
  }

  /**
   * Check if the user is authenticated and if not throw an unauthorized exception.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Exception\ClientException\UnauthorizedException
   */
  public function checkAuthorization($message) {
    if ($this->isAuthenticated === false) {
      throw new UnauthorizedException($message);
    }
    return $this;
  }

  /**
   * Check whether the user is an admin and if not throw a forbidden exception.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 403.
   * @return this
   * @throws \MovLib\Exception\ClientException\ForbiddenException
   */
  public function checkAuthorizationAdmin($message) {
    if ($this->isAdmin() === false) {
      throw new ForbiddenException($message);
    }
    return $this;
  }

  /**
   * Check if the client's authentication is within the grace time.
   *
   * @param string $message
   *   The already translated message that should be passed to the exception as reason for the 401.
   * @return this
   * @throws \MovLib\Exception\ClientException\UnauthorizedException
   */
  public function checkAuthorizationTime($message) {
    if ($this->isAuthenticated === false || ($this->authentication + self::PROTECTION_GRACE_TIME) < $this->request->time) {
      throw new UnauthorizedException($message);
    }
    return $this;
  }

  /**
   * Deletes this session from our session database.
   *
   * @delayed
   * @param array $ssids [optional]
   *   The unique session ID(s) that should be deleted. If no ID is passed along the current session ID of this instance
   *   will be used. If a numeric array is passed all values are treated as session IDs and deleted.
   * @return this
   * @throws \mysqli_sql_exception
   * @throws \MemcachedException
   */
  public function delete(array $ssids = null) {
    $mysqli = $this->getMySQLi();

    // Delete all session identifiers, including the current one, if none was passed.
    if (!$ssids) {
      $ssids = [ $this->ssid ];
      $result = $mysqli->query("SELECT `ssid` FROM `sessions` WHERE `user_id` = {$this->userId}");
      while ($row = $result->fetch_row()) {
        $ssids[] = $row[0];
      }
      $result->free();
      if (empty($ssids)) {
        return $this;
      }
    }

    // Fetch all configured Memcached servers from the PHP configuration and split them by the delimiter.
    $sessionPrefix = ini_get("memcached.sess_prefix");
    $servers       = explode(",", ini_get("session.save_path"));

    // Build the array as expected by Memcached::addServers().
    $c = count($servers);
    for ($i = 0; $i < $c; ++$i) {
      $servers[$i] = explode(":", $servers[$i]);
      // The port is mandatory!
      if (empty($servers[$i][1])) {
        $servers[$i][1] = 0;
      }
    }

    $memcached = new \Memcached();
    $memcached->addServers($servers);
    $stmt = $mysqli->prepare("DELETE FROM `sessions` WHERE `ssid` = ?");
    $c = count($ssids);
    for ($i = 0; $i < $c; ++$i) {
      $stmt->bind_param("s", $ssids[$i]);
      $stmt->execute();
      $ssids[$i] = "{$sessionPrefix}{$ssids[$i]}";
    }
    $stmt->close();
    $memcached->deleteMulti($ssids);

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
    // Remove all data associated with this session.
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_unset();
      session_destroy();
      session_write_close();
    }

    // Remove the session and language cookie.
    $this->response->deleteCookie([ $this->config->sessionName, "lang" ]);

    // Delete all sessions if the flag is set.
    if ($deleteAllSessions) {
      $this->delete();
    }
    // Otherwise only delete the current session.
    else {
      $this->delete([ $this->ssid ]);
    }

    // The user is no longer authenticated.
    $this->active               = false;
    $this->authentication       = null;
    $this->isAuthenticated      = false;
    $this->userId               = 0;
    $this->userImageCacheBuster = null;
    $this->userImageExtension   = null;
    $this->userName             = $this->request->remoteAddress;
    $this->userTimezoneId       = date_default_timezone_get();

    return $this;
  }

  /**
   * Get all active sessions.
   *
   * @see \MovLib\Stub\Core\HTTP\ActiveSession
   * @return array
   *   Array containing all active sessions.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getActiveSessions() {
    $activeSessions = [];
    $result = $this->getMySQLi()->query("SELECT `authentication`, `ssid`, `remote_address` AS `remoteAddress`, `user_agent` AS `userAgent` FROM `sessions` WHERE `user_id` = {$this->userId}");
    /* @var $activeSession \MovLib\Stub\Core\HTTP\ActiveSessionSet */
    while ($activeSession = $result->fetch_object()) {
      $activeSession->authentication = new DateTime($activeSession->authentication);
      $activeSession->remoteAddress  = inet_ntop($activeSession->remoteAddress);
      $activeSessions[]              = $activeSession;
    }
    $result->free();
    return $activeSessions;
  }

  /**
   * Fast version of image style retrieval for the user's session image.
   *
   * <b>NOTE</b><br>
   * The session doesn't support any styles, there is only one style available!
   *
   * @see \MovLib\Data\User\User::imageGetEffects()
   * @return \MovLib\Data\Image\ImageStyle
   *   The session user image style.
   */
  public function imageGetStyle() {
    if ($this->userImageCacheBuster) {
      $filename        = mb_strtolower($this->userName);
      $imageStyle      = new ImageStyle("upload://user/{$filename}.{$this->userImageExtension}", 50, 50);
      $imageStyle->url = "//{$this->config->hostnameStatic}/uploads/user/{$filename}.nav.{$this->userImageExtension}?{$this->userImageCacheBuster}";
    }
    else {
      $imageStyle = new ImageStylePlaceholder(50, $this->fs->getExternalURL("asset://img/logo/vector.svg"));
    }
    $imageStyle->route = $this->intl->r("/profile");
    return $imageStyle;
  }

  /**
   * Insert newly created session into persistent session storage.
   *
   * @return this
   */
  public function insert() {
    if ($this->ssid && $this->userId) {
      $remoteAddress = inet_pton($this->request->remoteAddress);
      $stmt          = $this->getMySQLi()->prepare(<<<SQL
INSERT INTO `sessions` (`authentication`, `ssid`, `remote_address`, `user_id`, `user_agent`)
VALUES (FROM_UNIXTIME(?), ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
  `authentication` = VALUES(`authentication`),
  `remote_address` = VALUES(`remote_address`),
  `user_agent` = VALUES(`user_agent`)
SQL
      );
      $stmt->bind_param("issds", $this->authentication, $this->ssid, $remoteAddress, $this->userId, $this->request->userAgent);
      $stmt->execute();
      $stmt->close();
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
    if ($this->isAuthenticated) {
      if ($isAdmin === null) {
        $result  = $this->getMySQLi()->query("SELECT `admin` FROM `users` WHERE `id` = {$this->userId} LIMIT 1");
        $isAdmin = $result && $result->fetch_row()[0] == 1;
        $result->free();
      }
      return $isAdmin;
    }
    return false;
  }

  /**
   * Regenerate session identifier and update persistent storage.
   *
   * @param boolean $force [optional]
   *   Whether to force regeneration or not. By default a session identifier is only regenerated once per request.
   * @return this
   * @throws \mysqli_sql_exception
   * @throws \RuntimeException
   *   If regenerating the session identifiers fails.
   */
  protected function regenerate($force = false) {
    static $regenerated = false;

    // Only regenerate the session identifier if we haven't done so during this request or if a regeneration is forced.
    if ($force === true || $regenerated === false) {
      // Try to regenerate the session identifier and delete the old identifier from the Memcached storage.
      if (session_regenerate_id(true) === true) {
        // Get the newly generated session identifier.
        $ssid = session_id();

        // Update persistent storage with the new session identifier.
        if ($this->isAuthenticated === true) {
          $this->kernel->delayMethodCall([ $this, "updateSecureIdentifier" ], [ $this->userId, $this->ssid, $ssid ]);
        }

        // Export new values to class scope and set flag that we regenerated the session identifier.
        $this->ssid     = $ssid;
        $this->initTime = $this->request->time;
        $regenerated    = true;
      }
      // Might be Memcached is down if we weren't able to regenerate the session identifier.
      else {
        $this->destroy();
        throw new \RuntimeException("Couldn't regenerate session identifier");
      }
    }

    return $this;
  }

  /**
   * Check if the user's password needs to be rehashed and do so if applicable.
   *
   * @param integer $userId
   *   The unique identifier of the user the password belongs to.
   * @param string $rawPassword
   *   The raw password that should be checked.
   * @param string $passwordHash
   *   The hashed password.
   * @return this
   */
  public function rehashPassword($userId, $rawPassword, $passwordHash) {
    if (password_needs_rehash($passwordHash, $this->config->passwordAlgorithm, $this->config->passwordOptions)) {
      $passwordHash = password_hash($rawPassword, $this->config->passwordAlgorithm, $this->config->passwordOptions);
      $stmt = $this->getMySQLi()->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");
      $stmt->bind_param("sd", $passwordHash, $userId);
      $stmt->execute();
      $stmt->close();
    }
    return $this;
  }

  /**
   * Resume existing HTTP session from client submitted cookies.
   *
   * @return this
   * @throws \MemcachedException
   * @throws \mysqli_sql_exception
   */
  public function resume() {
    // Only attempt to load the session if a non-empty session ID is present. Anonymous user's don't get any session to
    // ensure that HTTP proxies are able to cache anonymous pageviews.
    if (empty($this->request->cookies[$this->config->sessionName])) {
      $this->userName       = $this->request->remoteAddress;
      $this->userTimezoneId = date_default_timezone_get();
      $this->userTimezone   = new \DateTimeZone($this->userTimezoneId);
    }
    else {
      // Try to resume the session with the ID from the cookie.
      $this->start();

      // Try to load the session from the persistent session storage for known users if we just generated a new
      // session ID and have no data stored for it.
      if (empty($_SESSION)) {
        $mysqli = $this->getMySQLi();
        $stmt = $mysqli->prepare(<<<SQL
SELECT
  `users`.`id`,
  HEX(`users`.`image_cache_buster`),
  `users`.`image_extension`,
  `users`.`name`,
  `users`.`timezone`,
  `sessions`.`authentication`
FROM `sessions`
  INNER JOIN `users` ON `users`.`id` = `sessions`.`user_id`
WHERE `sessions`.`ssid` = ? LIMIT 1
SQL
        );
        $stmt->bind_param("s", $this->request->cookies[$this->config->sessionName]);
        $stmt->execute();
        $stmt->bind_result($this->userId, $this->userImageCacheBuster, $this->userImageExtension, $this->userName, $this->userTimezoneId, $this->authentication);
        $found = $stmt->fetch();
        $stmt->close();

        // We couldn't find a valid session and we have no data = invalid session.
        if ($found) {
          $this->regenerate();
          $_SESSION[self::AUTHENTICATION]          =& $this->authentication;
          $_SESSION[self::INIT_TIME]               =& $this->initTime;
          $_SESSION[self::USER_ID]                 =& $this->userId;
          $_SESSION[self::USER_IMAGE_CACHE_BUSTER] =& $this->userImageCacheBuster;
          $_SESSION[self::USER_IMAGE_EXTENSION]    =& $this->userImageExtension;
          $_SESSION[self::USER_NAME]               =& $this->userName;
          $_SESSION[self::USER_TIMEZONE]           =& $this->userTimezoneId;
          $this->userTimezone                      = new \DateTimeZone($this->userTimezoneId);
          $this->isAuthenticated = true;
        }
        else {
          $this->destroy();
        }
      }
      // Session data was loaded from Memcached.
      elseif (isset($_SESSION[self::USER_ID])) {
        $this->authentication       = $_SESSION[self::AUTHENTICATION];
        $this->initTime             = $_SESSION[self::INIT_TIME];
        $this->userId               = $_SESSION[self::USER_ID];
        $this->userImageCacheBuster = $_SESSION[self::USER_IMAGE_CACHE_BUSTER];
        $this->userImageExtension   = $_SESSION[self::USER_IMAGE_EXTENSION];
        $this->userName             = $_SESSION[self::USER_NAME];
        $this->userTimezoneId       = $_SESSION[self::USER_TIMEZONE];
        $this->userTimezone         = new \DateTimeZone($this->userTimezoneId);
        $this->isAuthenticated      = true;

        // Regenerate the sesson's identifier if the grace time is over.
        if (($this->initTime + self::REGENERATION_GRACE_TIME) < $this->request->time) {
          $this->regenerate();
        }
        // Otherwise make sure that the persistent storage contains this session.
        else {
          $this->kernel->delayMethodCall([ $this, "insert" ]);
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
   */
  public function shutdown() {
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
        $this->kernel->delayMethodCall([ $this, "updateUserAccess" ]);
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
    // Save current session data, because PHP will destroy it.
    $data = empty($_SESSION) ? null : $_SESSION;

    // Start new session (if exeution was started by nginx).
    if (($this->active = session_start()) === false) {
      throw new \MemcachedException("Couldn't start session (may be Memcached is down?)");
    }

    $this->ssid = session_id();

    // Restore session data.
    if ($data) {
      $_SESSION += $data;
    }

    return $this;
  }

  /**
   * Get the value from the current sessions.
   *
   * @param string $key
   *   The session's unique key to get the value for.
   * @param mixed $default [optional]
   *   The default value to return if the key is missing from the current storage.
   * @param boolean $delete [optional]
   *   Whether to delete the key after retrieval or not.
   * @return mixed
   *   The value identified by key from the current session's storage.
   * @throws \InvalidArgumentException
   */
  public function storageGet($key, $default = null, $delete = false) {
    if (isset($_SESSION[$key])) {
      $default = $_SESSION[$key];
      if ($delete) {
        unset($_SESSION[$key]);
      }
    }
    return $default;
  }

  /**
   * Delete a key from the current session.
   *
   * @param string $key
   *   The session's unique key to delete.
   * @return this
   * @throws \InvalidArgumentException
   */
  public function storageDelete($key) {
    // We use integers to store the sessions' data in the session array, make sure nobody else is using numeric keys
    // because they might overwrite the existing session's data.
    // @devStart
    // @codeCoverageIgnoreStart
    if (is_numeric($key)) {
      throw new \InvalidArgumentException("A session's key cannot be numeric!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (isset($_SESSION[$key])) {
      unset($_SESSION[$key]);
    }
    return $this;
  }

  /**
   * Store a value in the current user's session.
   *
   * @param string $key
   *   The unique session's key to identify the record.
   * @param mixed $value
   *   The value to store under <var>$key</var>.
   * @return mixed
   *   The stored <var>$value</var>.
   * @throws \InvalidArgumentException
   */
  public function storageSave($key, $value) {
    // We use integers to store the sessions' data in the session array, make sure nobody else is using numeric keys
    // because they might overwrite the existing session's data.
    // @devStart
    // @codeCoverageIgnoreStart
    if (is_numeric($key)) {
      throw new \InvalidArgumentException("A session's key cannot be numeric!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $_SESSION[$key] = $value;
    return $value;
  }

  /**
   * Update the persistent storage's stored secure session identifier for given user.
   *
   * @param integer $userId
   *   The user's unique identifier the session belongs to.
   * @param string $old
   *   The old session identifier.
   * @param string $new
   *   The new session identifier.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function updateSecureIdentifier($userId, $old, $new) {
    $stmt = $this->getMySQLi()->prepare("UPDATE `sessions` SET `ssid` = ? WHERE `user_id` = ? AND `ssid` = ?");
    $stmt->bind_param("sds", $new, $userId, $old);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Update the user's access time.
   *
   * @return this
   */
  public function updateUserAccess() {
    $this->getMySQLi()->query("UPDATE `users` SET `access` = CURRENT_TIMESTAMP WHERE `id` = {$this->userId}");
    return $this;
  }

}
