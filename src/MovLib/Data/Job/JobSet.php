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
namespace MovLib\Data\Job;

/**
 * Defines the job set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class JobSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "JobSet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `jobs`.`id` AS `id`,
  IFNULL(COLUMN_GET(`jobs`.`dyn_titles_sex0`, '{$this->intl->languageCode}' AS CHAR), COLUMN_GET(`dyn_titles_sex0`, '{$this->intl->defaultLanguageCode}' AS CHAR)) AS `title`,
  `jobs`.`count_companies` AS `companyCount`,
  `jobs`.`count_persons` AS `personCount`
FROM `jobs`
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
    $this->pluralKey   = "jobs";
    $this->singularKey = "job";
    return parent::init();
  }

}
