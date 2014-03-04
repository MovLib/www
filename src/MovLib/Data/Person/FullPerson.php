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

use \MovLib\Data\Place;
use \MovLib\Presentation\Error\NotFound;

/**
 * Contains all available information about a person.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FullPerson extends \MovLib\Data\Person\Person {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The person's translated biography.
   *
   * @var string
   */
  public $biography;

  /**
   * The person's translated Wikipedia URL.
   *
   * @var string
   */
  public $wikipedia;

  /**
   * The person's aliases as numeric array.
   *
   * @var null|array
   */
  protected $aliases = false;

  /**
   * The person's place of birth.
   *
   * @var \MovLib\Data\Place
   */
  public $birthplace;

  /**
   * The person's translated cause of death.
   *
   * @var string
   */
  public $causeOfDeath;

  /**
   * The person's place of death.
   *
   * @var \MovLib\Data\Place
   */
  public $deathplace;

  /**
   * The person's external links as numeric array.
   *
   * @var array
   */
  protected $links = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Intantiate new Person.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The person's unique ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    // Try to load the person for the given identifier.
    if ($id) {
      $this->id = $id;
      $stmt = $db->query(
        "SELECT
          `created`,
          `deleted`,
          COLUMN_GET(`dyn_biographies`, ? AS BINARY),
          COLUMN_GET(`dyn_wikipedia`, ? AS BINARY),
          `name`,
          `sex`,
          `birthdate`,
          `birthplace_id`,
          `born_name`,
          `cause_of_death_id`,
          `deathdate`,
          `deathplace_id`,
          `nickname`
        FROM `persons`
        WHERE
          `id` = ?
        LIMIT 1",
        "ssd",
        [ $i18n->languageCode, $i18n->languageCode, $this->id ]
      );
      $stmt->bind_result(
        $this->created,
        $this->deleted,
        $this->biography,
        $this->wikipedia,
        $this->name,
        $this->sex,
        $this->birthDate,
        $this->birthplace,
        $this->bornName,
        $this->causeOfDeath,
        $this->deathDate,
        $this->deathplace,
        $this->nickname
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
   * Insert a new person into the database.
   *
   * @todo Index data with Elastic.
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function create() {
    global $db, $i18n;
    $this->id = $db->query(
      "INSERT INTO `persons` SET
        `created` = CURRENT_TIMESTAMP,
        `dyn_biographies` = COLUMN_CREATE('{$i18n->languageCode}', ?),
        `dyn_wikipedia`= COLUMN_CREATE('{$i18n->languageCode}', ?),
        `dyn_image_descriptions` = '',
        `name` = ?,
        `sex` = ?,
        `birthdate` = ?,
        `born_name` = ?,
        `deathdate` = ?"
      ,
      "sssisss",
      [ $this->biography, $this->wikipedia, $this->name, $this->sex, $this->birthDate, $this->bornName, $this->deathDate ]
    )->insert_id;

    // Insert aliases.
    if ($this->aliases) {
      $query = null;
      $types = null;
      $params = [];
      $c = count($this->aliases);
      for ($i = 0; $i < $c; ++$i) {
        if ($query) {
          $query .= ", ";
        }
        $query .= "(?, ?)";
        $types .= "ds";
        $params[] = $this->id;
        $params[] = $this->aliases[$i];
      }
      $query = "INSERT INTO `persons_aliases` (`person_id`, `alias`) VALUES {$query}";
      $db->query($query, $types, $params);
    }

    // Insert external links.
    if ($this->links) {
      $query = null;
      $types = null;
      $params = [];
      $c = count($this->links);
      for ($i = 0; $i < $c; ++$i) {
        if ($query) {
          $query .= ", ";
        }
        $query .= "(?, ?, ?)";
        $types .= "dss";
        $params[] = $this->id;
        $params[] = $i18n->languageCode;
        $params[] = $this->links[$i];
      }
      $query = "INSERT INTO `persons_links` (`person_id`, `language_code`, `url`) VALUES {$query}";
      $db->query($query, $types, $params);
    }

    return $this;
  }

  /**
   * Get the person's aliases as numeric array.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return null|array
   *  Numeric array containing the person's aliases or <code>NULL</code> if none were found.
   */
  public function getAliases() {
    global $db, $i18n;
    if ($this->aliases === false) {
      $this->aliases = null;
      $result = $db->query(
        "SELECT
          `alias`
        FROM `persons_aliases`
        WHERE `person_id` = ?
        ORDER BY `alias` {$db->collations[$i18n->languageCode]}",
        "d",
        [ $this->id ]
      )->get_result();
      while ($row = $result->fetch_row()) {
        $this->aliases[] = $row[0];
      }
    }
    return $this->aliases;
  }

  /**
   * Get the person's place of birth.
   *
   * @return null|\MovLib\Data\Place
   *   The person's place of birth or <code>NULL</code> if none was found.
   */
  public function getBirthPlace() {
    if (is_numeric($this->birthplace)) {
      $this->birthplace = new Place($this->birthplace);
    }
    return $this->birthplace;
  }

  /**
   * Get the person's place of death.
   *
   * @return null|\MovLib\Data\Place
   *   The person's place of death or <code>NULL</code> if none was found.
   */
  public function getDeathPlace() {
    if (is_numeric($this->deathplace)) {
      $this->deathplace = new Place($this->deathplace);
    }
    return $this->deathplace;
  }

  /**
   * Get the person's external links as numeric array.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return null|array
   *  Numeric array containing the person's external links or <code>NULL</code> if none were found.
   */
  public function getLinks() {
    global $db, $i18n;
    if ($this->links === false) {
      $this->links = null;
      $result = $db->query(
        "SELECT `url` FROM `persons_links` WHERE `person_id` = ? AND `language_code` = ? ORDER BY `id` ASC",
        "ds",
        [ $this->id, $i18n->languageCode ]
      )->get_result();
      while ($row = $result->fetch_row()) {
        $this->links[] = $row[0];
      }
    }
    return $this->links;
  }

  /**
   * Get the mysqli result for all movies this person has appeared in.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The movies with appearances of this person.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovies() {
    global $db, $i18n;
    return $db->query(
      "SELECT DISTINCT
        `movies`.`id`,
        `movies`.`deleted`,
        `movies`.`year`,
        `movies`.`mean_rating` AS `ratingMean`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`,
        `md`.`job_id` AS `director`
      FROM `movies`
        LEFT JOIN `movies_directors` AS `md`
          ON `md`.`movie_id` = `movies`.`id`
          AND `md`.`person_id` = ?
        LEFT JOIN `movies_cast` AS `mc`
          ON `mc`.`movie_id` = `movies`.`id`
          AND `mc`.`person_id` = ?
        LEFT JOIN `movies_crew` AS `mcr`
          ON `mcr`.`movie_id` = `movies`.`id`
          AND `mcr`.`person_id` = ?
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
      WHERE `movies`.`deleted` = false
        AND NOT (`md`.`person_id` IS NULL AND `mc`.`person_id` IS NULL AND `mcr`.`person_id` IS NULL)",
      "dddss",
      [ $this->id, $this->id, $this->id, $i18n->languageCode, $i18n->languageCode ]
    )->get_result();
  }

  /**
   * Get the mysqli result for all movie IDs this person has played in.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all acted movie IDs.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCastIdsResult() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `movies`.`id`,
        COLUMN_GET(`movies_cast`.`roles`, ? AS BINARY) AS `roles`
      FROM `movies_cast`
        INNER JOIN `movies` ON `movies`.`id` = `movies_cast`.`movie_id`
      WHERE `movies_cast`.`person_id` = ?
        AND `movies`.`deleted` = false
      ORDER BY `movies`. `year` DESC, `movies_cast`.`weight` DESC",
      "sd",
      [ $i18n->languageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get the mysqli result for all movie IDs this person has worked behind the scenes.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movie IDs and job titles the person has worked on.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCrewIdsResult() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `movies`.`id`,
        IFNULL(COLUMN_GET(`jobs`.`dyn_titles`, ? AS BINARY), COLUMN_GET(`jobs`.`dyn_titles`, '{$i18n->defaultLanguageCode}' AS BINARY)) AS `job_title`
      FROM `movies_crew`
        INNER JOIN `movies` ON `movies`.`id` = `movies_crew`.`movie_id`
        INNER JOIN `jobs` ON `jobs`.`id` = `movies_crew`.`job_id`
      WHERE `movies_crew`.`person_id` = ?
        AND `movies`.`deleted` = false
      ORDER BY `movies`. `year` DESC, `job_title` ASC",
      "sd",
      [ $i18n->languageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get the mysqli result for all movie IDs this person has directed.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The mysqli result for all directed movie IDs.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieDirectorIdsResult() {
    global $db;
    return $db->query(
      "SELECT
        `movies`.`id`
      FROM `movies_directors`
        INNER JOIN `movies` ON `movies`.`id` = `movies_directors`.`movie_id`
      WHERE `movies_directors`.`person_id` = ?
        AND `movies`.`deleted` = false
      ORDER BY `movies`. `year` DESC",
      "d",
      [ $this->id ]
    )->get_result();
  }

}
