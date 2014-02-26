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

  /**
   * Amount of movies with this genre.
   *
   * @var integer
   */
  public $moviesCount;

  /**
   * The translated route of this genre.
   *
   * @var string
   */
  public $route;
  /**
   * Amount of series with this genre.
   *
   * @var integer
   */
  public $seriesCount;


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
        "ssssi",
        [ $i18n->languageCode, $i18n->defaultLanguageCode, $i18n->languageCode, $i18n->defaultLanguageCode, $id ]
      );
      $stmt->bind_result(
        $this->id,
        $this->name,
        $this->description,
        $this->moviesCount,
        $this->seriesCount
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
    $query = self::getQuery();
    return $db->query("
        {$query}
        ORDER BY `name` ASC
        LIMIT ? OFFSET ?",
      "ssssii",
      [ $i18n->languageCode, $i18n->defaultLanguageCode, $i18n->languageCode, $i18n->defaultLanguageCode, $rowCount, $offset ]
    )->get_result();
  }

/**
   * Get the mysqli result for all movies that are of this genre.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for all movies that are of this genre.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getMovieResult() {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `movies`.`year` AS `year`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`
      FROM `movies`
        LEFT JOIN `movies_genres`
          ON `movies`.`id` = `movies_genres`.`movie_id`
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
      WHERE `movies_genres`.`genre_id` = ?
      ORDER BY `displayTitle` DESC",
      "ssi",
      [ $i18n->languageCode, $i18n->languageCode, $this->id ]
    )->get_result();
  }

  /**
   * Get the default query.
   *
   * @staticvar string Used to cache the default query.
   * @return string
   *   The default query.
   */
  protected static function getQuery() {
    static $query = null;
    if (!$query) {
      $query =
        "SELECT
          `id`,
          IFNULL(COLUMN_GET(`dyn_names`, ? AS CHAR), COLUMN_GET(`dyn_names`, ? AS CHAR)) AS `name`,
          IFNULL(COLUMN_GET(`dyn_descriptions`, ? AS CHAR), COLUMN_GET(`dyn_descriptions`, ? AS CHAR)) AS `description`,
          `movies_count` AS `moviesCount`,
          `series_count` AS `seriesCount`
        FROM `genres`"
      ;
    }
    return $query;
  }

  /**
   * Initialize genre.
   *
   * @global type $i18n
   */
  protected function init() {
    global $i18n;

    $this->route = $i18n->r("/genre/{0}", [ $this->id ]);
  }
}
