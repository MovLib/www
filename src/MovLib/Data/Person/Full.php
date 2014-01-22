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
class Full extends \MovLib\Data\Person\Person {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  public $created;
  public $biography;
  public $wikipedia;
  public $aliases;
  public $birthplace;
  public $causeOfDeath;
  public $deathplace;
  public $links;


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
      $stmt = $db->query("
          SELECT
            `created`,
            `deleted`,
            COLUMN_GET(`dyn_biographies`, '{$i18n->languageCode}' AS BINARY),
            COLUMN_GET(`dyn_wikipedia`, '{$i18n->languageCode}' AS BINARY),
            `name`,
            `sex`,
            `birthdate` AS `birthDate`,
            `birthplace_id`,
            `born_name` AS `bornName`,
            `cause_of_death_id`,
            `deathdate` AS `deathDate`,
            `deathplace_id`,
            `links`,
            `nickname`
          FROM `persons`
          WHERE
            `id` = ?
          LIMIT 1",
        "d",
        [ $this->id ]
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
        $this->links,
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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Insert a new person into the database.
   *
   * @todo Index data with Elastic.
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return $this
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
        `deathdate` = ?
        ",
      "bssisss",
      [
        $this->biography,
        $this->wikipedia,
        $this->name,
        $this->sex,
        $this->birthDate,
        $this->bornName,
        $this->deathDate,
      ]
    )->insert_id;
    if ($this->aliases) {
      // @todo Insert aliases into table.
      $query = "INSERT INTO `persons_aliases` (`person_id`, `alias`) VALUES ";
      $types = null;
      $params = [];
      $c = count($this->aliases);
      for ($i = 0; $i < $c; ++$i) {
        if ($i === 0) {
          $query .= "(?, ?)";
        }
        else {
          $query .= ", (?, ?)";
        }
        $types .= "ds";
        $params[] = $this->id;
        $params[] = $this->aliases[$i];
      }
      $db->query($query, $types, $params);
    }
    // Create a display photo.
    parent::init();

    return $this;
  }

  /**
   * Get the mysqli result for all movie IDs this person has played in.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The mysqli result for all acted movie IDs.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCastIdsResult() {
    global $db;
    return $db->query(
      "SELECT
        `movies`.`id` AS `movie_id`,
        `movies_cast`.`roles` AS `roles`
      FROM `movies_cast`
        INNER JOIN `movies` ON `movies`.`id` = `movies_cast`.`movie_id`
      WHERE `movies_cast`.`person_id` = ?
        AND `movies`.`deleted` = false
      ORDER BY `movies`. `year` DESC, `movies_cast`.`weight` DESC",
      "d",
      [ $this->id ]
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
        `movies`.`id` AS `movie_id`,
        IFNULL(COLUMN_GET(`jobs`.`dyn_titles`, ? AS BINARY), COLUMN_GET(`jobs`.`dyn_titles`, ? AS BINARY)) AS `job_title`
      FROM `movies_crew`
        INNER JOIN `movies` ON `movies`.`id` = `movies_crew`.`movie_id`
        INNER JOIN `jobs` ON `jobs`.`id` = `movies_crew`.`job_id`
      WHERE `movies_crew`.`person_id` = ?
        AND `movies`.`deleted` = false
      ORDER BY `movies`. `year` DESC, `job_title` ASC",
      "ssd",
      [ $i18n->languageCode, $i18n->defaultLanguageCode, $this->id ]
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
        `movies`.`id` AS `movie_id`
      FROM `movies_directors`
        INNER JOIN `movies` ON `movies`.`id` = `movies_directors`.`movie_id`
      WHERE `movies_directors`.`person_id` = ?
        AND `movies`.`deleted` = false
      ORDER BY `movies`. `year` DESC",
      "d",
      [ $this->id ]
    )->get_result();
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   */
  protected function init() {
    global $db, $i18n;
    parent::init();
    $this->links = unserialize($this->links);
    if ($this->birthplace) {
      $this->birthplace = new Place($this->birthplace);
    }
    $result = $db->query("SELECT `alias` FROM `persons_aliases` WHERE `person_id` = ? ORDER BY `alias` {$db->collations[$i18n->languageCode]}", "d", [ $this->id ])->get_result();
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $this->aliases[] = $row[0];
    }
  }
}
