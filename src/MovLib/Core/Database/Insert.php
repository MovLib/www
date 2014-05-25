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
 * Defines the database insert object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Insert extends AbstractQuery {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Insert";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * String containing the columns to insert into.
   *
   * @var string
   */
  protected $columns;

  /**
   * String containing the placeholders.
   *
   * @var string
   */
  protected $placeholders;

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
   * {@inheritdoc}
   */
  public function __toString() {
    return "INSERT INTO `{$this->table}` ({$this->columns}) VALUES ({$this->placeholders})";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add column to insert statement.
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
  protected function addColumn($name, $type, $value, $placeholder = "?") {
    $this->columns && ($this->columns .= ",");
    $this->columns .= "`{$name}`";
    $this->types .= $type;
    $this->values[] = $value;
    $this->placeholders && ($this->placeholders .= ",");
    $this->placeholders .= $placeholder;
    return $this;
  }

  /**
   * Add boolean.
   *
   * @param string $name
   *   The column's name.
   * @param boolean $value
   *   The column's value.
   * @return this
   */
  public function columnBoolean($name, $value) {
    return $this->addColumn($name, "i", $value);
  }

  /**
   * Add date and time.
   *
   * @param string $name
   *   The column's name.
   * @param \MovLib\Component\DateTime $value
   *   The column's value.
   * @return this
   */
  public function columnDateTime($name, \MovLib\Component\DateTime $value) {
    return $this->addColumn($name, "s", (string) $value, "CAST(? AS DATETIME)");
  }

  /**
   * Add number.
   *
   * @param string $name
   *   The column's name.
   * @param float|integer $value
   *   The column's value.
   * @return this
   */
  public function columnNumber($name, $value) {
    return $this->addColumn($name, "d", $value);
  }

  /**
   * Add string.
   *
   * @param string $name
   *   The column's name.
   * @param string $value
   *   The column's value.
   * @return this
   */
  public function columnString($name, $value) {
    return $this->addColumn($name, "s", $value);
  }

  /**
   * Add dynamic column.
   *
   * @param string $name
   *   The dynamic column's name without the <code>"dyn_"</code> prefix.
   * @param array $keyValues
   *   The dynamic column's values as key-value pairs.
   * @return this
   */
  public function dynamicColumn($name, array $keyValues) {
    $this->columns && ($this->columns .= ",");
    $this->columns .= "`dyn_{$name}`";

    $values = null;
    foreach ($keyValues as $key => $value) {
      if (empty($value)) {
        continue;
      }

      $values && ($values .= ",");
      $values .= "?,?";

      $this->types   .= "ss";
      $this->values[] = $key;
      $this->values[] = $value;
    }

    $this->placeholders && ($this->placeholders .= ",");
    $this->placeholders .= $values ? "COLUMN_CREATE({$values})" : "''";

    return $this;
  }

  /**
   * Execute the insert statement.
   *
   * @return integer
   *   The value returned by the auto-increment field of the table.
   * @throws \mysqli_sql_exception
   */
  public function execute() {
    $stmt = $this->connection->prepare($this);
    // @codingStandardsIgnoreStart
    $stmt->bind_param($this->types, ...$this->values);
    // @codingStandardsIgnoreEnd
    $stmt->execute();
    // @codingStandardsIgnoreStart
    $insertId = $stmt->insert_id;
    // @codingStandardsIgnoreEnd
    $stmt->close();
    return $insertId;
  }

}
