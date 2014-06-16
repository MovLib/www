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
namespace MovLib\Core\Database\Query;

use \MovLib\Core\Database\Database;

/**
 * Defines the abstract query class which is the base for all query classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractQuery extends AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractQuery";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Active database connection.
   *
   * @var \MovLib\Core\Database\Connection
   */
  protected $connection;

  /**
   * The name of the primary table to work with.
   *
   * @var string
   */
  protected $table;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new SQL query object.
   *
   * @param \MovLib\Core\Database\Connection $connection [optional]
   *   Active database connection, defaults to <code>NULL</code> and a new connection will be used.
   * @param string $tableName [optional]
   *   The name of the primary table to work with.
   * @param stirng $tableAlias [optional]
   *   The alias for the primary table to work with.
   */
  public function __construct(\MovLib\Core\Database\Connection $connection = null, $tableName = null, $tableAlias = null) {
    $this->connection = $connection ?: Database::getConnection();
    $tableName && ($this->table = $this->getTable($tableName, $tableAlias));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the <code>"COLUMN_CREATE()"</code> placeholder and sanitize the field's name.
   *
   * @param string $fieldName
   *   The name of the dynamic field to set.
   * @param array|null $values [optional]
   *   The values of the dynamic field to set.
   * @return string
   *   The placeholder for the dynamic field.
   */
  final protected function dynamicColumnCreate(&$fieldName, array $values = null) {
    $fieldName = $this->sanitizeDynamicFieldName($fieldName);

    // We're done if we have no values at all from the start (NULL or empty array).
    if (empty($values)) {
      return "''";
    }

    $placeholder = null;
    foreach ($values as $key => $value) {
      if ($value == true || $value == "0") {
        $placeholder && ($placeholder .= ", ");
        $placeholder .= "{$this->getPlaceholder($key)}, {$this->getPlaceholder($value)}";
      }
    }

    // We insert an empty string if we have no keys to insert. This allows us to call COLUMN_ADD() and COLUMN_DELETE()
    // at any point later on because an empty string is considered valid in terms of dynamic columns. Sadly BLOB
    // columns cannot have a default value.
    return $placeholder ? "COLUMN_CREATE({$placeholder})" : "''";
  }

  /**
   * Get the formatted table name.
   *
   * @param string $name
   *   The name of the table.
   * @param string $alias [optional]
   *   The alias of the table.
   * @return string
   *   The formatted table name.
   */
  final protected function getTable($name, $alias = null) {
    $table = "`{$name}`";
    $alias && ($table .= " AS `{$alias}`");
    return $table;
  }

}
