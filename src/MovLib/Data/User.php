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

use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Exception\UserException;

/**
 * Retrieve user specific data from the database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class User extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Length of the authentication token.
   *
   * @var int
   */
  const AUTHENTICATION_TOKEN_LENGTH = 128;

  /**
   * Avatar style for span 3 elements.
   *
   * Width and height of this image will be 220 pixels.
   *
   * @var string
   */
  const IMAGE_STYLE_LARGE = 3;

  /**
   * Avatar style for span 2 elements.
   *
   * Width and height of this image will be 140 pixels.
   *
   * @var string
   */
  const IMAGE_STYLE_MEDIUM = 2;

  /**
   * Avatar style for span 1 elements.
   *
   * Width and height of this image will be 70 pixels.
   *
   * @var string
   */
  const IMAGE_STYLE_SMALL = 1;

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
   * The user's last access (UNIX timestamp).
   *
   * @var int
   */
  public $access;

  /**
   * The user's birthday (date).
   *
   * @var null|int
   */
  public $birthday;

  /**
   * The user's unique country ID.
   *
   * @var null|int
   */
  public $countryId;

  /**
   * The user's creation time (UNIX timestamp).
   *
   * @var int
   */
  public $created;

  /**
   * Flag defining if the user's profile is deactivated.
   *
   * @var boolean
   */
  public $deactivated;

  /**
   * The user's edit counter.
   *
   * @var int
   */
  public $edits;

  /**
   * The user's unique mail if logged in.
   *
   * @var string
   */
  public $email;

  /**
   * The user's unique ID, defaults to zero (anonymous user).
   *
   * @var int
   */
  public $id;

  /**
   * The user's last login (UNIX timestamp).
   *
   * @var int
   */
  public $login;

  /**
   * The user's unique name if logged in, otherwise the user's IP address will be used as name.
   *
   * @var string
   */
  public $name;

  /**
   * Flag defining if the user's personal data is private or not.
   *
   * @var boolean
   */
  public $private;

  /**
   * The user's profile text (in the current display language if available).
   *
   * @var string
   */
  public $profile;

  /**
   * The user's real name.
   *
   * @var null|string
   */
  public $realName;

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
   * The user's preferred system language's code (e.g. <code>"en"</code>).
   *
   * @var string
   */
  public $systemLanguageCode;

  /**
   * The user's time zone ID (e.g. <code>"Europe/Vienna"</code>).
   *
   * @var string
   */
  public $timeZoneId;

  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  private $types = [
    self::FROM_ID    => "d",
    self::FROM_EMAIL => "s",
    self::FROM_NAME  => "s",
  ];

  /**
   * The user's website.
   *
   * @var null|string
   */
  public $website;


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
    if (isset($from) && isset($value)) {
      $result = $this->select(
        "SELECT
          `user_id` AS `id`,
          `system_language_code` AS `systemLanguageCode`,
          `name`,
          `email`,
          UNIX_TIMESTAMP(`created`) AS `created`,
          UNIX_TIMESTAMP(`access`) AS `access`,
          UNIX_TIMESTAMP(`login`) AS `login`,
          `private`,
          `deactivated`,
          `time_zone_id` AS `timeZoneId`,
          `edits`,
          COLUMN_GET(`dyn_profile`, '{$i18n->languageCode}' AS BINARY) AS `profile`,
          `sex`,
          `country_id` AS `countryId`,
          `real_name` AS `realName`,
          `birthday`,
          `website`,
          `avatar_changed` AS `imageChanged`,
          `avatar_name` AS `imageName`,
          `avatar_type` AS `imageType`
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
      settype($this->deactivated, "boolean");
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
        `system_language_code` = ?,
        `private` = ?,
        `time_zone_id` = ?,
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
        $this->systemLanguageCode,
        $this->private,
        $this->timeZoneId,
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
   * Set deactivated flag, purge personal data.
   *
   * @todo Delete avatar image!
   * @global \MovLib\Data\Session $session
   * @return this
   * @throws \MovLib\Data\DatabaseException
   */
  public function deactivate() {
    global $session;
    $sessions = $session->getActiveSessions();
    DelayedMethodCalls::stack($session, "delete", array_column($sessions, "session_id"));
    return $this
      ->query(
        "UPDATE `users` SET
          `private`           = false,
          `deactivated`       = true,
          `time_zone_id`      = ?,
          `dyn_profile`       = '',
          `sex`               = 0,
          `country_id`        = NULL,
          `real_name`         = NULL,
          `birthday`          = NULL,
          `website`           = NULL,
          `facebook`          = NULL,
          `google_plus`       = NULL,
          `twitter`           = NULL,
          `avatar_extension`  = NULL,
          `avatar_name`       = NULL,
          `avatar_changed`    = NULL
        WHERE `user_id` = ?",
        "sd",
        [ ini_get("date.timezone"), $this->id ]
      )
    ;
  }

  /**
   * Get the user's country code.
   *
   * @return null|string
   *   The user's country code, or <code>NULL</code> if the user has no country set.
   * @throws \MovLib\Data\DatabaseException
   */
  public function getCountryCode() {
    if ($this->countryId) {
      return $this->select("SELECT `iso_alpha-2` FROM `countries` WHERE `country_id` = ? LIMIT 1", "i", [ $this->countryId ])[0]["iso_alpha-2"];
    }
  }

  /**
   * @inheritdoc
   */
  public function getImageStyle($style, array $attributes) {
    $extension            = image_type_to_extension($this->imageType);
    $attributes["height"] = $attributes["width"] = $this->span[$style];
    $style                = $style === self::IMAGE_STYLE_MEDIUM ? "" : ".{$style}";
    $attributes["src"]    = "{$GLOBALS["movlib"]["static_domain"]}user/{$this->imageName}{$style}{$extension}?c={$this->imageChanged}";
    return $attributes;
  }

  /**
   * Get the user's preferred locale.
   *
   * @return string
   *   The user's preferred locale.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getLocale() {
    return $GLOBALS["movlib"]["locales"][$this->systemLanguageCode];
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
    return trim(shell_exec("pwgen -cnBv 20 1"));
  }

  /**
   * Prepare registration data.
   *
   * @return string
   *   The key of the temporary table record.
   */
  public function prepareRegistration() {
    return $this->tmpSet([ "name" => $this->name, "email" => $this->email ]);
  }

  /**
   * Reactivate a deactivated account.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function reactivate() {
    return $this->query("UPDATE `users` SET `deactivated` = false WHERE `user_id` = ?", "d", [ $this->id ]);
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
   * @param string $name
   *   The valid unique user's name.
   * @param string $email
   *   The valid unique user's email address.
   * @param string $rawPassword
   *   The unhashed user's password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function register($name, $email, $rawPassword) {
    $this->query(
      "INSERT INTO `users` (`avatar_name`, `dyn_profile`, `email`, `name`, `password`, `system_language_code`) VALUES (?, '', ?, ?, ?, ?)",
      "sssss",
      [ $this->filename($name), $email, $name, password_hash($rawPassword, PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"] ]), $_SERVER["LANGUAGE_CODE"] ]
    );
    $this->email = $email;
    $this->id    = $this->getInsertID();
    $this->name  = $name;
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
    $this->email = $newEmail;
    return $this->query("UPDATE `users` SET `email` = ? WHERE `user_id` = ?", "sd", [ $this->email, $this->id ]);
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
    return $this->query("UPDATE `users` SET `password` = ? WHERE `user_id` = ?", "sd", [ password_hash($rawPassword, PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"] ]), $this->id ]);
  }

  /**
   * Helper method to validate a user submitted authentication token and retrieve the associated data from the temporary
   * database table.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param null $errors
   *   The errors variable used to collect validation error messages.
   * @return null|array
   *   <code>NULL</code> is returned if no data could be retrieved from the database, otherwise an associative array
   *   with the data from the temporary database table. Please check implementation to check the anatomy of the returned
   *   array.
   */
  public function validateToken(&$errors) {
    global $i18n;
    if (empty($_GET["token"]) || strlen($_GET["token"]) !== self::AUTHENTICATION_TOKEN_LENGTH) {
      $errors[] = $i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.");
    }
    elseif ($data = $this->tmpGetAndDelete($_GET["token"])) {
      return $data;
    }
    $errors[] = $i18n->t("Your authentication token has expired, please fill out the form again.");
  }

  /**
   * @inheritdoc
   */
  public function generateImageStyles($source, $type) {
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function moveUploadedImage($source, $type, $style = null){
    $this->query(
      "UPDATE `users` SET `avatar_changed` = CURRENT_TIMESTAMP, `avatar_type` = ? WHERE `user_id` = ?",
      "sd",
      [ $type, $this->id ]
    );
    return parent::moveUploaded($source, $type, "user/{$this->avatarName}", $style);
  }

}
