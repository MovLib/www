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
namespace MovLib\Data\Person;

use \MovLib\Data\Award\AwardSet;
use \MovLib\Data\Award\CategorySet;
use \MovLib\Data\Event\EventSet;
use \MovLib\Data\Movie\MovieSet;
use \MovLib\Data\Series\SeriesSet;
use \MovLib\Data\Date;
use \MovLib\Data\Revision;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Represents a single person including their photo.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Person extends \MovLib\Data\Image\AbstractImageEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The entity type used to store revisions.
   *
   * @var int
   */
  const REVISION_ENTITY_TYPE = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's total award count.
   *
   * @var integer
   */
  public $countAwards;

  /**
   * The person's translated biography.
   *
   * @var null|string
   */
  public $biography;

  /**
   * The person's date of birth in <code>"Y-m-d"</code> format.
   *
   * @var \MovLib\Data\Date
   */
  public $birthDate;

  /**
   * The person's place of birth's identifier.
   *
   * @var null|integer
   */
  protected $birthPlaceId;

  /**
   * The person's birth name.
   *
   * @var string
   */
  public $bornName;

  /**
   * The person's date of death in <code>"Y-m-d"</code> format.
   *
   * @var \MovLib\Data\Date
   */
  public $deathDate;

  /**
   * The person's place of death's identifier.
   *
   * @var null|integer
   */
  protected $deathPlaceId;

  /**
   * The photo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "person";

  /**
   * The person’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The person's name.
   *
   * @var string
   */
  public $name;

  /**
   * The person's total movie count.
   *
   * @var integer
   */
  public $countMovies;

  /**
   * The person's total release count.
   *
   * @var integer
   */
  public $countReleases;

  /**
   * The person's total series count.
   *
   * @var integer
   */
  public $countSeries;

  /**
   * The person's sex.
   *
   * @var integer
   */
  public $sex;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "persons";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "person";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The person's unique identifier to instantiate, defaults to <code>NULL</code> (no person will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  `deleted`,
  `name`,
  COLUMN_GET(`dyn_biographies`, '{$this->intl->languageCode}' AS CHAR),
  `sex`,
  `birthdate`,
  `birthplace_id`,
  `born_name`,
  `deathdate`,
  `deathplace_id`,
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `links`,
  `count_awards`,
  `count_movies`,
  `count_series`,
  `count_releases`,
  HEX(`image_cache_buster`),
  `image_extension`,
  `image_filesize`,
  `image_height`,
  `image_styles`,
  `image_width`
