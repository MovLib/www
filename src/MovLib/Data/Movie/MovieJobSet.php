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

use \MovLib\Data\Cast\Cast;
use \MovLib\Data\Cast\CastSet;
use \MovLib\Data\Director\Director;
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
class MovieJobSet extends \MovLib\Data\Movie\MovieSet {


  public function loadEntitiesByPerson(\MovLib\Data\Person\Person $person) {
    $result = $this->getMySQLi()->query(<<<SQL
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
  `persons`.`id` AS `personId`,
  `movies_directors`.`id` AS `directorId`,
  `movies_directors`.`created` AS `directorCreated`,
  `movies_directors`.`created` AS `directorChanged`,
  `movies_directors`.`job_id` AS `directorJobId`,
  IFNULL(
    COLUMN_GET(`director_job`.`dyn_names_sex{$person->sex}`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`director_job`.`dyn_names_sex{$person->sex}`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `directorJobName`,
  `director_alias`.`alias` AS `directorAlias`,
  `movies_cast`.`id` AS `castId`,
  `movies_cast`.`created` AS `castCreated`,
  `movies_cast`.`created` AS `castChanged`,
  `movies_cast`.`job_id` AS `castJobId`,
  IFNULL(
    COLUMN_GET(`cast_job`.`dyn_names_sex{$person->sex}`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`cast_job`.`dyn_names_sex{$person->sex}`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `castJobName`,
  `cast_alias`.`alias` AS `castAlias`,
  IFNULL(
    COLUMN_GET(`movies_cast`.`dyn_role`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`movies_cast`.`dyn_role`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `castRole`,
  `movies_cast`.`role_id` AS `castRoleId`,
  `cast_role`.`name` AS `castRoleName`,
  `movies_crew`.`id` AS `crewId`,
  `movies_crew`.`created` AS `crewCreated`,
  `movies_crew`.`created` AS `crewChanged`,
  `movies_crew`.`job_id` AS `crewJobId`,
  IFNULL(
    COLUMN_GET(`crew_job`.`dyn_names_sex{$person->sex}`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`crew_job`.`dyn_names_sex{$person->sex}`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `crewJobName`,
  `crew_alias`.`alias` AS `crewAlias`
FROM `movies` FORCE INDEX (`movies_deleted`)
  INNER JOIN `movies_original_titles`
    ON `movies_original_titles`.`movie_id` = `movies`.`id`
  INNER JOIN `movies_titles` AS `original_title` FORCE INDEX (PRIMARY)
    ON `original_title`.`id` = `movies_original_titles`.`title_id`
  LEFT JOIN `movies_display_titles`
    ON `movies_display_titles`.`movie_id` = `movies`.`id`
    AND `movies_display_titles`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `movies_titles` AS `display_title` FORCE INDEX (PRIMARY)
    ON `display_title`.`id` = `movies_display_titles`.`title_id`
  LEFT JOIN `display_posters`
    ON `display_posters`.`movie_id` = `movies`.`id`
    AND `display_posters`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `posters` FORCE INDEX (PRIMARY)
    ON `posters`.`id` = `display_posters`.`poster_id`
    AND `posters`.`deleted` = false
  LEFT JOIN `movies_directors` FORCE INDEX (`fk_movies_directors_movies`)
    ON `movies_directors`.`movie_id` = `movies`.`id`
  LEFT JOIN `jobs` AS `director_job`
    ON `director_job`.`id` = `movies_directors`.`job_id`
  LEFT JOIN `persons_aliases` AS `director_alias`
    ON `director_alias`.`id` = `movies_directors`.`alias_id`
  LEFT JOIN `movies_cast` FORCE INDEX (`fk_movies_cast_movies`)
    ON `movies_cast`.`movie_id` = `movies`.`id`
  LEFT JOIN `jobs` AS `cast_job`
    ON `cast_job`.`id` = `movies_cast`.`job_id`
  LEFT JOIN `persons_aliases` AS `cast_alias`
    ON `cast_alias`.`id` = `movies_cast`.`alias_id`
  LEFT JOIN `persons` AS `cast_role`
    ON `cast_role`.`id` = `movies_cast`.`role_id`
  LEFT JOIN `movies_crew` FORCE INDEX (`fk_movies_crew_movies`)
    ON `movies_crew`.`movie_id` = `movies`.`id`
  LEFT JOIN `jobs` AS `crew_job`
    ON `crew_job`.`id` = `movies_crew`.`job_id`
  LEFT JOIN `persons_aliases` AS `crew_alias`
    ON `crew_alias`.`id` = `movies_crew`.`alias_id`
  INNER JOIN `persons`
    ON `persons`.`id` = `movies_directors`.`person_id`
    OR `persons`.`id` = `movies_cast`.`person_id`
    OR `persons`.`id` = `movies_crew`.`person_id`
WHERE `movies`.`deleted` = false AND `persons`.`id` = {$person->id}
SQL
    );

    while ($row = $result->fetch_object()) {
      // Initialize the movie id offset if not present yet.
      if (empty($this->entities[$row->movieId])) {
        $this->entities[$row->movieId] = (object) [
          "movie"    => new Movie($this->diContainer),
          "cast"     => null,
          "crew"     => null,
          "director" => null,
        ];
        /* @var $this->entities[$row->movieId] \MovLib\Stub\Data\Movie\MovieJob */
        $this->entities[$row->movieId]->movie                            = new Movie($this->diContainer);
        $this->entities[$row->movieId]->movie->id                        = $row->movieId;
        $this->entities[$row->movieId]->movie->created                   = $row->movieCreated;
        $this->entities[$row->movieId]->movie->changed                   = $row->movieChanged;
        $this->entities[$row->movieId]->movie->meanRating                = $row->movieMeanRating;
        $this->entities[$row->movieId]->movie->year                      = $row->movieYear;
        $this->entities[$row->movieId]->movie->displayTitle              = $row->movieDisplayTitle;
        $this->entities[$row->movieId]->movie->displayTitleLanguageCode  = $row->movieDisplayTitleLanguageCode;
        $this->entities[$row->movieId]->movie->originalTitle             = $row->movieOriginalTitle;
        $this->entities[$row->movieId]->movie->originalTitleLanguageCode = $row->movieOriginalTitleLanguageCode;
        $this->entities[$row->movieId]->movie->imageFilename             = $row->movieImageFilename;
        $this->entities[$row->movieId]->movie->imageCacheBuster          = $row->movieImageCacheBuster;
        $this->entities[$row->movieId]->movie->imageExtension            = $row->movieImageExtension;
        $this->entities[$row->movieId]->movie->imageStyles               = $row->movieImageStyles;
        $reflector = new \ReflectionMethod($this->entities[$row->movieId]->movie, "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->movieId]->movie);
      }

      // Initialize the director if not present yet.
      if (isset($row->directorId) && empty($this->entities[$row->movieId]->director)) {
        $this->entities[$row->movieId]->director                      = new Director($this->diContainer);
        $this->entities[$row->movieId]->director->id                  = $row->directorId;
        $this->entities[$row->movieId]->director->created             = $row->directorCreated;
        $this->entities[$row->movieId]->director->changed             = $row->directorChanged;
        $this->entities[$row->movieId]->director->alias               = $row->directorAlias;
        $this->entities[$row->movieId]->director->jobId               = $row->directorJobId;
        $this->entities[$row->movieId]->director->names[$person->sex] = $row->directorJobName;
        $this->entities[$row->movieId]->director->movieId             = $row->movieId;
        $this->entities[$row->movieId]->director->personId            = $row->personId;
        $reflector = new \ReflectionMethod($this->entities[$row->movieId]->director, "init");
        $reflector->setAccessible(true);
        $reflector->invoke($this->entities[$row->movieId]->director);
      }

      // Add a cast entry.
      if (isset($row->castId)) {
        // Initialize the cast set if not present yet.
        if (empty($this->entities[$row->movieId]->cast)) {
          $this->entities[$row->movieId]->cast = new CastSet($this->diContainer);
        }
        if (empty($this->entities[$row->movieId]->cast->entities[$row->castId])) {
          $this->entities[$row->movieId]->cast->entities[$row->castId]                      = new Cast($this->diContainer, $person);
          $this->entities[$row->movieId]->cast->entities[$row->castId]->id                  = $row->castId;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->created             = $row->castCreated;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->changed             = $row->castChanged;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->alias               = $row->castAlias;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->jobId               = $row->castJobId;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->names[$person->sex] = $row->castJobName;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->movieId             = $row->movieId;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->personId            = $row->personId;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->role                = $row->castRole;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->roleId              = $row->castRoleId;
          $this->entities[$row->movieId]->cast->entities[$row->castId]->roleName            = $row->castRoleName;
          $reflector = new \ReflectionMethod($this->entities[$row->movieId]->cast->entities[$row->castId], "init");
          $reflector->setAccessible(true);
          $reflector->invoke($this->entities[$row->movieId]->cast->entities[$row->castId]);
        }
      }

      // Add a crew entry.
      if (isset($row->crewId)) {
        // Initialize the crew set if not present yet.
        if (empty($this->entities[$row->movieId]->crew)) {
          $this->entities[$row->movieId]->crew = new CrewSet($this->diContainer);
        }
        if (empty($this->entities[$row->movieId]->crew->entities[$row->crewId])) {
          $this->entities[$row->movieId]->crew->entities[$row->crewId]                      = new Crew($this->diContainer);
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->id                  = $row->crewId;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->created             = $row->crewCreated;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->changed             = $row->crewChanged;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->alias               = $row->crewAlias;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->jobId               = $row->crewJobId;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->names[$person->sex] = $row->crewJobName;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->movieId             = $row->movieId;
          $this->entities[$row->movieId]->crew->entities[$row->crewId]->personId            = $row->personId;
          $reflector = new \ReflectionMethod($this->entities[$row->movieId]->crew->entities[$row->crewId], "init");
          $reflector->setAccessible(true);
          $reflector->invoke($this->entities[$row->movieId]->crew->entities[$row->crewId]);
        }
      }
    }
    $this->log->debug("entities", $this->entities);
  }

}
