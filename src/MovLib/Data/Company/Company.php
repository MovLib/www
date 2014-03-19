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

use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single company including its logo.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Company extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 220x220>
   *
   * Image style used on the show page to display the company logo.
   *
   * @var integer
   */
  const STYLE_SPAN_03 = \MovLib\Data\Image\SPAN_03;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company's defunct date in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $defunctDate;

  /**
   * The company's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The logo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "company";

  /**
   * The company's founding date in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $foundingDate;

  /**
   * The company's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The company's translated logo route.
   *
   * @var string
   */
  public $imageRoute;

  /**
   * The company's name.
   *
   * @var string
   */
  public $name;

  /**
   * The company's translated route.
   *
   * @var string
   */
  public $route;

  /**
   * The route key of this award.
   *
   * @var string
   */
  public $routeKey;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $id [optional]
   *   The company's unique identifier, leave empty to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct($id = null) {
    global $db;

    // Try to load company based on given identifier.
    if ($id) {
      $stmt = $db->query("
        SELECT
          `id`,
          `deleted`,
          `name`,
          `founding_date`,
          `defunct_date`,
          `image_uploader_id`,
          `image_width`,
          `image_height`,
          `image_filesize`,
          `image_extension`,
          UNIX_TIMESTAMP(`image_changed`),
          `image_styles`
        FROM `companies`
        WHERE
          `id` = ?
        LIMIT 1",
        "d",
        [ $id ]
      );
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->name,
        $this->foundingDate,
        $this->defunctDate,
        $this->uploaderId,
        $this->width,
        $this->height,
        $this->filesize,
        $this->extension,
        $this->changed,
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
   * Get all companies matching the offset and row count.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $offset
   *   The offset in the result.
   * @param integer $rowCount
   *   The number of rows to retrieve.
   * @return \mysqli_result
   *   The query result.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getCompanies($offset, $rowCount) {
    global $db, $i18n;
    return $db->query("
      SELECT
        `id`,
        `deleted`,
        `name`,
        `founding_date` AS `foundingDate`,
        `defunct_date` AS `defunctDate`,
        `image_uploader_id` AS `uploaderId`,
        `image_width` AS `width`,
        `image_height` AS `height`,
        `image_filesize` AS `filesize`,
        `image_extension` AS `extension`,
        UNIX_TIMESTAMP(`image_changed`) AS `changed`,
        COLUMN_GET(`dyn_image_descriptions`, ? AS BINARY) AS `description`,
        `image_styles` AS `styles`
      FROM `companies`
      WHERE
        `deleted` = false
      ORDER BY `id` DESC
      LIMIT ? OFFSET ?",
      "sdi",
      [ $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * Get the total number of the movies this company was involved.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count of the company's unique movies.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesCount() {
    global $db;
    return $db->query(
      "SELECT count(DISTINCT `movie_id`) as `count` FROM `movies_crew` WHERE `company_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

  /**
   * Get random company id.
   *
   * @global \MovLib\Data\Database $db
   * @return integer|null
   *   Random company id or null in case of failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomCompanyId() {
    global $db;
    $query = "SELECT `id` FROM `companies` WHERE `companies`.`deleted` = false ORDER BY RAND() LIMIT 1";
    if ($result = $db->query($query)->get_result()) {
      return $result->fetch_assoc()["id"];
    }
  }

  /**
   * Get the total number of the releases this company was involved.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count of the company's unique releases.
   */
  public function getReleasesCount() {
    global $db;
    return $db->query(
      "SELECT count(*) as `count` FROM `master_releases_labels` WHERE `company_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

  /**
   * Get the total number of the series this company was involved.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count of the company's unique series.
   */
  public function getSeriesCount() {
    global $db;
    return $db->query(
      "SELECT count(DISTINCT `series_id`) as `count` FROM `episodes_crew` WHERE `company_id` = ?", "d", [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

  /**
   * Get the count of all companies which haven't been deleted.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   *   The count of all companies which haven't been deleted.
   * @return integer
   *   The count of all companies which haven't been deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `companies` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Initialize the company with its image, deleted flag and translate the route.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->deleted  = (boolean) $this->deleted;
    $this->routeKey = "/company/{0}";
    $this->route    = $i18n->r($this->routeKey, [ $this->id]);
    $key            = "edit";
    if ($this->uploaderId) {
      $this->imageExists = true;
      $key               = "logo";
      $this->styles      = unserialize($this->styles);
    }
    $this->imageRoute = $i18n->r("/company/{0}/{$key}", [ $this->id ]);

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
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param boolean $regenerate [optional]
   *   Whether to regenerate existing styles.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function generateStyles($source, $regenerate = false) {
    global $db, $i18n;

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
        $i18n->languageCode,
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
    $db->query($query, $types, $params)->close();

    return $this;
  }

  /**
   * Set deletion request identifier.
   *
   * @todo Implement deletion request
   * @global \MovLib\Data\Database $db
   * @param integer $id
   *   The deletion request's unique identifier to set.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function setDeletionRequest($id) {
    return $this;
  }

}
