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
namespace MovLib\Data\Event;

use \MovLib\Data\Award\Award;
use \MovLib\Data\Date;
use \MovLib\Data\Movie\FullMovie;
use \MovLib\Data\Place\Place;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the event entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Event extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 8;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The award this event belongs to.
   *
   * @var mixed
   */
  public $award;

  /**
   * The timestamp on which this event was changed.
   *
   * @var integer
   */
  public $changed;

  /**
   * The count of companies connected to this event.
   *
   * @var integer
   */
  public $companyCount;

  /**
   * The timestamp on which this event was created.
   *
   * @var integer
   */
  public $created;

  /**
   * The event's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The event's description in the current display language.
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
   * The event's unique identifier.
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
   * The event's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The count of movies connected to this event.
   *
   * @var integer
   */
  public $movieCount;

  /**
   * The count of persons connected to this event.
   *
   * @var integer
   */
  public $personCount;

  /**
   * The event’s place.
   *
   * @var integer|object
   */
  public $place;

  /**
   * The translated route of this event.
   *
   * @var string
   */
  public $route;

  /**
   * The count of series connected to this event.
   *
   * @var integer
   */
  public $seriesCount;

  /**
   * The event’s start date.
   *
   * @var string
   */
  public $startDate;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "events";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "event";


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Instantiate new event object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The event's unique identifier to instantiate, defaults to <code>NULL</code> (no event will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `events`.`aliases` AS `aliases`,
  `events`.`award_id` AS `award`,
  `events`.`changed` AS `changed`,
  `events`.`created` AS `created`,
  `events`.`deleted` AS `deleted`,
   COLUMN_GET(`events`.`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`,
  `events`.`end_date` AS `endDate`,
  `events`.`id` AS `id`,
  `events`.`links` AS `links`,
  `events`.`name` AS `name`,
  `events`.`place_id` AS `place`,
  `events`.`start_date` AS `startDate`,
  COLUMN_GET(`events`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  `events`.`count_movies` AS `movieCount`,
  `events`.`count_series` AS `seriesCount`,
  `events`.`count_persons` AS `personCount`,
  `events`.`count_companies` AS `companyCount`
FROM `events`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->aliases,
        $this->award,
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
        $this->wikipedia,
        $this->movieCount,
        $this->seriesCount,
        $this->personCount,
        $this->companyCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Event {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the event.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $this->aliases = empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases));
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `events` SET
  `aliases`          = ?,
  `award_id`         = ?,
  `dyn_descriptions` = COLUMN_ADD(`dyn_descriptions`, '{$this->intl->languageCode}', ?),
  `dyn_wikipedia`    = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `end_date`         = ?,
  `links`            = ?,
  `name`             = ?,
  `start_date`       = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sdssssss",
      $this->aliases,
      $this->award->id,
      $this->description,
      $this->wikipedia,
      $this->endDate,
      $this->links,
      $this->name,
      $this->startDate
    );
    $stmt->execute();

    $this->updateAwardYears();

    return $this;
  }

  /**
   * Create new new event.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $this->aliases = empty($this->aliases)? serialize([]) : serialize(explode("\n", $this->aliases));
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $mysqli = $this->getMySQLi();
    $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `events` (
  `aliases`,
  `award_id`,
  `dyn_descriptions`,
  `dyn_wikipedia`,
  `end_date`,
  `links`,
  `name`,
  `start_date`
) VALUES (
  ?,
  ?,
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  ?,
  ?,
  ?,
  ?
);
SQL
    );
    $stmt->bind_param(
      "sdssssss",
      $this->aliases,
      $this->award->id,
      $this->description,
      $this->wikipedia,
      $this->endDate,
      $this->links,
      $this->name,
      $this->startDate
    );

    $stmt->execute();
    $this->id = $stmt->insert_id;

    $this->updateAwardYears();

    return $this->init();
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    if (isset($this->award) && !$this->award instanceof \stdClass) {
      $this->award = new Award($this->diContainer, $this->award);
    }
    $this->place     && $this->place = new Place($this->diContainer, $this->place);
    $this->aliases   && ($this->aliases = unserialize(($this->aliases)));
    $this->links     && ($this->links = unserialize($this->links));
    $this->startDate && ($this->startDate = new Date($this->startDate));
    $this->endDate   && ($this->endDate = new Date($this->endDate));
    return parent::init();
  }

  private function updateAwardYears() {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `awards` SET
  `first_event_year` = (SELECT YEAR(MIN(`start_date`)) FROM `events` WHERE `award_id` = ?),
  `last_event_year`  = IFNULL(
    (SELECT YEAR(MAX(`end_date`)) FROM `events` WHERE `award_id` = ?),
    (SELECT YEAR(MAX(`start_date`)) FROM `events` WHERE `award_id` = ?)
   )
WHERE `awards`.`id` = ?
SQL
    );
    $stmt->bind_param(
      "dddd",
      $this->award->id,
      $this->award->id,
      $this->award->id,
      $this->award->id
    );
    $stmt->execute();
  }

 /**
   * Get the mysqli result for all movies connected to this event.
   *
   * @return \mysqli_result
   *   The mysqli result for all movies connected to this event.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesResult() {
    $result = $this->query(
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
        `movies_awards`.`award_category_id` AS `awardCategoryId`,
        `movies_awards`.`won` AS `awardCategoryWon`,
        `movies_awards`.`person_id` AS `personId`,
        `movies_awards`.`company_id` AS `companyId`
      FROM `movies_awards`
        LEFT JOIN `movies`
          ON `movies`.`id` = `movies_awards`.`movie_id`
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
      WHERE `movies_awards`.`award_id` = ? AND `movies_awards`.`award_event_id` = ?
      ORDER BY `displayTitle` DESC",
      "ssdd",
      [ $this->intl->languageCode, $this->intl->languageCode, $this->awardId, $this->id ]
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

}
