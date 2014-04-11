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
 * Count verification for companies.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CompanyCount extends \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->entityName = "Company";
    $this->tableName  = "companies";
    $this->addCountColumn("awards", [ $this, "getAwardCounts" ]);
    $this->addCountColumn("movies", [ $this, "getCounts" ], [ "company_id", null, "movie_id", "movies_crew", "company_id IS NOT NULL" ]);
    $this->addCountColumn("releases", [ $this, "getCompanyReleaseCounts" ]);
    $this->addCountColumn("series", [ $this, "getCounts" ], [ "company_id", null, "series_id", "episodes_crew", "company_id IS NOT NULL" ]);
    return parent::configure();
  }

  /**
   * Get the award counts of companies.
   *
   * @return array
   *   Associative array with the company identifiers as keys and the award counts as values.
   */
  protected function getAwardCounts() {
    $awardKeys   = [];
    $awardCounts = [];

    // Get the awards.
    $result = $this->mysqli->query(<<<SQL
SELECT
  `companies`.`id`,
  `movies_awards`.`award_id` AS `movieAwardId`,
  `movies_awards`.`award_category_id` AS `movieAwardCategoryId`,
  `movies_awards`.`event_id` AS `movieEventId`,
  `series_awards`.`award_id` AS `seriesAwardId`,
  `series_awards`.`award_category_id` AS `seriesAwardCategoryId`,
  `series_awards`.`event_id` AS `seriesEventId`
FROM `companies`
LEFT JOIN `movies_awards`
  ON `companies`.`id` = `movies_awards`.`company_id`
  AND `movies_awards`.`won` > 0
LEFT JOIN `series_awards`
  ON `companies`.`id` = `series_awards`.`company_id`
  AND `series_awards`.`won` > 0
WHERE `movies_awards`.`company_id` IS NOT NULL
  OR `series_awards`.`company_id` IS NOT NULL
ORDER BY `companies`.`id` ASC
SQL
    );

    while ($row = $result->fetch_object()) {
      foreach ([
          "{$row->movieAwardId}-{$row->movieAwardCategoryId}-{$row->movieEventId}",
          "{$row->seriesAwardId}-{$row->seriesAwardCategoryId}-{$row->seriesEventId}",
        ] as $key) {
        if ($key != "--" && empty($awardKeys[$row->id][$key])) {
          $awardKeys[$row->id][$key] = true;
          $awardCounts[$row->id]     = isset($awardCounts[$row->id]) ? $awardCounts[$row->id] + 1 : 1;
        }
      }
    }
    $result->free();

    return $awardCounts;
  }

  /**
   * Get the release counts of companies.
   *
   * @return array
   *   Associative array with the company identifiers as keys and the release counts as values.
   */
  protected function getCompanyReleaseCounts() {
    return $this->aggregateSimpleQuery(<<<SQL
SELECT
  `companies`.`id`,
  COUNT(DISTINCT `releases`.`id`) AS `count`
FROM `companies`
LEFT JOIN `releases_labels`
  ON `releases_labels`.`company_id` = `companies`.`id`
LEFT JOIN `releases_crew`
  ON `releases_crew`.`company_id` = `companies`.`id`
INNER JOIN `releases`
  ON `releases`.`id` = `releases_labels`.`release_id`
  OR `releases`.`id` = `releases_crew`.`release_id`
WHERE `releases_labels`.`release_id` IS NOT NULL
  OR `releases_crew`.`release_id` IS NOT NULL
GROUP BY `companies`.`id`
ORDER BY `companies`.`id` ASC
SQL
    );
  }

}
