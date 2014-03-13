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

use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single person including their photo.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Person extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 220x220>
   *
   * Image style used on the show page to display the person photo.
   *
   * @var integer
   */
  const STYLE_SPAN_03 = \MovLib\Data\Image\SPAN_03;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person's date of birth in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $birthDate;

  /**
   * The person's birth name.
   *
   * @var string
   */
  public $bornName;

  /**
   * The person's date of death in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $deathDate;

  /**
   * The person's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The photo's path within the upload directory.
   *
   * @var string
   */
  protected $directory = "person";

  /**
   * The person's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The person's name.
   *
   * @var string
   */
  public $name;

  /**
   * The person's nickname.
   *
   * @var string
   */
  public $nickname;

  /**
   * Numeric array containing person's roles in a movie.
   *
   * Every entry consists of the offsets <code>"id"</code> and <code>"name"</code>. If id is not set, the role has
   * no own person page and thus will never be linked.
   *
   * @var array
   */
  public $roles;

  /**
   * The person's translated route.
   *
   * @var string
   */
  public $route;

  /**
   * The person's sex.
   *
   * @var integer
   */
  public $sex;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $id [optional]
   *   The unique person's identifier to load, leave empty to create empty instance.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $db;

    // Try to load the person for the given identifier.
    if ($id) {
      $stmt = $db->query(
        "SELECT
          `deleted`,
          `name`,
          `sex`,
          `birthdate`,
          `born_name`,
          `deathdate`,
          `nickname`,
          `image_uploader_id`,
          `image_width`,
          `image_height`,
          `image_filesize`,
          `image_extension`,
          `image_styles`
        FROM `persons`
        WHERE
          `id` = ?
        LIMIT 1",
        "d",
        [ $id ]
      );
      $stmt->bind_result(
        $this->deleted,
        $this->name,
        $this->sex,
        $this->birthDate,
        $this->bornName,
        $this->deathDate,
        $this->nickname,
        $this->uploaderId,
        $this->width,
        $this->height,
        $this->filesize,
        $this->extension,
        $this->styles
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $id;
    }

    // The person's photo name is always the person's identifier, so set it here.
    $this->filename = &$this->id;

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the photo for this person.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the total number of the movies this person has appeared in.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count of the person's unique movies.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMoviesCount() {
    global $db;
    $ids = $db->query(
      "SELECT DISTINCT
        `movies`.`id`
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
      WHERE `movies`.`deleted` = false
        AND NOT (`md`.`person_id` IS NULL AND `mc`.`person_id` IS NULL AND `mcr`.`person_id` IS NULL)",
      "ddd",
      [ $this->id, $this->id, $this->id ]
    )->get_result()->fetch_all();
    return count($ids);
  }

  /**
   * Get all movies matching the offset and row count.
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
  public static function getPersons($offset, $rowCount) {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `id`,
        `deleted`,
        `name`,
        `sex`,
        `birthdate` AS `birthDate`,
        `born_name` AS `bornName`,
        `deathdate` AS `deathDate`,
        `nickname`,
        `image_uploader_id` AS `uploaderId`,
        `image_width` AS `width`,
        `image_height` AS `height`,
        `image_filesize` AS `filesize`,
        `image_extension` AS `extension`,
        UNIX_TIMESTAMP(`image_changed`) AS `changed`,
        COLUMN_GET(`dyn_image_descriptions`, ? AS BINARY) AS `description`,
        `image_styles` AS `styles`
      FROM `persons`
      WHERE `deleted` = false
      ORDER BY `id` DESC
      LIMIT ? OFFSET ?",
      "sdi",
      [ $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * Get random person id.
   *
   * @global \MovLib\Data\Database $db
   * @return integer|null
   *   Random person id or null in case of failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomPersonId() {
    global $db;
    $query = "SELECT `id` FROM `persons` WHERE `persons`.`deleted` = false ORDER BY RAND() LIMIT 1";
    if ($result = $db->query($query)->get_result()) {
      return $result->fetch_assoc()["id"];
    }
  }

  /**
   * Get the total number of the releases this person has worked on.
   *
   * @todo Implement when releases are implemented.
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count of the person's unique releases.
   */
  public function getReleasesCount() {
    return 0;
  }

  /**
   * Get the total number of the series this person has appeared in.
   *
   * @todo Implement when series are implemented.
   * @global \MovLib\Data\Database $db
   * @return integer
   *   The count of the person's unique series.
   */
  public function getSeriesCount() {
    return 0;
  }

  /**
   * Get the count of all persons who haven't been deleted.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   *   The count of all persons who haven't been deleted.
   * @return integer
   *   The count of all persons who haven't been deleted.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `persons` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Initialize the person with their image, deleted flag and translate their route.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function init() {
    global $i18n;

    $this->deleted = (boolean) $this->deleted;
    $this->route   = $i18n->r("/person/{0}", [ $this->id]);
    $key           = "edit";
    if ($this->uploaderId) {
      $this->imageExists = true;
      $key               = "photo";
      $this->styles      = unserialize($this->styles);
    }
    $this->imageRoute = $i18n->r("/person/{0}/{$key}", [ $this->id ]);

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Image Methods


  /**
   * Delete the image.
   *
   * @todo Implement delete person image
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
      $query  = "UPDATE `persons` SET `image_styles` = ? WHERE `id` = ?";
      $types  = "sd";
      $params = [ serialize($this->styles), $this->id ];
    }
    else {
      $this->changed = time();
      $query =
        "UPDATE `persons` SET
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
