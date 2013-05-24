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
use \MovLib\Model\AbstractModel;

/**
 * The movie model is responsible for all database related functionality of a single movie entry.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieModel extends AbstractModel {

  /**
   * The language code for the movie queries in ISO 639-1:2002 format.
   *
   * @var string
   */
  private $languageCode;

  /**
   * The movie id corresponding to the current movie.
   *
   * @var int
   */
  private $movieId = false;

  /**
   * The current movie as associative array.
   *
   * @var array
   */
  private $movie = [];

  public function __construct($languageCode) {
    parent::__construct();
    $this->languageCode = $languageCode;
  }

  public function create() {
    if ($this->movieId !== false) {
      throw new DatabaseException("The movie with id {$this->movieId} already exists.");
    }
    // @todo create movie entry
    //$this->movieId = last_insert_id_from_mysql
    return $this;
  }

  /**
   * Retrieve basic movie information as associative array.
   *
   * @param int $id
   *  The movie's id.
   * @return array
   *  The basic movie information as associative array.
   * @throws DatabaseException
   *  If the movie id does not exist.
   */
  public function getMovieBasic($id) {
    if (empty($this->movie) === false) {
      return $this->movie;
    }

    $query = "SELECT * FROM movies m LEFT JOIN movies_{$this->languageCode} ml ON m.movie_id = ml.movies_movie_id WHERE m.movie_id = ?";

    $queryResult = $this->query($query, "i", [ $id ]);

    if ( empty( $queryResult ) ) {
      throw new DatabaseException("Movie with ID: {$id} does not exist.");
    }

    $this->movie = $queryResult[0];

    $query = "SELECT t.`title_id`, t.`title`, t.`is_original_title`, t.`languages_language_id` FROM movies m INNER JOIN movie_titles t ON m.movie_id = t.movies_movie_id WHERE m.movie_id = ?";
    $this->movie["titles"] = $this->query($query, "i", [ $id ]);

    $this->movie["display"] = (boolean) $this->movie["display"];

    $titlesCount = count($this->movie["titles"]);
    for ($i = 0; $i < $titlesCount; ++$i) {
      $this->movie["titles"][$i]["is_original_title"] = (boolean) $this->movie["titles"][$i]["is_original_title"];
      if ($this->movie["titles"][$i]["is_original_title"] === true) {
        $this->movie["original_title_id"] = $this->movie["titles"][$i]["title_id"];
        $this->movie["original_title"] = $this->movie["titles"][$i]["title"];
        if ($this->movie["display_title_id"] === null) {
          $this->movie["display_title_id"] = &$this->movie["original_title_id"];
          $this->movie["display_title"] = &$this->movie["original_title"];
          break;
        }
      }
      if ($this->movie["titles"][$i]["title_id"] === $this->movie["display_title_id"]) {
        $this->movie["display_title_id"] = $this->movie["titles"][$i]["title_id"];
        $this->movie["display_title"] = $this->movie["titles"][$i]["title"];
      }
    }

    $this->movieId = $this->movie["movie_id"];
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
    $this->retrievePosters();

    return $this->movie;
  }

  private function retrieveMovieLanguages() {
    $this->movie["languages"] = $this->query("SELECT l.`language_id`, l.`name_en`, l.`iso_639-1`, l.`name_{$this->languageCode}`
      FROM `movies_has_languages` mhl INNER JOIN `languages` l
      ON mhl.`languages_language_id` = l.`language_id`
      WHERE mhl.`movies_movie_id` = ?
      ORDER BY l.`name_{$this->languageCode}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  private function retrieveMovieCountries() {
    $this->movie["countries"] = $this->query("SELECT c.`country_id`, c.`iso_alpha_2`, c.`iso_alpha_3`, c.`name_en`, c.`name_{$this->languageCode}`
      FROM `movies_has_countries` mhc INNER JOIN `countries` c
      ON mhc.`countries_country_id` = c.`country_id`
      WHERE mhc.`movies_movie_id` = ?
      ORDER BY c.`name_{$this->languageCode}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  private function retrieveGenres() {
    $this->movie["genres"] = $this->query("SELECT g.`genre_id`, g.`name_en`, g.`name_{$this->languageCode}`
      FROM `movies_has_genres` `mhg` INNER JOIN `genres` `g`
      ON `mhg`.`genres_genre_id` = `g`.`genre_id`
      WHERE `mhg`.`movies_movie_id` = ?
      ORDER BY g.`name_{$this->languageCode}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  private function retrieveStyles() {
    $this->movie["styles"] = $this->query("SELECT s.`style_id`, s.`name_en`, s.`name_{$this->languageCode}`
      FROM `movies_has_styles` `mhs` INNER JOIN `styles` s
      ON `mhs`.`styles_style_id` = `s`.`style_id`
      WHERE `mhs`.`movies_movie_id` = ?
      ORDER BY s.`name_{$this->languageCode}` ASC",
      "i",
      [ $this->movieId ]
    );
  }

  private function retrievePosters() {
    $this->movie["poster"] = null;
    $result = $this->query("SELECT i.`file_id`, i.`file_name`, i.`extension`, l.`iso_639-1` AS `language_code` FROM `images` i
      INNER JOIN `posters` p ON i.`file_id` = p.`images_file_id`
      INNER JOIN `languages` l
      ON p.`languages_language_id` = l.`language_id`
      WHERE p.`movies_movie_id` = ?
      ORDER BY p.`rating` DESC, i.`file_id` ASC LIMIT 1",
      "i",
      [ $this->movieId ]
    );
    if (count($result) > 0) {
      $this->movie["poster"] = $result[0];
    }
  }

}
