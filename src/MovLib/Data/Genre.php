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
   * @global \MovLib\Data\I18n $i18n
   * @param array $genreIds
   *   Numeric array containing the desired genre IDs.
   * @return array
   *   Array containing the genre names with the genre's unique ID as key.
   */
  public function getGenreNames(array $genreIds) {
    global $i18n;
    $genreIds = array_unique($genreIds);
    $c = count($genreIds);
    $in = rtrim(str_repeat("?,", $c), ",");

    if ($i18n->languageCode == "en") {
      $result = $this->select("SELECT `genre_id`, `name` FROM `genres` WHERE `genre_id` IN ({$in})",
        str_repeat("d", $c),
        $genreIds
      );
    }
    else {
      $result = $this->select(
        "SELECT `genre_id`, COLUMN_GET(`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name`, `name` AS `en_name`" .
          "FROM `genres` WHERE `genre_id` IN ({$in})",
          str_repeat("d", $c),
        $genreIds
      );
    }

    $genreNames = [];
    $c = count($result);
    for ($i = 0; $i < $c; ++$i) {
      $genreNames[$result[$i]["genre_id"]] = empty($result[$i]["name"]) ? $result[$i]["en_name"] : $result[$i]["name"];
    }
    return $genreNames;
  }

}
