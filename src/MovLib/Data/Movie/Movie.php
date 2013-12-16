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
   * The movie's release year.
   *
   * @var integer
   */
  public $year;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Database $db
   * @param integer $id [optional]
   *   The unique movie's ID to load.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \OutOfBoundsException
   */
  public function __construct($id = null) {
    global $i18n, $db;

    // Load the movie if an ID was passed to the constructor.
    if ($id) {
      $query = self::getQuery();
      $stmt  = $db->query("{$query} WHERE `movies`.`id` = ? LIMIT 1", "sd", [ $i18n->languageCode, $id ]);
      $stmt->bind_result(
        $this->id,
        $this->deleted,
        $this->displayTitle,
        $this->originalTitle,
        $this->year
      );
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find movie for ID '{$id}'");
      }
      $stmt->close();
    }

    // Load the display poster if the above query set the movie ID or this object was instantiated by PHP and the
    // property is already set.
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all images as mysqli result for the given type with the desired offset and row count.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $typeId
   *   The image type identifier, use the class constants of the various movie images.
   * @param integer $offset
   *   The offset, usually provided by the pagination trait.
   * @param integer $rowCount
   *   The row count, usually provided by the pagination trait.
   * @return \mysqli_result
   *   The mysqli result of the query.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getImageResult($typeId, $offset, $rowCount) {
    global $db;
    return $db->query(
      "SELECT
        `id`,
        `country_code` AS `countryCode`,
        `language_code` AS `languageCode`,
        `width`,
        `height`,
        `extension`,
        UNIX_TIMESTAMP(`changed`) AS `changed`,
        `upvotes`,
        `styles`
      FROM `movies_images`
      WHERE `movie_id` = ?
        AND `type_id` = ?
        AND `deleted` = false
      ORDER BY `upvotes` DESC, `id` ASC
      LIMIT ? OFFSET ?",
      "diii",
      [ $this->id, $typeId, $rowCount, $offset ]
    )->get_result();
  }

  public function getImageStreamResult($typeId, $id) {
    global $db;
    return $db->query(
      "SELECT
        `id`,
        `extension`,
        UNIX_TIMESTAMP(`changed`) AS `changed`,
        `styles`
      FROM `movies_images`
      WHERE `movie_id` = ?
        AND `type_id` = ?
        AND `id` != ?
      ORDER BY `upvotes` DESC, `id` ASC",
      "dii",
      [ $this->id, $typeId, $id ]
    )->get_result();
  }

  /**
   * Get the total image count for this movie and the given image type.
   *
   * @global \MovLib\Data\Database $db
   * @param integer $typeId
   *   The desired image type identifier, use the class constants of the movie images.
   * @return integer
   *   The total image count for this movie and the given image type.
   */
  public function getImageCount($typeId) {
    global $db;
    return $db->query(
      "SELECT COUNT(*) FROM `movies_images` WHERE `movie_id` = ? AND `type_id` = ? GROUP BY `type_id`",
      "di",
      [ $this->id, $typeId ]
    )->get_result()->fetch_row()[0];
  }

  public static function getUndeletedMoviesCount() {
    global $db;
    static $count = null;
    if (!$count) {
      $count = $db->query("SELECT COUNT(`id`) FROM `movies` WHERE `deleted` = false LIMIT 1")->get_result()->fetch_row()[0];
    }
    return $count;
  }

  public static function getMovies($offset, $rowCount) {
    global $db, $i18n;
    static $movies = [];

    if (!isset($movies[$i18n->locale])) {
      $query  = self::getQuery();
      $result = $db->query(
        "{$query} WHERE `movies`.`deleted` = false ORDER BY `movies`.`id` DESC LIMIT ? OFFSET ?",
        "sii",
        [ $i18n->languageCode, $rowCount, $offset ]
      )->get_result();
      while ($movie = $result->fetch_object(__CLASS__)) {
        $movies[$i18n->locale][$movie->id] = $movie;
      }
    }

    return $movies[$i18n->locale];
  }

  /**
   * Get the default query.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The default query.
   */
  private static function getQuery() {
    global $i18n;
    return
      "SELECT
        `movies`.`id`,
        `movies`.`deleted`,
        IFNULL(`titles`.`title`, `movies`.`original_title`) AS `displayTitle`,
        `movies`.`original_title` AS `originalTitle`,
        `movies`.`year`
      FROM `movies`
        LEFT JOIN `movies_titles` ON `movies_titles`.`movie_id` = `movies`.`id`
        LEFT JOIN `titles`
          ON `titles`.`movie_id` = `movies`.`id`
          AND `titles`.`language_code` = ?
      "
    ;
  }

  protected function init() {
    global $db, $i18n;

    // Create the full default display title.
    if ($this->year) {
      $this->displayTitleWithYear = $i18n->t("{movie_title} ({movie_year})", [
        "movie_title" => $this->displayTitle,
        "movie_year"  => $this->year,
      ]);
    }
    else {
      $this->displayTitleWithYear = $this->displayTitle;
    }

    // Always cast deleted to a real boolean if we are instantiating a movie.
    if ($this->deleted) {
      $this->deleted = (boolean) $this->deleted;
    }

    // Fetch the display poster from the database.
    $this->displayPoster = $db->query(
      "SELECT
        `id`,
        `extension`,
        UNIX_TIMESTAMP(`changed`) AS `changed`,
        `styles`
      FROM `movies_images`
      WHERE `movie_id` = ?
        AND `type_id` = ?
      ORDER BY `upvotes` DESC
      LIMIT 1",
      "di",
      [ $this->id, MoviePoster::TYPE_ID ]
    )->get_result()->fetch_object("\\MovLib\\Data\\Image\\MoviePoster", [ $this->id, $this->displayTitleWithYear ]);

    // Load an empty poster if we have no posters at all.
    if (!$this->displayPoster) {
      $this->displayPoster = new MoviePoster($this->id, $this->displayTitleWithYear);
    }

    return $this;
  }

}
