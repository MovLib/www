<?php

/* !
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

use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the select statement object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Select extends AbstractQuery {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Select";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  protected static $collations = [
    null    => null, // Used for performance reasons.
    "en_US" => null,
    "de_AT" => " COLLATE utf8mb4_german2_ci",
  ];

  protected $group;

  protected $limit;

  protected $order;

  protected $projection;

  protected $results;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new select statement.
   *
   * @param \MovLib\Core\Database\Connection $connection
   *   The active database connection.
   * @param string $tableName [optional]
   *   The name of the table to select from, defaults to <code>NULL</code> in which case you'll have to call
   *   {@see Select::from()}.
   * @param string $tableAlias [optional]
   *   The alias of the table to select from.
   */
  public function __construct(\MovLib\Core\Database\Connection $connection, $tableName = null, $tableAlias = null) {
    $this->connection = $connection;
    $tableName && $this->table($tableName, $tableAlias);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $projection = $this->projection ?: "*";
    return "SELECT {$projection} FROM `{$this->table}`{$this->tableAlias}{$this->conditions}{$this->group}{$this->order}{$this->limit}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the table to select from.
   *
   * @param string $tableName
   *   The name of the table to select from.
   * @param string $tableAlias [optional]
   *   The alias of the table to select from, defaults to <code>NULL</code> in which case you'll have to use the full
   *   table name or make all fields unambigous.
   * @return this
   */
  public function from($tableName, $tableAlias = null) {
    return $this->table($tableName, $tableAlias);
  }

  public function limit($limit = 1) {

    return $this;
  }

  public function offset($offset, $limit = PHP_INT_MAX) {

    return $this;
  }

  public function groupBy($field) {

    return $this;
  }

  public function orderBy($fieldName, $order = "ASC", $locale = null) {
    $this->order .= $this->order ? ", " : " ORDER BY ";
    $this->order .= "{$this->sanitizeFieldName($fieldName)} {$order}{$this::$collations[$locale]}";
    return $this;
  }

  public function select($fieldName, &$variable) {
    $this->projection && ($this->projection .= ", ");
    $this->projection .= $this->sanitizeFieldName($fieldName);
    $this->results[] =& $variable;
    return $this;
  }

  /**
   * Fetch the result from the select statement.
   *
   * <b>NOTE</b><br>
   * A <code>"LIMIT 1"</code> is always appended to the statement of this method.
   *
   * @return this
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If the statement didn't return any results.
   */
  public function fetch() {
    return $this;

    $stmt = $this->connection->prepare("{$this} LIMIT 1");
    // @codingStandardsIgnoreStart
    $this->values && $stmt->bind_param($this->types, ...$this->values);
    // @codingStandardsIgnoreEnd
    $stmt->execute();
    // @codingStandardsIgnoreStart
    $this->results && $stmt->bind_result(...$this->results);
    // @codingStandardsIgnoreEnd
    $found = $stmt->fetch();
    $stmt->close();
    if ($found === false) {
      throw new NotFoundException();
    }
    return $this;
  }

}
