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
class Company {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company's founding date in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $foundingDate;

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
   * The company's display photo.
   *
   * @var \MovLib\Data\Image\CompanyImage
   */
  public $displayPhoto;

  /**
   * The company's unique identifier.
   *
   * @var integer
   */
  public $id;

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
            `founding_date` AS `foundingDate`,
            `defunct_date` AS `defunctDate`
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
        $this->defunctDate
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
      $this->id = $id;
    }

    // If we have an identifier, either from the above query or directly set via PHP's fetch_object() method, try to
    // load the logo for this company.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the count of all companies which haven't been deleted.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   * @return integer
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
   */
  public static function getCompanies($offset, $rowCount) {
    global $db;
    return $db->query("
        SELECT
          `id`,
          `deleted`,
          `name`,
          `founding_date` AS `foundingDate`,
          `defunct_date` AS `defunctDate`
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
    $this->displayPhoto = new CompanyImage($this->id, $this->name);

    $this->route = $i18n->r("/company/{0}", [ $this->id ]);
  }
}
