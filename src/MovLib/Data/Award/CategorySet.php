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
 * Defines the award category set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CategorySet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CategorySet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `awards_categories`.`id` AS `id`,
  `awards_categories`.`award_id` AS `awardId`,
  `awards_categories`.`changed` AS `changed`,
  `awards_categories`.`created` AS `created`,
  `awards_categories`.`deleted` AS `deleted`,
  IFNULL(
    COLUMN_GET(`awards_categories`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`awards_categories`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  `awards_categories`.`first_year` AS `firstYear`,
  `awards_categories`.`last_year` AS `lastYear`,
  `awards_categories`.`count_movies` AS `movieCount`,
  `awards_categories`.`count_series` AS `seriesCount`,
  `awards_categories`.`count_persons` AS `personCount`,
  `awards_categories`.`count_companies` AS `companyCount`
FROM `awards_categories`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return <<<SQL

SQL;
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->pluralKey   = "categories";
    $this->singularKey = "category";
    $this->tableName   = "awards_categories";
    return parent::init();
  }

}
