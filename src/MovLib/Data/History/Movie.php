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
   * Instantiate new movie history model.
   *
   * @param int $id
   *  The movie id
   */
  public function __construct($id) {
    parent::__construct($id, ["original_title", "runtime", "year"], ["dyn_synopses"]);
  }

  /**
   * Implementation ob abstract method <code>writeFiles()</code>.
   * Writes all history relevant information in files.
   */
  public function writeFiles() {
    foreach (["original_title", "runtime", "year"] as $fildname) {
      $this->writeToFile($fildname, $this->historyObject[0][$fildname]);
    }

    foreach (json_decode($this->historyObject[0]["dyn_synopses"], true) as $synopsis_language => $synopsis) {
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

}
