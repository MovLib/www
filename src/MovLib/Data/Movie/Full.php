<?php

/* !
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
namespace MovLib\Data\Movie;

use \MovLib\Data\Country;

/**
 * Represents a single movie containing all available properties.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Full extends \MovLib\Data\Movie\Movie {

  public $cast;
  public $countries;
  public $created;
  public $directors;
  public $genres;
  public $rank;
  public $rating;
  public $ratingMean;
  public $runtime;
  public $styles;
  public $synopsis;
  public $taglines;
  public $title;
  public $votes;
  public $website;

  /**
   * Instantiate new movie.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id
   *   The unique movie's ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \OutOfBoundsException
   */
  public function __construct($id) {
    global $db, $i18n;
    $this->id = $id;
    $stmt     = $db->query(
      "SELECT
        `movies`.`original_title`,
        `movies`.`rating`,
        `movies`.`mean_rating`,
        `movies`.`votes`,
        `movies`.`deleted`,
        `movies`.`year`,
        `movies`.`runtime`,
        `movies`.`rank`,
        COLUMN_GET(`movies`.`dyn_synopses`, '{$i18n->languageCode}' AS BINARY),
        `movies`.`website`,
        `movies`.`created`,
        IFNULL(`titles`.`title`, `movies`.`original_title`)
      FROM `movies`
        LEFT JOIN `titles`
          ON `titles`.`movie_id` = `movies`.`id`
          AND `titles`.`language_code` = ?
      WHERE `movies`.`id` = ?
      LIMIT 1",
      "sd",
      [ $i18n->languageCode, $this->id ]
    );
    $stmt->bind_result(
      $this->originalTitle,
      $this->rating,
      $this->ratingMean,
      $this->votes,
      $this->deleted,
      $this->year,
      $this->runtime,
      $this->rank,
      $this->synopsis,
      $this->website,
      $this->created,
      $this->displayTitle
    );
    if (!$stmt->fetch()) {
      throw new \OutOfBoundsException("Couldn't find movie for ID '{$id}'.");
    }
    $stmt->close();
    $this->init();

    // ----------------------------------------------------------------------------------------------------------------- Countries

    $countries = null;
    $stmt      = $db->query("SELECT `country_code` FROM `movies_countries` WHERE `movie_id` = ?", "d", [ $this->id ]);
    $result    = $stmt->get_result();
    while ($row = $result->fetch_row()) {
      $countries[$row[0]] = "";
    }
    $stmt->close();
    if ($countries) {
      $this->countries = Country::getCountries($countries);
    }

    // ----------------------------------------------------------------------------------------------------------------- Genres

    $stmt = $db->query(
      "SELECT
        `genres`.`id`,
        IFNULL(COLUMN_GET(`genres`.`dyn_names`, ? AS CHAR), COLUMN_GET(`genres`.`dyn_names`, '{$i18n->languageCode}' AS CHAR)) AS `name`
      FROM `movies_genres`
        INNER JOIN `genres` ON `genres`.`id` = `movies_genres`.`genre_id`
      WHERE `movie_id` = ?
      ORDER BY `name` ASC",
      "sd",
      [ $i18n->languageCode, $this->id ]
    );
    $result = $stmt->get_result();
    while ($row = $result->fetch_row()) {
      $this->genres[$row[0]] = $row[1];
    }
    $stmt->close();

    // ----------------------------------------------------------------------------------------------------------------- Styles

    $stmt = $db->query(
      "SELECT
        `styles`.`id`,
        IFNULL(COLUMN_GET(`styles`.`dyn_names`, ? AS CHAR), COLUMN_GET(`styles`.`dyn_names`, '{$i18n->languageCode}' AS CHAR)) AS `name`
      FROM `movies_styles`
        INNER JOIN `styles` ON `styles`.`id` = `movies_styles`.`style_id`
      WHERE `movies_styles`.`movie_id` = ?
      ORDER BY `name` ASC",
      "sd",
      [ $i18n->languageCode, $this->id ]
    );
    $result = $stmt->get_result();
    while ($row = $result->fetch_row()) {
      $this->styles[$row[0]] = $row[1];
    }
    $stmt->close();
  }

  /**
   * Get the mysqli result for the movie's cast.
   *
   * @todo Order cast by weight not by name!
   * @global \MovLib\Data\Database $db
   * @param integer $limit [optional]
   *   The amount of cast members to fetch.
   * @return \mysqli_result
   *   The mysqli result for the movie's cast.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getCastResult($limit = 8) {
    global $db;
    return $db->query(
      "SELECT
        `persons`.`id`,
        `persons`.`name`
      FROM `movies_cast`
        INNER JOIN `persons` ON `persons`.`id` = `movies_cast`.`person_id`
      WHERE `movies_cast`.`movie_id` = ?
      ORDER BY `persons`.`name` ASC
      LIMIT ?",
      "di",
      [ $this->id, $limit ]
    )->get_result();
  }

  /**
   * Get the mysqli result for the movie's directors.
   *
   * @todo Order directors by weight not by name!
   * @global \MovLib\Data\Database $db
   * @return \mysqli_result
   *   The mysqli result for the movie's directors.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getDirectorsResult() {
    global $db;
    return $db->query(
      "SELECT
        `persons`.`id`,
        `persons`.`name`
      FROM `movies_directors`
        INNER JOIN `persons` ON `persons`.`id` = `movies_directors`.`person_id`
      WHERE `movies_directors`.`movie_id` = ?
      ORDER BY `persons`.`name` ASC",
      "d",
      [ $this->id ]
    )->get_result();
  }

}
