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
namespace MovLib\Data\Series;

use \MovLib\Core\Database\Database;

/**
 * Defines the series title set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class TitleSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "TitleSet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public static $tableName = "series_titles";

  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, "Titles", "Title", $container->intl->tp(-1, "Titles", "Title"));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {

  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {

  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->pluralKey   = "titles";
    $this->singularKey = "title";
    return parent::init();
  }

  /**
   * Load the all titles for a specific series.
   *
   * @param \MovLib\Data\Series\Series $series
   *   The series to load the information for.
   * @return this
   */
  public function loadEntitiesBySeries(\MovLib\Data\Series\Series $series) {
    $result = Database::getConnection()->query(<<<SQL
SELECT
  `series_titles`.`id` AS `id`,
  `series_titles`.`language_code` AS `languageCode`,
  `series_titles`.`title` AS `title`,
  (`series_original_titles`.`series_id` IS NOT NULL) AS `isOriginalTitle`
FROM `series_titles`
  LEFT JOIN `series_original_titles`
    ON `series_original_titles`.`title_id` = `series_titles`.`id`
WHERE `series_titles`.`series_id` = {$series->id}
ORDER BY `isOriginalTitle` DESC
SQL
    );

    while ($row = $result->fetch_assoc()) {
      $this->entities[] = $row;
    }
    $result->free();
    return $this;
  }

}
