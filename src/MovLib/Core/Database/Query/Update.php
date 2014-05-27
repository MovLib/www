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
   * The update's conditions instantiated on demand.
   *
   * @var \MovLib\Core\Database\Condition|null
   */
  protected $conditions;

  /**
   * String containing all fields (including their placeholders) to set.
   *
   * @var string
   */
  protected $setClause;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return "UPDATE `{$this->tableName}`{$this->tableAlias} SET {$this->setClause}{$this->conditions}";
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
  public function set($name, $type, $value, $placeholder = "?") {
    $this->setClause && ($this->setClause .= ", ");
    $this->setClause .= "{$this->sanitizeFieldName($name)} = {$placeholder}";
    $this->types     .= $type;
    $this->values[]   = $value;
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
  public function decrement($name, $substract = 1) {
    return $this->addField($name, "i", $substract, "({$this->sanitizeFieldName($name)} - ?)");
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
  public function increment($name, $add = 1) {
    return $this->addField($name, "i", $add, "({$this->sanitizeFieldName($name)} + ?)");
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
    $this->values && $stmt->bind_param($this->types, ...$this->values);
    // @codingStandardsIgnoreEnd
    $stmt->execute();
    // @codingStandardsIgnoreStart
    $affectedRows = $stmt->affected_rows;
    // @codingStandardsIgnoreEnd
    $stmt->close();
    return $affectedRows;
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

  /**
   *
   * @param type $fieldName
   * @param type $value
   * @param type $operator
   * @param type $conjunction
   * @return \MovLib\Core\Database\Query\Update
   */
  public function where($fieldName, $value, $operator = null, $conjunction = "AND") {
    if (!$this->conditions) {
      $this->conditions = new Condition();
    }
    $this->conditions->condition($fieldName, $value, $operator, $conjunction);
    return $this;
  }

}
