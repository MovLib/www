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
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedLogger;
use \MovLib\Utility\String;

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
   * Maximum length an email address can have.
   *
   * This length must be the same as it is defined in the database table. We redefine this here in order to validate the
   * length of the email address before attempting to insert it into our database. Be sure to count the strings length
   * with <code>mb_strlen()</code> because the length is defined per character and not per byte.
   *
   * @var int
   */
  const MAIL_MAX_LENGTH = 254;

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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The CSRF token for this user's session.
   *
   * @var null|string
   */
  public $csrfToken;

  /**
   * The user's unique ID, defaults to zero (anonymous user).
   *
   * @var int
   */
  public $id;

  /**
   * The user's unique name if logged in, otherwise the user's IP address will be used as name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's login status.
   *
   * <tt>TRUE</tt> if the user is logged in, otherwise <tt>FALSE</tt>.
   *
   * @var boolean
   */
  public $isLoggedIn = false;

  public $timestampCreated;

  public $timestampLastAccess;

  public $timestampLastLogin;

  public $deleted;

  public $timezone;

  public $avatarId;

  public $realName;

  public $countryId;

  public $languageId;

  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user model object.
   *
   * @param string $from
   *   [Optional] Defines how the object should be filled with data. Possible <var>$from</var> values are:
   *   <ul>
   *     <li><em>session</em>: Try to load the user data from the session (cookie).</li>
   *     <li><em>user_id</em>: Load the user from the given unique user ID in <var>$value</var>.</li>
   *     <li><em>name</em>: Load the user from the given unique username in <var>$value</var>.</li>
   *     <li><em>mail</em>: Load the user from the given unique email address in <var>$value</var>.</li>
   *   </ul>
   * @param mixed $value
   *   [Optional] Should contain the data to identify the user upon loading, see description of <var>$from</var>.
   * @throws \MovLib\Exception\ErrorException
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\UserException
   *   If no user could be found for the given <var>$value</var> (if <var>$value</var> is not <tt>NULL</tt>).
   */
  public function __construct($from = null, $value = null) {
    switch ($from) {
      case "session":
        $this->sessionInitialize();
        break;

      case "user_id":
        $type = "d";
        break;

      case "name":
      case "mail":
        $type = "s";
        break;
    }
    if (isset($type) && $value !== null) {
      try {
        foreach ($this->select(
          "SELECT
            `user_id` AS `id`,
            `name`,
            `created` AS `timestampCreated`,
            `access` AS `timestampLastAccess`,
            `login` AS `timestampLastLogin`,
            `deleted`,
            `timezone`,
            `image_id` AS `imageId`,
            `real_name` AS `realName`,
            `country_id` AS `countryId`,
            `language_id` AS `languageId`
          FROM `users`
            WHERE `{$from}` = ?
          LIMIT 1", $type, [ $value ]
        )[0] as $name => $value) {
          $this->{$name} = $value;
        }
        settype($this->deleted, "boolean");
      } catch (ErrorException $e) {
        throw new UserException("Could not find user for {$from} '{$value}'!", $e);
      }
    }
    // If we have no ID until this point, create anon user.
    if (!$this->id) {
      $this->id = 0;
      // @todo It is important to configure our servers that they will always submit this key with the user's IP address
      //       especially if we start using proxies. It's no problem to alter the submitted FastCGI parameters (something
      //       we are doing heavily already).
      $this->name = $_SERVER["REMOTE_ADDR"];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Create new user account.
   *
   * @param string $name
   *   The desired user's unique name.
   * @param string $mail
   *   The user's unique valid mail.
   * @return string
   *   The randomly generated password for this user.
   * @throws \MovLib\Exception\DatabaseException
   *   If creating the new user failed.
   * @throws \MovLib\Exception\UserException
   *   If the name or mail is already in use, the exception message will be translated and can be used for displaying
   *   to the user.
   */
  public function createAccount($name, $mail, $password) {
    global $i18n;
    if ($this->exists("name", $name) === true) {
      throw new UserException($i18n->t("The {0} {1} is already in use.", [ $i18n->t("username"), String::placeholder($name) ]));
    }
    if ($this->exists("mail", $mail) === true) {
      throw new UserException($i18n->t("The {0} {1} is already in use.", [ $i18n->t("email address"), String::placeholder($mail) ]));
    }
    try {
      $this
        ->prepareAndBind(
          "INSERT INTO `users`
          (`name`, `mail`, `pass`, `created`, `access`, `login`, `deleted`, `timezone`, `init`, `dyn_data`, `language_id`)
          VALUES
          (?, ?, ?, NOW() + 0, NOW() + 0, NOW() + 0, FALSE, 'UTC', ?, '', ?)",
          "ssssi",
          [
            $name,
            $mail,
            password_hash($password, PASSWORD_DEFAULT),
            $mail,
            $this->select("SELECT `language_id` FROM `languages` WHERE `iso_alpha-2` = ? LIMIT 1", "s", [ $i18n->languageCode ])[0]["language_id"]
          ]
        )
        ->execute()
        ->close()
      ;
    } catch (ErrorException $e) {
      // If we cannot fetch the ID for a server supported language, we really have a problem.
      $e = new DatabaseException("Could not fetch ID for language '{$i18n->languageCode}'!", $e);
      DelayedLogger::logException($e, E_ERROR);
      throw $e;
    }
    return $this;
  }

  /**
   * Check if a user with the given value exists.
   *
   * @param string $column
   *   The column name against which the value should be checked.
   * @param mixed $value
   *   The user attribute to check.
   * @param string $type
   *   [Optional] The datatype of the column in the database, defaults to string.
   * @return boolean
   *   <tt>TRUE</tt> if a user exists with the given value, otherwise <tt>FALSE</tt>.
   */
  public function exists($column, $value, $type = "s") {
    return !empty($this->select("SELECT `user_id` FROM `users` WHERE `{$column}` = ? LIMIT 1", $type, [ $value ]));
  }

  /**
   * Get the user's preferred ISO 639-1 alpha-2 language code.
   *
   * @staticvar string $languageCode
   *   Used to cache the lookup.
   * @return null|string
   *   The user's language code or <tt>NULL</tt> if the user has none.
   */
  public function getLanguageCode() {
    static $languageCode = null;
    try {
      if ($languageCode === null) {
        $languageCode = $this->select("SELECT `iso_alpha-2` FROM `languages` WHERE `language_id` = ? LIMIT 1", "i", $this->languageId)[0]["iso_alpha-2"];
      }
      return $languageCode;
    } catch (ErrorException $e) {
      return null;
    }
  }

  /**
   * Get the route to the user's public profile.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The route to the user's public profile.
   */
  public function getProfileRoute() {
    global $i18n;
    static $profileRoute = null;
    if ($profileRoute === null) {
      $profileRoute = $i18n->r("/user/{0,number,integer}", [ $this->id ]);
    }
    return $profileRoute;
  }

  /**
   * Register new user for activation in our temporary database table.
   *
   * @param string $hash
   *   The activation hash used in the activation link for identification.
   * @param string $name
   *   The valid name of the new user.
   * @param string $mail
   *   The valid mail of the new user.
   */
  public function insertRegistrationData($hash, $name, $mail) {
    // @todo Catch exceptions, log and maybe even send a mail to the user?
    $this
      ->prepareAndBind("INSERT INTO `tmp` (`key`, `dyn_data`) VALUES (?, COLUMN_CREATE('name', ?, 'mail', ?, 'time', NOW() + 0))", "sss", [ $hash, $name, $mail ])
      ->execute()
      ->close()
    ;
  }

  /**
   * Add reset password request to our temporary database table.
   *
   * @param string $hash
   *   The password reset hash used in the password reset link for identification.
   * @param string $mail
   *   The valid mail of the user.
   */
  public function insertResetPasswordData($hash, $mail) {
    // @todo Catch exceptions, log and maybe even send a mail to the user?
    $this
      ->prepareAndBind("INSERT INTO `tmp` (`key`, `dyn_data`) VALUES (?, COLUMN_CREATE('mail', ?, 'time', NOW() + 0))", "ss", [ $hash, $mail ])
      ->execute()
      ->close()
    ;
  }


  public function login($mail, $pass) {
    try {
      foreach ($this->select(
        "SELECT
          `user_id` AS `id`,
          `name`,
          `created` AS `timestampCreated`,
          `access` AS `timestampLastAccess`,
          `login` AS `timestampLastLogin`,
          `deleted`,
          `timezone`,
          `image_id` AS `imageId`,
          `real_name` AS `realName`,
          `country_id` AS `countryId`,
          `language_id` AS `languageId`
        FROM `users`
          WHERE `mail` = ?
          AND `pass` = ?
        LIMIT 1", "ss", [ $mail, password_hash($pass, PASSWORD_DEFAULT) ]
      )[0] as $name => $value) {
        $this->{$name} = $value;
      }
      settype($this->deleted, "boolean");
    } catch (ErrorException $e) {
      throw new UserException("Could not find a user with the given mail/password combination.", $e);
    }
  }

  /**
   * Select the data that was previously stored from the temporary database.
   *
   * @param string $hash
   *   The user submitted hash to identify the reset password request.
   * @return null|array
   *   <tt>NULL</tt> if no record was found for the hash, otherwise an associative array with the following keys:
   *   <em>name</em>, <em>mail</em>, and <em>time</em>. The name might be <tt>NULL</tt>, depending on the data that
   *   was stored previously (e.g. reset password requests do not have the name).
   */
  public function selectTmpData($hash) {
    try {
      return $this->select(
        "SELECT
          COLUMN_GET(`dyn_data`, 'name' AS CHAR(" . self::NAME_MAX_LENGTH . ")) AS `name`,
          COLUMN_GET(`dyn_data`, 'mail' AS CHAR(" . self::MAIL_MAX_LENGTH . ")) AS `mail`,
          COLUMN_GET(`dyn_data`, 'time' AS UNSIGNED) AS `time`
        FROM `tmp`
          WHERE `key` = ?
        LIMIT 1", "s", [ $hash ]
      )[0];
    } catch (ErrorException $e) {
      return null;
    }
  }

  /**
   * Initializes the session and starts a session if needed.
   *
   * @return $this
   */
  public function sessionInitialize() {
    // Empty session IDs are not valid.
    if (!empty($_COOKIE["MOVSID"])) {

    }
  }

  /**
   * Starts a session forcefully, preserving already set session data. A new CSRF token will be generated for the user.
   *
   * @return $this
   */
  public function sessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
      // Save current session before starting it, as PHP will destroy it.
      $sessionData = isset($_SESSION) ? $_SESSION : null;
      session_start();
      $this->csrfToken = $_SESSION["TOKEN"] = Crypt::randomHashBase64();
      $this->isLoggedIn = true;
      $_SESSION["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
      // Restore session data.
      if ($sessionData !== null) {
        $_SESSION += $sessionData;
      }
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Validate the given username.
   *
   * <b>Usage example:</b>
   * <pre>if ($error = UserModel::validateName($name)) {
   *   throw new Exception($error);
   * }</pre>
   *
   * @link http://api.drupal.org/api/drupal/core!modules!user!user.module/function/user_validate_name/8
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
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

}
