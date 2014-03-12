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

use \MovLib\Presentation\Error\NotFound;

/**
 * Represents one ore more jobs.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Job extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The job's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The job's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The job's translated description.
   *
   * @var string
   */
  public $description;

  /**
   * The job's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The translated route of this job.
   *
   * @var string
   */
  public $route;

  /**
   * The job's translated unisex name.
   *
   * @var string
   */
  public $name;

  /**
   * The job's translated male name.
   *
   * @var string
   */
  public $maleName;

  /**
   * The job's translated female name.
   *
   * @var string
   */
  public $femaleName;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new job.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The job's unique identifier, omit to create empty instance.
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
        "ssssd",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->name,
        $this->maleName,
        $this->femaleName,
        $this->description
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
   * Get all jobs matching the offset and row count.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $offset
   *   The offset in the result.
   * @param integer $rowCount
   *   The number of rows to retrieve.
   * @return \mysqli_result
   *   The query result.
   */
  public static function getJobs($offset, $rowCount) {
    global $db, $i18n;
    $query = self::getQuery();
    return $db->query("
      {$query}
      WHERE `deleted` = false
      ORDER BY `name` ASC
      LIMIT ? OFFSET ?",
      "ssssdd",
      [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * The count of movies connected with this job.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCount() {
    global $db;
    return $db->query(
      "SELECT count(DISTINCT `movie_id`) as `count` FROM `movies_crew` WHERE `job_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

 /**
   * Get the mysqli result for all movies that are connected with this job.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movies that are connected with this job.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieResult() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `movies`.`id` AS `id`,
        `movies`.`year` AS `year`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`
      FROM `movies`
        LEFT JOIN `movies_crew`
          ON `movies`.`id` = `movies_crew`.`movie_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
      WHERE `movies_crew`.`job_id` = ?
      ORDER BY `displayTitle` DESC",
      "ssd",
      [ $i18n->languageCode, $i18n->languageCode, $this->id ]
    )->get_result();
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
  protected static function getQuery() {
    global $i18n;
    static $query = null;
    if (!$query) {
      $query =
        "SELECT
          `id`,
          `deleted`,
          IFNULL(COLUMN_GET(`dyn_names_sex0`, ? AS CHAR), COLUMN_GET(`dyn_names_sex0`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          IFNULL(COLUMN_GET(`dyn_names_sex1`, ? AS CHAR), COLUMN_GET(`dyn_names_sex1`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `maleName`,
          IFNULL(COLUMN_GET(`dyn_names_sex2`, ? AS CHAR), COLUMN_GET(`dyn_names_sex2`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `femaleName`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`
        FROM `jobs`"
      ;
    }
    return $query;
  }

  /**
   * Get random job identifier.
   *
   * @global \MovLib\Data\Database $db
   * @return integer|null
   *   Random job identifier, or <code>NULL</code> on failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomJobId() {
    global $db;
    $result = $db->query("SELECT `id` FROM `jobs` WHERE `jobs`.`deleted` = false ORDER BY RAND() LIMIT 1")->get_result()->fetch_row();
    if (isset($result[0])) {
      return $result[0];
    }
  }

  /**
   * The count of series connected with this job.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesCount() {
    global $db;
    return $db->query(
      "SELECT count(DISTINCT `series_id`) as `count` FROM `episodes_crew` WHERE `job_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

  /**
   * Get the count of all jobs.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   *   The amount of all jobs.
   * @return integer
   *   The amount of all jobs.
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `jobs` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Initialize job.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->deleted = (boolean) $this->deleted;
    $this->route   = $i18n->r("/job/{0}", [ $this->id ]);
  }

}
