<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Model;

use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\ErrorException;
use \MovLib\Exception\MovieException;
use \MovLib\Model\AbstractModel;
use \MovLib\Utility\AsyncLogger;

/**
 * The movie model is responsible for all database related functionality of a single movie entry.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieModel extends AbstractModel {

  /**
   * The unique movie's ID.
   *
   * @var boolean|int
   */
  private $movieId;

  /**
   * The current movie as associative array.
   *
   * @var array
   */
  private $movie;

  /**
   * Keyed array containing all posters sorted by rating.
   *
   * @var array
   */
  private $posters;

  /**
   * Construct new (empty) movie model.
   *
   * @param string $languageCode
   *   The language code for the movie queries in ISO 639-1:2002 format.
   * @param int $movieId
   *   [Optional] The unique movie ID of the movie that should be loaded from the database.
   */
  public function __construct($movieId = false) {
    parent::__construct();
    $this->movieId = $movieId;
  }

  /**
   * Retrieve basic movie data.
   *
   * <b>Basic</b> in this context refers to the data that is stored in the <tt>movies</tt>, <tt>movies_[lang]</tt> and
   * <tt>movie_titles</tt> database tables.
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   * @param int $id
   *   The movie's unique ID.
   * @return array
   *   Associative array containing the basic movie data.
   * @throws \MovLib\Exception\MovieException
   *   If no movie exists with this ID.
   * @throws \MovLib\Exception\DatabaseException
   *   If <tt>movie_titles</tt> table contains no data.
   */
  public function getMovieBasic($id) {
    global $i18n;

    // If we already have a movie, return it.
    if ($this->movie) {
      return $this->movie;
    }

    // Try to get the movie and localized movie data from the database.
    try {
      $movie = $this->query(
        "SELECT *
        FROM `movies` `m`
        LEFT JOIN `movies_{$i18n->getLanguageCode()}` `ml`
          ON `m`.`movie_id` = `ml`.`movies_movie_id`
        WHERE `m`.`movie_id` = ?
        LIMIT 1", "i", [ $id ]
      )[0];
    } catch (ErrorException $e) {
      throw new MovieException("Could not find movie with ID '{$id}'!");
    }

    // Try to get the titles from the database.
    $titles = $this->query("SELECT * FROM `movie_titles` WHERE `movies_movie_id` = ?", "i", [ $id ]);
    if (empty($titles)) {
      // If the above query returns zero results, something is terribly wrong.
      $e = new DatabaseException("Could not fetch title for movie with ID '{$id}'!");
      AsyncLogger::logException($e, AsyncLogger::LEVEL_FATAL);

      throw $e;
    }

    // Set original and display titles.
    $titlesCount = count($titles);
    for ($i = 0; $i < $titlesCount; ++$i) {
      settype($titles[$i]["is_original_title"], "boolean");

      // Check if this is the original title.
      if ($titles[$i]["is_original_title"] === true) {
        $movie["original_title_id"] = &$titles[$i]["title_id"];
        $movie["original_title"] = &$titles[$i]["title"];

        // Check if the display title might be the original title as well.
        if ($movie["display_title_id"] === null) {
          $movie["display_title_id"] = &$movie["original_title_id"];
          $movie["display_title"] = &$movie["original_title"];
          break;
        }
      }

      // Check if this is the display title.
      if ($titles[$i]["title_id"] === $movie["display_title_id"]) {
        $movie["display_title_id"] = &$titles[$i]["title_id"];
        $movie["display_title"] = &$titles[$i]["title"];
      }
    }

    settype($movie["display"], "boolean");

    $this->movie = $movie;
    $this->movie["titles"] = $titles;
    $this->movieId = $movie["movie_id"];

    return $this->movie;
  }

  /**
   * Retrieve full movie information e.g. for the movie show view.
   * @param int $id
   *  The movie's id.
   * @return array
   *  The movie information as an associative array.
   */
  public function getMovieFull($id) {
    $this->getMovieBasic($id);
    $this->retrieveMovieLanguages();
    $this->retrieveMovieCountries();
    $this->retrieveGenres();
    $this->retrieveStyles();
    $this->retrievePoster();

    return $this->movie;
  }

  /**
   *
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   */
  private function retrieveMovieLanguages() {
    global $i18n;
    $this->movie["languages"] = $this->query("SELECT l.`language_id`, l.`name_en`, l.`iso_639-1`, l.`name_{$i18n->getLanguageCode()}`
      FROM `movies_has_languages` mhl INNER JOIN `languages` l
      ON mhl.`languages_language_id` = l.`language_id`
      WHERE mhl.`movies_movie_id` = ?
      ORDER BY l.`name_{$this->languageCode}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  /**
   *
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   */
  private function retrieveMovieCountries() {
    global $i18n;
    $this->movie["countries"] = $this->query("SELECT c.`country_id`, c.`iso_alpha_2`, c.`iso_alpha_3`, c.`name_en`, c.`name_{$i18n->getLanguageCode()}`
      FROM `movies_has_countries` mhc INNER JOIN `countries` c
      ON mhc.`countries_country_id` = c.`country_id`
      WHERE mhc.`movies_movie_id` = ?
      ORDER BY c.`name_{$this->languageCode}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  /**
   *
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   */
  private function retrieveGenres() {
    global $i18n;
    $this->movie["genres"] = $this->query("SELECT g.`genre_id`, g.`name_en`, g.`name_{$i18n->getLanguageCode()}`
      FROM `movies_has_genres` `mhg` INNER JOIN `genres` `g`
      ON `mhg`.`genres_genre_id` = `g`.`genre_id`
      WHERE `mhg`.`movies_movie_id` = ?
      ORDER BY g.`name_{$i18n->getLanguageCode()}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  /**
   *
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   */
  private function retrieveStyles() {
    global $i18n;
    $this->movie["styles"] = $this->query("SELECT s.`style_id`, s.`name_en`, s.`name_{$i18n->getLanguageCode()}`
      FROM `movies_has_styles` `mhs` INNER JOIN `styles` s
      ON `mhs`.`styles_style_id` = `s`.`style_id`
      WHERE `mhs`.`movies_movie_id` = ?
      ORDER BY s.`name_{$i18n->getLanguageCode()}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  /**
   *
   *
   * @global \MovLib\Utility\I18n $i18n
   *   The global i18n instance.
   */
  private function retrievePoster() {
    global $i18n;
    $this->movie["poster"] = null;
    $result = $this->query("SELECT i.`file_id`, i.`file_name`, i.`extension`, l.`iso_639-1` AS `language_code` FROM `images` i
      INNER JOIN `posters` p ON i.`file_id` = p.`images_file_id`
      INNER JOIN `languages` l
      ON p.`languages_language_id` = l.`language_id`
      WHERE p.`movies_movie_id` = ? AND l.`iso_639-1` = ?
      ORDER BY p.`rating` DESC, i.`file_id` ASC LIMIT 1",
      "is",
      [ $this->movieId, $i18n->getLanguageCode() ]
    );
    if (count($result) > 0) {
      $this->movie["poster"] = $result[0];
    }
  }

  /**
   * Get the primary poster data for the current movie.
   *
   * @todo Which poster should we use as primary poster if none has an upvote?
   * @return array
   *   Associative array containing every bit of data from the poster.
   */
  public function getPrimaryPoster() {
    if ($this->posters) {
      return $this->posters[0];
    }
    try {
      $poster = $this->query(
        "SELECT *
        FROM `posters` `p`
        INNER JOIN `images` `i`
          ON `p`.`id` = `i`.`id`
        WHERE `p`.`movie_id` = ?
        ORDER BY `p`.`rating` DESC
        LIMIT 1",
        "i", $this->movieId
      )[0];
      $this->posters = [ $poster ];
      return $this->posters;
    } catch (ErrorException $e) {
      // If accessing offset 0 of the array fails. Do nothing!
    }
  }

  /**
   * Get all posters data for the current movie.
   *
   * @return array
   *   Keyed array containing every poster's data.
   */
  public function getPosters() {
    if ($this->posters) {
      return $this->posters;
    }
    $this->posters = $this->query(
      "SELECT *
      FROM `posters` `p`
      INNER JOIN `images` `i`
        ON `p`.`id` = `i`.`id`
      WHERE `p`.`movie_id` = ?
      ORDER BY `p`.`rating` DESC",
      "i", $this->movieId
    );
    return $this->posters;
  }

}
