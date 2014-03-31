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

use \MovLib\Data\Date;
use \MovLib\Exception\ClientException\GoneException;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the user entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class User extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Init user via unique identifier.
   *
   * @var string
   */
  const FROM_ID = "id";

  /**
   * Init user via name.
   *
   * @var string
   */
  const FROM_NAME = "name";

  /**
   * Init user via email
   *
   * @var string
   */
  const FROM_EMAIL = "email";

  /**
   * Maximum attempts for actions like signing in, reseting password, ...
   *
   * @var integer
   */
  const MAXIMUM_ATTEMPTS = 5;

  /**
   * Maximum username length (chracter count, not bytes).
   *
   * @var integer
   */
  const NAME_MAXIMUM_LENGTH = 40;

  /**
   * Characters which aren't allowed within a username.
   *
   * @var string
   */
  const NAME_ILLEGAL_CHARACTERS = "/_@#<>|()[]{}?\\=:;,'\"&$*~";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's profile text.
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
   * The user's country code.
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
   * The user's unique email.
   *
   * @var null|string
   */
  public $email;

  /**
   * The user's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The user's unique name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's hashed password.
   *
   * @var null|string
   */
  public $password;

  /**
   * Whether the user's personal data is private or not.
   *
   * @var null|boolean
   */
  public $private;

  /**
   * The user's total amount of profile views.
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
   * The user's reputation count.
   *
   * @var null|integer
   */
  public $reputation;

  /**
   * The user's translated route.
   *
   * @var string
   */
  public $route;

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
   * The user's time zone identifier (e.g. <code>"Europe/Vienna"</code>).
   *
   * @var null|string
   */
  public $timeZone;

  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  protected static $types = [
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


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize existing user from given value.
   *
   * @param string $from
   *   From which column the existing user should be loaded, use the <var>FROM_*</var> class constants.
   * @param string $value
   *   The value of the column.
   * @return this
   * @throws \MovLib\Exception\ClientException\NotFoundException
   * @throws \MovLib\Exception\ClientException\GoneException
   */
  public function init($from, $value) {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  `name`,
  UNIX_TIMESTAMP(`access`),
  `birthdate`,
  `country_code`,
  UNIX_TIMESTAMP(`created`),
  `currency_code`,
  COLUMN_GET(`dyn_about_me`, '{$this->intl->languageCode}' AS BINARY),
  `edits`,
  `email`,
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
WHERE `{$from}` = ?
LIMIT 1
SQL
    );
    $stmt->bind_param(self::$types[$from], $value);
    $stmt->execute();
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
      $this->password,
      $this->private,
      $this->profileViews,
      $this->realName,
      $this->reputation,
      $this->sex,
      $this->systemLanguageCode,
      $this->timeZone,
      $this->website
    );
    try {
      $stmt->fetch();
    }
    catch (\mysqli_sql_exception $e) {
      throw new NotFoundException("Couldn't find user {$from} '{$value}'");
    }
    finally {
      $stmt->close();
    }
    return $this->initFetchObject();
  }

  /**
   * {@inheritdoc}
   */
  public function initFetchObject() {
    $this->birthday = new Date($this->birthday);
    $this->route    = $this->intl->r("/user/{0}", mb_strtolower($this->name));
    return $this;
  }

  /**
   * Check if given user property is already in use.
   *
   * @param string $what
   *   Either <var>User::FROM_NAME</var> or <var>User::FROM_EMAIL</var>.
   * @param string $nameOrEmail
   *   The name or email address to check.
   * @return boolean
   *   <code>TRUE</code> if the email address is already in use, <code>FALSE</code> otherwise.
   */
  public function inUse($what, $nameOrEmail) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert($what == self::FROM_EMAIL || $what == self::FROM_NAME, "You can only check usage for 'name' and 'email'.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    return (null !== $this->query("SELECT `{$what}` FROM `users` WHERE `{$what}` = ? LIMIT 1", "s", [ $nameOrEmail ]));
  }

  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "users";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "user";
  }

}
