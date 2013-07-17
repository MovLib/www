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

use \MovLib\Model\AbstractModel;

/**
 * Description of RatingModel
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RatingModel extends AbstractModel {

  /**
   * Retrieve a user's rating for a specific movie.
   *
   * @param int $userId
   *   The user's unique ID.
   * @param int $movieId
   *   The movie's unique ID.
   * @return int|null
   *   The user's rating for this movie, null if none is present.
   */
  public function getMovieRating($userId, $movieId) {
    $rating = $this->select(
      "SELECT
        `user_id` AS `userId`,
        `movie_id` AS `movieId`
        FROM `movies_ratings`
        WHERE `user_id` = ?
          AND `movie_id` = ?
        LIMIT 1",
      "dd",
      [ $userId, $movieId ]
    );
    if (isset($rating[0])) {
      return $rating[0];
    }
  }

}