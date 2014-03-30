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
namespace MovLib\Data\Company;

/**
 * Defines the company set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CompanySet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public function getEntityClassName() {
    return "\\MovLib\\Data\\Company\\Company";
  }

  /**
   * {@inheritdoc}
   */
  public function getOrdered($by, $offset, $limit) {
    return $this->getMySQLi()->query(<<<SQL
SELECT
  `companies`.`id` AS `id`,
  `companies`.`name` AS `name`,
  `companies`.`founding_date` AS `foundingDate`,
  `companies`.`defunct_date` AS `defunctDate`,
  COUNT(DISTINCT `movies_crew`.`movie_id`) AS `movieCount`,
  '0' AS `seriesCount`,
  COUNT(DISTINCT `releases_labels`.`release_id`) AS `releaseCount`
FROM `companies`
  LEFT JOIN `movies_crew`
    ON `movies_crew`.`company_id` = `companies`.`id`
  LEFT JOIN `releases_labels`
    ON `releases_labels`.`company_id` = `companies`.`id`
WHERE `deleted` = false
GROUP BY `companies`.`id`, `companies`.`name`, `companies`.`founding_date`, `companies`.`defunct_date`
ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}
SQL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "companies";
  }

}
