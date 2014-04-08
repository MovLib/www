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
namespace MovLib\Data\Help;

/**
 * Defines the help category set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CategorySet extends \MovLib\Data\AbstractSet {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `help_categories`.`id` AS `id`,
  `help_categories`.`changed` AS `changed`,
  `help_categories`.`created` AS `created`,
  `help_categories`.`deleted` AS `deleted`,
  `help_categories`.`icon`,
  IFNULL(
    COLUMN_GET(`help_categories`.`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`help_categories`.`dyn_descriptions`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `description`,
  IFNULL(
    COLUMN_GET(`help_categories`.`dyn_titles`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`help_categories`.`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `title`,
  COLUMN_GET(`help_categories`.`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR) AS `routeKey`
FROM `help_categories`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->pluralKey   = $this->tableName = "help_categories";
    $this->route       = $this->intl->r("/help");
    $this->singularKey = "help_category";
    return parent::init();
  }

}