FROM `persons` WHERE `id` = ? LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->name,
        $this->biography,
        $this->sex,
        $this->birthDate,
        $this->birthPlaceId,
        $this->bornName,
        $this->deathDate,
        $this->deathPlaceId,
        $this->wikipedia,
        $this->links,
        $this->countAwards,
        $this->countMovies,
        $this->countSeries,
        $this->countReleases,
        $this->imageCacheBuster,
        $this->imageExtension,
        $this->imageFilesize,
        $this->imageHeight,
        $this->imageStyles,
        $this->imageWidth
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find person {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->birthDate && ($this->birthDate = new Date($this->birthDate));
    $this->deathDate && ($this->deathDate = new Date($this->deathDate));
    $this->imageAlternativeText = $this->intl->t("Photo of {name}", [ "name" => $this->name]);
    $this->imageDirectory       = "upload://person";
    $this->imageFilename        = $this->id;
    $this->links     && ($this->links = unserialize($this->links));
    return parent::init();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Update the person.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function commit() {
    $this->links = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $stmt = $this->getMySQLi()->prepare(<<<SQL
UPDATE `persons` SET
  `birthdate`              = ?,
  `born_name`              = ?,
  `deathdate`              = ?,
  `dyn_biographies`        = COLUMN_ADD(`dyn_biographies`, '{$this->intl->languageCode}', ?),
  `dyn_image_descriptions` = '',
  `dyn_wikipedia`          = COLUMN_ADD(`dyn_wikipedia`, '{$this->intl->languageCode}', ?),
  `links`                  = ?,
  `name`                   = ?,
  `sex`                    = ?
WHERE `id` = {$this->id}
SQL
    );
    $stmt->bind_param(
      "sssssssi",
      $this->birthDate,
      $this->bornName,
      $this->deathDate,
      $this->biography,
      $this->wikipedia,
      $this->links,
      $this->name,
      $this->sex
    );
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Create a new person.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function create() {
    $this->links   = empty($this->links)? serialize([]) : serialize(explode("\n", $this->links));

    $stmt = $this->getMySQLi()->prepare(<<<SQL
INSERT INTO `persons` (
  `birthdate`,
  `born_name`,
  `deathdate`,
  `dyn_biographies`,
  `dyn_image_descriptions`,
  `dyn_wikipedia`,
  `links`,
  `name`,
  `sex`
) VALUES (
  ?,
  ?,
  ?,
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  '',
  COLUMN_CREATE('{$this->intl->languageCode}', ?),
  ?,
  ?,
  ?
);
SQL
    );
    $stmt->bind_param(
      "sssssssi",
      $this->birthDate,
      $this->bornName,
      $this->deathDate,
      $this->biography,
      $this->wikipedia,
      $this->links,
      $this->name,
      $this->sex
    );

    $stmt->execute();
    $this->id = $stmt->insert_id;

    return $this->init();
  }

  /**
   * Get the person's aliases.
   *
   * @return array
   *   Numeric array containing the person's aliases.
   */
  public function getAliases() {
    $result = $this->getMySQLi()->query(<<<SQL
SELECT
  `alias`
FROM `persons_aliases`
WHERE `person_id` = {$this->id}
ORDER BY `alias`{$this->collations[$this->intl->languageCode]} ASC
SQL
    );
    return array_column($result->fetch_all(), 0);
  }

  /**
   * Get the person's award wins and nominations.
   *
   * @return \MovLib\Data\Award\AwardSet
   */
  public function getAwards() {
    $awards = new AwardSet($this->diContainer);

    $result = $this->getMySQLi()->query(<<<SQL
(
  SELECT
    `award_category_id` AS `awardCategoryId`,
    `award_id` AS `awardId`,
    `event_id` AS `eventId`,
    `movie_id` AS `movieId`,
    NULL AS `seriesId`,
    `won`
  FROM `movies_awards`
  WHERE `person_id` <=> {$this->id}
)
UNION ALL
(
  SELECT
    `award_category_id` AS `awardCategoryId`,
    `award_id` AS `awardId`,
    `event_id` AS `eventId`,
    NULL AS `movieId`,
    `series_id` AS `seriesId`,
    `won`
  FROM `series_awards`
  WHERE `person_id` <=> {$this->id}
)
UNION ALL
(
  SELECT
    `award_category_id` AS `awardCategoryId`,
    `award_id` AS `awardId`,
    `event_id` AS `eventId`,
    NULL AS `movieId`,
    NULL AS `seriesId`,
    `won`
  FROM `persons_awards`
  WHERE `person_id` = {$this->id}
)
SQL
    );

    // Gather the identifiers in order to load the appropriate entitites into the sets.
    $awardIds          = [];
    $awardCategoryIds  = [];
    $eventIds          = [];
    $movieIds          = null;
    $seriesIds         = null;
    $eventCategorywins = [];
    while ($row = $result->fetch_object()) {
      $awardIds[$row->awardId]                                           = true;
      $awardCategoryIds[$row->awardCategoryId]                           = true;
      $eventIds[$row->eventId]                                           = true;
      if (isset($row->movieId)) {
        $movieIds[$row->movieId] = true;
      }
      if (isset($row->seriesId)) {
        $seriesIds[$row->seriesId] = true;
      }
      // Keep track of the wins in the respective event categories to maintain their association.
      $eventCategorywins[$row->eventId][$row->awardCategoryId]["movie"]  = isset($row->movieId) ? (integer) $row->movieId : null;
      $eventCategorywins[$row->eventId][$row->awardCategoryId]["series"] = isset($row->seriesId) ? (integer) $row->seriesId : null;
      $eventCategorywins[$row->eventId][$row->awardCategoryId]["won"]    = (integer) $row->won;
    }

    // No awards found, return the empty set.
    if (empty($awardIds)) {
      return $awards;
    }

    // Load all awards.
    $awards->loadIdentifiers(array_keys($awardIds), "`name`{$this->collations[$this->intl->languageCode]} ASC");

    // Load all events, regardless of the award.
    $events = new EventSet($this->diContainer);
    $events->loadIdentifiers(array_keys($eventIds), "`start_date` DESC");

    // Load all award categories regardless of the event.
    $categories = new CategorySet($this->diContainer);
    $categoryOrder = <<<SQL
IFNULL(
  COLUMN_GET(`dyn_names`, '{$this->intl->languageCode}' AS CHAR(255)),
  COLUMN_GET(`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR(255))
){$this->collations[$this->intl->languageCode]} ASC
SQL
    ;
    $categories->loadIdentifiers(array_keys($awardCategoryIds), $categoryOrder);

    // Load all movies, if any.
    $movies = new MovieSet($this->diContainer);
    if (isset($movieIds)) {
      $movies->loadIdentifiers(array_keys($movieIds));
    }

    // Load all series, if any.
    $series = new SeriesSet($this->diContainer);
    if (isset($seriesIds)) {
      $series->loadIdentifiers(array_keys($seriesIds));
    }

    // Fill the awards with the appropriate events and categories.
    /* @var $event \MovLib\Data\Event\Event */
    foreach ($events as $eventId => $event) {
      // Events are pretty straight forward.
      if (empty($awards->entities[$event->award]->events)) {
        $awards->entities[$event->award]->events = new EventSet($this->diContainer);
      }
      $awards->entities[$event->award]->events->entities[$eventId] = $event;

      $awards->entities[$event->award]->events->entities[$eventId]->categories = new CategorySet($this->diContainer);

      // Get the event -> category association from the array constructed above.
      foreach ($eventCategorywins[$eventId] as $categoryId => $categoryProperties) {
        $awards->entities[$event->award]->events->entities[$eventId]->categories->entities[$categoryId] = $categories->entities[$categoryId];
        if (isset($categoryProperties["movie"])) {
          $awards->entities[$event->award]->events->entities[$eventId]->categories->entities[$categoryId]->movie = $movies->entities[$categoryProperties["movie"]];
        }
        if (isset($categoryProperties["series"])) {
          $awards->entities[$event->award]->events->entities[$eventId]->categories->entities[$categoryId]->series = $series->entities[$categoryProperties["series"]];
        }
        $awards->entities[$event->award]->events->entities[$eventId]->categories->entities[$categoryId]->won = $categoryProperties["won"];
      }
    }

    return $awards;
  }

  /**
   * Get the person's place of birth.
   *
   * @return null|\MovLib\Data\Place\Place
   *   The person's place of birth or <code>NULL</code> if none was found.
   */
  public function getBirthPlace() {
    if ($this->birthPlaceId) {
      return new \MovLib\Data\Place\Place($this->diContainer, $this->birthPlaceId);
    }
  }

  /**
   * Get the person's place of death.
   *
   * @return null|\MovLib\Data\Place\Place
   *   The person's place of death or <code>NULL</code> if none was found.
   */
  public function getDeathPlace() {
    if ($this->deathPlaceId) {
      return new \MovLib\Data\Place\Place($this->diContainer, $this->deathPlaceId);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = $this->getMySQLi()->prepare("UPDATE `persons` SET `image_styles` = ? WHERE `id` = ?");
    $stmt->bind_param("sd", $styles, $this->id);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

}
