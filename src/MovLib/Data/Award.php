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
 * Represents one or more awards including photos.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Award extends \MovLib\Data\Image\AbstractImage {


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
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The award's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The award's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The photo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "award";

  /**
   * The award's unique identifier.
   *
   * @var integer
   */
  public $id;

 /**
   * The photo's translated description.
   *
   * @var string
   */
  public $imageDescription;

  /**
   * The photo's translated photo route.
   *
   * @var string
   */
  public $imageRoute;

  /**
   * The award’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The award's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The awards’s place.
   *
   * @var integer|object
   */
  public $place;

  /**
   * The translated route of this award.
   *
   * @var string
   */
  public $route;

  /**
   * The award’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods

  /**
   * Instantiate new award.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $id [optional]
   *   The award's unique identifier, leave empty to create empty instance.
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct($id = null) {
    global $db, $i18n;

    // Try to load award based on given identifier.
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
        $this->description,
        $this->wikipedia,
        $this->uploaderId,
        $this->width,
        $this->height,
        $this->filesize,
        $this->extension,
        $this->changed,
        $this->imageDescription,
        $this->styles,
        $this->place,
        $this->links,
        $this->aliases
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $id;
    }

    // The award's photo name is always the award's identifier.
    $this->filename = &$this->id;

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the photo for this award.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all awards matching the offset and row count.
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
  public static function getAwards($offset, $rowCount) {
    global $db, $i18n;
    $query = self::getQuery();
    return $db->query("
        {$query}
        ORDER BY `name` ASC
        LIMIT ? OFFSET ?",
      "ssssid",
      [ $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * Get all categories of an award.
   *
   * @todo Implement when award categories are implemented.
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The query result containing award categories.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getCategories() {
    return $this;
  }

  /**
   * The total count of movies that received this award.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The total count of movies.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieCount() {
    global $db;
    return $db->query(
      "SELECT
        count(DISTINCT `movie_id`) as `count`
        FROM `movies_awards` as `ma`
        LEFT JOIN `awards_categories` as `mac`
          ON `ma`.award_category_id = `mac`.`id`
        LEFT JOIN `awards`
          ON `mac`.`award_id` = `awards`.`id`
        WHERE `awards`.`id` = ?",
      "d",
      [ $this->id ]
    )->get_result()->fetch_assoc()["count"];
  }

  /**
   * Get the mysqli result for all movies that have received this award.
   *
   * @todo Implement
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movies that have received this award.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieResult() {
    return $this;
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
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`,
          IFNULL(COLUMN_GET(`dyn_wikipedia`, ? AS BINARY), COLUMN_GET(`dyn_wikipedia`, '{$i18n->defaultLanguageCode}' AS BINARY)),
          `image_uploader_id` AS `uploaderId`,
          `image_width` AS `width`,
          `image_height` AS `height`,
          `image_filesize` AS `filesize`,
          `image_extension` AS `extension`,
          UNIX_TIMESTAMP(`image_changed`) AS `changed`,
          IFNULL(COLUMN_GET(`dyn_image_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_image_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `imageDescription`,
          `image_styles` AS `styles`,
          `place_id` AS `place`,
          `links`,
          `aliases`
        FROM `awards`"
      ;
    }
    return $query;
  }

  /**
   * Get random award identifier.
   *
   * @global \MovLib\Data\Database $db
   * @return integer|null
   *   Random award identifier, or <code>NULL</code> on failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomAwardId() {
    global $db;
    $result = $db->query("SELECT `id` FROM `awards` WHERE `awards`.`deleted` = false ORDER BY RAND() LIMIT 1");
    if ($result = $db->query($query)->get_result()) {
      return $result->fetch_assoc()["id"];
    }
  }

  /**
   * The count of series that received this award.
   *
   * @todo Implement when series are implemented
   * @global \MovLib\Data\Database $db
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesCount() {
    return 0;
  }

  /**
   * Get the mysqli result for all series that have received this award.
   *
   * @todo Implement when series are implemented
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all series that have received this award.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesResult() {
    return $this;
  }

  /**
   * Get the count of all awards.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   *   The total amount of awards which haven't been deleted.
   * @return integer
   *   The total amount of awards which haven't been deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `awards` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Initialize the award with its photo, deleted flag and translate the route.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;
    if ($this->place) {
      $this->place = new Place($this->place);
    }

    $this->aliases = $this->aliases ? unserialize($this->aliases) : [];
    $this->links   = $this->links ? unserialize($this->links) : [];

    $this->deleted = (boolean) $this->deleted;
    $this->route   = $i18n->r("/award/{0}", [ $this->id]);
    $key           = "edit";
    if ($this->uploaderId) {
      $this->imageExists = true;
      $key               = "photo";
      $this->styles      = unserialize($this->styles);
    }
    $this->imageRoute = $i18n->r("/award/{0}/{$key}", [ $this->id ]);

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Image Methods


  /**
   * Delete the award photo.
   *
   * @todo Implement delete award photo.
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
      $query  = "UPDATE `awards` SET `image_styles` = ? WHERE `id` = ?";
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
        $this->imageDescription,
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
