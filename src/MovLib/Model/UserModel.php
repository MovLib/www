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
use \MovLib\Model\AbstractImageModel;
use \MovLib\Utility\String;
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
class UserModel extends AbstractImageModel {


  // ------------------------------------------------------------------------------------------------------------------- Constants


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
  const FROM_MAIL = "mail";

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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  private $types = [
    self::FROM_ID => "d",
    self::FROM_MAIL => "s",
    self::FROM_NAME => "s",
  ];

  /**
   * The user's unique ID, defaults to zero (anonymous user).
   *
   * @var int
   */
  public $userId;

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
  public $mail;

  /**
   * The user's hashed password.
   *
   * @var string
   */
  public $pass;

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
   * The mail the user originally registered the account.
   *
   * @var string
   */
  public $init;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user model object.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty user model will be instanciated.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n instance.
   * @param string $from
   *   [Optional] Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value
   *   [Optional] Data to identify the user, see the various <var>FROM_*</var> class constants.
   * @throws \MovLib\Exception\UserException
   *   If no user could be found for the given <var>$value</var> (if <var>$value</var> is not <code>NULL</code>).
   */
  public function __construct($from = null, $value = null) {
    global $i18n;
    if (!empty($from) && !empty($value)) {
      $result = $this->select(
        "SELECT
          `user_id` AS `userId`,
          `language_id` AS `languageId`,
          `name`,
          `mail`,
          `pass`,
          UNIX_TIMESTAMP(`created`) AS `created`,
          UNIX_TIMESTAMP(`access`) AS `access`,
          UNIX_TIMESTAMP(`login`) AS `login`,
          `private`,
          `deleted`,
          `timezone`,
          `init`,
          `edits`,
          COLUMN_GET(`dyn_profile`, '{$i18n->languageCode}' AS BINARY) AS `profile`,
          `sex`,
          `country_id` AS `countryId`,
          `real_name` AS `realName`,
          `birthday`,
          `website`,
          `avatar_extension` AS `imageExtension`,
          `avatar_hash` AS `imageHash`
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
      $this->initImage(String::convertToRoute($this->name), [
        new ResizeImageStyle(self::IMAGESTYLE_SMALL),
        new ResizeImageStyle(self::IMAGESTYLE_NORMAL),
        new ResizeImageStyle(self::IMAGESTYLE_BIG),
      ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Update the user model in the database with the data of the current class instance.
   *
   * @todo Update profile text as well!
   * @return this
   */
  public function commit() {
    return $this->prepareAndBind(
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
        `avatar_hash` = ?
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
        $this->userId
      ]
    )->execute()->close();;
  }

  /**
   * Check if a user with the given mail exists.
   *
   * @param string $mail
   *   The mail to search for.
   * @return boolean
   *   <code>TRUE</code> if a user exists with this mail, otherwise <code>FALSE</code>.
   */
  public function existsMail($mail) {
    return !empty($this->select("SELECT `user_id` FROM `users` WHERE `mail` = ? OR `init` = ? LIMIT 1", "ss", [ $mail, $mail ]));
  }

  /**
   * Check if a user with the given name exists.
   *
   * @param string $name
   *   The name to search for.
   * @return boolean
   *   <code>TRUE</code> if a user exists with this name, otherwise <code>FALSE</code>.
   */
  public function existsName($name) {
    return !empty($this->select("SELECT `user_id` FROM `users` WHERE `name` = ? LIMIT 1", "s", [ $name ]));
  }

  /**
   * Get the user's preferred ISO 639-1 alpha-2 language code.
   *
   * @staticvar string $languageCode
   *   Used to cache the lookup.
   * @return null|string
   *   The user's language code or <code>NULL</code> if the user has none.
   */
  public function getLanguageCode() {
    static $languageCode = null;
    try {
      if ($languageCode === null) {
        $languageCode = $this->select(
          "SELECT `iso_alpha-2` FROM `languages` WHERE `language_id` = ? LIMIT 1",
          "i",
          $this->languageId
        )[0]["iso_alpha-2"];
      }
      return $languageCode;
    } catch (ErrorException $e) {
      return null;
    }
  }

  /**
   * Pre-register a new user account.
   *
   * The pre-registration step is to put the provided information into our temporary database where we can pull it out
   * after the user clicked the link in the mail we've sent him.
   *
   * @param string $hash
   *   The activation hash used in the activation link for identification.
   * @param string $name
   *   The valid name of the new user.
   * @param string $mail
   *   The valid mail of the new user.
   */
  public function preRegister($hash, $name, $mail) {
    // @todo Catch exceptions, log and maybe even send a mail to the user?
    $this->prepareAndBind(
      "INSERT INTO `tmp` (`key`, `dyn_data`) VALUES (?, COLUMN_CREATE('name', ?, 'mail', ?, 'time', NOW() + 0))",
      "sss",
      [ $hash, $name, $mail ]
    )->execute()->close();
  }

  /**
   * Register a new user account.
   *
   * After the user clicked the activation link for the account we finally are able to creat an account for her/him. The
   * validation process is something the model does not care about, this is handled by the presenter, who's also
   * responsible to display the correct error messages. This method simply inserts the new data. Note that the object
   * this method is called on will automatically became the user that was just registered. Think of it like passing
   * the variable by reference. So if you call this on the global user object, the formerly anonymous global user is
   * now the registered new user. This is the desired behavior during our registration process because we directly
   * want to display the password settings page within the user's account.
   *
   * @param string $name
   *   The valid unique user's name.
   * @param string $mail
   *   The valid unique user's mail.
   * @param string $pass
   *   The unhashed user's password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   *   If inserting the data into our database failed.
   * @throws \MovLib\Exception\SessionException
   *   If starting the session failed. This should not be catched, this failure is related to Memcached being
   *   unavailable or full. Something that we cannot recover at runtime.
   */
  public function register($name, $mail, $pass) {
    global $i18n;
    $this->languageId = $i18n->getLanguageId();
    $this->name       = $name;
    $this->mail       = $mail;
    $this->pass       = password_hash($pass, PASSWORD_BCRYPT);
    $this->deleted    = false;
    $this->timezone   = ini_get("date.timezone");
    $this->prepareAndBind(
      "INSERT INTO `users`
        (`language_id`, `name`, `mail`, `pass`, `created`, `login`, `timezone`, `init`, `dyn_profile`)
      VALUES
        (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, ?, ?, '')",
      "dsssss",
      [ $this->languageId, $this->name, $this->mail, $this->pass, $this->timezone, $this->mail ]
    )->execute();
    $this->userId = $this->stmt->insert_id;
    return $this->close();
  }

  /**
   * Add reset password request to our temporary database table.
   *
   * @param string $hash
   *   The password reset hash used in the password reset link for identification.
   * @param string $mail
   *   The valid mail of the user.
   */
  public function preResetPassword($hash, $mail) {
    // @todo Catch exceptions, log and maybe even send a mail to the user?
    $this->prepareAndBind(
      "INSERT INTO `tmp` (`key`, `dyn_data`) VALUES (?, COLUMN_CREATE('mail', ?, 'time', NOW() + 0))",
      "ss",
      [ $hash, $mail ]
    )->execute()->close();
  }

  /**
   * Select the data that was previously stored and directly delete it.
   *
   * @param string $hash
   *   The user submitted hash to identify the reset password request.
   * @return null|array
   *   <code>NULL</code> if no record was found for the hash, otherwise an associative array with the following keys:
   *   <i>name</i>, <i>mail</i>, and <i>time</i>. The name might be <code>NULL</code>, depending on the data that
   *   was stored previously (e.g. reset password requests do not have the name).
   */
  public function selectAndDeleteTemporaryData($hash) {
    try {
      // Fetch the data from our temporary table.
      $data = $this->select(
        "SELECT
          COLUMN_GET(`dyn_data`, 'name' AS CHAR(" . self::NAME_MAX_LENGTH . ")) AS `name`,
          COLUMN_GET(`dyn_data`, 'mail' AS CHAR(" . self::MAIL_MAX_LENGTH . ")) AS `mail`,
          COLUMN_GET(`dyn_data`, 'time' AS UNSIGNED) AS `time`
        FROM `tmp`
          WHERE `key` = ?
        LIMIT 1", "s", [ $hash ]
      )[0];
      // Now directly delete it. Any of the temporary user data is only valid for one time usage.
      $this->delete("tmp", "s", [ "key" => $hash ]);
      return $data;
    } catch (ErrorException $e) {
      return null;
    }
  }

  /**
   * Validate the user submitted password against the stored hash of the current user.
   *
   * <b>IMPORTANT!</b> Only validates against a password that was submitted via <var>$_POST</var>. Additionally the
   * key must be <code>pass</code> within the <var>$_POST</var> array.
   *
   * @return this
   * @throws \MovLib\Exception\UserException
   *   If the password isn't valid.
   */
  public function validatePassword() {
    if (!isset($_POST["pass"]) || password_verify($_POST["pass"], $this->pass) === false) {
      throw new UserException("The submitted password is invalid or empty.");
    }
    return $this;
  }

}
