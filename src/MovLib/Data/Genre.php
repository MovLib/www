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

/**
 * Handling of one Genre.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Genre extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The genre's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The genre's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The genre's name in the current display language.
   *
   * @var string
   */
  public $name;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __construct($id = null) {
    global $db, $i18n;
    if ($id) {
      $query = self::getQuery();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the count of all genres.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar null|integer $count
   * @return integer
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `genres` LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Get all genres matching the offset and row count.
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
  public static function getGenres($offset, $rowCount) {
    global $db, $i18n;
    return $db->query("
        SELECT
          `id`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, ? AS CHAR)) AS `name`
        FROM `genres`
        ORDER BY `name` ASC
        LIMIT ? OFFSET ?",
      "ssii",
      [ $i18n->languageCode, $i18n->defaultLanguageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * Get the default query.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar string Used to cache the default query.
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
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->languageCode}' AS CHAR)) AS `name`,
          COLUMN_GET('dyn_descriptions`, ? AS CHAR) AS `description`
        FROM `genres`"
      ;
    }
    return $query;
  }
}
