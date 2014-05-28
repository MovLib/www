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
namespace MovLib\Data\Movie;

use \MovLib\Data\Genre\GenreSet;

/**
 * Defines the movie set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "MovieSet";
  // @codingStandardsIgnoreEnd

  public static $tableName = "movies";

  public $bundle = "Movies";

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `movies`.`id` AS `id`,
  `movies`.`created` AS `created`,
  `movies`.`deleted` AS `deleted`,
  `movies`.`mean_rating` AS `meanRating`,
  `movies`.`year` AS `year`,
  IFNULL(`display_title`.`title`, `original_title`.`title`) AS `displayTitle`,
  IFNULL(`display_title`.`language_code`, `original_title`.`language_code`) AS `displayTitleLanguageCode`,
  `original_title`.`title` AS `originalTitle`,
  `original_title`.`language_code` AS `originalTitleLanguageCode`,
  `posters`.`id` AS `imageFilename`,
  HEX(`posters`.`cache_buster`) AS `imageCacheBuster`,
  `posters`.`extension` AS `imageExtension`,
  `posters`.`styles` AS `imageStyles`
FROM `movies`
  INNER JOIN `movies_original_titles`
    ON `movies_original_titles`.`movie_id` = `movies`.`id`
  INNER JOIN `movies_titles` AS `original_title`
    ON `original_title`.`id` = `movies_original_titles`.`title_id`
  LEFT JOIN `movies_display_titles`
    ON `movies_display_titles`.`movie_id` = `movies`.`id`
    AND `movies_display_titles`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `movies_titles` AS `display_title`
    ON `display_title`.`id` = `movies_display_titles`.`title_id`
  LEFT JOIN `display_posters`
    ON `display_posters`.`movie_id` = `movies`.`id`
    AND `display_posters`.`language_code` = '{$this->intl->languageCode}'
  LEFT JOIN `posters`
    ON `posters`.`id` = `display_posters`.`poster_id`
    AND `posters`.`deleted` = false
{$where} {$orderBy}
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
   * Load the genres for each movie entity in this set.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function loadGenres() {
    (new GenreSet($this->container))->loadEntitySets($this);
    return $this;
  }

}
