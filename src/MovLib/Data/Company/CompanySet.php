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
 * Defines the companies set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CompanySet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `companies`.`id`,
  `companies`.`name`,
  `companies`.`founding_date` AS `foundingDate`,
  `companies`.`defunct_date` AS `defunctDate`,
  `companies`.`deleted`,
  `companies`.`changed`,
  `companies`.`created`,
  `companies`.`place_id` AS `placeId`,
  COUNT(DISTINCT `movies_crew`.`movie_id`) AS `movieCount`,
  COUNT(DISTINCT `episodes_crew`.`series_id`) AS `seriesCount`,
  COUNT(DISTINCT `releases_labels`.`release_id`) AS `releaseCount`
FROM `companies`
  LEFT JOIN `movies_crew`     ON `movies_crew`.`company_id`     = `companies`.`id`
  LEFT JOIN `episodes_crew`   ON `episodes_crew`.`company_id`   = `companies`.`id`
  LEFT JOIN `releases_labels` ON `releases_labels`.`company_id` = `companies`.`id`
{$where}
GROUP BY `id`,`name`,`foundingDate`,`defunctDate`,`deleted`,`changed`,`created`,`placeId`
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->pluralKey   = $this->tableName = "companies";
    $this->route       = $this->intl->rp("/companies");
    $this->singularKey = "company";
    return parent::init();
  }

}
