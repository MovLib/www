<?php

/* !
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
namespace MovLib\Data\Series;

/**
 * Defines the series set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SeriesSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "SeriesSet";
  // @codingStandardsIgnoreEnd

  public static $tableName = "series";

  public $bundle = "Series";

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `series`.`id` AS `id`,
  `series`.`changed` AS `changed`,
  `series`.`created` AS `created`,
  `series`.`deleted` AS `deleted`,
  `series`.`end_year` AS `endYear`,
  `series`.`mean_rating` AS `ratingMean`,
  `series`.`rank` AS `ratingRank`,
  `series`.`rating` AS `ratingBayes`,
  `series`.`start_year` AS `startYear`,
  `series`.`status` AS `status`,
  `original_title`.`title` AS `originalTitle`,
  `original_title`.`language_code` AS `originalTitleLanguageCode`,
  IFNULL(`display_title`.`title`, `original_title`.`title`) AS `displayTitle`,
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`) AS `displayTitleLanguageCode`,
  `series`.`votes` AS `ratingVotes`,
  `series`.`count_awards` AS `awardCount`,
  `series`.`count_seasons` AS `seasonCount`,
  `series`.`count_releases` AS `releaseCount`
FROM `series`
  LEFT JOIN `series_display_titles`
    ON `series_display_titles`.`series_id` = `series`.`id`
    AND `series_display_titles`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `series_titles` AS `display_title`
    ON `display_title`.`id` = `series_display_titles`.`title_id`
  LEFT JOIN `series_original_titles`
    ON `series_original_titles`.`series_id` = `series`.`id`
  LEFT JOIN `series_titles` AS `original_title`
    ON `original_title`.`id` = `series_original_titles`.`title_id`
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

}
