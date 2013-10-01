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
namespace MovLib\Data;

use \MovLib\Exception\DatabaseException;

/**
 * Handling of Genres.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Genre extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Static Methods

  /**
   * Get array with genre names.
   *
   * @param array $ids
   *   Numeric array containing the desired genre IDs.
   * @return array
   *   Array containing the genre names with the genre's unique ID as key.
   */
  public function getGenreNames(array $ids) {
    $ids = array_unique($ids);
    $c = count($ids);
    $in = rtrim(str_repeat("?,", $c), ",");
    $result = $this->select("SELECT `name` FROM `genres` WHERE `genre_id` IN ({$in})",
      str_repeat("d", $c),
      $ids
    );
    $genreNames = null;
    for ($i = 0; $i < $c; ++$i) {
      $genreNames[$ids[$i]] = $result[$i]["name"];
    }
    return $genreNames;
  }

}
