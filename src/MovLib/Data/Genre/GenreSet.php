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
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class GenreSet extends \MovLib\Data\AbstractEntitySet {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `genres`.`id` AS `id`,
  `genres`.`changed` AS `changed`,
  `genres`.`created` AS `created`,
  `genres`.`deleted` AS `deleted`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  `genres`.`count_movies` AS `movieCount`,
  `genres`.`count_series` AS `seriesCount`
FROM `genres`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return <<<SQL
SELECT
  `{$set->tableName}_genres`.`{$set->singularKey}_id` AS `entityId`,
  `genres`.`id`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`
FROM `{$set->tableName}_genres`
  INNER JOIN `genres` ON `genres`.`id` = `{$set->tableName}_genres`.`genre_id`
WHERE `{$set->tableName}_genres`.`{$set->singularKey}_id` IN ({$in})
ORDER BY `name` {$this->collations[$this->intl->languageCode]} DESC
SQL;
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->pluralKey   = "genres";
    $this->singularKey = "genre";
    return parent::init();
  }

}
