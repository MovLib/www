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
   * The separator character for fields as defined in the SQL standard.
   *
   * @internal We include a space to ease readability (debugging).
   * @var string
   */
  protected static $fieldSeparator = ", ";

  /**
   * The query's table alias to operate on.
   *
   * @var string
   */
  protected $tableAlias;

  /**
   * The query's table name to operate on.
   *
   * @var string
   */
  protected $tableName;

  /**
   * Characters used to separate a table name or alias from a field name as defined in the SQL standard.
   *
   * @var string
   */
  protected static $tableFieldSeparator = ".";

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


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the SQL statement.
   *
   * @return string
   *   The SQL statement.
   */
  abstract public function __toString();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Sanitize the name of a field.
   *
   * This method will clean the field name and make it save for inclusion in any SQL query. It will also take care of
   * the correct escaping of table name or alias usage.
   *
   * @param string $name
   *   The name of the field, with or without table name / alias.
   * @return string
   *   The sanitized field name.
   */
  protected function sanitizeFieldName($name) {
    if (strpos($name, self::$tableFieldSeparator) !== false) {
      $name = str_replace(self::$tableFieldSeparator, "`{$this::$tableFieldSeparator}`", $name);
    }
    return "`{$name}`";
  }

  /**
   * Sanitize the name of a dynamic field and prefix it with <code>"dyn_"</code>.
   *
   * This method will clean the dynamic field name and prefix it with the <code>"dyn_"</code> string that every dynamic
   * column has in our architecture. It will also take care of the correct escaping of table name or alias usage.
   *
   * @param string $name
   *   The name of the dynamic field, with or without table name / alias.
   * @return string
   *   The sanitized dynamic field name prefixed with <code>"dyn_"</code>.
   */
  protected function sanitizeDynamicFieldName($name) {
    if (strpos($name, self::$tableFieldSeparator) === false) {
      $name = "dyn_{$name}";
    }
    else {
      $name = str_replace(self::$tableFieldSeparator, "`{$this::$tableFieldSeparator}`dyn_", $name);
    }
    return "`{$name}`";
  }

  /**
   * Get the placeholder for the value, this will also export the value and its type to class scope.
   *
   * This method will check the type of the value and decide which placeholder is needed. It will also add the correct
   * type to the types string of the class and export the value to the values array of the class. The value is casted to
   * a string if its of type object and the instance will be asked if it has a special placeholder.
   *
   * <b>NOTE</b><br>
   * This method only accepts atomic values, objects are considered atomic values because they are casted to a string
   * (basically their <code>__toString()</code> method is called and asked for a real atomic value). Arrays will throw
   * an <code>assert()</code> {@see \ErrorException} because arrays always require some special handling in the
   * concrete implementation.
   *
   * @param mixed $value
   *   The value to get the placeholder for and to export to class scope.
   * @return string
   *   The placeholder for the value.
   */
  protected function getPlaceholder($value) {
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

      case "object":
        // This object might have a custom placeholder attached.
        isset($value::$sqlPlaceholder) && ($placeholder = $value::$sqlPlaceholder);
        // no break

      default:
        $this->types .= "s";
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
   * @internal
   *   This method is kept protected to allow concrete classes to redefine the name of it for their public interface.
   *   The purpose of this is to create more human readable public interfaces and make development even more fluent.
   * @param string $name
   *   The table's name.
   * @param string $alias [optional]
   *   An aliad for the table for referencing, defaults to <code>NULL</code> and no alias will be assigned.
   * @return this
   */
  protected function table($name, $alias = null) {
    $this->tableName = $name;
    $alias && ($this->tableAlias = " AS `{$alias}`");
    return $this;
  }

}
