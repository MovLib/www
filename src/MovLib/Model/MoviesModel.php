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

/**
 * The MoviesModel is responsible for all database functionality when it comes to movie listings.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviesModel extends BaseModel {

  /**
   * Get a movie list ordered by entry date.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param int $lowerBound [optional]
   *   The lower limit for the pagination (defaults to 0).
   * @param int $upperBound [optional]
   *   The upper limit for the pagination (defaults to 100).
   * @return array
   *   Sorted numeric array containing the movie information as <code>\MovLib\Model\MovieModel</code> objects.
   */
  public function getMoviesByCreated($lowerBound = 0, $upperBound = 100) {
    $movies = $this->select("SELECT `movie_id` AS `id` FROM `movies` WHERE `deleted` = 0 ORDER BY `created` DESC LIMIT ?, ?", "ii", [ $lowerBound, $upperBound ]);
    $c = count($movies);
    for ($i = 0; $i < $c; ++$i) {
      $movies[$i] = new MovieModel($movies[$i]["id"]);
    }
    return $movies;
  }

}