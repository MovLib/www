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
namespace MovLib\Model;

use \Memcached;
use \MovLib\Exception\ErrorException;
use \MovLib\Exception\NetworkException;
use \MovLib\Exception\SessionException;
use \MovLib\Exception\UserException;
use \MovLib\Model\BaseModel;
use \MovLib\Model\I18nModel;
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedMethodCalls;
use \MovLib\Utility\String;
use \MovLib\Utility\Validator;

/**
 * The session model loads the basic user information, creates, updates and deletes sessions.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class SessionModel extends BaseModel {


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The user's CSRF token.
   *
   * @var string
   */
  public $csrfToken;

  /**
   * The user's account status.
   *
   * <tt>TRUE</tt> if the user is deleted or deactivated, otherwise <tt>TRUE</tt>.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The user's unique ID.
   *
   * @var int
   */
  public $id;

  /**
   * The user's login status.
   *
   * <tt>TRUE</tt> if the user is logged in, otherwise <tt>FALSE</tt>.
   *
   * @var boolean
   */
  public $isLoggedIn = false;

  /**
   * The user's name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's session ID.
   *
   * @var string
   */
  public $sessionId;

  /**
   * The user's timezone.
   *
   * @see timezone_identifiers_list()
   * @see \DateTimeZone::listIdentifiers()
   * @var string
   */
  public $timezone;

  /**
   * The user agent string as submitted by the client.
   *
   * @var string
   */
  public $userAgent;

  /**
   * The time to life for this session (UNIX timestamp).
   *
   * @var int
   */
  public $ttl;

  /**
   * The IP address as submitted by the client.
   *
   * @var string
   */
  public $ipAddress;

  /**
   * The user's preferrec system language's ID.
   *
   * @var int
   */
  public $languageId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new session model.
   *
   * @throws \MovLib\Exception\NetworkException
   *   If user agent string or IP address are invalid.
   * @throws \MovLib\Exception\SessionException
   *   If no session can be started or if the user with the ID stored in the session does not exist.
   * @throws \MovLib\Exception\UserException
   *   If the user is deleted or deactivated.
   */
  public function __construct() {
    // Always export user agent and IP address to class scope, no matter if this is a user we know or not.
    // Do not even attempt to check if the filter was successful, the user agent string may contain all kind of funny
    // stuff. We really don't care, we only store it to display it to the user later on so they are able to check
    // for themselves.
    $this->userAgent = filter_input(INPUT_SERVER, "HTTP_USER_AGENT", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    // Of course we validate the IP address. The source IP of the TCP connection cannot be substituted by changing a
    // simple HTTP header. We only have to ensure that this variable will still contain the correct IP address of the
    // client when we begin to use proxy servers.
    if (($this->ipAddress = Validator::inputIpAddress("REMOTE_ADDR", INPUT_SERVER)) === false) {
      throw new NetworkException("The IP address is empty, or not a valid IPv4 nor IPv6 address. The address was: <code>" . String::checkPlain($_SERVER["REMOTE_ADDR"]) . "</code>");
    }
    // Check if a cookie is present and not empty.
    // Only attempt to start a session if no session is already active.
    if (($sessionId = Validator::inputString("MOVSID", INPUT_COOKIE)) !== false && session_status() === PHP_SESSION_NONE) {
      if (session_start() === false) {
        throw new SessionException("Could not start session.");
      }
      try {
        $this->loadSession(
          "SELECT
            `user_id` AS `id`,
            `name`,
            `deleted`,
            `timezone`,
            `language_id` AS `languageId`
          FROM `users`
          WHERE `user_id` = ?
          LIMIT 1",
          "d", $_SESSION["UID"]
        );
        if ($_SESSION["TTL"] < time()) {
          $this->destroySessionAndRedirectToLogin();
        }
        else {
          $this->sessionId = session_id();
          $this->csrfToken = $_SESSION["CSRF"];
          $this->ttl       = $_SESSION["TTL"];
          $this->isLoggedIn = true;
        }
      } catch (UserException $e) {
        // @todo Account is deleted or deactivated, redirect and display help.
        throw new SessionException("@todo Account is deleted or deactivated, redirect and display help.", $e);
      } catch (ErrorException $e) {
        // Catching this exception might have several reasons. Maybe Memcached was down or reloaded and the newly
        // generated session ID doesn't have the necessary fields UID, CSRF and TTL stored along. Let's check our
        // persistent storage.
        try {
          $result = $this->loadSession(
            "SELECT
              `u`.`user_id` AS `id`,
              `u`.`name` AS `name`,
              `u`.`deleted` AS `deleted`,
              `u`.`timezone` AS `timezone`,
              `u`.`language_id` AS `languageId`
            FROM `users` `u`
              INNER JOIN `sessions` `s`
                ON `s`.`user_id` = `u`.`user_id`
            WHERE `s`.`session_id` = ?
            LIMIT 1",
            "s", $sessionId
          );
          // Fake an object, we don't want to load a complete user model.
          $this->startSession((object) $result);
        } catch (UserException $e) {
          // @todo Account is deleted or deactivated, redirect and display help.
          throw new SessionException("@todo Account is deleted or deactivated, redirect and display help.", $e);
        } catch (ErrorException $e) {
          $this->destroySessionAndRedirectToLogin();
        }
      }
    }
  }

  /**
   * Destroy the currently active session and redirect the user to the login page.
   *
   * This method can be called during any bootstrap stage. If no i18n instance is active, a new one will be created.
   * Note that this will exit the request and no deferred methods and nothing else will be executed!
   *
   * @global \MovLib\Model\I18nModel $i18n
   */
  private function destroySessionAndRedirectToLogin() {
    global $i18n;
    if (!isset($i18n)) {
      $i18n = new I18nModel();
    }
    $this->destroySession()->deleteSession();
    $loginRoute = $i18n->r("/user/login");
    $redirectTo = "";
    if ($_SERVER["REQUEST_URI"] !== $loginRoute) {
      $redirectTo = "?redirect_to={$_SERVER["REQUEST_URI"]}";
    }
    header("Location: {$loginRoute}{$redirectTo}", true, 302);
    exit("<html><head><title>302 Moved Temporarily</title></head><body bgcolor=\"white\"><center><h1>302 Moved Temporarily</h1></center><hr><center>nginx/{$_SERVER["SERVER_VERSION"]}</center></body></html>");
  }

  /**
   * Load session data from the specified query and export values to class scope.
   *
   * @param string $query
   *   The query to execute.
   * @param string $type
   *   The type of the value in mysqli bind param syntax.
   * @param mixed $value
   *   The value to search for (will be converted to array automatically).
   * @throws \MovLib\Exception\UserException
   *   If the account is either deleted or deactivated.
   */
  private function loadSession($query, $type, $value) {
    foreach ($this->select($query, $type, [ $value ])[0] as $name => $value) {
      $this->{$name} = $value;
    }
    settype($this->deleted, "boolean");
    if ($this->deleted === true) {
      // @todo Redirect and tell to user about possible actions.
      throw new UserException("The user's account is either deleted or deactivated.");
    }
  }

  /**
   * Deletes this session from our session database.
   *
   * @param string $sessionId
   *   [Optional] The unique session ID that should be deleted. If no ID is passed along the current session ID of this
   *   instance will be used.
   * @return this
   */
  public function deleteSession($sessionId = null) {
    $sessionId = $sessionId ?: $this->sessionId;
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
    return $this->delete("sessions", "s", [ "session_id" => $sessionId ]);
  }

  /**
   * Destroy this session.
   *
   * Removes this session ID from our database and logs the user out.
   *
   * @return this
   */
  public function destroySession() {
    // The user is no longer logged in.
    $this->isLoggedIn = false;
    // Remove all data associated with this session.
    session_destroy();
    // Remove the cookie.
    $cookieParams = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $cookieParams["path"], $cookieParams["domain"], $cookieParams["secure"], $cookieParams["httponly"]);
    // Remove the session ID from our database.
    DelayedMethodCalls::stack($this, "deleteSession");
    return $this;
  }

  /**
   * Retrieve a list of all active sessions.
   *
   * @return array
   *   Associative array containing all active sessions of this user. The key is the session ID and the value is an
   *   associative array as well, containing the following information:
   *   <ul>
   *     <li><b>CSRF:</b> The CSRF token.</li>
   *     <li><b>IP:</b> The IP address.</li>
   *     <li><b>TTL:</b> Time to life for this session.</li>
   *     <li><b>UA:</b> The user agent string.</li>
   *   </ul>
   * @throws \MovLib\Exception\SessionException
   *   If no sessions could be retrieved from the database.
   */
  public function getActiveSessions() {
    $sessions = $this->select("SELECT `session_id`, `user_agent`, `ip_address`, UNIX_TIMESTAMP(`ttl`) AS `ttl` FROM `sessions` WHERE `user_id` = ?", "d", [ $this->id ]);
    $c = count($sessions);
    for ($i = 0; $i < $c; ++$i) {
      // Transform each IP address into a humand readable form.
      $sessions[$i]["ip_address"] = inet_ntop($sessions[$i]["ip_address"]);
    }
    return $sessions;
  }

  /**
   * Insert newly created session into our persistent database.
   *
   * @return this
   */
  public function insertSession() {
    return $this->prepareAndBind(
      "INSERT INTO `sessions` (`session_id`, `user_id`, `user_agent`, `ip_address`, `ttl`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?))",
      "sdssi",
      [ $this->sessionId, $this->id, $this->userAgent, inet_pton($this->ipAddress), $this->ttl ]
    )->execute()->close();
  }

  /**
   * Forcefully starts a new session for the given user model, regenerating the session's ID. Preserving any
   * previously set session data. This should be called after a user has successfully logged in.
   *
   * @param \MovLib\Model\UserModel $userModel
   *   The user model for which we should start a new session.
   * @return this
   * @throws \MovLib\Exception\SessionException
   *   If starting a new session failed. Do not attempt to catch this exception, this is fatal and this request should
   *   fail!
   */
  public function startSession($userModel) {
    // Create a copy of any previously set session data, the call to session_start() will empty this array.
    $sessionData = isset($_SESSION) ? $_SESSION : null;
    // It's important to destroy any active session, otherwise we can't start a new one.
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
    }
    // Memcached is most likely down or full if starting a new session fails.
    if (session_start() === false) {
      throw new SessionException("Could not start session.");
    }
    // After PHP has started the session, we can combine the arrays again.
    if (!empty($sessionData)) {
      $_SESSION += $sessionData;
    }
    // IP address and user agent string are already set.
    $this->sessionId  = session_id();
    $this->csrfToken  = $_SESSION["CSRF"] = Crypt::randomHash();
    $this->id         = $_SESSION["UID"]  = $userModel->id;
    $this->ttl        = $_SESSION["TTL"]  = time() + ini_get("session.gc_maxlifetime");
    $this->deleted    = $userModel->deleted;
    $this->name       = $userModel->name;
    $this->timezone   = $userModel->timezone;
    $this->languageId = $userModel->languageId;
    $this->isLoggedIn = true;
    // Be sure to insert this new session into the database.
    DelayedMethodCalls::stack($this, "insertSession");
    return $this;
  }

}
