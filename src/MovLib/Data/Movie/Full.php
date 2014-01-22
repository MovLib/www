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
use \MovLib\Presentation\Error\NotFound;

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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  public $cast;
  public $commit;
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
  public $userRating;
  public $votes;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param integer $id
   *   The unique movie's ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id) {
    global $db, $i18n, $session;
    $this->id = $id;
    $stmt = $db->query(
      "SELECT
        `movies`.`created`,
        `movies`.`deleted`,
        COLUMN_GET(`movies`.`dyn_synopses`, ? AS CHAR),
        `movies`.`mean_rating`,
        `movies`.`rating`,
        `movies`.`votes`,
        `movies`.`commit`,
        `movies`.`rank`,
        `movies`.`runtime`,
        `movies`.`year`,
        IFNULL(`dt`.`title`, `ot`.`title`),
        IFNULL(`dt`.`language_code`, `ot`.`language_code`),
        `ot`.`title`,
        `ot`.`language_code`
      FROM `movies`
        LEFT JOIN `movies_display_titles`  AS `mdt` ON `mdt`.`movie_id` = `movies`.`id` AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles`          AS `dt`  ON  `dt`.`title_id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot` ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles`          AS `ot`  ON  `ot`.`title_id` = `mot`.`title_id`
      WHERE `movies`.`id` = ?
      LIMIT 1",
      "sd",
      [ $i18n->languageCode, $id ]
    );
    $stmt->bind_result(
      $this->created,
      $this->deleted,
      $this->synopsis,
      $this->ratingMean,
      $this->originalTitle,
      $this->originalTitleLanguageCode,
      $this->rating,
      $this->votes,
      $this->commit,
      $this->rank,
      $this->runtime,
      $this->year
    );
    if (!$stmt->fetch()) {
      throw new NotFound;
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

    // ----------------------------------------------------------------------------------------------------------------- User Rating

    $stmt = $db->query(
      "SELECT `rating` FROM `movies_ratings` WHERE `user_id` = ? AND `movie_id` = ? LIMIT 1",
      "dd",
      [ $session->userId, $this->id ]
    );
    $result = $stmt->get_result()->fetch_row();
    if (isset($result[0])) {
      $this->userRating = $result[0];
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

  /**
   * Get the mysqli result for the movie's trailers.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return \mysqli_result
   *   The mysqli result for the movie's trailers.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getTrailersResult() {
    global $db, $i18n;
    return $db->query(
      "SELECT `url` FROM `movies_trailers` WHERE `movie_id` = ? AND `language_code` IN(?, 'xx') ORDER BY `weight` DESC",
      "ds",
      [ $this->id, $i18n->languageCode ]
    )->get_result();
  }

  /**
   * Rate this movie.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\User\Session $session
   * @param integer $rating
   *   The user's rating for this movie.
   * @return this
   * @throws \ErrorException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function rate($rating) {
    global $db, $session;

    // Insert or update the user's rating for this movie.
    if (!$this->userRating) {
      $db->query("INSERT INTO `movies_ratings` SET `movie_id` = ?, `user_id` = ?, `rating` = ?", "ddi", [ $this->id, $session->userId, $rating ])->close();
      $this->votes++;
    }
    else {
      $db->query("UPDATE `movies_ratings` SET `rating` = ? WHERE `movie_id` = ? AND `user_id` = ?", "idd", [ $rating, $this->id, $session->userId ])->close();
    }

    // Update the mean rating of this movie.
    $db->query(
      "UPDATE `movies` SET `mean_rating` = (
        SELECT ROUND(SUM(`mr`.`rating`) / COUNT(`mr`.`rating`), 1) FROM `movies_ratings` AS `mr` WHERE `mr`.`movie_id` = ?
      ), `votes` = ? WHERE `id` = ?",
      "did",
      [ $this->id, $this->votes, $this->id ]
    )->close();

    // Get the updated mean rating for us.
    $this->ratingMean = $db->query("SELECT `mean_rating` FROM `movies` WHERE `id` = ? LIMIT 1", "d", [ $this->id ])->get_result()->fetch_row()[0];

    // Update the old rating with the new rating.
    $this->userRating = $rating;

    return $this;
  }

}
