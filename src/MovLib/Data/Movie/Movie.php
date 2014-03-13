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
namespace MovLib\Data\Movie;

use \MovLib\Data\Image\MoviePoster;
use \MovLib\Presentation\Error\NotFound;

/**
 * Represents a single movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movie {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The movie's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The movie's display poster.
   *
   * @var \MovLib\Data\Image\MoviePoster
   */
  public $displayPoster;

  /**
   * The movie's display title.
   *
   * @var string
   */
  public $displayTitle;

  /**
   * The movie's display title's ISO alpha-2 language code.
   *
   * @var string
   */
  public $displayTitleLanguageCode;

  /**
   * The movie's display title with the year appended in brackets.
   *
   * @internal
   *   This is used by so many other classes that we only want to concatenate it once and for all.
   * @var string
   */
  public $displayTitleWithYear;

  /**
   * The movie's original title.
   *
   * @var string
   */
  public $originalTitle;

  /**
   * The movie's original title's ISO alpha-2 language code.
   *
   * @var string
   */
  public $originalTitleLanguageCode;

  /**
   * Default query to fetch a movie from the database.
   *
   * @var string
   */
  protected static $query =
    "SELECT
      `movies`.`id`,
      `movies`.`deleted`,
      `movies`.`year`,
      `movies`.`mean_rating` AS `ratingMean`,
      IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
      IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
      `ot`.`title` AS `originalTitle`,
      `ot`.`language_code` AS `originalTitleLanguageCode`,
      `p`.`poster_id` AS `displayPoster`
    FROM `movies` FORCE INDEX (movies_deleted)
      LEFT JOIN `movies_display_titles` AS `mdt`
        ON `mdt`.`movie_id` = `movies`.`id`
        AND `mdt`.`language_code` = ?
      LEFT JOIN `movies_titles` AS `dt`
        ON `dt`.`movie_id` = `movies`.`id`
        AND `dt`.`id` = `mdt`.`title_id`
      LEFT JOIN `movies_original_titles` AS `mot`
        ON `mot`.`movie_id` = `movies`.`id`
      LEFT JOIN `movies_titles` AS `ot`
        ON `ot`.`movie_id` = `movies`.`id`
        AND `ot`.`id` = `mot`.`title_id`
      LEFT JOIN `display_posters` AS `p`
        ON `p`.`movie_id` = `movies`.`id`
        AND `p`.`language_code` = ?"
  ;

  /**
   * The movie's average rating.
   *
   * @var float
   */
  public $ratingMean;

  /**
   * The movie's translated route.
   *
   * @var string
   */
  public $route;

  /**
   * The movie's rating by a user.
   *
   * @var integer
   */
  protected $userRating = false;

  /**
   * The timestamp of the user's rating.
   *
   * @var integer
   */
  protected $userRatingCreated;

  /**
   * The movie's release year.
   *
   * @var integer
   */
  public $year;

  public $directorTitle;
  public $castTitle;
  public $roles;
  public $jobs;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Database $db
   * @param integer $id [optional]
   *   The movie's unique identifier to load, defaults to no identifier which creates an empty movie object.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($id = null) {
    global $i18n, $db;

    // Try to load the movie if an identifier was passed to the constructor.
    if ($id) {
      $query = self::$query;
      $stmt  = $db->query("{$query} WHERE `movies`.`id` = ? LIMIT 1", "ssd", [ $i18n->languageCode, $i18n->languageCode, $id ]);
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->year,
        $this->ratingMean,
        $this->displayTitle,
        $this->displayTitleLanguageCode,
        $this->originalTitle,
        $this->originalTitleLanguageCode,
        $this->displayPoster
      );
      if (!$stmt->fetch()) {
        throw new NotFound;
      }
      $stmt->close();
    }

    // Initialize the movie if we have an identifier, either from above query or from fetch object.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get paginated movies result.
   *
   * @internal The returned {@see \mysqli_result} is prepared for direct instantiating via fetch object of this class.
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $offset
   *   The offset, usually provided by the pagination trait.
   * @param integer $rowCount
   *   The row count, usually provided by the pagination trait.
   * @return \mysqli_result
   *   Paginated movies result.
   */
  public static function getMovies($offset, $rowCount) {
    global $db, $i18n;
    $query = self::$query;
    return $db->query("{$query} WHERE `movies`.`deleted` = false ORDER BY `movies`.`id` DESC LIMIT ? OFFSET ?", "ssii", [
      $i18n->languageCode, $i18n->languageCode, $rowCount, $offset
    ])->get_result();
  }

  /**
   * Get total movies count.
   *
   * @global \MovLib\Data\Database $db
   * @param null|boolean $deleted [optional]
   *   Pass <code>TRUE</code> to count only deleted movies, <code>NULL</code> to count absolutely all movies and
   *   <code>FALSE</code> (default) to count only undeleted movies.
   * @return integer
   *   Total movies count.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getMoviesCount($deleted = false) {
    global $db;

    // It's not a problem that we aren't usign a prepared statement to insert the deleted state because 99% of all
    // queries calling this method will use the default.
    $where = null;
    if ($deleted === true || $deleted === false) {
      $deleted = (integer) $deleted;
      $where   = "WHERE `deleted` = {$deleted}";
    }

    return $db->query("SELECT COUNT(*) FROM `movies` {$where} LIMIT 1")->get_result()->fetch_row()[0];
  }

  /**
   * Get random movie identifier.
   *
   * @global \MovLib\Data\Database $db
   * @return integer|null
   *   Random movie identifier, or <code>NULL</code> on failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomMovieId() {
    global $db;
    $result = $db->query("SELECT `id` FROM `movies` WHERE `deleted` = false ORDER BY RAND() LIMIT 1")->get_result()->fetch_row();
    if (isset($result[0])) {
      return $result[0];
    }
  }

  /**
   * Get the rating of a specific user for this movie.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\User\Session $session
   * @param integer $userId
   *   The user's ID to get the rating for.
   * @return null|integer
   *   The rating for the user or <code>NULL</code> if the user has not rated this movie yet.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getUserRating($userId) {
    global $db;

    // Guardian pattern.
    if ($this->userRating !== false) {
      return [ "rating" => $this->userRating, "created" => $this->userRatingCreated ];
    }

    $result = $db->query("SELECT `rating`, `created` FROM `movies_ratings` WHERE `user_id` = ? AND `movie_id` = ? LIMIT 1", "dd", [ $userId, $this->id ])->get_result()->fetch_row();
    if (isset($result[0])) {
      $this->userRating = $result[0];
      $this->userRatingCreated = $result[1];
      return [ "rating" => $this->userRating, "created" => $this->userRatingCreated ];
    }
  }

  /**
   * Fetch rated movies for a user.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $userId
   *   The user's ID to fetch the ratings for.
   * @return \mysqli_result
   */
  public static function getUserRatings($userId) {
    global $db, $i18n;
    return $db->query(
      "SELECT
        `movies`.`id`,
        `movies`.`deleted`,
        `movies`.`year`,
        `movies`.`mean_rating` AS `ratingMean`,
        `mr`.`rating` AS `userRating`,
        `mr`.`created` AS `userRatingCreated`,
        IFNULL(`dt`.`title`, `ot`.`title`) AS `displayTitle`,
        IFNULL(`dt`.`language_code`, `ot`.`language_code`) AS `displayTitleLanguageCode`,
        `ot`.`title` AS `originalTitle`,
        `ot`.`language_code` AS `originalTitleLanguageCode`,
        `p`.`poster_id` AS `displayPoster`
      FROM `movies` FORCE INDEX (movies_deleted)
        LEFT JOIN `movies_display_titles` AS `mdt`
          ON `mdt`.`movie_id` = `movies`.`id`
          AND `mdt`.`language_code` = ?
        LEFT JOIN `movies_titles` AS `dt`
          ON `dt`.`movie_id` = `movies`.`id`
          AND `dt`.`id` = `mdt`.`title_id`
        LEFT JOIN `movies_original_titles` AS `mot`
          ON `mot`.`movie_id` = `movies`.`id`
        LEFT JOIN `movies_titles` AS `ot`
          ON `ot`.`movie_id` = `movies`.`id`
          AND `ot`.`id` = `mot`.`title_id`
        LEFT JOIN `display_posters` AS `p`
          ON `p`.`movie_id` = `movies`.`id`
          AND `p`.`language_code` = ?
        INNER JOIN `movies_ratings` AS `mr`
          ON `mr`.`movie_id` = `movies`.`id`
      WHERE `movies`.`deleted` = FALSE AND `mr`.`user_id` = ?
      ORDER BY `mr`.`created` DESC",
      "ssd",
      [ $i18n->languageCode, $i18n->languageCode, $userId ]
    )->get_result();
  }

  /**
   * Initialize the loaded movie.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function init() {
    global $db, $i18n;

    // Build the route to the movie.
    $this->route = $i18n->r("/movie/{0}", [ $this->id ]);

    // Ensure deleted has to the correct type for later comparisons.
    $this->deleted = (boolean) $this->deleted;

    // Construct full display title including the year. This combination is needed all over the place.
    $this->displayTitleWithYear = isset($this->year) ? $i18n->t("{0} ({1})", [ $this->displayTitle, $this->year ]) : $this->displayTitle;

    // Load the oldest poster if we don't have a display poster for the current language.
    if (!$this->displayPoster) {
      $stmt   = $db->query("SELECT MIN(`id`) FROM `posters` WHERE `movie_id` = ?", "d", [ $this->id ]);
      $result = $stmt->get_result()->fetch_row();
      if (isset($result[0])) {
        $this->displayPoster = $result[0];
      }
      $stmt->close();
    }

    // Load the actual display poster for this movie.
    $stmt = $db->query("SELECT `id`, UNIX_TIMESTAMP(`changed`) AS `changed`, `extension`, `styles` FROM `posters` WHERE `id` = ? LIMIT 1", "d", [ $this->displayPoster ]);
    $this->displayPoster = $stmt->get_result()->fetch_object("\\MovLib\\Data\\Image\\MoviePoster", [ $this->id, $this->displayTitleWithYear ]);
    $stmt->close();

    // Load an empty poster if above query returned with no result (fetch object will simply return NULL in that case).
    if (!$this->displayPoster) {
      $this->displayPoster = new MoviePoster($this->id, $this->displayTitleWithYear);
    }

    return $this;
  }

}
