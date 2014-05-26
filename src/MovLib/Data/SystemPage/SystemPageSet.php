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
namespace MovLib\Data\SystemPage;

/**
 * Defines the system page set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SystemPageSet extends \MovLib\Data\AbstractEntitySet {

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `id`,
  IFNULL(
    COLUMN_GET(`dyn_titles`, '{$this->intl->languageCode}' AS CHAR(255)),
    COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR(255))
  ) AS `title`,
  IFNULL(
    COLUMN_GET(`dyn_texts`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`dyn_texts`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ) AS `text`,
  COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR(255)) AS `routeKey`
FROM `system_pages`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->tableName   = "system_pages";
    $this->pluralKey   = "systempages";
    $this->singularKey = "systempage";
    return parent::init();
  }

}
