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


  /**
   * Set the table to operate on.
   *
   * @param string $table
   *   The table's name.
   * @return this
   */
  public function table($table) {
    $this->table = $table;
    return $this;
  }

}
