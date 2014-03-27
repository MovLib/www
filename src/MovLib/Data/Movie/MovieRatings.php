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
 * Handling of one Ratings
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieRatings extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Load the movie ratings from MOVIE_ID.
   *
   * @var int
   */
  const FROM_MOVIE_ID = "movie_id";

  /**
   * Load the movie ratings from USER_ID.
   *
   * @var int
   */
  const FROM_USER_ID = "user_id";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The query to fetch movie ratings without <code>WHERE</code> clause.
   *
   * @var string
   */
  protected $query =
    "SELECT
      `movie_id` AS `movieId`,
      `user_id` AS `userId`,
      `created`,
      `rating`
    FROM `movies_ratings`"
  ;

  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  protected $types = [
    self::FROM_MOVIE_ID => "i",
    self::FROM_USER_ID  => "i",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * Get all movie ratings ordered by their creation timestamp.
   *
   * @param string $from
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value
   *   Data to identify the movie ratings, see the various <var>FROM_*</var> class constants.
   * @return array
   *   Numeric array containing MovieRatings.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function getOrderedByCreated($from, $value) {
    $ratings = [];
    $result = $db->query("{$this->query} WHERE `{$from}` = ? ORDER BY `created` DESC", $this->types[$from], [ $value ])->get_result();
    while ($movieRating = $result->fetch_object("\\MovLib\\Data\\Movie\\MovieRating")) {
      $ratings[] = $movieRating;
    }
    return $ratings;
  }

}
