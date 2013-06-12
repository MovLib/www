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
   * The user's unique ID,
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
        $this->sessionLoad();
        return 2;

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
        )[0] as $propertyName => $propertyValue) {
          $this->{$propertyName} = $this->{$propertyValue};
        }
        settype($this->deleted, "boolean");
      } catch (ErrorException $e) {
        throw new UserException("Could not find user for {$from} '{$value}'!", $e);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


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

  /**
   * Select the data that was previously stored for this account registration from the temporary database.
   *
   * @param string $hash
   *   The user submitted hash to identify the registration.
   * @return null|array
   *   <tt>NULL</tt> if no record was found for the hash, otherwise an associative array with the following keys:
   *   <em>name</em>, <em>mail</em>, and <em>time</em>.
   */
  public function selectRegistrationData($hash) {
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
   * Select the data that was previously stored for this reset password request from the temporary database.
   *
   * @param string $hash
   *   The user submitted hash to identify the reset password request.
   * @return null|array
   *   <tt>NULL</tt> if no record was found for the hash, otherwise an associative array with the following keys:
   *   <em>mail</em>, and <em>time</em>.
   */
  public function selectResetPasswordData($hash) {
    try {
      return $this->select(
        "SELECT
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
   * @todo Implement
   */
  public function sessionLoad() {

  }

  /**
   * Start session if none is active. This will also generate the CSRF token for this user's session.
   *
   * @return $this
   */
  public function sessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
      $this->csrfToken = Crypt::getRandomHash();
      $this->sessionStore("csrf_token", $this->csrfToken);
    }
    return $this;
  }

  /**
   * Store value identified by key in user's session.
   *
   * <b>IMPORTANT!</b> This method will silently overwrite the key's value if it already exists.
   *
   * @param mixed $key
   *   Unique key for identification.
   * @param mixed $value
   *   The value to store under the key.
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
