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
namespace MovLib\Data\User;

use \MovLib\Exception\UserException;

/**
 * Extended user.
 *
 * The extended user class provides all properties available for a user and several methods for interacting with this
 * data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Full extends \MovLib\Data\User\User {


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
   * The user's last login (UNIX timestamp).
   *
   * @var int
   */
  public $login;

  /**
   * The user's hashed password.
   *
   * @var string
   */
  public $password;

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
   * The user's website.
   *
   * @var null|string
   */
  public $website;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty user model will be created.
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
    if ($from && $value) {
      $stmt = $this->query(
        "SELECT
          `id`,
          `systemLanguageCode`,
          `name`,
          `email`,
          UNIX_TIMESTAMP(`created`),
          UNIX_TIMESTAMP(`access`),
          UNIX_TIMESTAMP(`login`),
          `password`,
          `private`,
          `deactivated`,
          `timeZoneId`,
          `edits`,
          COLUMN_GET(`profile`, '{$i18n->languageCode}' AS BINARY),
          `sex`,
          `countryId`,
          `realName`,
          `birthday`,
          `website`,
          UNIX_TIMESTAMP(`imageChanged`),
          `imageExtension`,
          `imageChanged` IS NOT NULL
        FROM `users`
        WHERE `{$from}` = ?",
        $this->types[$from],
        [ $value ]
      );
      $stmt->bind_result(
        $this->id,
        $this->systemLanguageCode,
        $this->name,
        $this->email,
        $this->created,
        $this->access,
        $this->login,
        $this->password,
        $this->private,
        $this->deactivated,
        $this->timeZoneId,
        $this->edits,
        $this->profile,
        $this->sex,
        $this->countryId,
        $this->realName,
        $this->birthday,
        $this->website,
        $this->imageChanged,
        $this->imageExtension,
        $this->imageExists
      );
      if (!$stmt->fetch()) {
        throw new UserException("Couldn't find user for {$from} '{$value}'.");
      }
      $stmt->close();
      $this->imageExists = (boolean) $this->imageExists;
      $this->imageName   = rawurlencode($this->name);
      $this->private     = (boolean) $this->private;
      $this->deactivated = (boolean) $this->deactivated;
      // The image name already has all unsave characters removed.
      $this->route       = $i18n->r("/user/{0}", [ rawurlencode($this->imageName) ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Check if this email address is already in use.
   *
   * @param string $email
   *   The email address to look up.
   * @return boolean
   *   <code>TRUE</code> if this email address is already in use, otherwise <code>FALSE</code>.
   */
  public function checkEmail($email) {
    return !empty($this->query("SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ])->get_result()->fetch_row());
  }

  /**
   * Check if this name is already in use.
   *
   * @param string $username
   *   The username to look up.
   * @return boolean
   *   <code>TRUE</code> if this name is already in use, otherwise <code>FALSE</code>.
   */
  public function checkName($username) {
    return !empty($this->query("SELECT `id` FROM `users` WHERE `name` = ? LIMIT 1", "s", [ $username ])->get_result()->fetch_row());
  }

  /**
   * Update the user model in the database with the data of the current class instance.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function commit() {
    global $i18n;
    return $this->query(
      "UPDATE `users` SET
        `birthday`           = ?,
        `countryId`          = ?,
        `imageChanged`       = FROM_UNIXTIME(?),
        `imageExtension`     = ?,
        `private`            = ?,
        `profile`            = COLUMN_ADD(`profile`, ?, ?),
        `realName`           = ?,
        `sex`                = ?,
        `systemLanguageCode` = ?,
        `timeZoneId`         = ?,
        `website`            = ?
      WHERE `id` = ?
        LIMIT 1",
      "sississsisssd",
      [
        $this->birthday,
        $this->countryId,
        $this->imageChanged,
        $this->imageExtension,
        $this->private,
        $i18n->languageCode,
        $this->profile,
        $this->realName,
        $this->sex,
        $this->systemLanguageCode,
        $this->timeZoneId,
        $this->website,
        $this->id,
      ]
    );
  }

  /**
   * Get the <var>$rawPassword</var> hash.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $rawPassword
   *   The user supplied raw password.
   * @return string
   *   The <var>$rawPassword</var> hash.
   */
  public function hashPassword($rawPassword) {
    global $kernel;
    return password_hash($rawPassword, PASSWORD_DEFAULT, $kernel->passwordOptions);
  }

  /**
   * Reactivate a deactivated account.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function reactivate() {
    $this->deactivated = false;
    $this->query("UPDATE `users` SET `deactivated` = false WHERE `id` = ?", "d", [ $this->id ]);
    return $this;
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
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function register() {
    global $i18n;
    $stmt = $this->query(
      "INSERT INTO `users` (`profile`, `email`, `name`, `password`, `systemLanguageCode`) VALUES ('', ?, ?, ?, ?)",
      "ssss",
      [ $this->email, $this->name, $this->password, $i18n->languageCode ]
    );
    $this->id = $stmt->insert_id;
    return $this;
  }

  /**
   * Change the user's password.
   *
   * @param string $password
   *   The new hashed password.
   * @return this
   */
  public function updatePassword($password) {
    return $this->query("UPDATE `users` SET `password` = ? WHERE `id` = ?", "sd", [ $password, $this->id ]);
  }

  /**
   * Verify the user's password.
   *
   * @param string $rawPassword
   *   The user supplied raw password.
   * @return boolean
   *   Returns <code>TRUE</code> if the password and hash match, or <code>FALSE</code> otherwise.
   */
  public function verifyPassword($rawPassword) {
    return password_verify($rawPassword, $this->password);
  }

}
