<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovDev;

use \MovLib\Exception\DatabaseException;
use \ReflectionMethod;

/**
 * The developer database class allows direct access to all methods from anywhere.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Data\Database {

  /**
   * @inheritdoc
   */
  public $affectedRows;

  /**
   * @inheritdoc
   */
  public $database;

  /**
   * @inheritdoc
   */
  public $insertId;

  /**
   * @inheritdoc
   */
  public $stmt;

  /**
   * @inheritdoc
   */
  public $transactionActive = false;

  /**
   * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the
   * connection.
   *
   * @param string $str
   *   The string to be escaped.
   * @return string
   *   The esacped string.
   */
  public function escapeString($str) {
    return self::$mysqli[$this->database]->real_escape_string($str);
  }

  /**
   * Get the current MySQLi instance.
   *
   * @return \mysqli
   *   The current MySQLi instance.
   */
  public function getMySQLi() {
    return self::$mysqli[$this->database];
  }

  /**
   * Execute multiple queries against the database.
   *
   * <b>IMPORTANT!</b> You have to properly escape the data in the queries.
   *
   * @param string $queries
   *   Multiple queries to execute.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function queries($queries) {
    if (!isset(self::$mysqli[$this->database])) {
      $rm = new ReflectionMethod($this, "connect");
      $rm->setAccessible(true);
      $rm->invoke($this);
    }
    $error = self::$mysqli[$this->database]->multi_query($queries);
    do {
      if ($error === false) {
        throw new DatabaseException("Execution of multiple queries failed", self::$mysqli[$this->database]->error, self::$mysqli[$this->database]->errno);
      }
      self::$mysqli[$this->database]->use_result();
      if (($more = self::$mysqli[$this->database]->more_results())) {
        $error = self::$mysqli[$this->database]->next_result();
      }
    }
    while ($more);
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function query($query, $types = null, array $params = null, $closeStmt = true) {
    return parent::query($query, $types, $params, $closeStmt);
  }

  /**
   * @inheritdoc
   */
  public function select($query, $types = null, array $params = null) {
    return parent::select($query, $types, $params);
  }

  /**
   * @inheritdoc
   */
  public function selectAssoc($query, $types = null, $params = null) {
    return parent::selectAssoc($query, $types, $params);
  }

  /**
   * @inheritdoc
   */
  public function tmpSet($data, $ttl = self::TMP_TTL_DAILY) {
    return parent::tmpSet($data, $ttl);
  }

  /**
   * @inheritdoc
   */
  public function tmpGetAndDelete($key) {
    return parent::tmpGetAndDelete($key);
  }

  /**
   * @inheritdoc
   */
  public function transactionCommit($flags = null) {
    return parent::transactionCommit($flags);
  }

  /**
   * @inheritdoc
   */
  public function transactionRollback($flags = null){
    return parent::transactionRollback($flags);
  }

  /**
   * @inheritdoc
   */
  public function transactionStart($flags = MYSQLI_TRANS_START_READ_WRITE) {
    return parent::transactionStart($flags);
  }

}
