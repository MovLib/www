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
 * Count verification for jobs.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class JobCount extends \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->entityName = "Job";
    $this->tableName  = "jobs";
    $this->addCountColumn("persons", [ $this, "getPersonCounts" ]);
    $this->addCountColumn("companies", [ $this, "getCompanyCounts" ]);
    return parent::configure();
  }

  /**
   * Get the company counts of jobs.
   *
   * @return array
   *   Associative array with the job identifiers as keys and the company counts as values.
   */
  protected function getCompanyCounts() {
    return $this->aggregateSimpleQuery(<<<SQL
SELECT
`jobs`.`id`,
COUNT(DISTINCT `companies`.`id`) AS `count`
FROM `jobs`
LEFT JOIN `movies_crew`
  ON `movies_crew`.`job_id` = `jobs`.`id`
LEFT JOIN `episodes_crew`
  ON `episodes_crew`.`job_id` = `jobs`.`id`
LEFT JOIN `releases_labels`
  ON `releases_labels`.`job_id` = `jobs`.`id`
LEFT JOIN `releases_crew`
  ON `releases_crew`.`job_id` = `jobs`.`id`
INNER JOIN `companies`
  ON `companies`.`id` = `movies_crew`.`company_id`
  OR `companies`.`id` = `episodes_crew`.`company_id`
  OR `companies`.`id` = `releases_labels`.`company_id`
  OR `companies`.`id` = `releases_crew`.`company_id`
GROUP BY `jobs`.`id`
ORDER BY `jobs`.`id` ASC
SQL
    );
  }

  /**
   * Get the person counts of jobs.
   *
   * @return array
   *   Associative array with the job identifiers as keys and the person counts as values.
   */
  protected function getPersonCounts() {
    return $this->aggregateSimpleQuery(<<<SQL
SELECT
`jobs`.`id`,
COUNT(DISTINCT `persons`.`id`) AS `count`
FROM `jobs`
LEFT JOIN `movies_directors`
  ON `movies_directors`.`job_id` = `jobs`.`id`
LEFT JOIN `movies_cast`
  ON `movies_cast`.`job_id` = `jobs`.`id`
LEFT JOIN `movies_crew`
  ON `movies_crew`.`job_id` = `jobs`.`id`
LEFT JOIN `episodes_directors`
  ON `episodes_directors`.`job_id` = `jobs`.`id`
LEFT JOIN `episodes_cast`
  ON `episodes_cast`.`job_id` = `jobs`.`id`
LEFT JOIN `episodes_crew`
  ON `episodes_crew`.`job_id` = `jobs`.`id`
LEFT JOIN `releases_crew`
  ON `releases_crew`.`job_id` = `jobs`.`id`
INNER JOIN `persons`
  ON `persons`.`id` = `movies_directors`.`person_id`
  OR `persons`.`id` = `movies_cast`.`person_id`
  OR `persons`.`id` = `movies_crew`.`person_id`
  OR `persons`.`id` = `episodes_directors`.`person_id`
  OR `persons`.`id` = `episodes_cast`.`person_id`
  OR `persons`.`id` = `episodes_crew`.`person_id`
  OR `persons`.`id` = `releases_crew`.`person_id`
GROUP BY `jobs`.`id`
ORDER BY `jobs`.`id` ASC
SQL
    );
  }

}
