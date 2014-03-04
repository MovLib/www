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

use \MovLib\Data\Image\CompanyImage;
use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single company.
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
   * @see CompanyImage::__construct()
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
   * The company logo's translated route.
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

    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete the company logo.
   *
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
   * Get the count of all companies which haven't been deleted.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   *   The companies count.
   * @return integer
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
   * Get all companies matching the offset and row count.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $offset
   *   The offset in the result.
   * @param integer $rowCount
   *   The number of rows to retrieve.
   * @return \mysqli_result
   *   The query result.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getCompanies($offset, $rowCount) {
    global $db;
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
        `image_styles` AS `styles`
      FROM `companies`
      WHERE
        `deleted` = false
      ORDER BY `id` DESC
      LIMIT ? OFFSET ?",
      "di",
      [ $rowCount, $offset ]
    )->get_result();
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
   * Initialize company.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;
    $this->deleted = (boolean) $this->deleted;
    $this->route   = $i18n->r("/company/{0}", [ $this->id ]);

    if ($this->uploaderId) {
      $this->imageExists = true;
    }

    $this->alternativeText = $i18n->t("Logo of {company_name}.", [ "company_name" => $this->name ]);
    $this->filename        = $this->id;
    $key                   = $this->imageExists === true ? "photo" : "edit";
    $this->imageRoute      = $i18n->r("/company/{0}/{$key}", [ $this->id ]);
  }

  /**
   * Set deletion request identifier.
   *
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
