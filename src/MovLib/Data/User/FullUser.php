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
use \MovLib\Presentation\Error\NotFound;

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
class FullUser extends \MovLib\Data\User\User {


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
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the user, see the various <var>FROM_*</var> class constants.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($from = null, $value = null) {
    global $db, $i18n;

    if ($from && $value) {
      $stmt = $db->query(
        "SELECT
          `id`,
          `name`,
          UNIX_TIMESTAMP(`access`),
          `birthdate`,
          `country_code`,
          UNIX_TIMESTAMP(`created`),
          `currency_code`,
          COLUMN_GET(`dyn_about_me`, '{$i18n->languageCode}' AS BINARY),
          `edits`,
          `email`,
          UNIX_TIMESTAMP(`image_changed`),
          `image_extension`,
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
        $this->changed,
        $this->extension,
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
        throw new NotFound;
      }
      $stmt->close();
    }

    if ($this->id) {
      $this->init();
      $this->private = (boolean) $this->private;
      if (!$this->currencyCode) {
        $this->currencyCode = Currency::getDefaultCode();
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Check if this email address is already in use.
   *
   * @global \MovLib\Data\Database $db
   * @param string $email
   *   The email address to look up.
   * @return boolean
   *   <code>TRUE</code> if this email address is already in use, otherwise <code>FALSE</code>.
   */
  public function checkEmail($email) {
    global $db;
    return !empty($db->query("SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ])->get_result()->fetch_row());
  }

  /**
   * Check if this name is already in use.
   *
   * @global \MovLib\Data\Database $db
   * @param string $username
   *   The username to look up.
   * @return boolean
   *   <code>TRUE</code> if this name is already in use, otherwise <code>FALSE</code>.
   */
  public function checkName($username) {
    global $db;
    return !empty($db->query("SELECT `id` FROM `users` WHERE `name` = ? LIMIT 1", "s", [ $username ])->get_result()->fetch_row());
  }

  /**
   * Update the user model in the database with the data of the current class instance.
   *
   * @global \MovLib\Data\Database $db
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function commit() {
    global $db, $i18n;
    $db->query(
      "UPDATE `users` SET
        `birthdate`            = ?,
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
        $i18n->languageCode, $this->aboutMe,
        $this->changed,
        $this->extension,
        $this->private,
        $this->realName,
        $this->sex,
        $this->systemLanguageCode,
        $this->timeZoneIdentifier,
        $this->website,
        $this->id,
      ]
    );
    return $this;
  }

  /**
   * Delete this user.
   *
   * @todo   Delete avatar.
   *
   * @global \MovLib\Data\Database $db
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function deleteAccount() {
    global $db;

    $this->deleteAvatar();

    $db->query(
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
        `country_code`         = NULL,
        `birthdate`            = NULL,
        `image_changed`        = NULL,
        `image_extension`      = NULL,
        `real_name`            = NULL,
        `reputation`           = NULL,
        `website`              = NULL
      WHERE `id` = ?",
      "d",
      [ $this->id ]
    );

    return $this;
  }

  /**
   * Delete the user's avatar image and all styles of it.
   *
   * @global \MovLib\Data\User\Session $session
   * @return this
   */
  public function deleteAvatar() {
    global $session;
    if ($this->imageExists == true) {
      foreach ([ self::STYLE_SPAN_02, self::STYLE_SPAN_01, self::STYLE_HEADER_USER_NAVIGATION ] as $style) {
        try {
          $path = $this->getPath($style);
          unlink($path);
        }
        catch (\ErrorException $e) {
          error_log("Couldn't delete '{$path}'.");
        }
      }
      $this->imageExists        = false;
      $this->changed       = $this->extension = null;
      $session->userAvatar = $this->getStyle(self::STYLE_HEADER_USER_NAVIGATION);
    }
    return $this;
  }

  /**
   * Get the user's total collection count.
   *
   * @todo We should propably save this within the user table.
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The user's total collection count.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getTotalCollectionCount() {
    global $db;
    return $db->query("SELECT COUNT(*) FROM `users_collections` WHERE `user_id` = ? LIMIT 1", "d", [ $this->id ])->get_result()->fetch_row()[0];
  }

  /**
   * Get the user's total count of movie ratings.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The user's total count of movie ratings.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getTotalRatingsCount() {
    global $db;
    return $db->query("SELECT COUNT(*) FROM `movies_ratings` WHERE `user_id` = ? LIMIT 1", "d", [ $this->id ])->get_result()->fetch_row()[0];
  }

  /**
   * Get the user's total count of all uploaded images.
   *
   * @todo We should propably save this within the user table.
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The user's total count of all uploaded images.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getTotalUploadsCount() {
    global $db;
    return $db->query(
      "SELECT
      (SELECT COUNT(*) FROM `posters` WHERE `uploader_id` = ?)
      +
      (SELECT COUNT(*) FROM `lobby_cards` WHERE `uploader_id` = ?)
      +
      (SELECT COUNT(*) FROM `backdrops` WHERE `uploader_id` = ?)
      +
      (SELECT COUNT(*) FROM `persons` WHERE `image_uploader_id` = ?)
      +
      (SELECT COUNT(*) FROM `companies` WHERE `image_uploader_id` = ?)",
      "ddddd",
      [ $this->id, $this->id, $this->id, $this->id, $this->id ]
    )->get_result()->fetch_row()[0];
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
   * Join MovLib.
   *
   * After the user clicked the activation link for the account, we are finally able to create an account for her/him.
   * The validation process is something the model does not care about, this is handled by the presenter, who's also
   * responsible to display the correct error messages. This method simply inserts the new data. Note that the object
   * this method is called on will automatically become the user that just joined. Think of it like passing the
   * variable by reference. So if you call this on the global user object, the formerly anonymous global user is now the
   * newly joined user. This is the desired behavior during our joining process, because we want to display the password
   * settings page within the user's account directly.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function join() {
    global $db, $i18n;
    $stmt = $db->query(
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
   * @global \MovLib\Data\Database $db
   * @param string $email
   *   The new email address.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function updateEmail($email) {
    global $db;
    $db->query("UPDATE `users` SET `email` = ? WHERE `id` = ?", "sd", [ $email, $this->id ]);
    $this->email = $email;
    return $this;
  }

  /**
   * Change the user's password.
   *
   * @global \MovLib\Data\Database $db
   * @param string $password
   *   The new hashed password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function updatePassword($password) {
    global $db;
    return $db->query("UPDATE `users` SET `password` = ? WHERE `id` = ?", "sd", [ $password, $this->id ]);
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
