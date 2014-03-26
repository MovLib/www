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
namespace MovLib\Data;

use \MovLib\Data\AwardCategory;
use \MovLib\Data\Event;
use \MovLib\Data\Movie\FullMovie;
use \MovLib\Presentation\Error\NotFound;

/**
 * Represents one or more awards including icons.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Award extends \MovLib\Data\Image\AbstractImage {


// ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 220x220>
   *
   * Image style used on the show page to display the company logo.
   *
   * @var integer
   */
  const STYLE_SPAN_03 = \MovLib\Data\Image\SPAN_03;

  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The award's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The award's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The icon's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "award";

  /**
   * The award's unique identifier.
   *
   * @var integer
   */
  public $id;

 /**
   * The icon's translated description.
   *
   * @var string
   */
  public $imageDescription;

  /**
   * The icon's translated icon route.
   *
   * @var string
   */
  public $imageRoute;

  /**
   * The award’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The award's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The translated route of this award.
   *
   * @var string
   */
  public $route;

  /**
   * The route key of this award.
   *
   * @var string
   */
  public $routeKey;

  /**
   * The award’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;

  /**
   * The first year this award was awarded.
   *
   * @var integer
   */
  public $firstAwardingYear;

  /**
   * The last year this award was awarded.
   *
   * @var integer
   */
  public  $lastAwardingYear;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods

  /**
   * Instantiate new award.
   *
   * @param integer $id [optional]
   *   The award's unique identifier, leave empty to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct($id = null) {
    // Try to load award based on given identifier.
    if ($id) {
      $query = self::getQuery();
      $stmt = $db->query("
          {$query}
          WHERE
            `id` = ?
          LIMIT 1",
        "ssssd",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->name,
        $this->description,
        $this->wikipedia,
        $this->uploaderId,
        $this->width,
        $this->height,
        $this->filesize,
        $this->extension,
        $this->changed,
        $this->imageDescription,
        $this->styles,
        $this->links,
        $this->aliases,
        $this->firstAwardingYear,
        $this->lastAwardingYear
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $id;
    }

    // The award's icon name is always the award's identifier.
    $this->filename = &$this->id;

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the icon for this award.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all awards matching the offset and row count.
   *
   * @param integer $offset
   *   The offset in the result.
   * @param integer $rowCount
   *   The number of rows to retrieve.
   * @return \mysqli_result
   *   The query result.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getAwards($offset, $rowCount) {
    $query = self::getQuery();
    return $db->query("
        {$query}
        ORDER BY `name` ASC
        LIMIT ? OFFSET ?",
      "ssssid",
      [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * The total count of award categories which are not deleted..
   *
   * @staticvar null|integer $count
   *   The total amount of award categories which haven't been deleted.
   * @return integer
   *   The total amount of award categories which haven't been deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getCategoriesCount() {
    static $count = null;
    if (!$count) {
      $count = $db->query(
        "SELECT count(DISTINCT `id`) as `count` FROM `awards_categories` WHERE `deleted` = false AND `award_id` = ?", "d", [ $this->id ]
      )->get_result()->fetch_assoc()["count"];
    }
    return $count;
  }

  /**
   * The total count of award events which are not deleted.
   *
   * @staticvar null|integer $count
   *   The total amount of award events which haven't been deleted.
   * @return integer
   *   The total amount of award events which haven't been deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getEventsCount() {
    static $count = null;
    if (!$count) {
      $count = $db->query(
        "SELECT count(DISTINCT `id`) as `count` FROM `awards_events` WHERE `deleted` = false AND `award_id` = ?", "d", [ $this->id ]
      )->get_result()->fetch_assoc()["count"];
    }
    return $count;
  }

  /**
   * Get all award events.
   *
   * @return \mysqli_result
   *   The query result.
   */
  public function getEventsResult() {
    $query = Event::getQuery();
    return $db->query("
      {$query}
      WHERE `award_id` = ?
      ORDER BY `startDate` ASC",
      "sssd",
      [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get all award categories.
   *
   * @return \mysqli_result
   *   The query result.
   */
  public function getCategoriesResult() {
    $query = AwardCategory::getQuery();
    return $db->query("
      {$query}
      WHERE `award_id` = ?
      ORDER BY `name` ASC",
      "ssd",
      [ $i18n->languageCode, $i18n->languageCode, $this->id ]
    )->get_result();
  }

  /**
   * The total count of movies that received this award.
   *
   * @return integer
   *   The total count of movies.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesCount() {
    return $db->query(
        "SELECT count(DISTINCT `movie_id`) as `count` FROM `movies_awards` WHERE `award_id` = ?", "d", [ $this->id ]
      )->get_result()->fetch_assoc()["count"];
  }

  /**
   * Get the mysqli result for all movies that have received this award.
   *
   * @todo Implement
   * @return \mysqli_result
   *   The mysqli result for all movies that have received this award.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesResult() {
    $result = $db->query(
      "SELECT
        `movies`.`id`,
        `movies`.`deleted`,
        `movies`.`year`,
        `movies`.`mean_rating` AS `ratingMean`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`,
        `ma`.`won` AS `won`
      FROM `movies_awards` AS `ma`
        LEFT JOIN `movies` AS `movies`
          ON `movies`.`id` = `ma`.`movie_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`movie_id` = `movies`.`id`
          AND `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`movie_id` = `movies`.`id`
          AND `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
      WHERE `ma`.`award_id` = ?
      ORDER BY
        `ma`.`won` DESC,
        `movies`.`year` DESC",
      "ssd",
      [ $i18n->languageCode, $i18n->languageCode, $this->id ]
    )->get_result();

    $movies = [];
    while ($row = $result->fetch_assoc()) {
      // Instantiate and initialize a Movie if it is not present yet.
      if (!isset($movies[$row["id"]])) {
        $movies[$row["id"]] = (object) [
          "movie" => new FullMovie()
        ];
        $movies[$row["id"]]->movie->id                        = $row["id"];
        $movies[$row["id"]]->movie->deleted                   = $row["deleted"];
        $movies[$row["id"]]->movie->year                      = $row["year"];
        $movies[$row["id"]]->movie->ratingMean                = $row["ratingMean"];
        $movies[$row["id"]]->movie->displayTitle              = $row["displayTitle"];
        $movies[$row["id"]]->movie->displayTitleLanguageCode  = $row["displayTitleLanguageCode"];
        $movies[$row["id"]]->movie->originalTitle             = $row["originalTitle"];
        $movies[$row["id"]]->movie->originalTitleLanguageCode = $row["originalTitleLanguageCode"];
        $movies[$row["id"]]->movie->displayPoster             = $row["displayPoster"];
        $movies[$row["id"]]->movie->nominationCount           = 0;
        $movies[$row["id"]]->movie->wonCount                  = 0;
        $movies[$row["id"]]->movie->init();
      }
      if ($row["won"]) {
        ++$movies[$row["id"]]->movie->nominationCount;
        ++$movies[$row["id"]]->movie->wonCount;
      }
      else {
        ++$movies[$row["id"]]->movie->nominationCount;
      }
    }
    return $movies;
  }

  /**
   * Get the default query.
   *
   * @staticvar string $query
   *   Used to cache the default query.
   * @return string
   *   The default query.
   */
  protected static function getQuery() {
    static $query = null;
    if (!$query) {
      $query =
        "SELECT
          `id`,
          `deleted`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`,
          IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS BINARY), COLUMN_GET(`dyn_wikipedia`, '{$i18n->defaultLanguageCode}' AS BINARY)),
          `image_uploader_id` AS `uploaderId`,
          `image_width` AS `width`,
          `image_height` AS `height`,
          `image_filesize` AS `filesize`,
          `image_extension` AS `extension`,
          UNIX_TIMESTAMP(`image_changed`) AS `changed`,
          IFNULL(COLUMN_GET(`dyn_image_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_image_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `imageDescription`,
          `image_styles` AS `styles`,
          `links`,
          `aliases`,
          `first_awarding_year` AS `firstAwardingYear`,
          `last_awarding_year` AS `lastAwardingYear`
        FROM `awards`"
      ;
    }
    return $query;
  }

  /**
   * Get random award identifier.
   *
   * @return integer|null
   *   Random award identifier, or <code>NULL</code> on failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomAwardId() {
    $query = "SELECT `id` FROM `awards` WHERE `awards`.`deleted` = false ORDER BY RAND() LIMIT 1";
    if ($result = $db->query($query)->get_result()) {
      return $result->fetch_assoc()["id"];
    }
  }

  /**
   * The count of series that received this award.
   *
   * @todo Implement when series are implemented
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesCount() {
    return 0;
  }

  /**
   * Get the mysqli result for all series that have received this award.
   *
   * @todo Implement when series are implemented
   * @return \mysqli_result
   *   The mysqli result for all series that have received this award.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesResult() {
    return $this;
  }

  /**
   * Get the count of all awards.
   *
   * @staticvar null|integer $count
   *   The total amount of awards which haven't been deleted.
   * @return integer
   *   The total amount of awards which haven't been deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getTotalCount() {
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `awards` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Initialize the award with its icon, deleted flag and translate the route.
   *
   */
  protected function init() {
    $this->aliases  = $this->aliases ? unserialize($this->aliases) : [];
    $this->links    = $this->links ? unserialize($this->links) : [];

    $this->deleted  = (boolean) $this->deleted;

    $this->routeKey = "/award/{0}";
    $this->route    = $i18n->r($this->routeKey, [ $this->id]);
    $key            = "edit";
    if ($this->uploaderId) {
      $this->imageExists = true;
      $key               = "icon";
      $this->styles      = unserialize($this->styles);
    }
    $this->imageRoute = $i18n->r("/award/{0}/{$key}", [ $this->id ]);

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Image Methods


  /**
   * Delete the award icon.
   *
   * @todo Implement delete award icon.
   * @return this
   */
  public function delete() {
    return $this;
  }

   /**
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param boolean $regenerate [optional]
   *   Whether to regenerate existing styles.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function generateStyles($source, $regenerate = false) {
    // Generate the various image's styles and always go from best quality down to worst quality.
    $this->convert($source, self::STYLE_SPAN_03, self::STYLE_SPAN_03, self::STYLE_SPAN_03, true);
    $this->convert($source, self::STYLE_SPAN_02, self::STYLE_SPAN_02, self::STYLE_SPAN_02, true);
    $this->convert($source, self::STYLE_SPAN_01, self::STYLE_SPAN_01, self::STYLE_SPAN_01, true);

    if ($regenerate === true) {
      $query  = "UPDATE `awards` SET `image_styles` = ? WHERE `id` = ?";
      $types  = "sd";
      $params = [ serialize($this->styles), $this->id ];
    }
    else {
      $this->changed = time();
      $query =
        "UPDATE `companies` SET
          `image_changed`          = FROM_UNIXTIME(?),
          `dyn_image_descriptions` = COLUMN_ADD(`dyn_image_descriptions`, ?, ?),
          `image_extension`        = ?,
          `image_filesize`         = ?,
          `image_height`           = ?,
          `image_styles`           = ?,
          `image_uploader_id`      = ?,
          `image_width`            = ?
        WHERE `id` = ?"
      ;
      $types  = "isssiisdid";
      $params = [
        $this->changed,
        $i18n->languageCode,
        $this->imageDescription,
        $this->extension,
        $this->filesize,
        $this->height,
        serialize($this->styles),
        $this->uploaderId,
        $this->width,
        $this->id,
      ];
    }
    $db->query($query, $types, $params)->close();

    return $this;
  }

  /**
   * Set deletion request identifier.
   *
   * @todo Implement deletion request
   * @param integer $id
   *   The deletion request's unique identifier to set.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function setDeletionRequest($id) {
    return $this;
  }

}
