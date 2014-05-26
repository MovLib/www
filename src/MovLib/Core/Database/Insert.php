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
   * String containing the set clause.
   *
   * @var string
   */
  protected $setClause;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new insert statement object.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   Active database connection.
   * @param string $tableName [optional]
   *   The name of the table to insert into, defaults to <code>NULL</code> in which case you'll have to call
   *   {@see Insert::into()}.
   */
  public function __construct(Connection $connection, $tableName = null) {
    $this->connection = $connection;
    $this->tableName  = $tableName;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // We use the MaraiDB (MySQL) specific set syntax for insert statements because it's easier to build and debug.
    return "INSERT INTO `{$this->tableName}`{$this->setClause}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the table to insert into.
   *
   * @param string $name
   *   The table's name to insert into.
   * @return this
   */
  public function into($tableName) {
    $this->tableName = $tableName;
    return $this;
  }

  /**
   * Set field to value.
   *
   * @param string $fieldName
   *   The name of the field to set.
   * @param mixed $value
   *   The value to set. <b>NOTE</b> that arrays are considered dynamic columns.
   * @return this
   */
  public function set($fieldName, $value) {
    $placeholder = null;

    // We assume a dynamic column if the value is an array.
    if (is_array($value)) {
      $fieldName = $this->sanitizeDynamicFieldName($fieldName);

      foreach ($value as $key => $dynValue) {
        if (empty($dynValue)) {
          continue;
        }

        $placeholder && ($placeholder .= self::$fieldSeparator);
        $placeholder .= "{$this->getPlaceholder($key)}{$this::$fieldSeparator}{$this->getPlaceholder($dynValue)}";
      }

      // We insert an empty string if we have no keys to insert. This allows us to call COLUMN_ADD() and COLUMN_DELETE()
      // at any point later on because an empty string is considered valid in terms of dynamic columns. Sadly BLOB
      // columns cannot have a default value.
      $placeholder = $placeholder ? "COLUMN_CREATE({$placeholder})" : "''";
    }
    // Otherwise we have an atomic value and can include it directly.
    else {
      $fieldName = $this->sanitizeFieldName($fieldName);
      $placeholder = $this->getPlaceholder($value);
    }

    // Put the set clause together after compilation.
    $this->setClause .= $this->setClause ? self::$fieldSeparator : " SET ";
    $this->setClause .= "{$fieldName} = {$placeholder}";

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
