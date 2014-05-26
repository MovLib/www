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
 * Defines the database update object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Update extends AbstractQuery {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Update";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * String containing all fields (including their placeholders) to set.
   *
   * @var string
   */
  protected $query;

  /**
   * String containing the types of the values for auto-sanitization by the prepared statement.
   *
   * @var string
   */
  protected $types;

  /**
   * Numeric array containing the values for the fields.
   *
   * @var array
   */
  protected $values;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "UPDATE `{$this->table}` {$this->query}";
  }

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add column to update statement.
   *
   * @param string $name
   *   The column's name.
   * @param string $type
   *   The column's type.
   * @param mixed $value
   *   The column's value.
   * @param string $placeholder [optional]
   *   The column's placeholder, defaults to <code>"?"</code>.
   * @return this
   */
  public function addField($name, $type, $value, $placeholder = "?") {
    $this->query && ($this->query .= ",");
    $this->query .= "`{$name}`={$placeholder}";
    $this->types .= $type;
    $this->values[] = $value;
    return $this;
  }

  /**
   * Decrement a column.
   *
   * @param string $name
   *   The column's name to decrement.
   * @param float|integer $substract [optional]
   *   The amount to substract, defaults to <code>1</code>.
   * @return this
   */
  public function fieldDecrement($name, $substract = 1) {
    return $this->expression($name, "i", $substract, "(`{$name}`-?)");
  }

  /**
   * Increment a column.
   *
   * @param string $name
   *   The column's name to increment.
   * @param float|integer $add [optional]
   *   The amount to add, defaults to <code>1</code>.
   * @return this
   */
  public function fieldIncrement($name, $add = 1) {
    return $this->expression($name, "i", $add, "(`{$name}`+?)");
  }

  /**
   * Execute the update against the database.
   *
   * @return integer
   *   The number of affected rows.
   */
  public function execute() {
    $stmt = $this->connection->prepare($this);
    // @codingStandardsIgnoreStart
    $stmt->bind_param($this->types, ...$this->values);
    // @codingStandardsIgnoreEnd
    $stmt->execute();
    // @codingStandardsIgnoreStart
    $affectedRows = $stmt->affected_rows;
    // @codingStandardsIgnoreEnd
    $stmt->close();
    return $affectedRows;
  }

}
