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
use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Exception\SessionException;

/**
 * The session model loads the basic user information, creates, updates and deletes sessions.
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
  private $id;

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
  public $userId;

  /**
   * The session's user name.
   *
   * @var string
   */
  public $userName;

  /**
   * Timestamp this session was first authenticated.
   *
   * @var int
   */
  public $signIn;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Resume existing session if any.
   *
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\SessionException
   */
  public function __construct() {
    // Export the session's name to class scope.
    $this->name = session_name();

    // Only attempt to load the session if a non-empty session ID is present. Anonymous user's don't get any session to
    // ensure that HTTP proxies are able to cache anonymous pageviews.
    if (!empty($_COOKIE[$this->name])) {
      // Try to resume the session with the ID from the cookie.
      if (session_start() === false) {
        throw new SessionException("Could not resume session.");
      }
      $this->id = session_id();

      // We have to try loading the session from our persistent session storage if the session IDs don't match.
      if ($_COOKIE[$this->name] !== $this->id) {
        $result = $this->select("SELECT `user_id`, UNIX_TIMESTAMP(`sign_in`) AS `sign_in` FROM `sessions` WHERE `session_id` = ? LIMIT 1", "s", $_COOKIE[$this->name]);

        // This is an old session that requires sign in and it's expired for anonymous users.
        if (empty($result[0])) {
          $this->destroy();
        }
        // Otherwise we have to initialize this new session with fresh data and update the record in our persistent
        // session storage.
        else {
          $this->init($result[0]["user_id"], $result[0]["sign_in"]);
          DelayedMethodCalls::stack($this, "update", [ $_COOKIE[$this->name] ]);
        }
      }
      // Maybe somebody is trying with a random session ID to get a session?
      elseif (!isset($_SESSION["user_id"])) {
        $this->destroy();
      }
      // If we have a user ID everything should be fine, export default session data to class scope.
      else {
        $this->csrfToken = $_SESSION["csrf_token"];
        $this->signIn    = $_SESSION["sign_in"];
        $this->userId    = $_SESSION["user_id"];
        $this->userName  = $_SESSION["user_name"];
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
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\SessionException
   */
  public function authenticate($email, $rawPassword) {
    // Load necessary user data from storage.
    $result = $this->select("SELECT `user_id`, `name`, `password` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ]);

    // We couldn't find a user for the given email address if above query's result is empty.
    if (empty($result[0])) {
      throw new SessionException("Could not find user with email {$email}", E_NOTICE);
    }

    // Validate the submitted password.
    if (password_verify($rawPassword, $result[0]["password"]) === false) {
      throw new SessionException("Invalid password for user with email {$email}", E_NOTICE);
    }

    // Start a new session for this user. Failing could mean that Memcached is down!
    if (session_start() === false) {
      throw new SessionException("Could not start session (may be Memcached is down?).");
    }

    // Initialize this new session and insert it into our persistent session storage.
    $this->init($result[0]["user_id"], time());
    DelayedMethodCalls::stack($this, "insert");

    return $this;
  }

  /**
   * Deletes this session from our session database.
   *
   * Must be public for delayed execution.
   *
   * @param string $sessionId [optional]
   *   The unique session ID that should be deleted. If no ID is passed along the current session ID of this instance
   *   will be used.
   * @return this
   */
  public function delete($sessionId = null) {
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
    $memcached = new Memcached();
    $memcached->addServers($servers);
    // Remove the session from our volatile storage.
    $memcached->delete(ini_get("memcached.sess_prefix") . $sessionId);
    // Remove the session from our persistent storage as well.
    return $this->query("DELETE FROM `sessions` WHERE `session_id` = ?", "s", [ $sessionId ]);
  }

  /**
   * Destroy this session.
   *
   * Removes this session ID from our database and logs the user out.
   *
   * @return this
   */
  public function destroy() {
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
    return $this;
  }

  /**
   * Retrieve a list of all active sessions.
   *
   * @return array
   *   Numeric array ontaining all sessions currently stored in the persistent session storage for the currently signed
   *   in user. Each entry in the numeric array is an associative array with the following entries:
   *   <ul>
   *     <li><code>"session_id"</code> is the entries session ID</li>
   *     <li><code>"user_agent"</code> is the browser submitted user agent</li>
   *     <li><code>"ip_address"</code> is the IP address stored during authentication or regeneration</li>
   *     <li><code>"sign_in"</code> is the timestamp when this session was initially created</li>
   *   </ul>
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getActiveSessions() {
    $sessions = $this->select(
      "SELECT `session_id`, `user_agent`, `ip_address`, UNIX_TIMESTAMP(`sign_in`) AS `sign_in` FROM `sessions` WHERE `user_id` = ? LIMIT 1",
      "d",
      [ $_SESSION["user_id"] ]
    );
    $c = count($sessions);
    for ($i = 0; $i < $c; ++$i) {
      // Transform each IP address into a humand readable form.
      $sessions[$i]["ip_address"] = inet_ntop($sessions[$i]["ip_address"]);
    }
    return $sessions;
  }

  /**
   * Initialize session with default data.
   *
   * @param int $userId [optional]
   *   The ID of the user for wish we should initialize a session. Zero is used if no value is passed, this will
   *   initialize the session for an anonymous user.
   * @param int $signIn [optional]
   *   The timestamp of the last time this user signed in. Zero is used if no value is passed, this will initialize the
   *   session for an anonymous user.
   * @return this
   * @throws \MovLib\Exception\SessionException
   */
  private function init($userId = 0, $signIn = 0) {
    $this->id               = session_id();
    $this->csrfToken        = $_SESSION["csrf_token"] = hash("sha512", openssl_random_pseudo_bytes(1024));
    $_SESSION["ip_address"] = filter_input(INPUT_SERVER, "REMOTE_ADDR", FILTER_SANITIZE_STRING);
    $_SESSION["user_agent"] = filter_input(INPUT_SERVER, "HTTP_USER_AGENT", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

    // We are initializing this session for a registered user.
    if ($userId > 0) {
      $result = $this->select("SELECT `name` FROM `users` WHERE `user_id` = ? LIMIT 1", "d", [ $userId ]);
      if (empty($result[0]["name"])) {
        throw new SessionException("Could not fetch user name for user ID {$userId}.");
      }
      $this->userId          = $_SESSION["user_id"]   = $userId;
      $this->userName        = $_SESSION["user_name"] = $result[0]["name"];
      $this->signIn          = $_SESSION["sign_in"]   = $signIn;
      $this->isAuthenticated = true;
    }
    // Initialize this session for an anonymous user.
    else {
      $this->userId   = $_SESSION["user_id"]   = 0;
      $this->userName = $_SESSION["user_name"] = $_SESSION["ip_address"];
      $this->signIn   = $_SESSION["sign_in"]   = 0;
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
      "INSERT INTO `sessions` (`session_id`, `user_id`, `user_agent`, `ip_address`, `sign_in`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
      "sdssi",
      [ $this->id, $_SESSION["user_id"], $_SESSION["user_agent"], inet_pton($_SESSION["ip_address"]), $_SESSION["sign_in"] ]
    );
  }

  /**
   * Shutdown the currently active session and start one for anonymous users if we have to.
   *
   * @return this
   * @throws \MovLib\Exception\SessionException
   */
  public function shutdown() {
    // Only start a session for this anonymous user if there is any data that we need to remember and if no session is
    // already active (which is the case if this request was made by an authenticated user).
    if (session_status() === PHP_SESSION_NONE && !empty($_SESSION)) {
      // Create a copy of the data stored in this session, the call to session_start() would delete everything.
      $sessionData = $_SESSION;
      if (session_start() === false) {
        throw new SessionException("Could not start session (may be Memcached is down?).");
      }
      $_SESSION += $sessionData;
      $this->init();
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
      [ $this->id, inet_pton($_SESSION["ip_address"]), $_SESSION["user_agent"], $oldSessionId, $_SESSION["user_id"] ]
    );
  }

}
