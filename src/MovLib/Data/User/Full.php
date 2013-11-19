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

use \MovLib\Data\Currency;
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
   * The user's profile text (in the current display language if available).
   *
   * @var null|string
   */
  public $aboutMe;

  /**
   * The user's last access (UNIX timestamp).
   *
   * @var null|integer
   */
  public $access;

  /**
   * The user's birthday (date).
   *
   * @var null|string
   */
  public $birthday;

  /**
   * The user's unique country code.
   *
   * @var null|string
   */
  public $countryCode;

  /**
   * The user's creation time (UNIX timestamp).
   *
   * @var null|integer
   */
  public $created;

  /**
   * The user's currency code.
   *
   * @var string
   */
  public $currencyCode;

  /**
   * The user's edit counter.
   *
   * @var null|integer
   */
  public $edits;

  /**
   * The user's unique mail if logged in.
   *
   * @var null|string
   */
  public $email;

  /**
   * The user's hashed password.
   *
   * @var null|string
   */
  public $password;

  /**
   * Flag defining if the user's personal data is private or not.
   *
   * @var null|boolean
   */
  public $private;

  /**
   * Total amount of profile views.
   *
   * @var null|integer
   */
  public $profileViews;

  /**
   * The user's real name.
   *
   * @var null|string
   */
  public $realName;

  /**
   * The user's reputation counter.
   *
   * @var null|integer
   */
  public $reputation;

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
   * @var null|integer
   */
  public $sex;

  /**
   * The user's preferred system language's code (e.g. <code>"en"</code>).
   *
   * @var null|string
   */
  public $systemLanguageCode;

  /**
   * The user's time zone ID (e.g. <code>"Europe/Vienna"</code>).
   *
   * @var null|string
   */
  public $timeZoneIdentifier;

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
          `name`,
          UNIX_TIMESTAMP(`access`),
          `birthday`,
          `country_code`,
          UNIX_TIMESTAMP(`created`),
          `currency_code`,
          COLUMN_GET(`dyn_about_me`, '{$i18n->languageCode}' AS BINARY),
          `edits`,
          `email`,
          UNIX_TIMESTAMP(`image_changed`),
          `image_extension`,
          `image_changed` IS NOT NULL,
          `password`,
          `private`,
          `profile_views`,
          `real_name`,
          `reputation`,
          `sex`,
          `system_language_code`,
          `time_zone_identifier`,
          `website`
        FROM `users`
        WHERE `{$from}` = ?",
        $this->types[$from],
        [ $value ]
      );
      $stmt->bind_result(
        $this->id,
        $this->name,
        $this->access,
        $this->birthday,
        $this->countryCode,
        $this->created,
        $this->currencyCode,
        $this->aboutMe,
        $this->edits,
        $this->email,
        $this->imageChanged,
        $this->imageExtension,
        $this->imageExists,
        $this->password,
        $this->private,
        $this->profileViews,
        $this->realName,
        $this->reputation,
        $this->sex,
        $this->systemLanguageCode,
        $this->timeZoneIdentifier,
        $this->website
      );
      if (!$stmt->fetch()) {
        throw new UserException("Couldn't find user for {$from} '{$value}'.");
      }
      $stmt->close();
      $this->imageExists = (boolean) $this->imageExists;
      $this->imageName   = rawurlencode($this->name);
      $this->private     = (boolean) $this->private;
      // The image name already has all unsave characters removed.
      $this->route       = $i18n->r("/user/{0}", [ rawurlencode($this->imageName) ]);
      if (!$this->currencyCode) {
        $this->currencyCode = Currency::getDefaultCode();
      }
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
        `birthday`             = ?,
        `country_code`         = ?,
        `currency_code`        = ?,
        `dyn_about_me`         = COLUMN_ADD(`dyn_about_me`, ?, ?),
        `image_changed`        = FROM_UNIXTIME(?),
        `image_extension`      = ?,
        `private`              = ?,
        `real_name`            = ?,
        `sex`                  = ?,
        `system_language_code` = ?,
        `time_zone_identifier` = ?,
        `website`              = ?
      WHERE `id` = ?
        LIMIT 1",
      "sisssisisisssd",
      [
        $this->birthday,
        $this->countryCode,
        $this->currencyCode,
        $i18n->languageCode,
        $this->aboutMe,
        $this->imageChanged,
        $this->imageExtension,
        $this->private,
        $this->realName,
        $this->sex,
        $this->systemLanguageCode,
        $this->timeZoneIdentifier,
        $this->website,
        $this->id,
      ]
    );
  }

  /**
   * Delete this user.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete() {
    return $this
      ->deleteImage()
      ->query(
        "UPDATE `users` SET
          `email`                = NULL,
          `password`             = NULL,
          `access`               = NULL,
          `created`              = NULL,
          `currency_code`        = NULL,
          `dyn_about_me`         = NULL,
          `edits`                = NULL,
          `private`              = NULL,
          `profile_views`        = NULL,
          `sex`                  = NULL,
          `system_language_code` = NULL,
          `time_zone_identifier` = NULL,
          `country_id`           = NULL,
          `birthday`             = NULL,
          `image_changed`        = NULL,
          `image_extension`      = NULL,
          `real_name`            = NULL,
          `reputation`           = NULL,
          `website`              = NULL
        WHERE `id` = ?",
        "d",
        [ $this->id ]
      )
    ;
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
      "INSERT INTO `users` (`dyn_about_me`, `email`, `name`, `password`, `system_language_code`) VALUES ('', ?, ?, ?, ?)",
      "ssss",
      [ $this->email, $this->name, $this->password, $i18n->languageCode ]
    );
    $this->id = $stmt->insert_id;
    return $this;
  }

  /**
   * Change the user's email address.
   *
   * @param string $email
   *   The new email address.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function updateEmail($email) {
    return $this->query("UPDATE `users` SET `email` = ? WHERE `id` = ?", "sd", [ $email, $this->id ]);
  }

  /**
   * Change the user's password.
   *
   * @param string $password
   *   The new hashed password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
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
