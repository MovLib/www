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

/**
 * Description of MovieTitles
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTitles extends \MovLib\Data\DatabaseArrayObject {  
 
  
  // ------------------------------------------------------------------------------------------------------------------- Properties
  
  
  /**
   * The movie's unique identifier.
   *
   * @var int
   */
  public $movieId;
  
  
  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new MovieTitles object.
   * 
   * @param int $movieId
   *   The unique ID of the movie the titles belong to.
   */
  public function __construct($movieId) {
    $this->movieId = $movieId;
    $this->query = "SELECT
      `id`,
      `language_id` AS `languageId`,
      `movie_id` AS `movieId`,
      `title`,
      `is_display_title` AS `isDisplayTitle`,
      COLUMN_JSON(`dyn_comments`) AS `dynComments`
    FROM `movies_titles`";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Order all movie titles of one movie by ID.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function orderById() {
    $this->objectsArray = [];

    $result   = $this->query("{$this->query} WHERE `movie_id` = ?", "i", [ $this->movieId ])->get_result();

    /* @var $movieTitle \MovLib\Data\Genre */
    while ($movieTitle = $result->fetch_object("\\MovLib\\Data\\Movie\\MovieTitle")) {
      if (!empty($movieTitle->dynComments)) {
        $dynComments = json_decode($movieTitle->dynComments, true);
        unset($movieTitle->dynComments);
        foreach ($dynComments as $language => $comment) {
          $movieTitle->dynComments[] = [$language => $comment];
        }
      }
      else {
        $movieTitle->dynComments = [];
      }
      
      $this->objectsArray[] = $movieTitle;
    }
    return $this;
  }

}
