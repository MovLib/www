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
 * Retrieve several movies from the database for listings.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Data\DatabaseArrayObject implements \MovLib\Data\Pagination {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The basic query for most methods.
   *
   * @todo Improve query!
   * @var string
   */
  protected $query =
    "SELECT
      `movie`.`movie_id` AS `id`,
      IFNULL(`title`.`title`, `movie`.`original_title`) AS `displayTitle`,
      `movie`.`original_title` AS `originalTitle`,
      `movie`.`year`
    FROM `movies` AS `movie`
      LEFT JOIN `movies_titles` AS `title` ON `title`.`movie_id` = `movie`.`movie_id`
    WHERE `movie`.`deleted` = false AND `title`.`is_display_title` = true
    "
  ;

  /**
   * Get a movie list ordered by entry date.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $offset [optional]
   *   The lower limit for the pagination (defaults to 0).
   * @param int $rowCount [optional]
   *   The upper limit for the pagination (defaults to 25).
   * @return array
   *   Sorted numeric array containing the movie information as <code>\MovLib\Data\Movie</code> objects.
   */
  public function getMoviesByCreated($offset = 0, $rowCount = self::SPAN_08) {
    $result = $this->query("{$this->query} ORDER BY `movie`.`created` DESC LIMIT ?, ?", "ii", [ $offset, $rowCount ])->get_result();
    while ($movie = $result->fetch_object("\\MovLib\\Data\\Movie\\Movie")) {
      $this->objectsArray[] = $movie;
    }
    return $this;
  }

}