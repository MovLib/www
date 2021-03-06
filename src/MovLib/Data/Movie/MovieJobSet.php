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
namespace MovLib\Data\Movie;

use \MovLib\Core\Database\Database;
use \MovLib\Data\Cast\Cast;
use \MovLib\Data\Cast\CastSet;
use \MovLib\Data\Crew\Crew;
use \MovLib\Data\Crew\CrewSet;
use \MovLib\Data\Movie\Movie;

/**
 * Defines the movie job set object.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieJobSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "MovieJobSet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public static $tableName = "movies";

  /**
   * @deprecated
   */
  public $singularKey = "movie";

  /**
   * @deprecated
   */
  public $pluralKey = "movies";

  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, "Movies", "Movie", null);
  }

  /**
   * Load the movies and jobs for a specific person.
   *
   * @param \MovLib\Data\Person\Person $person
   *   The person to load the information for.
   * @return this
   */
  public function loadEntitiesByPerson(\MovLib\Data\Person\Person $person) {
    $result = Database::getConnection()->query(<<<SQL
SELECT
  `movies`.`id` AS `movieId`,
  `movies`.`created` AS `movieCreated`,
  `movies`.`changed` AS `movieChanged`,
  `movies`.`mean_rating` AS `movieMeanRating`,
  `movies`.`year` AS `movieYear`,
  IFNULL(`display_title`.`title`, `original_title`.`title`) AS `movieDisplayTitle`,
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`) AS `movieDisplayTitleLanguageCode`,
  `original_title`.`title` AS `movieOriginalTitle`,
  `original_title`.`language_code` AS `movieOriginalTitleLanguageCode`,
  `posters`.`id` AS `movieImageFilename`,
  HEX(`posters`.`cache_buster`) AS `movieImageCacheBuster`,
  `posters`.`extension` AS `movieImageExtension`,
  `posters`.`styles` AS `movieImageStyles`,
  `movies_crew`.`id` AS `crewId`,
  `movies_crew`.`person_id` AS `personId`,
  `movies_crew`.`job_id` AS `crewJobId`,
  IFNULL(
    COLUMN_GET(`jobs`.`dyn_titles_sex{$person->sex}`, '{$this->intl->code}' AS BINARY),
    COLUMN_GET(`jobs`.`dyn_titles_sex{$person->sex}`, '{$this->intl->defaultCode}' AS BINARY)
  ) AS `crewJobTitle`,
  `crew_alias`.`alias` AS `crewAlias`,
  IFNULL(
    COLUMN_GET(`movies_crew`.`dyn_role`, '{$this->intl->code}' AS BINARY),
    COLUMN_GET(`movies_crew`.`dyn_role`, '{$this->intl->defaultCode}' AS BINARY)
  ) AS `crewRole`,
  `movies_crew`.`role_id` AS `crewRoleId`,
  `crew_role`.`name` AS `crewRoleName`
FROM `movies_crew`
  INNER JOIN `jobs`
    ON `jobs`.`id` = `movies_crew`.`job_id`
  INNER JOIN `movies`
    ON `movies`.`id` = `movies_crew`.`movie_id`
  INNER JOIN `movies_original_titles`
    ON `movies_original_titles`.`movie_id` = `movies`.`id`
  INNER JOIN `movies_titles` AS `original_title`
    ON `original_title`.`id` = `movies_original_titles`.`title_id`
  LEFT JOIN `movies_display_titles`
    ON `movies_display_titles`.`movie_id` = `movies`.`id`
    AND `movies_display_titles`.`language_code` = '{$this->intl->code}'
  LEFT JOIN `movies_titles` AS `display_title`
    ON `display_title`.`id` = `movies_display_titles`.`title_id`
  LEFT JOIN `display_posters`
    ON `display_posters`.`movie_id` = `movies`.`id`
    AND `display_posters`.`language_code` = '{$this->intl->code}'
  LEFT JOIN `posters`
    ON `posters`.`id` = `display_posters`.`poster_id`
    AND `posters`.`deleted` = false
  LEFT JOIN `persons` AS `crew_role`
    ON `movies_crew`.`role_id` = `crew_role`.`id`
  LEFT JOIN `persons_aliases` AS `crew_alias`
    ON `crew_alias`.`id` = `movies_crew`.`alias_id`
WHERE `movies`.`deleted` = false
  AND `movies_crew`.`person_id` IS NOT NULL
  AND `movies_crew`.`person_id` = {$person->id}
ORDER BY `movies`.`year` DESC, `movies_crew`.`job_id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      // Initialize the movie id offset if not present yet.
      if (empty($this[$row->movieId])) {
        $this[$row->movieId] = (object) [
          "movie"    => new Movie($this->container),
          "cast"     => new CastSet($this->container),
          "crew"     => new CrewSet($this->container),
        ];
        /* @var $this[$row->movieId] \MovLib\Stub\Data\Movie\MovieJob */
        $this[$row->movieId]->movie                            = new Movie($this->container);
        $this[$row->movieId]->movie->id                        = $row->movieId;
        $this[$row->movieId]->movie->created                   = $row->movieCreated;
        $this[$row->movieId]->movie->changed                   = $row->movieChanged;
        $this[$row->movieId]->movie->meanRating                = $row->movieMeanRating;
        $this[$row->movieId]->movie->year                      = $row->movieYear;
        $this[$row->movieId]->movie->displayTitle              = $row->movieDisplayTitle;
        $this[$row->movieId]->movie->displayTitleLanguageCode  = $row->movieDisplayTitleLanguageCode;
        $this[$row->movieId]->movie->originalTitle             = $row->movieOriginalTitle;
        $this[$row->movieId]->movie->originalTitleLanguageCode = $row->movieOriginalTitleLanguageCode;
        $this[$row->movieId]->movie->imageFilename             = $row->movieImageFilename;
        $this[$row->movieId]->movie->imageCacheBuster          = $row->movieImageCacheBuster;
        $this[$row->movieId]->movie->imageExtension            = $row->movieImageExtension;
        $this[$row->movieId]->movie->imageStyles               = $row->movieImageStyles;
        $reflector = new \ReflectionMethod($this[$row->movieId]->movie, "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this[$row->movieId]->movie);
      }

      $row->movieId = (integer) $row->movieId;
      $row->crewId = (integer) $row->crewId;

      if (empty($this[$row->movieId]->cast[$row->crewId]) && empty($this[$row->movieId]->crew[$row->crewId])) {
        // Add a cast entry.
        if ((integer) $row->crewJobId === Cast::JOB_ID) {
          $this[$row->movieId]->cast[$row->crewId]                      = new Cast($this->container, $person);
          $this[$row->movieId]->cast[$row->crewId]->id                  = $row->crewId;
          $this[$row->movieId]->cast[$row->crewId]->alias               = $row->crewAlias;
          $this[$row->movieId]->cast[$row->crewId]->jobId               = (integer) $row->crewJobId;
          $this[$row->movieId]->cast[$row->crewId]->names[$person->sex] = $row->crewJobTitle;
          $this[$row->movieId]->cast[$row->crewId]->movieId             = (integer) $row->movieId;
          $this[$row->movieId]->cast[$row->crewId]->personId            = (integer) $row->personId;
          $this[$row->movieId]->cast[$row->crewId]->role                = $row->crewRole;
          $this[$row->movieId]->cast[$row->crewId]->roleId              = (integer) $row->crewRoleId;
          $this[$row->movieId]->cast[$row->crewId]->roleName            = $row->crewRoleName;
          $this[$row->movieId]->cast[$row->crewId]->routeArgs           = (integer) $row->crewJobId;
          $reflector = new \ReflectionMethod($this[$row->movieId]->cast[$row->crewId], "init");
          $reflector->setAccessible(true);
          $reflector->invoke($this[$row->movieId]->cast[$row->crewId], null, $person->sex);
        }
        // Add a crew entry.
        else {
          $this[$row->movieId]->crew[$row->crewId]                      = new Crew($this->container);
          $this[$row->movieId]->crew[$row->crewId]->id                  = $row->crewId;
          $this[$row->movieId]->crew[$row->crewId]->alias               = $row->crewAlias;
          $this[$row->movieId]->crew[$row->crewId]->jobId               = (integer) $row->crewJobId;
          $this[$row->movieId]->crew[$row->crewId]->names[$person->sex] = $row->crewJobTitle;
          $this[$row->movieId]->crew[$row->crewId]->movieId             = (integer) $row->movieId;
          $this[$row->movieId]->crew[$row->crewId]->personId            = (integer) $row->personId;
          $this[$row->movieId]->crew[$row->crewId]->routeArgs           = $row->crewJobId;
          $reflector = new \ReflectionMethod($this[$row->movieId]->crew[$row->crewId], "init");
          $reflector->setAccessible(true);
          $reflector->invoke($this[$row->movieId]->crew[$row->crewId]);
        }

      }
    }
    $result->free();

    (new \MovLib\Data\Genre\GenreSet($this->container))->loadEntitySets($this);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return;
  }

}
