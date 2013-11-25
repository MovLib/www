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
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param integer $id [optional]
   *   The unique movie's ID to load.
   */
  public function __construct($id = null) {
    global $db, $i18n;

    // Load the movie if an ID was passed to the constructor.
    if ($id) {
      $this->id = $id;
      $stmt     = $db->query(
        "SELECT
          `movie`.`deleted`,
          IFNULL(`title`.`title`, `movie`.`original_title`),
          `movie`.`original_title`,
          `movie`.`year`
        FROM `movies` AS `movie`
          LEFT JOIN `movies_titles` AS `mt` ON `mt`.`movie_id` = `movie`.`id`
          LEFT JOIN `titles` AS `title`
            ON `title`.`movie_id` = `movie`.`id`
            AND `title`.`id` = `mt`.`display_title_{$i18n->languageCode}`
        WHERE `movie`.`id` = ?
        LIMIT 1",
        "d",
        [ $this->id ]
      );
      $stmt->bind_result(
        $this->deleted,
        $this->displayTitle,
        $this->originalTitle,
        $this->year
      );
      if (!$stmt->fetch()) {
        throw new \DomainException("Couldn't find movie for ID '{$this->id}'.");
      }
      $stmt->close();
    }

    // Load the display poster if the above query set the movie ID or this object was instantiated by PHP and the
    // property is already set.
    if ($this->id) {
      $this->init();
    }
  }

  protected function init() {
    global $db, $i18n;

    // Create the full default display title.
    $this->displayTitleWithYear = $this->displayTitle;
    if ($this->year) {
      $this->displayTitleWithYear .= " {$i18n->t("({0})", [ $this->year ])}";
    }

    // Always cast deleted to a real boolean if we are instantiating a movie.
    $this->deleted = (boolean) $this->deleted;

    // Fetch the display poster from the database.
    $this->displayPoster = $db->query(
      "SELECT `id`, `extension`, `changed` FROM `movies_images` WHERE `movie_id` = ? ORDER BY `upvotes` DESC LIMIT 1",
      "d",
      [ $this->id ]
    )->get_result()->fetch_object("\\MovLib\\Data\\Image\\MoviePoster", [ $this->id, $this->displayTitleWithYear ]);

    // Load an empty poster if we have no posters at all.
    if (!$this->displayPoster) {
      $this->displayPoster = new MoviePoster($this->id, $this->displayTitleWithYear);
    }

    return $this;
  }

}
