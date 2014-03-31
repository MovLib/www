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

use \MovLib\Data\Place;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the company object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Company extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The company's creation timestamp.
   *
   * @var string
   */
  public $created;

  /**
   * The company's defunct date.
   *
   * @var null|\MovLib\Data\Date
   */
  public $defunctDate;

  /**
   * The company's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The company's translated descriptions.
   *
   * @var null|string
   */
  public $description;

  /**
   * The logo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "company";

  /**
   * The company's founding date.
   *
   * @var null|\MovLib\Data\Date
   */
  public $foundingDate;

  /**
   * The company's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The company logo's description.
   *
   * @var null|string
   */
  public $imageDescription;

  /**
   * The company's translated logo route.
   *
   * @var string
   */
  public $imageRoute;

  /**
   * The company's weblinks.
   *
   * @var null|array
   */
  public $links;

  /**
   * The company's name.
   *
   * @var string
   */
  public $name;

  /**
   * The company's place.
   *
   * @var \MovLib\Data\Place
   */
  public $place;

  /**
   * The company's translated route.
   *
   * @var string
   */
  public $route;

  /**
   * The company's translated Wikipedia link.
   *
   * @var null|string
   */
  public $wikipedia;

  /**
   * The company's total movie count.
   *
   * @var integer
   */
  public $movieCount = 0;

  /**
   * The company's total series count.
   *
   * @var integer
   */
  public $seriesCount = 0;

  /**
   * The company's total release count.
   *
   * @var integer
   */
  public $releaseCount = 0;


  // ------------------------------------------------------------------------------------------------------------------- Initialization


  /**
   * Initialize existing company from unique identifier.
   *
   * @param integer $id
   *   The company's unique identifier to load.
   * @return this
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function init($id) {
    $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `companies`.`id`,
  `companies`.`name`,
  `companies`.`aliases`,
  `companies`.`founding_date`,
  `companies`.`defunct_date`,
  COLUMN_GET(`companies`.`dyn_descriptions`, '{$this->intl->languageCode}' AS BINARY),
  COLUMN_GET(`companies`.`dyn_wikipedia`, '{$this->intl->languageCode}' AS BINARY),
  `companies`.`links`,
  `companies`.`count_movies`,
  `companies`.`count_series`,
  `companies`.`count_releases`,
  `companies`.`deleted`,
  `companies`.`created`,
  `companies`.`place_id`,
  IFNULL(COLUMN_GET(`places`.`dyn_names`, '{$this->intl->languageCode}' AS BINARY), `places`.`name`),
  `places`.`country_code`,
  `places`.`latitude`,
  `places`.`longitude`
FROM `companies`
  LEFT JOIN `places`
    ON `places`.`id` = `companies`.`place_id`
WHERE `companies`.`id` = ?
LIMIT 1
SQL
    );
    $stmt->bind_param("d", $id);
    $stmt->execute();
    $this->place = new Place($this->diContainer);
    $stmt->bind_result(
      $this->id,
      $this->name,
      $this->aliases,
      $this->foundingDate,
      $this->defunctDate,
      $this->description,
      $this->wikipedia,
      $this->links,
      $this->movieCount,
      $this->seriesCount,
      $this->releaseCount,
      $this->deleted,
      $this->created,
      $this->place->id,
      $this->place->name,
      $this->place->countryCode,
      $this->place->latitude,
      $this->place->longitude
    );
    $found = $stmt->fetch();
    $stmt->close();
    if ($found === null) {
      throw new NotFoundException("Couldn't find company for '{$id}'!");
    }
    $this->place->initFetchObject();
    return $this->initFetchObject();
  }

  /**
   * {@inheritdoc}
   */
  public function initFetchObject() {
    $this->unserialize([ &$this->aliases, &$this->links ]);
    $this->toDates([ &$this->foundingDate, &$this->defunctDate ]);
    $this->deleted = (boolean) $this->deleted;
    $this->route   = $this->intl->r("/company/{0}", $this->id);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Insert a new company into the database.
   *
   * @todo Index data with Elastic.
   * @return $this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function create() {
    $this->id = $this->query(
      "INSERT INTO `companies` SET
        `aliases` = ?,
        `created` = CURRENT_TIMESTAMP,
        `defunct_date` = ?,
        `dyn_descriptions` = COLUMN_CREATE('{$this->intl->languageCode}', ?),
        `dyn_wikipedia`= COLUMN_CREATE('{$this->intl->languageCode}', ?),
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
   * @return \mysqli_result
   *   The mysqli result for all movies of this company.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieResult() {
    $result = $this->query(
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
        IFNULL(COLUMN_GET(`jobs`.`dyn_names_sex0`, ? AS CHAR), COLUMN_GET(`jobs`.`dyn_names_sex0`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `jobTitle`
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
      [ $this->intl->languageCode, $this->intl->languageCode, $this->intl->languageCode, $this->id ]
    )->get_result();

    $movies = [];
    while ($row = $result->fetch_assoc()) {
      // Instantiate and initialize a Movie if it is not present yet.
      if (!isset($movies[$row["id"]])) {
        $movies[$row["id"]] = (object) [
          "movie" => new FullMovie($this->diContainer)
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
        $movies[$row["id"]]->movie->initFetchObject();
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
   * @return \mysqli_result
   *   The mysqli result for all releases of this company.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getReleasesResult() {
    return $this->query(
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
   * @return \mysqli_result
   *   The mysqli result for all series of this company.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesResult() {
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Image Methods


  /**
   * Delete the company logo.
   *
   * @todo Implement delete company logo
   * @return this
   */
  public function delete() {
    return $this;
  }

  /**
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param boolean $regenerate [optional]
   *   Whether to regenerate existing styles.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function generateStyles($source, $regenerate = false) {
    // Generate the various image's styles and always go from best quality down to worst quality.
    $this->convert($source, self::STYLE_SPAN_03, self::STYLE_SPAN_03, self::STYLE_SPAN_03, true);
    $this->convert($source, self::STYLE_SPAN_02, self::STYLE_SPAN_02, self::STYLE_SPAN_02, true);
    $this->convert($source, self::STYLE_SPAN_01, self::STYLE_SPAN_01, self::STYLE_SPAN_01, true);

    if ($regenerate === true) {
      $query  = "UPDATE `companies` SET `image_styles` = ? WHERE `id` = ?";
      $types  = "sd";
      $params = [ serialize($this->styles), $this->id ];
    }
    else {
      $this->changed = time();
      $query =
        "UPDATE `companies` SET
          `image_changed`          = FROM_UNIXTIME(?),
          `dyn_image_descriptions` = COLUMN_ADD(`dyn_image_descriptions`, ?, ?),
          `image_extension`        = ?,
          `image_filesize`         = ?,
          `image_height`           = ?,
          `image_styles`           = ?,
          `image_uploader_id`      = ?,
          `image_width`            = ?
        WHERE `id` = ?"
      ;
      $types  = "isssiisdid";
      $params = [
        $this->changed,
        $this->intl->languageCode,
        $this->description,
        $this->extension,
        $this->filesize,
        $this->height,
        serialize($this->styles),
        $this->uploaderId,
        $this->width,
        $this->id,
      ];
    }
    $this->query($query, $types, $params)->close();

    return $this;
  }

  /**
   * Set deletion request identifier.
   *
   * @todo Implement deletion request
   * @param integer $id
   *   The deletion request's unique identifier to set.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function setDeletionRequest($id) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "companies";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "company";
  }

}
