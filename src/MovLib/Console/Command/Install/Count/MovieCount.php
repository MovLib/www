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
namespace MovLib\Console\Command\Install\Count;

/**
 * Count verification for movies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieCount extends \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("entity-count-movie");
    $this->entityName = "Movie";
    $this->tableName  = "movies";
    $this->addCountColumn(
      "awards",
      "getCounts",
      [ [ "movie_id" ], [ "award_id", "award_category_id", "event_id" ], "movies_awards", "WHERE `won` = true"  ]
    );
    $this->addCountColumn("releases", "getReleasesCount");
    return parent::configure();
  }

  /**
   * Get the release counts of movies.
   *
   * @return array
   *   Associative array with the movie identifiers as keys and the release counts as values.
   */
  protected function getReleasesCount() {
    $releaseCounts = [];
    $result = $this->mysqli->query(<<<SQL
SELECT
  `media_movies`.`movie_id` AS `movieId`,
  COUNT(DISTINCT `releases`.`id`) AS `count`
FROM `media_movies`
INNER JOIN `media`
  ON `media`.`id` = `media_movies`.`medium_id`
INNER JOIN `releases_media`
  ON `releases_media`.`medium_id` = `media`.`id`
INNER JOIN `releases`
  ON `releases`.`id` = `releases_media`.`release_id`
WHERE `releases`.`deleted` = false
GROUP BY `media_movies`.`movie_id`
ORDER BY `media_movies`.`movie_id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      $releaseCounts[$row->movieId] = $row->count;
    }

    return $releaseCounts;
  }

}
