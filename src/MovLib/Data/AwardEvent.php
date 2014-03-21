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

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Data\Place;

/**
 * Handling of one or more award events.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardEvent extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's unique identifier this event belongs to.
   *
   * @var integer
   */
  public $awardId;

  /**
   * The timestamp on which this event was changed.
   *
   * @var integer
   */
  public $changed;

  /**
   * The timestamp on which this event was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The award event's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The award event's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The event’s end date.
   *
   * @var string
   */
  public $endDate;

  /**
   * The award event's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The event’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The award event's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The event’s place.
   *
   * @var integer|object
   */
  public $place;

  /**
   * The translated route of this award event.
   *
   * @var string
   */
  public $route;

  /**
   * The route key of this award event.
   *
   * @var string
   */
  public $routeKey;

  /**
   * The event’s start date.
   *
   * @var string
   */
  public $startDate;

  /**
   * The award event’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award event.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The award event's unique identifier, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    if ($id) {
      $query = self::getQuery();
      $stmt = $db->query("
        {$query}
        WHERE
          `id` = ?
        LIMIT 1",
        "sssd",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->awardId,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->description,
        $this->endDate,
        $this->id,
        $this->links,
        $this->name,
        $this->place,
        $this->startDate,
        $this->wikipedia
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * The count of movies connected to this award event.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesCount() {
    global $db;
    return $db->query(
      "SELECT count(DISTINCT `movie_id`) as `count` FROM `movies_awards` WHERE `award_event_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

 /**
   * Get the mysqli result for all movies connected to this event.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movies connected to this event.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesResult() {
    global $db, $i18n;
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
        `ma`.`award_category_id` AS `awardCategoryId`,
        `ma`.`won` AS `awardCategoryWon`,
        `ma`.`person_id` AS `personId`,
        `ma`.`company_id` AS `companyId`
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
      WHERE `ma`.`award_id` = ? AND `ma`.`award_event_id` = ?
      ORDER BY `displayTitle` DESC",
      "ssdd",
      [ $i18n->languageCode, $i18n->languageCode, $this->awardId, $this->id ]
    )->get_result();

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
        $movies[$row["id"]]->movie->awardCategoryIds          = [];
        $movies[$row["id"]]->movie->awardCategoryWon          = [];
        $movies[$row["id"]]->movie->awardedCompanyIds         = [];
        $movies[$row["id"]]->movie->awardedPersonIds          = [];
        $movies[$row["id"]]->movie->init();
      }
      // We need all awarded companies and persions with the correct award category.
      array_push($movies[$row["id"]]->movie->awardCategoryIds, $row["awardCategoryId"]);
      array_push($movies[$row["id"]]->movie->awardCategoryWon, $row["awardCategoryWon"]);
      array_push($movies[$row["id"]]->movie->awardedPersonIds, $row["personId"]);
      array_push($movies[$row["id"]]->movie->awardedCompanyIds, $row["companyId"]);
    }
    return $movies;
  }

  /**
   * Get the default query.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar string $query
   *   Used to cache the default query.
   * @return string
   *   The default query.
   */
  public static function getQuery() {
    global $i18n;
    static $query = null;
    if (!$query) {
      $query =
        "SELECT
          `award_id` AS `awardId`,
          `changed`,
          `created`,
          `deleted`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`,
          `end_date` AS `endDate`,
          `id`,
          `links`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          `place_id` AS `place`,
          `start_date` AS `startDate`,
          IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS CHAR), COLUMN_GET(`dyn_wikipedia`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `wikipedia`
        FROM `awards_events`"
      ;
    }
    return $query;
  }

  /**
   * The count of movies connected to this event.
   *
   * @todo Implement when series are implemented.
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesCount() {
    return 0;
  }

  /**
   * Initialize event.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    if ($this->place) {
      $this->place = new Place($this->place);
    }
    $this->deleted  = (boolean) $this->deleted;
    $this->links    = $this->links ? unserialize($this->links) : [];
    $this->routeKey = "/award/{0}/event/{1}";
    $this->route    = $i18n->r($this->routeKey, [ $this->awardId, $this->id ]);
  }

}
