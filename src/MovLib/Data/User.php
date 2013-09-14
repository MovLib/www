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

use \MovLib\Exception\UserException;
use \MovLib\View\ImageStyle\ResizeImageStyle;

/**
 * Retrieve user specific data from the database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class User extends \MovLib\Data\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Length of the authentication token.
   *
   * @var int
   */
  const AUTHENTICATION_TOKEN_LENGTH = 128;

  /**
   * Load the user from ID.
   *
   * @var string
   */
  const FROM_ID = "user_id";

  /**
   * Load the user from name.
   *
   * @var string
   */
  const FROM_NAME = "name";

  /**
   * Load the user from mail.
   *
   * @var string
   */
  const FROM_EMAIL = "email";

  /**
   * Small image style.
   *
   * @var int
   */
  const IMAGESTYLE_SMALL = "50x50";

  /**
   * Normal image style.
   *
   * @var int
   */
  const IMAGESTYLE_NORMAL = "100x100";

  /**
   * Big image style.
   *
   * @var int
   */
  const IMAGESTYLE_BIG = "140x140";

  /**
   * Maximum length an email address can have.
   *
   * This length must be the same as it is defined in the database table. We redefine this here in order to validate the
   * length of the email address before attempting to insert it into our database. Be sure to count the strings length
   * with <code>mb_strlen()</code> because the length is defined per character and not per byte.
   *
   * @var int
   */
  const MAX_LENGTH_EMAIL = 254;

  /**
   * Maximum length a username can have.
   *
   * This length must be the same as it is defined in the database table. We redefine this here in order to validate the
   * length of the chosen username before attempting to insert it into our database. Be sure to count the strings length
   * with <code>mb_strlen()</code> because the length is defined per character and not per byte.
   *
   * @var int
   */
  const MAX_LENGTH_NAME = 40;

  /**
   * Minimum length for a password.
   *
   * @var int
   */
  const MIN_LENGTH_PASSWORD = 6;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  private $types = [
    self::FROM_ID => "d",
    self::FROM_EMAIL => "s",
    self::FROM_NAME => "s",
  ];

  /**
   * The user's unique ID, defaults to zero (anonymous user).
   *
   * @var int
   */
  public $id;

  /**
   * The user's preferred system language's ID.
   *
   * @var int
   */
  public $languageId;

  /**
   * The user's unique name if logged in, otherwise the user's IP address will be used as name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's unique mail if logged in.
   *
   * @var string
   */
  public $email;

  /**
   * The user's creation time (UNIX timestamp).
   *
   * @var int
   */
  public $created;

  /**
   * The user's last access (UNIX timestamp).
   *
   * @var int
   */
  public $access;

  /**
   * The user's last login (UNIX timestamp).
   *
   * @var int
   */
  public $login;

  /**
   * Flag defining if the user's personal data is private or not.
   *
   * @var boolean
   */
  public $private;

  /**
   * Flag defining if the user's profile is deactivated or deleted.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * PHP timezone string of the user's timezone.
   *
   * @see timezone_identifiers_list()
   * @see \DateTimeZone::listIdentifiers()
   * @var string
   */
  public $timezone;

  /**
   * The user's edit counter.
   *
   * @var int
   */
  public $edits;

  /**
   * The user's profile text (in the current display language if available).
   *
   * @var string
   */
  public $profile;

  /**
   * The user's unique country ID.
   *
   * @var null|int
   */
  public $countryId;

  /**
   * The user's real name.
   *
   * @var null|string
   */
  public $realName;

  /**
   * The user's birthday (date).
   *
   * @var null|int
   */
  public $birthday;

  /**
   * The user's sex according to ISO/IEC 5218.
   *
   * We are only using the following three values from the standard:
   * <ul>
   *   <li><b><code>0</code>:</b> not known</li>
   *   <li><b><code>1</code>:</b> male</li>
   *   <li><b><code>2</code>:</b> female</li>
   * </ul>
   * The fourth value makes no sense in our software.
   *
   * @link https://en.wikipedia.org/wiki/ISO/IEC_5218
   * @var int
   */
  public $sex;

  /**
   * The user's website.
   *
   * @var null|string
   */
  public $website;

  /**
   * Name of the directory within the uploads directory on the server.
   *
   * @var string
   */
  public $imageDirectory = "user";

  /**
   * The user's authentication token.
   *
   * @see \MovLib\Data\User::setAuthenticationToken()
   * @var string
   */
  public $authenticationToken;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user data object.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty user model will be instanciated.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the user, see the various <var>FROM_*</var> class constants.
   * @throws \MovLib\Exception\UserException
   */
  public function __construct($from = null, $value = null) {
    global $i18n;
    if (!empty($from) && !empty($value)) {
      $result = $this->select(
        "SELECT
          `user_id` AS `id`,
          `language_id` AS `languageId`,
          `name`,
          `email`,
          UNIX_TIMESTAMP(`created`) AS `created`,
          UNIX_TIMESTAMP(`access`) AS `access`,
          UNIX_TIMESTAMP(`login`) AS `login`,
          `private`,
          `deleted`,
          `timezone`,
          `edits`,
          COLUMN_GET(`dyn_profile`, '{$i18n->languageCode}' AS BINARY) AS `profile`,
          `sex`,
          `country_id` AS `countryId`,
          `real_name` AS `realName`,
          `birthday`,
          `website`,
          `avatar_extension` AS `imageExtension`,
          `avatar_name` AS `imageHash`
        FROM `users`
          WHERE `{$from}` = ?
        LIMIT 1", $this->types[$from], [ $value ]
      );
      if (empty($result[0])) {
        throw new UserException("Could not find user for {$from} '{$value}'!");
      }
      foreach ($result[0] as $k => $v) {
        $this->{$k} = $v;
      }
      settype($this->private, "boolean");
      settype($this->deleted, "boolean");
      $this->initImage($this->imageHash, [
        new ResizeImageStyle(self::IMAGESTYLE_SMALL),
        new ResizeImageStyle(self::IMAGESTYLE_NORMAL),
        new ResizeImageStyle(self::IMAGESTYLE_BIG),
      ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Check if this email address is already in use.
   *
   * @param string $email
   *   The email address to check.
   * @return boolean
   *   <code>TRUE</code> if this email address is already in use, otherwise <code>FALSE</code>.
   */
  public function checkEmail($email) {
    $result = $this->select("SELECT `user_id` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ]);
    return !empty($result[0]);
  }

  /**
   * Check if this name is already in use.
   *
   * @param string $name
   *   The name to check.
   * @return boolean
   *   <code>TRUE</code> if this name is already in use, otherwise <code>FALSE</code>.
   */
  public function checkName($name) {
    $result = $this->select("SELECT `user_id` FROM `users` WHERE `name` = ? LIMIT 1", "s", [ $name ]);
    return !empty($result[0]);
  }

  /**
   * Update the user model in the database with the data of the current class instance.
   *
   * @todo Update profile text as well!
   * @return this
   */
  public function commit() {
    return $this->query(
      "UPDATE `users` SET
        `language_id` = ?,
        `private` = ?,
        `timezone` = ?,
        `country_id` = ?,
        `real_name` = ?,
        `birthday` = ?,
        `sex` = ?,
        `website` = ?,
        `avatar_extension` = ?,
        `avatar_name` = ?
      WHERE `user_id` = ?",
      "iisississsd",
      [
        $this->languageId,
        $this->private,
        $this->timezone,
        $this->countryId,
        $this->realName,
        $this->birthday,
        $this->sex,
        $this->website,
        $this->imageExtension,
        $this->imageHash,
        $this->id
      ]
    );
  }

  /**
   * Get the user's preferred ISO 639-1 alpha-2 language code.
   *
   * @return string
   *   The user's preferred system language code.
   * @throws \MovLib\Data\DatabaseException
   */
  public function getLanguageCode() {
    // All fields are mandatory and if missing something is terribly wrong, therefor we can access the result directly.
    return $this->select("SELECT `iso_alpha-2` FROM `languages` WHERE `language_id` = ? LIMIT 1", "i", [ $this->languageId ])[0]["iso_alpha-2"];
  }

  /**
   * Helper method to retrieve previously stored data from the temporary database table and directly delete it if the
   * query returned a result.
   *
   * @param string $hash
   *   The user submitted hash to identify the email change request.
   * @param array $dynamicData
   *   Numeric array with numeric arrays, the inner numeric array must be in the following format:
   *   <ul>
   *     <li><code>0</code> is the name of the column</li>
   *     <li><code>1</code> is the data type of the column</li>
   *   </ul>
   * @return null|array
   *   <code>NULL</code> if no record was found, otherwise an associative array consisting of the dynamic columns
   *   specified in <var>$dynamicData</var>.
   * @throws \MovLib\Exception\DatabaseException
   */
  private function getTemporaryData($hash, array $dynamicData) {
    $select = null;
    foreach ($dynamicData as $name => $type) {
      if ($select) {
        $select .= ", ";
      }
      $select .= "COLUMN_GET(`dyn_data`, '{$name}' AS {$type}) AS `{$name}`";
    }
    $result = $this->select("SELECT {$select} FROM `tmp` WHERE `key` = ? LIMIT 1", "s", [ $hash ]);
    if (!empty($result[0])) {
      $this->query("DELETE FROM `tmp` WHERE `key` = ?", "s", [ $hash ]);
      return $result[0];
    }
  }

  /**
   * Helper method to insert data into the temporary database table in a consistent format.
   *
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param()</code> syntax.
   * @param array $dynamicColumns
   *   Numeric array containing the names of the dynamic columns that should be created.
   * @param array $params
   *   Numeric array containing the values for the dynamic columns.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function prepareTemporaryData($types, array $dynamicColumns, array $params) {
    $columns = null;
    $c = count($dynamicColumns);
    for ($i = 0; $i < $c; ++$i) {
      if ($i !== 0) {
        $columns .= ", ";
      }
      $columns .= "'{$dynamicColumns[$i]}', ?";
    }
    array_unshift($params, $this->authenticationToken);
    return $this->query("INSERT INTO `tmp` (`key`, `dyn_data`) VALUES (?, COLUMN_CREATE({$columns}, 'time', CURRENT_TIMESTAMP))", "s{$types}", $params);
  }

  /**
   * Prepare reset password data for existing user account.
   *
   * This method must be public for delayed execution.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function prepareResetPassword() {
    return $this->prepareTemporaryData("s", [ "email" ], [ $this->email ]);
  }

  /**
   * Get random password.
   *
   * Passwords are generated with <i>pwgen</i> and much like the ones KeePass generates by default. Definitely not easy
   * to remember and not pronouncable if you ask me (unlike the man page promises). The following options (in that order)
   * are used to generate the passwords:
   * <ul>
   *   <li><code>"-c"</code> include at least one capital letter</li>
   *   <li><code>"-n"</code> include at least one number</li>
   *   <li><code>"-B"</code> don't include ambiguous characters</li>
   *   <li><code>"-v"</code> don't include any vowels (avoid accidental nasty words)</li>
   *   <li><code>20</code> the final length</li>
   *   <li><code>1</code> return a single password</li>
   * </ul>
   *
   * @return string
   *   The random password.
   */
  public static function getRandomPassword() {
    return shell_exec("pwgen -cnBv 20 1");
  }

  /**
   * Register a new user account.
   *
   * After the user clicked the activation link for the account, we are finally able to create an account for her/him.
   * The validation process is something the model does not care about, this is handled by the presenter, who's also
   * responsible to display the correct error messages. This method simply inserts the new data. Note that the object
   * this method is called on will automatically become the user that was just registered. Think of it like passing
   * the variable by reference. So if you call this on the global user object, the formerly anonymous global user is
   * now the registered new user. This is the desired behavior during our registration process, because we
   * want to display the password settings page within the user's account directly.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $name
   *   The valid unique user's name.
   * @param string $email
   *   The valid unique user's email address.
   * @param string $rawPassword
   *   The unhashed user's password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Exception\SessionException
   */
  public function register($name, $email, $rawPassword) {
    global $i18n;
    $password         = password_hash($rawPassword, PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"] ]);
    $this->languageId = $i18n->getLanguageId();
    $this->name       = $name;
    $this->email      = $email;
    $this->deleted    = false;
    $this->timezone   = ini_get("date.timezone");
    $this->query(
      "INSERT INTO `users` (`language_id`, `name`, `email`, `password`, `created`, `login`, `timezone`, `dyn_profile`) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, '')",
      "dssss",
      [ $this->languageId, $this->name, $this->email, $password, $this->timezone ],
      false
    );
    $this->id = $this->stmt->insert_id;
    return $this;
  }

  /**
   * Set an authentication token for this instance.
   *
   * Calculates URL-safe SHA-512 hash from highly randomized bytes (full 8-bit range). This kind of token is used for
   * all actions that require the user to confirm an action via email. This includes but is not limited to: Registration,
   * Email Change, and Password Change.
   *
   * We do not utilize <code>/dev/urandom</code> to generate our random bytes. While it is a perfect source for pseudo
   * random bytes, the <code>openssl_random_pseudo_bytes()</code> function is a magnitude faster (benchmarks have shown
   * that it takes approx <i>0.1ms</i> while opening a stream, reading and closing <code>/dev/urandom</code> always was
   * over <i>1ms</i>) and generates perfect random bytes, especially if we read 1024 bytes.
   *
   * Please also note that the statement about PHP always reading 4096 bytes from a stream in the Drupal comment of
   * <code>\Drupal\Component\Utility\Crypt::randomBytes()</code> is not true, simply check the C implementation:
   * {@link https://github.com/php/php-src/blob/master/main/streams/streams.c#L703}
   *
   * The collision probability of SHA-512 is extremely, extremely low. There is absolutely no need to generate some
   * special hash based on environment values or anything else. It's impossible to guess the hash and nearly impossible
   * that the hash collides with another hash. If you still have concerns, read the following:
   * {@link http://stackoverflow.com/a/4014407/1251219}
   *
   * SHA-512 is extremely fast on a 64-bit machine, most of the time close to MD5 and SHA-1.
   *
   * @return this
   */
  public function setAuthenticationToken() {
    $this->authenticationToken = hash("sha512", openssl_random_pseudo_bytes(1024));
    return $this;
  }

  /**
   * Change the user's email address.
   *
   * @param string $newEmail
   *   The already validated new email address.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function updateEmail($newEmail) {
    $this->query("UPDATE `users` SET `email` = ? WHERE `user_id` = ?", "sd", [ $newEmail, $this->id ]);
    $this->email = $newEmail;
    return $this;
  }

  /**
   * Change the user's password.
   *
   * This method must be public for delayed execution!
   *
   * @param string $rawPassword
   *   The new unhashed password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function updatePassword($rawPassword) {
    $password = password_hash($rawPassword, PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"] ]);
    $this->query("UPDATE `users` SET `password` = ? WHERE `user_id` = ?", "sd", [ $password, $this->id ]);
    return $this;
  }

  /**
   * Helper method to validate a user submitted authentication token and retrieve the associated data from the temporary
   * database table.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param null $errors
   *   The errors variable used to collect validation error messages.
   * @param string $type
   *   The type of authentication token to validate, simply pass the <var>AbstractPage::$id</var>.
   * @return null|array
   *   <code>NULL</code> is returned if no data could be retrieved from the database, otherwise an associative array
   *   with the data from the temporary database table. Please check implementation to check the anatomy of the returned
   *   array.
   */
  public function validateAuthenticationToken(&$errors, $type) {
    global $i18n;
    if (empty($_GET["token"]) || strlen($_GET["token"]) !== self::AUTHENTICATION_TOKEN_LENGTH) {
      $errors[] = $i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.");
    }
    else {
      switch ($type) {
        case "user-emailsettings":
          $data = $this->getTemporaryData($_GET["token"], [ "id" => "UNSIGNED", "email" => "BINARY" ]);
          break;

        case "user-passwordsettings":
          $data = $this->getTemporaryData($_GET["token"], [ "id" => "UNSIGNED", "password" => "BINARY" ]);
          break;

        case "user-registration":
          $data = $this->getTemporaryData($_GET["token"], [ "name" => "BINARY", "email" => "BINARY" ]);
          break;
      }
      if (!$data) {
        $errors[] = $i18n->t("Your authentication token has expired, please fill out the form again.");
      }
      else {
        return $data;
      }
    }
  }

}
