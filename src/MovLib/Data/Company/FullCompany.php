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
namespace MovLib\Data\Company;

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Data\Place;
use \MovLib\Presentation\Error\NotFound;

/**
 * Contains all available information about a company.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FullCompany extends \MovLib\Data\Company\Company {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The company’s creation timestamp.
   *
   * @var string
   */
  public $created;

  /**
   * The company’s translated descriptions.
   *
   * @var string
   */
  public $description;

  /**
   * The company logo’s description.
   *
   * @var string
   */
  public $imageDescription;

  /**
   * The company’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The company’s place.
   *
   * @var integer|object
   */
  public $place;

  /**
   * The company’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Intantiate new Company.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The company's unique ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db, $i18n;
    // Try to load the company for the given identifier.
    if ($id) {
      $this->id = $id;
      $stmt = $db->query("
        SELECT
          `aliases`,
          `created`,
          `defunct_date`,
          `deleted`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS BINARY), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS BINARY)),
          IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS BINARY), COLUMN_GET(`dyn_wikipedia`, '{$i18n->defaultLanguageCode}' AS BINARY)),
          `founding_date`,
          `links`,
          `name`,
          `place_id`,
          `image_uploader_id`,
          `image_width`,
          `image_height`,
          `image_filesize`,
          `image_extension`,
          UNIX_TIMESTAMP(`image_changed`),
          IFNULL(COLUMN_GET(`dyn_image_descriptions`, ? AS BINARY), COLUMN_GET(`dyn_image_descriptions`, '{$i18n->defaultLanguageCode}' AS BINARY)),
          `image_styles`
        FROM `companies`
        WHERE
          `id` = ?
        LIMIT 1",
        "sssd",
        [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $this->id ]
      );
      $stmt->bind_result(
        $this->aliases,
        $this->created,
        $this->defunctDate,
        $this->deleted,
        $this->description,
        $this->wikipedia,
        $this->foundingDate,
        $this->links,
        $this->name,
        $this->place,
        $this->uploaderId,
        $this->width,
        $this->height,
        $this->filesize,
        $this->extension,
        $this->changed,
        $this->imageDescription,
        $this->styles
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $id;
    }

    // The company's logo name is always the company's identifier.
    $this->filename = &$this->id;

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the logo for this company.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Insert a new company into the database.
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
      "INSERT INTO `companies` SET
        `aliases` = ?,
        `created` = CURRENT_TIMESTAMP,
        `defunct_date` = ?,
        `dyn_descriptions` = COLUMN_CREATE('{$i18n->languageCode}', ?),
        `dyn_wikipedia`= COLUMN_CREATE('{$i18n->languageCode}', ?),
        `dyn_image_descriptions` = '',
        `founding_date` = ?,
        `links` = ?,
        `name` = ?,
        `place_id` = ?
        ",
      "sssssssd",
      [
        serialize($this->aliases),
        $this->defunctDate,
        $this->description,
        $this->wikipedia,
        $this->foundingDate,
        serialize($this->links),
        $this->name,
        $this->place,
      ]
    )->insert_id;

    return $this;
  }

  /**
   * Get the mysqli result for all movies this company was involved.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movies of this company.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieResult() {
    global $db, $i18n;
    $result = $db->query(
      "SELECT
        `movies`.`id`,
        `movies`.`year`,
        `movies`.`deleted`,
        `movies`.`year`,
        `movies`.`mean_rating` AS `ratingMean`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`,
        `jobs`.`id` AS `jobId`,
        IFNULL(COLUMN_GET(`jobs`.`dyn_names_sex0`, ? AS CHAR), COLUMN_GET(`jobs`.`dyn_names_sex0`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `jobTitle`
      FROM `movies_crew`
        LEFT JOIN `movies` AS `movies`
          ON `movies`.`id` = `movies_crew`.`movie_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies_crew`.`movie_id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies_crew`.`movie_id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies_crew`.`movie_id`
          AND `p`.`language_code` = ?
        LEFT JOIN `jobs` ON `movies_crew`.`job_id` = `jobs`.`id`
      WHERE `movies_crew`.`company_id` = ?
      ORDER BY `jobTitle` DESC",
      "sssd",
      [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $this->id ]
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
        $movies[$row["id"]]->movie->jobIds                    = [];
        $movies[$row["id"]]->movie->jobTitles                 = [];
        $movies[$row["id"]]->movie->init();
      }
      // We need the job id and translated title for all jobs done by the company.
      array_push($movies[$row["id"]]->movie->jobIds, $row["jobId"]);
      array_push($movies[$row["id"]]->movie->jobTitles, $row["jobTitle"]);
    }
    return $movies;
  }

  /**
   * Get the mysqli result for all releases this company was involved.
   *
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The mysqli result for all releases of this company.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getReleasesResult() {
    global $db;
    return $db->query(
      "SELECT
        `master_releases_labels`.`company_id` AS `company_id`,
        `master_releases_labels`.`master_release_id` AS `master_release_id`
      FROM `master_releases_labels'
        INNER JOIN `title` AS `master_releases_title` ON `master_releases`.`id` = `master_releases_labels`.`master_release_id`
      WHERE `master_releases_labels`.`company_id` = ?
      ORDER BY `master_releases`.`release_date` DESC",
      "d",
      [ $this->id ]
    )->get_result();
  }

  /**
   * Get the mysqli result for all series this company was involved.
   *
   * @todo Implement when series are implemented
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The mysqli result for all series of this company.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesResult() {
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function init() {
    parent::init();
    if ($this->place) {
      $this->place = new Place($this->place);
    }

    $this->aliases = $this->aliases ? unserialize($this->aliases) : [];
    $this->links   = $this->links ? unserialize($this->links) : [];
  }

}
