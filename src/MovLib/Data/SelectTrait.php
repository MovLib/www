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
namespace MovLib\Data;

/**
 * Defines the default implementation for the select interface.
 *
 * @see \MovLib\Data\SelectInterface
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait SelectTrait {

  /**
   * Load options for a select field.
   *
   * @param string $column [optional]
   *   The column to get the option from, defaults to <var>"`name`"</var> e.g.: <var>"`title`"</var> or
   *   <var>"COLUMN_GET(`dyn_titles`, '{$this->intl->languageCode}' AS CHAR)"</var>.
   * @return array
   *   Assiciative array with entity id as key and option content as value.
   * @throws \mysqli_sql_exception
   */
  public function loadSelectOptions($column = "`name`") {
    // @devStart
    // @codeCoverageIgnoreStart
    assert($this instanceof \MovLib\Data\AbstractEntitySet, "SelectTrait can only be used in an instance of AbstractSet.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $options = [];
    $result = $this->getMySQLi()->query("SELECT `id`, {$column} FROM {$this->tableName}");
    /* @var $row \MovLib\Data\AbstractEntity */
    while ($row = $result->fetch_row()) {
      $options[$row[0]] = $row[1];
    }
    $result->free();
    return $options;
  }

}
