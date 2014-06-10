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


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "GenreSet";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * {@inheritdoc}
   */
  public static $tableName = "genres";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, "Genres", "Genre", $container->intl->tp(-1, "Genres", "Genre"));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function doLoad(\MovLib\Core\Database\Query\Select $select) {
    return $select
      ->select("id")
      ->select("changed")
      ->select("created")
      ->select("deleted")
      ->selectIfNull(
        "name",
        "COLUMN_GET(`dyn_names`, '{$this->intl->languageCode}' AS CHAR(255))",
        "COLUMN_GET(`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR(255))"
      )
      ->select("count_movies")
      ->select("count_series")
    ;
  }

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
  `genres`.`count_movies` AS `countMovies`,
  `genres`.`count_series` AS `countSeries`
FROM `genres`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    $collation = \MovLib\Core\Database\Query\Select::$collations[$this->intl->locale];
    return <<<SQL
SELECT
  `{$set::$tableName}_genres`.`{$set->singularKey}_id` AS `entityId`,
  `genres`.`id`,
  `genres`.`created`,
  `genres`.`changed`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`
FROM `{$set::$tableName}_genres`
  INNER JOIN `genres` ON `genres`.`id` = `{$set::$tableName}_genres`.`genre_id`
WHERE `{$set::$tableName}_genres`.`{$set->singularKey}_id` IN ({$in})
ORDER BY `name` {$collation} DESC
SQL;
  }

}
