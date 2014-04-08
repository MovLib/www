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
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class EventSet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `events`.`id` AS `id`,
  `events`.`award_id` AS `awardId`,
  `events`.`deleted` AS `deleted`,
  `events`.`changed` AS `changed`,
  `events`.`created` AS `created`,
  `events`.`name` AS `name`,
  `events`.`place_id` AS `place`,
  `events`.`end_date` AS `endDate`,
  `events`.`start_date` AS `startDate`,
  '0' AS `seriesCount`,
  COUNT(DISTINCT `movies_awards`.`movie_id`) AS `movieCount`
FROM `events`
  LEFT JOIN `movies_awards` ON `events`.`id` = `movies_awards`.`event_id`
{$where}
GROUP BY `id`, `awardId`, `deleted`, `changed`, `created`, `name`, `place`, `startDate`, `endDate`, `seriesCount`
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->pluralKey   = "events";
    $this->singularKey = "event";
    return parent::init();
  }

}
