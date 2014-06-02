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
   * String containing the set clause.
   *
   * @var string
   */
  protected $setClause;

  /**
   * The name of the table to insert into.
   *
   * @var string
   */
  protected $table;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new insert statement object.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database connection.
   * @param string $table [optional]
   *   The name of the table to insert into, defaults to <code>NULL</code> in which case you'll have to call
   *   {@see Insert::into()}.
   */
  public function __construct(\MovLib\Core\Database\Connection $connection, $table = null) {
    $this->connection = $connection;
    $this->table      = $table;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // We use the MariaDB (MySQL) specific set syntax for insert statements because it's easier to build and debug.
    return "INSERT INTO `{$this->table}`{$this->setClause}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add field with placeholder to insert statement.
   *
   * @param string $fieldName
   *   The sanitized field name.
   * @param string $placeholder
   *   The prepared placeholder.
   * @return this
   */
  protected function addField($fieldName, $placeholder) {
    $this->setClause .= $this->setClause ? ", " : " SET ";
    $this->setClause .= "{$fieldName} = {$placeholder}";
    return $this;
  }

  /**
   * Set the table to insert into.
   *
   * @param string $table
   *   The table's name to insert into.
   * @return this
   */
  public function into($table) {
    $this->table = $table;
    return $this;
  }

  /**
   * Set field to value.
   *
   * @param string $fieldName
   *   The name of the field to set.
   * @param mixed $value
   *   The value to set. <b>NOTE</b> that arrays are automatically serialized.
   * @return this
   */
  public function set($fieldName, $value) {
    return $this->addField($this->sanitizeFieldName($fieldName), $this->getPlaceholder($value));
  }

  /**
   * Set dynamic field to value.
   *
   * @param string $fieldName
   *   The name of the dynamic field to set without the <code>"dyn_"</code> prefix.
   * @param array|null $value
   *   The value to set.
   * @return this
   */
  public function setDynamic($fieldName, $value) {
    // Prepare field name, export types and values and get the placeholder.
    $placeholder = $this->dynamicColumnCreate($fieldName, $value);

    // Add the dynamic column to the insert statement.
    return $this->addField($fieldName, $placeholder);
  }

  /**
   * Execute the insert statement.
   *
   * @return integer
   *   The value returned by the auto-increment field of the table.
   * @throws \mysqli_sql_exception
   */
  public function execute() {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->table), "You must set the table name in order to execute an INSERT query.");
    // Note that the set clause is optional, one might want to insert only default values.
    // @codeCoverageIgnoreEnd
    // @devEnd

    $stmt = $this->connection->prepare($this);
    // @codingStandardsIgnoreStart
    $this->values && $stmt->bind_param($this->types, ...$this->values);
    // @codingStandardsIgnoreEnd
    $stmt->execute();
    // @codingStandardsIgnoreStart
    $insertId = $stmt->insert_id;
    // @codingStandardsIgnoreEnd
    $stmt->close();
    return $insertId;
  }

}
