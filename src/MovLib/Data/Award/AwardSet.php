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
final class AwardSet extends \MovLib\Data\AbstractDatabaseSet {
  use \MovLib\Data\Award\AwardTrait;

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `awards`.`id` AS `id`,
  `awards`.`name` AS `name`,
  `awards`.`links` AS `links`,
  `awards`.`aliases` AS `aliases`,
  `awards`.`deleted` AS `deleted`,
  `awards`.`changed` AS `changed`,
  `awards`.`created` AS `created`,
  COUNT(DISTINCT `movie_id`) AS `movieCount`,
  `awards`.`last_event_year` AS `lastEventYear`,
  `awards`.`first_event_year` AS `firstEventYear`,
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR) AS `wikipedia`,
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR) AS `description`
FROM `awards`
  LEFT JOIN `movies_awards` ON `movies_awards`.`award_id` = `awards`.`id`
{$where}
GROUP BY `id`, `name`, `links`, `aliases`, `deleted`, `changed`, `created`, `lastEventYear`, `firstEventYear`, `wikipedia`, `description`
{$orderBy}
SQL;
  }

}
