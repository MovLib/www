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
namespace MovLib\Data\Genre;

/**
 * Defines the genre set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class GenreSet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public function getEntityClassName() {
    return "\\MovLib\\Data\\Genre\\Genre";
  }

  /**
   * {@inheritdoc}
   */
  public function getOrdered($by, $offset, $limit) {
    // @todo Store counts as columns in table.
    return $this->getMySQLi()->query(<<<SQL
SELECT
  `genres`.`id` AS `id`,
  COLUMN_GET(`dyn_names`, '{$this->intl->languageCode}' AS CHAR) AS `name`,
  COUNT(DISTINCT `movies_genres`.`movie_id`) AS `movieCount`,
  COUNT(DISTINCT `series_genres`.`series_id`) AS `seriesCount`
FROM `genres`
  LEFT JOIN `movies_genres`
    ON `movies_genres`.`genre_id` = `genres`.`id`
  LEFT JOIN `series_genres`
    ON `series_genres`.`genre_id` = `genres`.`id`
WHERE `deleted` = false
GROUP BY `id`, `name`
ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}
SQL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "genres";
  }

}
