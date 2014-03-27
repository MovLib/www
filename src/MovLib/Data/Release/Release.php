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
namespace MovLib\Data\Release;

use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single release.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Release extends \MovLib\Core\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Cinema type.
   *
   * @var integer
   */
  const TYPE_CINEMA = 0;

  /**
   * Home video type.
   *
   * @var integer
   */
  const TYPE_HOME_VIDEO = 1;

  /**
   * TV type.
   *
   * @var integer
   */
  const TYPE_TV = 2;

  /**
   * Web type.
   *
   * @var integer
   */
  const TYPE_WEB = 3;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The release's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The release's last edit timestamp.
   *
   * @var integer
   */
  public $changed;

  /**
   * The release's creation timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The release's ISO-3166-alpha2 country code of the production country.
   *
   * @var string
   */
  public $countryCode;

  /**
   * The release's edition.
   *
   * @var string
   */
  public $edition;

  /**
   * The release's identifiers (aside of catalog numbers).
   *
   * @var \MovLib\Stub\Data\Release\Identifier
   */
  public $identifiers;

  /**
   * The release's labels.
   *
   * Associative containing the release's label identifiers as keys and {@see \MovLib\Stub\Data\Release\Label} objects
   * as values.
   *
   * @var array
   */
  public $labels;

  /**
   * The release's media.
   *
   * Associative array containing the media identifiers as keys and {@see \MovLib\Data\Medium\Medium} objects as values.
   *
   * @var array
   */
  public $media;

  /**
   * The counts of every media format contained in this release.
   *
   * Associative array: Keys are {@see \MovLib\Presentation\Partial\Format\FormatFactory}::FORMAT_* constants,
   * values are the respective counts of these formats.
   *
   * @var array
   */
  public $mediaCounts;

  /**
   * The release's translated notes.
   *
   * @var string
   */
  public $notes;

  /**
   * The release's publishing date for rental.
   *
   * @var string
   */
  public $publishingDateRental;

  /**
   * The release's publishing date for sale.
   *
   * @var string
   */
  public $publishingDateSale;

  /**
   * The release's title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new release.
   *
   * @param identifier $id [optional]
   *   The release's identifier to load, leave empty to create empty instances.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    // Try to load the release for the given identifier.
    if ($id) {
      $result = $this->query(
        "SELECT
          `releases`.`changed`,
          `releases`.`created`,
          `releases`.`country_code`,
          IFNULL(
            COLUMN_GET(`releases`.`dyn_notes`, ? AS BINARY),
            COLUMN_GET(`releases`.`dyn_notes`, {$this->intl->defaultLanguageCode} AS BINARY)
          ) AS `notes`,
          `releases`.`title`,
          `releases`.`publishing_date_rental`,
          `releases`.`publishing_date_sale`,
          `releases`.`edition`,
          `releases`.`bin_identifiers`,
          `releases_media`.`medium_id`,
          `media`.`changed` AS `medium_changed`,
          `media`.`created` AS `medium_created`,
          `media`.`bootleg` AS `medium_bootleg`,
          IFNULL(
            COLUMN_GET(`media`.`dyn_notes`, ? AS BINARY),
            COLUMN_GET(`media`.`dyn_notes`, {$this->intl->defaultLanguageCode} AS BINARY)
          ) AS `medium_notes`,
          `media`.`bin_format` AS `medium_format`,
          `media_movies`.`movie_id`,
          `movies`.`deleted` AS `movie_deleted`,
          `movies`.`year` AS `movie_year`,
          `movies`.`mean_rating` AS `movie_mean_rating`,
          IFNULL(`dt`.`title`, `ot`.`title`) AS `movie_display_title`,
          IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `movie_display_title_language_code`,
          `ot`.`title` AS `movie_original_title`,
          `ot`.`language_code` AS `movie_original_title_language_code`,
          `releases_labels`.`company_id`,
          `companies`.`name` AS `company_name`
        FROM `releases`
        INNER JOIN `releases_media`
          ON `releases_media`.`release_id` = ?
        INNER JOIN `media`
          ON `media`.`id` = `releases_media`.`medium_id`
        LEFT JOIN `media_movies`
          ON `media_movies`.`medium_id` = `media`.`id`
        LEFT JOIN `movies` FORCE INDEX (movies_deleted)
          ON `movies`.`id` = `media_movies`.`movie_id`
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
        LEFT JOIN `releases_labels`
          ON `releases_labels`.`release_id` = ?
        LEFT JOIN `companies`
          ON `companies`.`id` = `releases_label`.`company_id`
        WHERE `releases`.`id` = ?",
        "sdsdd",
        [ $this->intl->languageCode, $id, $this->intl->languageCode, $id, $id ]
      )->get_result();

      while ($row = $result->fetch_object()) {
        // @todo: implement
      }
      if (!$this->created) {
        throw new NotFound;
      }

      $this->id = $id;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the release's labels as associative arrays.
   *
   * @return array
   *   Numeric array containing the labels.
   *
   *   Format: [ "id" => LABEL_ID, "name" => LABEL_NAME, "catalog_number" => CATALOG_NUMBER ]
   */
  public function getLabels() {
    return $this->query(
      "SELECT
        `companies`.`id`,
        `companies`.`name`,
        `releases_labels`.`catalog_number`
      FROM `releases_labels`
      INNER JOIN `companies`
        ON `companies`.`id` = `releases_labels`.`company_id`
      WHERE `releases_labels`.`release_id` = ?
      ORDER BY `companies`.`name`{$this->collations[$this->intl->languageCode]} ASC",
      "d",
      [ $this->id ]
    )->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  /**
   * Get paginated releases result.
   *
   * @internal The returned {@see \mysqli_result} is prepared for direct instantiating via fetch object of this class.
   * @param integer $offset [optional]
   *   The offset, usually provided by the pagination trait, defaults to <code>0</code>.
   * @param integer $rowCount
   *   The row count, usually provided by the pagination trait, defaults to <code>25</code>.
   * @return \mysqli_result
   *   Paginated releases result.
   */
  public function getReleases($offset = 0, $rowCount = 8) {
    return $this->query(
      "SELECT
        `releases`.`id`,
        `releases`.`changed`,
        `releases`.`created`,
        `releases`.`country_code` AS `countryCode`,
        `releases`.`title`,
        `releases`.`publishing_date_rental` AS `publishingDateRental`,
        `releases`.`publishing_date_sale` AS `publishingDateSale`,
        `releases`.`edition`,
        `releases`.`bin_media_counts` AS `mediaCounts`
      FROM `releases`
      ORDER BY `releases`.`created` DESC
      LIMIT ?
      OFFSET ?",
      "di",
      [ $rowCount, $offset ]
    )->get_result();
  }

}
