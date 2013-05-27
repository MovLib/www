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
namespace MovLib\Entity;

use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\UserException;
use \MovLib\Model\UserModel;
use \MovLib\Utility\Crypt;

/**
 *
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class User {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Maximum length a username can have.
   *
   * @var int
   */
  const USERNAME_MAX_LENGTH = 40;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The CSRF token for this user's session.
   *
   * @var string
   */
  private $csrfToken = false;

  /**
   * The unique email of this user.
   *
   * @var string
   */
  private $email = "";

  /**
   * The unique ID of this user.
   *
   * @var int
   */
  private $id = 0;

  /**
   * Login status of this user.
   *
   * @var boolean
   */
  private $loggedIn = false;

  /**
   * The unique name of this user.
   *
   * @var string
   */
  private $name = "";

  /**
   * The status of the user.
   *
   * Use <code>User::getStatus()</code> if you want to receive this value as boolean!
   * <ul>
   *   <li><b>0</b> = disabled</li>
   *   <li><b>1</b> = enabled</li>
   * </ul>
   *
   * @var int
   */
  private $status = 0;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods




  // ------------------------------------------------------------------------------------------------------------------- Public methods


  /**
   * Fill user object with data stored in database identified by <var>$field</var>.
   *
   * @param string $field
   *   The field which is used to identify the user.
   * @param string $type
   *   Type string in <code>mysqli_prepare()</code>-syntax (note that the <tt>BIGINT</tt> data type must be <em>d</em>).
   * @param mixed $value
   *   The value of <var>$field</var> in the database.
   * @return $this
   * @throws \MovLib\Exception\UserException
   *   If no active user could be found.
   */
  protected function constructFrom($field, $type, $value) {
    try {
      return $this->setUserData((new UserModel())->getUserFrom($field, $type, $value));
    } catch (DatabaseException $e) {
      throw new UserException($e->getMessage());
    }
  }

  /**
   * Construct new user object from given ID.
   *
   * @param int $id
   *   The ID from which a user object should be created.
   * @return $this
   * @throws \MovLib\Exception\UserException
   *   If there is no user with the given ID.
   */
  public function constructFromId($id) {
    return $this->constructFrom("user_id", "d", $id);
  }

  /**
   * Construct new user object from given email address.
   *
   * @param string $email
   *   The email address from which a user object should be created.
   * @return $this
   * @throws \MovLib\Exception\UserException
   *   If there is no user with the given email address.
   */
  public function constructFromEmail($email) {
    return $this->constructFrom("email", "s", $email);
  }

  /**
   * Construct new user object from given name.
   *
   * @param string $name
   *   The name from which a user object should be created.
   * @return $this
   * @throws \MovLib\Exception\UserException
   *   If there is no user with the given name.
   */
  public function constructFromName($name) {
    return $this->constructFrom("name", "s", $name);
  }

  /**
   * Construct new user object from session.
   *
   * @return $this
   */
  public function constructFromSession() {

  }

  /**
   * Get a CSRF token for a form to validate that the user really wanted to submit this form.
   *
   * @link https://owasp.org/index.php/Cross-Site_Request_Forgery_%28CSRF%29_Prevention_Cheat_Sheet
   * @link https://owasp.org/index.php/PHP_CSRF_Guard
   * @return string
   *   The unique CSRF token.
   */
  public function getCsrfToken() {
    return $this->csrfToken;
  }

  /**
   * Get the user email.
   *
   * @return string
   *   The email address of the current user object.
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * Get the user ID.
   *
   * @return int
   *   The ID of the current user object.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Get the username.
   *
   * @return string
   *   The username of the current user object.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get the route to this user's profile page.
   *
   * @staticvar null|string $profileUrl
   *   Cache the route.
   * @return string
   *   The route to this user's profile page.
   */
  public function getProfileRoute() {
    static $profileUrl = null;
    if ($profileUrl === null) {
      $profileUrl = route("user/%s", mb_strtolower($this->name));
    }
    return $profileUrl;
  }

  /**
   * Get the status of this user.
   *
   * @return boolean
   *   <tt>TRUE</tt> if the user is enabled, <tt>FALSE</tt> if the profile is disabled.
   */
  public function getStatus() {
    return (boolean) $this->status;
  }

  /**
   * Check if user is logged in.
   *
   * @return boolean
   *   <tt>TRUE</tt> if the current user is logged in, otherwise <tt>FALSE</tt>.
   */
  public function loggedIn() {
    return $this->loggedIn;
  }

  public function resetPassword() {

  }

  /**
   * Validate a user submitted CSRF token against the token present in the user's session.
   *
   * @param string $submittedToken
   *   The user submitted CSRF token.
   * @return boolean
   *   <tt>TRUE</tt> if the token matches, <tt>FALSE</tt> if no token is present in the session or they are not equal.
   */
  public function validateCsrfToken($submittedToken) {
    return $this->csrfToken === $submittedToken;
  }

  /**
   * Validate the given username.
   *
   * @link http://api.drupal.org/api/drupal/core!modules!user!user.module/function/user_validate_name/8
   * @param string $name
   *   The username to validate.
   * @return string
   *   The username if it is valid.
   */
  public static function validateName($name) {
    if (empty($name)) {
      return __("You must enter a username.");
    }
    if (substr($name, 0, 1) === " ") {
      return __("The username cannot begin with a space.");
    }
    if (substr($name, -1) === " ") {
      return __("The username cannot end with a space.");
    }
    if (strpos($name, "  ") !== false) {
      return __("The username cannot contain multiple spaces in a row.");
    }
    if (preg_match("/[^\x{80}-\x{F7} a-z0-9@_.\'-]/i", $name) === 1) {
      return __("The username contains an illegal character.");
    }
    if (preg_match(
      "/[\x{80}-\x{A0}" .   // Non-printable ISO-8859-1 + NBSP
      "\x{AD}" .            // Soft-hyphen
      "\x{2000}-\x{200F}" . // Various space characters
      "\x{2028}-\x{202F}" . // Bidirectional text overrides
      "\x{205F}-\x{206F}" . // Various text hinting characters
      "\x{FEFF}" .          // Byte order mark
      "\x{FF01}-\x{FF60}" . // Full-width latin
      "\x{FFF9}-\x{FFFD}" . // Replacement characters
      "\x{0}-\x{1F}]/u",    // NULL byte and control characters
      $name
    ) === 1) {
      return __("The username contains an illegal character.");
    }
    if (mb_strlen($name) > self::USERNAME_MAX_LENGTH) {
      return __("The username %name is too long: it must be %max characters or less.", [ "%name" => $name, "%max" => self::USERNAME_MAX_LENGTH ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Private methods


  /**
   * Retrieve value identified by key from session.
   *
   * @param string $key
   *   The key for selecting the value from the session.
   * @param mixed $default
   *   [Optional] The content of this variable is returned if the session does not contain any value.
   * @return mixed
   *   The value identified by key if present else value from <var>$default</var>.
   */
  private function sessionGet($key, $default = false) {
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return $default;
  }

  /**
   * Start session if none is active.
   *
   * @return $this
   */
  private function sessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
      $this->csrfToken = Crypt::getHash();
      $this->sessionStore("csrf_token", $this->csrfToken);
    }
    return $this;
  }

  /**
   * Store a value identified by key in the users session.
   *
   * @param string $key
   *   Unique key for identification.
   * @param mixed $value
   *   The value to store.
   * @return $this
   */
  private function sessionStore($key, $value) {
    $this->sessionStart();
    $_SESSION[$key] = $value;
    return $this;
  }

  /**
   * Export user data to class scope (e.g. from database query).
   *
   * @param array $data
   *   Associative array containing the user's data.
   * @return $this
   */
  private function setUserData($data) {
    foreach ($data as $property => $value) {
      if (property_exists($this, $property)) {
        $this->{$property} = $value;
      }
    }
    return $this;
  }

}
