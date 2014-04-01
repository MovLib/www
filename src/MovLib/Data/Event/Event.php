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

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Data\Place;
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
class Event extends \MovLib\Data\AbstractDatabaseEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

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
   * The event’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Instantiate new event.
   *
   * @param integer $id
   *   The event's unique identifier, omit to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init($id) {
    $stmt = $this->getMySQLi()->prepare("
      {$this->getDefaultQuery()}
      WHERE
        `id` = ?
      LIMIT 1"
    );
    $stmt->bind_param("ssd", $this->intl->languageCode, $this->intl->languageCode, $id);
    $stmt->execute();
    $stmt->bind_result(
      $this->aliases,
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
    $found = $stmt->fetch();
    $stmt->close();
    if ($found === null) {
      throw new NotFoundException("Couldn't find event for '{$id}'!");
    }

    // @todo Store counts as columns in table.
    $this->movieCount  = $this->getCount("movies_genres", "DISTINCT `movie_id`");
    $this->seriesCount = $this->getCount("series_genres", "DISTINCT `series_id`");

    return $this->initFetchObject();
  }

  /**
   * Initialize after instantiation via PHP's built in <code>\mysqli_result::fetch_object()}
   */
  public function initFetchObject() {
    if ($this->place) {
      $this->place = (new Place($this->diContainer))->init($this->place);
    }
    $this->deleted = (boolean) $this->deleted;
    $this->aliases = $this->aliases ? unserialize($this->aliases) : [];
    $this->links   = $this->links ? unserialize($this->links) : [];
    $this->route   = $this->intl->r("/event/{0}", $this->id);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * The default query.
   *
   * @return string
   */
  public function getDefaultQuery() {
    return
      "SELECT
        `aliases`
        `award_id` AS `awardId`,
        `changed`,
        `created`,
        `deleted`,
        COLUMN_GET(`dyn_descriptions`, ? AS CHAR) AS `description`,
        `end_date` AS `endDate`,
        `id`,
        `links`,
        `name`,
        `place_id` AS `place`,
        `start_date` AS `startDate`,
        IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS CHAR), COLUMN_GET(`dyn_wikipedia`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `wikipedia`
      FROM `events`"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "events";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "event";
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
