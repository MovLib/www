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
namespace MovLib\Data\User;

use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Exception\UserException;

/**
 * Extended user.
 *
 * The extended user class provides all properties available for a user and several methods for interacting with this
 * data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Full extends \MovLib\Data\User\User {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Maximum attempts for actions like registration, login, etc..
   *
   * @var int
   */
  const MAXIMUM_ATTEMPTS = 5;


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
          `user_id`,
          `system_language_code`,
          `name`,
          `email`,
          UNIX_TIMESTAMP(`created`),
          UNIX_TIMESTAMP(`access`),
          UNIX_TIMESTAMP(`login`),
          `private`,
          `deactivated`,
          `time_zone_id`,
          `edits`,
          COLUMN_GET(`dyn_profile`, '{$i18n->languageCode}' AS BINARY),
          `sex`,
          `country_id`,
          `real_name`,
          `birthday`,
          `website`,
          `avatar_name`,
          UNIX_TIMESTAMP(`avatar_changed`),
          `avatar_extension`,
          `avatar_changed` IS NOT NULL
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
        $this->imageName,
        $this->imageChanged,
        $this->imageExtension,
        $this->imageExists
      );
      if (!$stmt->fetch()) {
        throw new UserException("Could not find user for {$from} '{$value}'!");
      }
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
   * @return boolean
   *   <code>TRUE</code> if this email address is already in use, otherwise <code>FALSE</code>.
   */
  public function checkEmail() {
    return !empty($this->query("SELECT `user_id` FROM `users` WHERE `email` = ?", "s", [ $this->email ])->get_result()->fetch_row());
  }

  /**
   * Check if this name is already in use.
   *
   * @return boolean
   *   <code>TRUE</code> if this name is already in use, otherwise <code>FALSE</code>.
   */
  public function checkName() {
    return !empty($this->query("SELECT `user_id` FROM `users` WHERE `name` = ?", "s", [ $this->name ])->get_result()->fetch_row());
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
        `country_id`           = ?,
        `dyn_profile`          = COLUMN_ADD(`dyn_profile`, ?, ?),
        `facebook`             = ?,
        `google_plus`          = ?,
        `private`              = ?,
        `real_name`            = ?,
        `sex`                  = ?,
        `system_language_code` = ?,
        `time_zone_id`         = ?,
        `twitter`              = ?,
        `website`              = ?
      WHERE `user_id`          = ?
        LIMIT 1",
      "sissssisissssd",
      [
        $this->birthday,
        $this->countryId,
        $i18n->languageCode,
        $this->profile,
        null, // Facebook
        null, // Google Plus
        $this->private,
        $this->realName,
        $this->sex,
        $this->systemLanguageCode,
        $this->timeZoneId,
        null, // Twitter
        $this->website,
        $this->id,
      ]
    );
  }

  /**
   * Set deactivated flag, purge personal data.
   *
   * @todo Delete avatar image!
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Data\DatabaseException
   */
  public function deactivate() {
    global $session;
    $sessions = $session->getActiveSessions();
    DelayedMethodCalls::stack($session, "delete", array_column($sessions, "session_id"));
    $this->deleteImage();
    $this->query(
      "UPDATE `users` SET
        `avatar_changed`    = NULL,
        `avatar_extension`  = NULL,
        `birthday`          = NULL,
        `country_id`        = NULL,
        `deactivated`       = true,
        `dyn_profile`       = '',
        `facebook`          = NULL,
        `google_plus`       = NULL,
        `private`           = false,
        `real_name`         = NULL,
        `sex`               = 0,
        `time_zone_id`      = ?,
        `twitter`           = NULL,
        `website`           = NULL
      WHERE `user_id` = ?",
      "sd",
      [ ini_get("date.timezone"), $this->id ]
    );
    return $this;
  }

  /**
   * @inheritdoc
   * @internal
   *   No need to store this data in our database, the dimensions and paths are fixed and never change. Plus creating
   *   the src and grabbing the height and width is ultra fast.
   */
  public function getImageStyleAttributes($style, array &$attributes = []) {
    $attributes["height"] = $attributes["width"] = $style;
    $attributes["src"]    = "{$GLOBALS["movlib"]["static_domain"]}{$this->imageDirectory}/{$this->imageName}.{$style}.{$this->imageExtension}?c={$this->imageChanged}";
    return $attributes;
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
   * Get the <var>$rawPassword</var> hash.
   *
   * @param string $rawPassword
   *   The user supplied raw password.
   * @return string
   *   The <var>$rawPassword</var> hash.
   */
  public function passwordHash($rawPassword) {
    return password_hash($rawPassword, PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"] ]);
  }

  /**
   * Prepare registration data.
   *
   * A user exception is thrown if too many registration attempts were made with this email address in the past 24 hours.
   *
   * @param string $rawPassword
   *   The user supplied raw password.
   * @return string
   *   The key of the temporary table record.
   * @throws \MovLib\Exception\UserException
   */
  public function prepareRegistration($rawPassword) {
    $password    = $this->passwordHash($rawPassword);
    $key         = "registration-{$this->email}";
    $result      = $this->query("SELECT DATEDIFF(CURRENT_TIMESTAMP, `created`) AS `created`, `data` FROM `tmp` WHERE `key` = ? LIMIT 1", "s", [ $key ])->get_result()->fetch_assoc();
    if ($result) {
      $data             = unserialize($result["data"]);
      $data["password"] = $password;
      if ($result["created"] > 0) {
        $data["attempts"] = 0;
      }
      elseif ($data["attempts"] > self::MAXIMUM_ATTEMPTS) {
        throw new UserException("Too many registration attempts from this email address.");
      }
      $data["attempts"]++;
      $this->query("UPDATE `tmp` SET `data` = ? WHERE `key` = ?", "ss", [ serialize($data), $key ]);
    }
    else {
      $this->query("INSERT INTO `tmp` (`data`, `key`, `ttl`) VALUES (?, ?, ?)", "sss", [ serialize([
        "attempts" => 1,
        "email"    => $this->email,
        "name"     => $this->name,
        "password" => $password,
      ]), $key, self::TMP_TTL_DAILY ]);
    }
    return $this;
  }

  /**
   * Get previously stored registration data.
   *
   * @return null|array
   *   Array containing the registration data, or <code>NULL</code> if no data was found.
   * @throws \MovLib\Exception\UserException
   */
  public function getRegistrationData() {
    $result = $this->query("SELECT DATEDIFF(CURRENT_TIMESTAMP, `created`) AS `created`, `data` FROM `tmp` WHERE `key` = ? LIMIT 1", "s", [ "registration-{$this->email}" ])->get_result()->fetch_assoc();
    if (!$result || $result["created"] > 0) {
      throw new UserException("No data found for this user.");
    }
    $data        = unserialize($result["data"]);
    $this->name  = $data["name"];
    $this->email = $data["email"];
    return $data;
  }

  /**
   * Reactivate a deactivated account.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function reactivate() {
    $this->deactivated = false;
    $this->query("UPDATE `users` SET `deactivated` = false WHERE `user_id` = ?", "d", [ $this->id ]);
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
   * @param string $password
   *   The user's hashed password.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function register($password) {
    global $i18n;
    $this->query("DELETE FROM `tmp` WHERE `key` = ?", "s", [ "registration-{$this->email}" ]);
    $stmt = $this->query(
      "INSERT INTO `users` (`avatar_name`, `dyn_profile`, `email`, `name`, `password`, `system_language_code`) VALUES (?, '', ?, ?, ?, ?)",
      "sssss",
      [ $this->filename(html_entity_decode($this->name, ENT_QUOTES|ENT_HTML5)), $this->email, $this->name, $password, $i18n->languageCode ]
    );
    $this->id = $stmt->insert_id;
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
    return $this->query("UPDATE `users` SET `password` = ? WHERE `user_id` = ?", "sd", [ $this->passwordHash($rawPassword), $this->id ]);
  }

}