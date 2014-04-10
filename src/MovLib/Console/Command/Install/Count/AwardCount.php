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
 * Count verification for awards.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardCount extends \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->entityName = "Award";
    $this->tableName  = "awards";
    $this->addCountColumn("companies", "getAwardeeCounts", [ "company_id", "companies" ]);
    $this->addCountColumn("events", "getCounts", [ "award_id", null, "id", "events" ]);
    $this->addCountColumn("movies", "getCounts", [ "award_id", null, "movie_id", "movies_awards", "`won` = true" ]);
    $this->addCountColumn("persons", "getAwardeeCounts", [ "person_id", "persons" ]);
    $this->addCountColumn("series", "getCounts", [ "award_id", null, "series_id", "series_awards", "`won` = true" ]);
    return parent::configure();
  }

  /**
   * Get the awardee counts of awards (of a single type, e.g. person).
   *
   * @param string $awardeeIdColumn
   *   The name of the awardee identifier column in the intermediate tables.
   * @param string $awardeeTable
   *   The name of the awardee table
   * @return array
   *   Associative array with the award identifiers as keys and the awardee counts as values.
   */
  protected function getAwardeeCounts($awardeeIdColumn, $awardeeTable) {
    return $this->aggregateSimpleQuery(<<<SQL
SELECT
  `awards`.`id`,
  COUNT(DISTINCT `{$awardeeTable}`.`id`) AS `count`
FROM `awards`
LEFT JOIN `movies_awards`
  ON `movies_awards`.`award_id` = `awards`.`id`
LEFT JOIN `series_awards`
  ON `series_awards`.`award_id` = `awards`.`id`
INNER JOIN `{$awardeeTable}`
  ON `movies_awards`.`{$awardeeIdColumn}` = `{$awardeeTable}`.`id`
  OR `series_awards`.`{$awardeeIdColumn}` = `{$awardeeTable}`.`id`
WHERE (`movies_awards`.`award_id` IS NOT NULL AND `movies_awards`.`won` = true)
  OR (`series_awards`.`award_id` IS NOT NULL AND `series_awards`.`won` = true)
GROUP BY `awards`.`id`
ORDER BY `awards`.`id` ASC
SQL
    );
  }

}
