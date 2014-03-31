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
namespace MovLib\Data\Award;

/**
 * Defines the award set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AwardSet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public function getEntityClassName() {
    return "\\MovLib\\Data\\Award\\Award";
  }

  /**
   * {@inheritdoc}
   */
  public function getOrdered($by, $offset, $limit) {
    // @todo One international name per organization (award) plus aliases, same as companies.
    return $this->getMySQLi()->query(<<<SQL
SELECT
  `awards`.`id` AS `id`,
  `awards`.`name` AS `name`,
  `awards`.`first_event_year` AS `firstEventYear`,
  `awards`.`last_event_year` AS `lastEventYear`,
  COUNT(DISTINCT `movies_awards`.`movie_id`) AS `movieCount`,
  '0' AS `seriesCount`
FROM `awards`
  LEFT JOIN `movies_awards`
    ON `movies_awards`.`award_id` = `awards`.`id`
WHERE `deleted` = false
GROUP BY `awards`.`id`, `awards`.`name`, `awards`.`first_event_year`, `awards`.`last_event_year`
ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}
SQL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "awards";
  }

}
