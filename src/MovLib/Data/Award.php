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
 * Handling of one Award.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Award extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The award's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The award's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The translated route of this award.
   *
   * @var string
   */
  public $route;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __construct($id = null) {
    global $db, $i18n;
    if ($id) {
      $query = self::getQuery();
      $stmt = $db->query("
          {$query}
          WHERE
            `id` = ?
          LIMIT 1",
        "ssd",
        [ $i18n->languageCode, $i18n->languageCode, $id ]
      );
      $stmt->bind_result(
        $this->id,
        $this->name,
        $this->description
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();

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
   */
  public static function getAwards($offset, $rowCount) {
    global $db, $i18n;
    $query = self::getQuery();
    return $db->query("
        {$query}
        ORDER BY `name` ASC
        LIMIT ? OFFSET ?",
      "ssid",
      [ $i18n->languageCode, $i18n->languageCode, $rowCount, $offset ]
    )->get_result();
  }

  /**
   * The count of movies that received this award.
   *
   * @global \MovLib\Data\Database $db
   * @return integer
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
          ON `ma`.`award_id` = `awards`.`id`
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
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `name`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, '{$i18n->defaultLanguageCode}' AS CHAR)) AS `description`
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
    $result = $db->query("SELECT `id` FROM `awards` ORDER BY RAND() LIMIT 1")->get_result()->fetch_row();
    if (isset($result[0])) {
      return $result[0];
    }
  }

  /**
   * The count of series that received this award.
   *
   * @todo Implement
   * @global \MovLib\Data\Database $db
   * @return integer
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getSeriesCount() {
    global $db;
    return 0;
  }

  /**
   * Get the mysqli result for all series that have received this award.
   *
   * @todo Implement
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
   *   The total amount of awards.
   * @return integer
   *   The total amount of awards.
   */
  public static function getTotalCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `awards` LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  /**
   * Initialize award.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->route = $i18n->r("/award/{0}", [ $this->id ]);
  }
  
}
