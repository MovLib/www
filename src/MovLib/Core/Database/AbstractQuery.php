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
namespace MovLib\Core\Database;

/**
 * @todo Description of AbstractQuery
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractQuery {


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
   * The query's table name to operate on.
   *
   * @var string
   */
  protected $table;

  /**
   * The query's table alias to operate on.
   *
   * @var string
   */
  protected $tableAlias;

  /**
   * String containing the types of the values for auto-sanitization by the prepared statement.
   *
   * @var string
   */
  protected $types;

  /**
   * Numeric array containing the values to insert.
   *
   * @var array
   */
  protected $values;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new insert statement.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   The active database connection.
   */
  public function __construct(\MovLib\Core\Database\Connection $connection) {
    $this->connection = $connection;
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the query.
   *
   * @return string
   *   The query.
   */
  abstract public function __toString();

  /**
   * Execute the query against the database.
   *
   * @return mixed
   *   Concrete classes decide what will be returned.
   */
  abstract public function execute();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function sanitizeFieldName($name) {
    $name = str_replace(".", "`.`", $name);
    return "`{$name}`";
  }

  protected function sanitizeDynamicFieldName($name) {
    $name = str_replace(".", "`.`dyn_", $name);
    return "`{$name}`";
  }

  protected function setValue($value) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(
      !is_array($value),
      "This method is only for atomic values because you have to perform additional work if it's an array, loop once " .
      "and call this method with each value in your loop, this is more efficient than a recursive handling of the " .
      "values with an additional loop to build the placeholders."
    );
    // @codeCoverageIgnoreEnd
    // @devEnd

    $placeholder = "?";
    switch (gettype($value)) {
      case "boolean":
        $this->types .= "i";
        $this->values[] = $value;
        break;

      case "double":
      case "integer":
        $this->types .= "d";
        $this->values[] = $value;
        break;

      default:
        $this->types .= "s";
        // This object might have a custom placeholder attached to ti.
        isset($value->sqlPlaceholder) && ($placeholder = $value->sqlPlaceholder);
        // Directly cast to string in case this is an object, we want it to fail as early as possible and we don't want
        // to store a reference to the complete object.
        $this->values[] = (string) $value;
        break;
    }

    return $placeholder;
  }

  /**
   * Set the table to operate on.
   *
   * @param string $name
   *   The table's name.
   * @param string $alias [optional]
   *   An aliad for the table for referencing, defaults to <code>NULL</code> and no alias will be assigned.
   * @return this
   */
  public function table($name, $alias = null) {
    $this->table      = $name;
    $this->tableAlias = $alias;
    return $this;
  }

}
