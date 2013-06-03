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

use \MovLib\Exception\ErrorException;
use \MovLib\Exception\UserException;
use \MovLib\Model\AbstractModel;

/**
 * Retrieve user specific data from the database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserModel extends AbstractModel {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Maximum length a username can have.
   *
   * This length must be the same as it is defined in the database table. We redefine this here in order to validate the
   * length of the chosen username before attempting to insert it into our database. Be sure to count the strings length
   * with <code>mb_strlen()</code> because the length is defined per character and not per byte.
   *
   * @var int
   */
  const NAME_MAX_LENGTH = 40;

  /**
   * Maximum length an email address can have.
   *
   * This length must be the same as it is defined in the database table. We redefine this here in order to validate the
   * length of the email address before attempting to insert it into our database. Be sure to count the strings length
   * with <code>mb_strlen()</code> because the length is defined per character and not per byte.
   *
   * @var int
   */
  const MAIL_MAX_LENGTH = 254;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The CSRF token for this user's session.
   *
   * @var string
   */
  private $csrfToken;

  /**
   * The user's unique ID.
   *
   * @var int
   */
  private $id;

  /**
   * The user's unique name if logged in, otherwise the user's IP address will be used as name.
   *
   * @var string
   */
  private $name;

  /**
   * The user's login status.
   *
   * @var boolean
   */
  private $loggedIn = false;

  /**
   * The user's data as associative array.
   *
   * @var array
   */
  private $user;


  // ------------------------------------------------------------------------------------------------------------------- Constructors


  /**
   * Create new user model from unique user's ID.
   *
   * @param int $id
   *   The user's unique ID.
   * @return $this
   *   The user model that was created from the user's data found in the database via his unique ID.
   * @throws \MovLib\Exception\UserException
   *   If no user exists with the given ID.
   */
  public function constructFromId($id) {
    return $this->constructFrom("user_id", "d", $id);
  }

  /**
   * Create new user model from unique user's mail.
   *
   * @param string $mail
   *   The user's unique mail.
   * @return $this
   *   The user model that was created from the user's data found in the database via his unique mail.
   * @throws \MovLib\Exception\UserException
   *   If no user exists with the given mail.
   */
  public function constructFromMail($mail) {
    return $this->constructFrom("mail", "s", $mail);
  }

  /**
   * Create new user model from unique user's name.
   *
   * @param string $name
   *   The user's unique name.
   * @return $this
   *   The user model that was created from the user's data found in the database via his unique name.
   * @throws \MovLib\Exception\UserException
   *   If no user exists with the given name.
   */
  public function constructFromName($name) {
    return $this->constructFrom("name", "s", $name);
  }

  public function constructFromSession() {
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * The user's login status.
   *
   * @return boolean
   *   <tt>TRUE</tt> if the user is logged in, otherwise <tt>FALSE</tt>.
   */
  public function isLoggedIn() {
    return $this->loggedIn;
  }

  /**
   * The user's CSRF token.
   *
   * @return null|string
   *   The CSRF token or <tt>NULL</tt> if there is no valid session.
   */
  public function getCsrfToken() {
    return $this->csrfToken;
  }

  /**
   * The user's unique ID.
   *
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * The user's unique name.
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * The user's deletion status.
   *
   * @return boolean
   *   <tt>TRUE</tt> if the user was deleted, otherwise <tt>FALSE</tt>.
   */
  public function getDeleted() {
    return $this->user["deleted"];
  }

  /**
   * @todo Fetch language code and country from database for this user.
   */
  public function getLocale() {

  }

  /**
   * The user's basic data.
   *
   * <em>Basic</em> in this context refers to all the data that was loaded from the database up to the point this
   * method is called, but it will at least contain all publicly available data from the users database table. Refer
   * to the construction methods or the database scheme to see what data this is.
   *
   * @return array
   *   Associative array containing the user's basic data.
   */
  public function getUser() {
    return $this->user;
  }

  /**
   * Get the route to the user's public profile.
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global I18n instance.
   * @return string
   *   The route to the user's public profile.
   */
  public function getProfileRoute() {
    global $i18n;
    if (!$this->user["route"]) {
      $this->user["route"] = $i18n->r("/user/{0}", [ $this->id ]);
    }
    return $this->user["route"];
  }

  public function resetPassword() {

  }

  /**
   * Get value identified by key from session.
   *
   * @param string $key
   *   The key for identifying the value in the associative session array.
   * @param mixed $default
   *   [Optional] The content of this variable is returned if the session does not contain the desired <var>$key</var>.
   * @return mixed
   *   The desired value's content or <var>$default</var> if the <var>$key</var> does not exist.
   */
  public function sessionGet($key, $default = false) {
    if (isset($_SESSION[$key])) {
      return $_SESSION[$key];
    }
    return $default;
  }

  /**
   * Store value identified by key in user's session.
   *
   * <b>IMPORTANT!</b> This method will silently overwrite the key's value if it already exists.
   *
   * @param mixed $key
   *   Unique key for identification.
   * @param mixed $value
   *   The value to store under key.
   * @return $this
   * @throws \MovLib\Exception\UserException
   *   If the session is not active.
   */
  public function sessionStore($key, $value) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      throw new UserException("No active session for storage!");
    }
    $_SESSION[$key] = $value;
    return $this;
  }

  /**
   * Validate the user submitted CSRF token against the user's session CSRF token.
   *
   * @param string $submittedCsrfToken
   *   The user submitted CSRF token.
   * @return boolean
   *   <tt>TRUE</tt> if the tokens match, otherwise <tt>FALSE</tt>.
   */
  public function validateCsrfToken($submittedCsrfToken) {
    return $this->csrfToken === $submittedCsrfToken;
  }

  /**
   * Validate the given username.
   *
   * <b>Usage example:</b>
   * <pre>if ($error = UserModel::validateName($name)) {
   *   throw new Exception($error);
   * }</pre>
   *
   * @link http://api.drupal.org/api/drupal/core!modules!user!user.module/function/user_validate_name/8
   * @global \MovLib\Utility\I18n $i18n
   *   The global I18n instance.
   * @param string $name
   *   The username to validate.
   * @return null|string
   *   A translated string containing the error message if something is wrong with the given name, otherwise <tt>NULL</tt>.
   */
  public static function validateName($name) {
    global $i18n;
    if (empty($name)) {
      return $i18n->t("You must enter a username.");
    }
    if (substr($name, 0, 1) === " ") {
      return $i18n->t("The username cannot begin with a space.");
    }
    if (substr($name, -1) === " ") {
      return $i18n->t("The username cannot end with a space.");
    }
    if (strpos($name, "  ") !== false) {
      return $i18n->t("The username cannot contain multiple spaces in a row.");
    }
    if (preg_match("/[^\x{80}-\x{F7} a-z0-9@_.\'-]/i", $name) === 1) {
      return $i18n->t("The username contains an illegal character.");
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
      return $i18n->t("The username contains an illegal character.");
    }
    if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
      return $i18n->t("The username {0} is too long: it must be {1,number,integer} characters or less.", [ $name, self::NAME_MAX_LENGTH ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods


  /**
   * Helper method for the various constructors.
   *
   * @param string $column
   *   The table's column for which we search.
   * @param string $type
   *   The column's data type in <code>mysqli_stmt::bind_param()</code> syntax.
   * @param mixed $value
   *   The value for which we search.
   * @return $this
   * @throws \MovLib\Exception\ErrorException
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\UserException
   *   If no user could be found for the given value.
   */
  private function constructFrom($column, $type, $value) {
    try {
      $this->user = $this->query(
        "SELECT
          `user_id` AS `id`,
          `name`,
          `created`,
          `access`,
          `login`,
          `deleted`,
          `timezone`,
          `image_id`,
          `real_name`,
          `country_id`,
          `language_id`
        FROM `users`
        WHERE `{$column}` = ?
        LIMIT 1", $type, [ $value ]
      )[0];
      $this->id = $this->user["id"];
      $this->name = $this->user["name"];
      settype($this->user["deleted"], "boolean");
    } catch (ErrorException $e) {
      throw new UserException("Could not find user for <em>{$column}</em> '{$value}'!", 0, $e);
    }
    return $this;
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

}
