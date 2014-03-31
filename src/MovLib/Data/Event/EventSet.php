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
namespace MovLib\Data\Event;

/**
 * Defines the event set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class EventSet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  public function getEntityClassName() {
    return "\\MovLib\\Data\\Event\\Event";
  }

  /**
   * {@inheritdoc}
   */
  public function getOrdered($by, $offset, $limit) {
    // @todo Rename table to Events, as it is a main entity now.
    return $this->getMySQLi()->query(<<<SQL
SELECT
  `events`.`id` AS `id`,
  `events`.`name` AS `name`,
  `events`.`end_date` AS `endDate`,
  COUNT(DISTINCT `movies_awards`.`movie_id`) AS `movieCount`,
  `events`.`place_id` AS `place`,
  '0' AS `seriesCount`,
  `events`.`start_date` AS `startDate`
FROM `events`
  LEFT JOIN `movies_awards`
    ON `events`.`id` = `movies_awards`.`event_id`
WHERE `deleted` = false
GROUP BY `id`, `name`, `endDate`, `place`, `seriesCount`, `startDate`
ORDER BY {$by} LIMIT {$limit} OFFSET {$offset}
SQL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "events";
  }

}