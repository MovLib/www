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
namespace MovLib\Data\History;

use \MovLib\Exception\HistoryException;

/**
 * Description of MovieHistoryModel
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Movie extends AbstractHistory {

  /**
   * The current movie
   * @var associative array
   */
  public $movie;

  /**
   * Instantiate new movie history model.
   *
   * Construct new movie history model from given ID and gather basic movie information available in the movies table.
   * If the ID is invalid a <code>\MovLib\Exception\HistoryException</code> will be thrown.
   *
   * @param int $id
   *  The movie id
   * @throws HistoryException
   *  If movie is not found
   */
  public function __construct($id) {
    parent::__construct($id);

    $this->movie = $this->select(
      "SELECT `original_title`, `runtime`, `year`
        FROM `movies`
        WHERE `movie_id` = ?",
      "d",
      [$this->id]
    );

    if (isset($this->movie[0]) === false) {
      throw new HistoryException("Could not find movie with ID '{$this->id}'!");
    }
  }

  /**
   * Implementation ob abstract method <code>writeFiles()</code>.
   * Writes all history relevant information in files.
   */
  public function writeFiles() {
    foreach (["original_title", "runtime", "year"] as $fildname) {
      $this->writeToFile($fildname, $this->movie[0][$fildname]);
    }

    foreach ($this->getSynopses() as $synopsis_language => $synopsis) {
      $this->writeToFile("{$synopsis_language}_synopsis", $synopsis);
    }

    $this->writeRelatedRowsToFile("movies_titles",    ["title", "is_display_title", "language_id"], ["dyn_comments"]);
    $this->writeRelatedRowsToFile("movies_taglines",  ["tagline", "language_id"], ["dyn_comments"]);
    $this->writeRelatedRowsToFile("movies_links",     ["title", "text", "url", "language_id"]);
    $this->writeRelatedRowsToFile("movies_trailers");
    $this->writeRelatedRowsToFile("movies_cast",      ["person_id", "roles"]);
    $this->writeRelatedRowsToFile("movies_crew",      ["crew_id"]);
    $this->writeRelatedRowsToFile("movies_awards",    ["award_count"]);
    $this->writeRelatedRowsToFile("movies_relationships",    ["movie_id_other", "relationship_type_id"]);
    $this->writeRelatedRowsToFile("movies_genres",    ["genre_id"]);
    $this->writeRelatedRowsToFile("movies_styles",    ["style_id"]);
    $this->writeRelatedRowsToFile("movies_languages", ["language_id"]);
    $this->writeRelatedRowsToFile("movies_countries", ["country_id"]);
    $this->writeRelatedRowsToFile("movies_directors", ["person_id"]);
  }

  /**
   * Get all translated synopses and return them as associative array.
   *
   * @return associative array
   */
  private function getSynopses() {
    $synopses = $this->select(
      "SELECT COLUMN_JSON(dyn_synopses) AS `dyn_synopses`
        FROM `movies`
        WHERE `movie_id` = ?",
      "d",
      [$this->id]
    );

    return json_decode($synopses[0]["dyn_synopses"], true);
  }

}
