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

use \MovLib\Component\Date;
use \MovLib\Component\DateTime;
use \MovLib\Core\Database\Database;
use \MovLib\Core\HTTP\Session;
use \MovLib\Data\Image\ImageResizeEffect;
use \MovLib\Data\Movie\MovieSet;
use \MovLib\Data\Series\SeriesSet;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the user entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class User extends \MovLib\Data\Image\AbstractImageEntity {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "User";
  // @codingStandardsIgnoreEnd


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
   * @var \DateTime
   */
  public $access;

  /**
   * The user's birthday (date).
   *
   * @var null|\Date
   */
  public $birthdate;

  /**
   * The active config instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The user's contribution count.
   *
   * @var null|integer
   */
  public $contributionCount;

  /**
   * The user's country code.
   *
   * @var null|string
   */
  public $countryCode;

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
   * The user's preferred system language's code (e.g. <code>"en"</code>).
   *
   * @var null|string
   */
  public $languageCode;

  /**
   * The user's list count.
   *
   * @var null|integer
   */
  public $listCount;

  /**
   * The user's unique name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's hashed password.
   *
   * @var string
   */
  public $passwordHash;

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
   * The user's time zone.
   *
   * @var null|\DateTimeZone
   */
  public $timezone;

  /**
   * The user's time zone identifier (e.g. <code>"Europe/Vienna"</code>).
   *
   * @var null|string
   */
  public $timezoneId;

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
   * The user's upload count.
   *
   * @var null|integer
   */
  public $uploadCount;

  /**
   * The user's website.
   *
   * @var null|string
   */
  public $website;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param mixed $value [optional]
   *   The value the column has.
   * @param string $from [optional]
   *   From what column the user object should be created, defaults to <var>User::FROM_NAME</var>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $value = null, $from = self::FROM_NAME) {
    if ($value && $from) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `id`,
  `name`,
  `email`,
  `password`,
  `access`,
  `changed`,
  `created`,
  `birthdate`,
  `count_contributions`,
  `country_code`,
  `currency_code`,
  COLUMN_GET(`dyn_about_me`, '{$container->intl->languageCode}' AS CHAR),
  `edits`,
  HEX(`image_cache_buster`),
  `image_extension`,
  `image_styles`,
  `private`,
  `profile_views`,
  `real_name`,
  `reputation`,
  `sex`,
  `language_code`,
  `count_lists`,
  `timezone`,
  `count_uploads`,
  `website`
FROM `users` WHERE `{$from}` = ? LIMIT 1
SQL
      );
      $stmt->bind_param(self::$types[$from], $value);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->name,
        $this->email,
        $this->passwordHash,
        $this->access,
        $this->changed,
        $this->created,
        $this->birthdate,
        $this->contributionCount,
        $this->countryCode,
        $this->currencyCode,
        $this->aboutMe,
        $this->edits,
        $this->imageCacheBuster,
        $this->imageExtension,
        $this->imageStyles,
        $this->private,
        $this->profileViews,
        $this->realName,
        $this->reputation,
        $this->sex,
        $this->languageCode,
        $this->listCount,
        $this->timezoneId,
        $this->uploadCount,
        $this->website
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find user {$from} {$value}");
      }
    }
    parent::__construct($container);
  }

  /**
   * Called if this object is serialized.
   *
   * @return array
   *   Array containing the names of the properties that should be serialized.
   */
  public function __sleep() {
    return [
      "aboutMe", "access", "birthdate", "contributionCount", "countryCode", "currencyCode", "edits", "email",
      "languageCode", "listCount", "name", "passwordHash", "private", "profileViews", "realName", "repuation", "sex",
      "timezone", "uploadCount", "website",
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete this user's account.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function deleteAccount() {
    Database::getConnection()->query("UPDATE `users` SET `" . implode("` = NULL, `", [
      "admin", "birthdate", "country_code", "dyn_about_me", "edits", "email", "image_cache_buster", "image_extension",
      "image_styles", "password", "private", "profile_views", "real_name", "reputation", "sex", "language_code",
      "timezone", "website",
    ]) . "` = NULL WHERE `id` = {$this->id}");
    return $this;
  }

  /**
   * Delete the user's avatar image.
   *
   * @param \MovLib\Core\HTTP\Session $session
   *   The user's active session.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function deleteAvatar(\MovLib\Core\HTTP\Session $session) {
    if ($this->imageExists === true) {
      $this->imageDeleteStyles();
      $this->imageDelete();
      Database::getConnection()->query("UPDATE `users` SET `image_cache_buster` = NULL, `image_extension` = NULL, `image_styles` = NULL WHERE `id` = {$this->id}");
      $session->userImageCacheBuster = $session->userImageExtension = $_SESSION[Session::USER_IMAGE_CACHE_BUSTER] = $_SESSION[Session::USER_IMAGE_EXTENSION] = null;
    }
    return $this;
  }

  /**
   * Get paginated contributions by an user.
   *
   * @param integer $offset
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}, defaults to 0.
   * @param integer $limit
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}, defaults to 10.
   * @param string $orderBy [optional]
   *   The filtering <code>ORDER BY</code> clause without <code>ORDER BY</code>, defaults to <code>`revisionId` DESC</code>.
   * @return \mysqli_result
   * @throws \mysqli_sql_exception
   */
  public function getContributions($offset, $limit, $orderBy = "`revisionId` DESC") {
    $contributions = $entities = [];

    // Select all contributions from the database matching the filter criteria and offset + limit.
    $result = Database::getConnection()->query(<<<SQL
SELECT
  `revisions`.`id` + 0 AS `revisionId`,
  `revisions`.`entity_id` AS `entityId`,
  `revision_entities`.`class` AS `entityClass`,
  `revision_entities`.`id` AS `entityTypeId`
FROM `revisions`
  INNER JOIN `revision_entities`
    ON `revisions`.`revision_entity_id` = `revision_entities`.`id`
WHERE `revisions`.`user_id` = {$this->id}
ORDER BY {$orderBy}
LIMIT {$limit} OFFSET {$offset}
SQL
    );

    // Create a \MovLib\Stub\Data\User\Contribution instance for each contribution.
    while ($contribution = $result->fetch_object()) {
      // We have to create a unique delta for each contribution to preserve the order that the above SQL query produced
      // for us.
      $contributions["{$contribution->entityTypeId}_{$contribution->entityId}_{$contribution->revisionId}"] = (object) [
        "entity"          => null,
        "entityClassName" => $contribution->entityClass,
        "entityId"        => $contribution->entityId,
        "dateTime"        => new DateTime($contribution->revisionId),
        "revisionId"      => $contribution->revisionId,
      ];

      // We don't know anything about the actual entities the user contributed to. Therefore we have to collect the
      // unique identifiers and load presentable instances of the entities.
      $entities[$contribution->entityClass][] = $contribution->entityId;
    }
    $result->free();

    // Go through all entity classes and identifiers that we collected in the previous loop and instantiate sets that
    // will load the minimal entities for us. We lower the amount of queries if some entities share the same set.
    foreach ($entities as $entityClassName => $entityIds) {
      $setClassName = "{$entityClassName}Set";
      $entities[$entityClassName] = (new $setClassName($this->container))->loadIdentifiers($entityIds);
    }

    // Now we map the previously collected entities to their corresponding contribution (revision).
    /* @var $contribution \MovLib\Stub\Data\User\Contribution */
    foreach ($contributions as $contribution) {
      $contribution->entity = $entities[$contribution->entityClassName][$contribution->entityId];
    }

    return $contributions;
  }

  /**
   * Get the rating for an entity.
   *
   * @param \MovLib\Core\Entity\AbstractEntity $entity
   *   The entity to get the rating for.
   * @param integer $userId [optional]
   *   The user's unique identifier to get the rating for, defaults to <code>NULL</code> and the id from the current
   *   instance will be used.
   * @param return integer
   *   The user's rating for this movie, or <code>NULL</code> if the user hasn't rated this movie.
   */
  public function getRating(\MovLib\Core\Entity\AbstractEntity $entity, $userId = null) {
    if (empty($userId)) {
      $userId = $this->id;
    }
    $singular = strtolower($entity->bundle);
    $result = Database::getConnection()->query("SELECT `rating` FROM `{$entity::$tableName}_ratings` WHERE `user_id` = {$userId} AND `{$singular}_id` = {$entity->id} LIMIT 1");
    $rating = $result->fetch_row()[0];
    $result->free();
    return $rating;
  }

  /**
   * Get the total count of contributions this user has performed.
   *
   * @return integer
   *   The total count of contributions this user has performed.
   */
  public function getTotalContributionCount() {
    if (empty($this->edits)) {
      $this->edits = (integer) Database::getConnection()->query(
        "SELECT COUNT(*) FROM `revisions` WHERE `user_id` = {$this->id} LIMIT 1"
      )->fetch_all()[0][0];
    }
    return $this->edits;
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    $this->access               = new DateTime($this->access);
    $this->birthdate            && ($this->birthdate = new Date($this->birthdate));
    $this->deleted              = !(boolean) $this->email;
    $this->imageAlternativeText = $this->intl->t("{username}’s avatar image.", [ "username" => $this->name ]);
    $this->imageDirectory       = "upload://user";
    $this->imageFilename        = mb_strtolower($this->name);
    $this->private              = (boolean) $this->private;
    $this->route->args          = [ "args" => $this->imageFilename ];
    $this->route->route         = "/user/" . sanitize_filename($this->name);
    $this->timezoneId           && ($this->timezone = new \DateTimeZone($this->timezoneId));
    return $this;
  }

  /**
   * Let the current user instance join MovLib.
   *
   * @return this
   */
  public function join() {
    // @devStart
    // @codeCoverageIgnoreStart
    $info = password_get_info($this->passwordHash);
    if (empty($info) || empty($info["algo"])) {
      throw new \LogicException("The password seems to be unhashed.");
    }
    if ($info["algo"] === 0) {
      throw new \LogicException("The password was hashed with the following unsupported algorithm: {$info["algoName"]}");
    }
    if ($info["algo"] !== $this->config->passwordAlgorithm) {
      throw new \LogicException("The password wasn’t hashed with the currently configured password hashing algorithm.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $mysqli = Database::getConnection();
    $stmt = $mysqli->prepare("INSERT INTO `users` (`dyn_about_me`, `email`, `name`, `password`, `language_code`) VALUES('', ?, ?, ?, ?)");
    $stmt->bind_param("ssss", $this->email, $this->name, $this->passwordHash, $this->languageCode);
    $stmt->execute();
    $stmt->close();
    $this->id = $mysqli->insert_id;
    return $this;
  }

  /**
   * Load all rated entities by an user.
   *
   * @param integer $offset [optional]
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}, defaults to 0.
   * @param integer $limit [optional]
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}, defaults to 10.
   * @return array
   *   Array containing rated entities with rating and rating timestamp.
   * @throws \mysqli_sql_exception
   */
  public function loadRatedEntities($offset = 0, $limit = 10) {
    $ratedEntities  = [];
    $result = Database::getConnection()->query(<<<SQL
(
SELECT
  'Movie' AS `entity`,
  `movies_ratings`.`movie_id` AS `id`,
  `movies_ratings`.`rating` AS `rating`,
  `movies_ratings`.`created` AS `created`
FROM `movies_ratings`
WHERE `movies_ratings`.`user_id` = {$this->id}
)
UNION ALL
(
SELECT
  'Series' AS `entity`,
  `series_ratings`.`series_id` AS `id`,
  `series_ratings`.`rating` AS `rating`,
  `series_ratings`.`created` AS `created`
FROM `series_ratings`
WHERE `series_ratings`.`user_id` = {$this->id}
)
ORDER BY `created` DESC
LIMIT {$limit}
OFFSET {$offset}
SQL
    );

    $ratedMovieIds  = [];
    $ratedSeriesIds = [];
    while ($row = $result->fetch_object()) {
      $ratedEntities[] = $row;
      if ($row->entity == "Movie") {
        $ratedMovieIds[] = $row->id;
      }
      elseif ($row->entity == "Series") {
        $ratedSeriesIds[] = $row->id;
      }
    }
    $result->free();
    $ratedMoviesSet = empty($ratedMovieIds) ? [] : (new MovieSet($this->container))->loadIdentifiers($ratedMovieIds);
    $ratedSeriesSet = empty($ratedSeriesIds) ? [] : (new SeriesSet($this->container))->loadIdentifiers($ratedSeriesIds);

    $c = count($ratedEntities);
    for ($i = 0; $i < $c; ++$i) {
      if ($ratedEntities[$i]->entity == "Movie") {
        $ratedEntities[$i]->entity = $ratedMoviesSet[$ratedEntities[$i]->id];
      }
      elseif ($ratedEntities[$i]->entity == "Series") {
        $ratedEntities[$i]->entity = $ratedSeriesSet[$ratedEntities[$i]->id];
      }
    }
    return $ratedEntities;
  }

  /**
   * {@inheritdoc}
   */
  protected function imageGetEffects() {
    return [
      "nav" => new ImageResizeEffect(50, 50, true),
      "s1"  => new ImageResizeEffect(\MovLib\Data\Image\S01, \MovLib\Data\Image\S01, true),
      "s2"  => new ImageResizeEffect(\MovLib\Data\Image\S02, \MovLib\Data\Image\S02, true),
    ];
  }

  /**
   * Save the image styles to persistent storage.
   *
   * @return this
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = Database::getConnection()->prepare("UPDATE `users` SET `image_styles` = ? WHERE `id` = ?");
    $stmt->bind_param("sd", $styles, $this->id);
    $stmt->execute();
    $stmt->close();
    return $this->imageUpdateSession();
  }

  /**
   * Update the session after the user image has changed.
   *
   * @return this
   */
  protected function imageUpdateSession() {
    if ($this->container->kernel->http) {
      $_SESSION[Session::USER_IMAGE_CACHE_BUSTER] = $this->imageCacheBuster;
      $_SESSION[Session::USER_IMAGE_EXTENSION]    = $this->imageExtension;
      if (isset($this->container->session)) {
        $this->container->session->userImageCacheBuster = $this->imageCacheBuster;
        $this->container->session->userImageExtension   = $this->imageExtension;
      }
    }
    return $this;
  }

  /**
   * Check if given user property is already in use.
   *
   * @param string $what
   *   The name of the column that should be checked.
   * @param string $value
   *   The value to check.
   * @return boolean
   *   <code>TRUE</code> if the value is already in use, <code>FALSE</code> otherwise.
   */
  public function inUse($what, $value) {
    $stmt = Database::getConnection()->prepare("SELECT `id` FROM `users` WHERE `{$what}` = ? LIMIT 1");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $found = $stmt->fetch();
    $stmt->close();
    if (!$found) {
      return false;
    }
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    return $this->name;
  }

  /**
   * Update the user's account.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function updateAccount() {
    // If a new image was uploaded rename the images with the temporary name to the actual name.
    if ($this->imageUploaded) {
      $this->imageRename($this->imageFilename, mb_strtolower($this->name))->imageUpdateSession();
    }

    $styles = serialize($this->imageStyles);
    $stmt = Database::getConnection()->prepare(<<<SQL
UPDATE `users` SET
  `dyn_about_me`       = COLUMN_ADD(`dyn_about_me`, '{$this->intl->languageCode}', ?),
  `birthdate`          = ?,
  `country_code`       = ?,
  `currency_code`      = ?,
  `image_cache_buster` = UNHEX(?),
  `image_extension`    = ?,
  `image_styles`       = ?,
  `private`            = ?,
  `real_name`          = ?,
  `sex`                = ?,
  `language_code`      = ?,
  `timezone`           = ?,
  `website`            = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sssssssisisss",
      $this->aboutMe,
      $this->birthdate,
      $this->countryCode,
      $this->currencyCode,
      $this->imageCacheBuster,
      $this->imageExtension,
      $styles,
      $this->private,
      $this->realName,
      $this->sex,
      $this->languageCode,
      $this->timezoneId,
      $this->website
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Update the user's email address.
   *
   * @param string $newEmail
   *   The valid new email address.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function updateEmail($newEmail) {
    $stmt = Database::getConnection()->prepare("UPDATE `users` SET `email` = ? WHERE `id` = {$this->id}");
    $stmt->bind_param("s", $newEmail);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Update the user's password.
   *
   * @param string $passwordHash
   *   The new and already hashed password.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function updatePassword($passwordHash) {
    // check if the password is hashed with our configured algorithm, otherwise hash it.
    if (password_get_info($passwordHash)["algo"] !== $this->config->passwordAlgorithm) {
      $passwordHash = password_hash($passwordHash, $this->config->passwordAlgorithm, $this->config->passwordOptions);
    }
    $stmt = Database::getConnection()->prepare("UPDATE `users` SET `password` = ? WHERE `id` = ?");
    $stmt->bind_param("sd", $passwordHash, $this->id);
    $stmt->execute();
    $stmt->close();
    $this->passwordHash = $passwordHash;
    return $this;
  }

}
