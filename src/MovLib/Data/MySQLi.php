<?php

/* !
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
namespace MovLib\Data;

use \MovLib\Exception\DatabaseException;
use \mysqli as PHPMySQLi;

/**
 * Wrapper class for MySQLi for automated connecting and closing plus better transaction handling.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MySQLi {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Current PHP mysqli instance.
   *
   * @var \mysqli
   */
  public $mysqli;

  /**
   * The name of the database to connect to.
   *
   * @var string
   */
  private $database;

  /**
   * Flag indicating if a transaction is active.
   *
   * @var boolean
   */
  private $transaction = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Method


  /**
   * Instantiate new MySQLi wrapper and connect to <var>$database</var>.
   *
   * @param string $database [optional]
   *   The name of the database to connect to, defaults to the global default database.
   */
  public function __construct($database = null) {
    $this->database = $database ?: $GLOBALS["movlib"]["default_database"];
  }

  /**
   * Close MySQLi connection.
   *
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __destruct() {
    if ($this->mysqli && !$this->mysqli->errno && $this->mysqli->close() === false) {
      throw new DatabaseException("Could not close MySQLi connection: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods



  /**
   * Check if connected to database.
   *
   * @return boolean
   *   <code>TRUE</code> if connected, otherwise <code>FALSE</code>
   */
  public function connected() {
    return $this->mysqli instanceof PHPMySQLi;
  }

  /**
   * Prepare an SQL statement for execution
   *
   * Prepares the SQL query, and returns a statement handle to be used for further operations on the statement. The
   * query must consist of a single SQL statement. The parameter markers must be bound to application variables using
   * {@see \mysqli_stmt::bind_param()} and/or {@see \mysqli_stmt::bind_result()} before executing the statement or
   * fetching rows.
   *
   * @see \mysqli::prepare()
   * @param string $query
   *   The query, as a string.
   * @return \mysqli_stmt
   *   MySQLi statement object.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function prepare($query) {
    if (($stmt = $this->connect()->prepare($query)) === false) {
      throw new DatabaseException("Could not prepare statement: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    return $stmt;
  }

  /**
   * Performas multiple queries on the database.
   *
   * @param string $queries
   *   The queries, data inside the query should be {@link http://www.php.net/manual/en/mysqli.real-escape-string.php properly escaped}.
   * @return mixed
   *   Returns <code>FALSE</code> if the first statement failed. To retrieve subsequent errors from other statements you
   *   have to call {see \mysqli::next_result()} first.
   */
  public function multiQuery($queries) {
    return $this->connect()->multi_query($queries);
  }

  /**
   * Performs and consumes multiple queries on the database.
   *
   * @param string $queries
   *   The queries, data inside the query should be {@link http://www.php.net/manual/en/mysqli.real-escape-string.php properly escaped}.
   * @param string $exceptionMessage
   *   The message that should be displayed if the multi query fails.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function mutliQueryConsume($queries, $exceptionMessage = "Could not consume multi query") {
    $this->connect()->multi_query($queries);
    do {
      if ($this->mysqli->errno) {
        throw new DatabaseException("{$exceptionMessage}: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
      $this->mysqli->use_result();
    }
    while ($this->mysqli->next_result() && $this->mysqli->more_results());
    return $this;
  }

  /**
   * Performs a query on the database.
   *
   * @param string $query
   *   The query string. Data inside the query should be {@link http://www.php.net/manual/en/mysqli.real-escape-string.php properly escaped}.
   * @param int $resultMode [optional]
   *   Either the constant <var>MYSQLI_USE_RESULT</var> or <var>MYSQLI_STORE_RESULT</var> depending on the desired
   *   behavior. By default, <var>MYSQLI_STORE_RESULT</var> is used.
   * @return boolean|\mysqli_result
   *   Returns <code>FALSE</code> on failure. For successful <code>SELECT</code>, <code>SHOW</code>, <code>DESCRIBE</code>
   *   or <code>EXPLAIN</code> queries a {@see \mysqli_result} object is returned. For other successful queries
   *   <code>TRUE</code>.
   */
  public function query($query, $resultMode = MYSQLI_STORE_RESULT) {
    return $this->connect()->query($query, $resultMode);
  }

  /**
   * Check if a transaction is currently active.
   *
   * @return boolean
   *   <code>TRUE</code> if a transaction is active, otherwise <code>FALSE</code>.
   */
  public function transactionActive() {
    return $this->transaction;
  }

  /**
   * Commit a transaction.
   *
   * @see \mysqli::commit()
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function transactionCommit() {
    $this->_transactionActive("commit");
    if ($this->mysqli->errno) {
      $this->transactionRollback();
      throw new DatabaseException("Error in transaction, rolling back: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    if ($this->mysqli->commit() === false) {
      throw new DatabaseException("Error commiting: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    return $this->transactionStop();
  }

  /**
   * Rollback a transaction.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function transactionRollback() {
    $this->_transactionActive("rollback");
    if ($this->mysqli->rollback() === false) {
      throw new DatabaseException("Could not rollback: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    return $this->transactionStop();
  }

  /**
   * Start a transaction.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function transactionStart() {
    if ($this->connect()->autocommit(false) === false) {
      throw new DatabaseException("Could not start transaction: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    $this->transaction = true;
    return $this;
  }

  /**
   * Stop a transaction.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function transactionStop() {
    $this->_transactionActive("stop transaction");
    if ($this->mysqli->autocommit(true) === false) {
      throw new DatabaseException("Error re-activating auto commiting: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods


  /**
   * Establish connection to database.
   *
   * @return \mysqli
   */
  private function connect() {
    if (!$this->mysqli) {
      $this->mysqli = new PHPMySQLi();
      $this->mysqli->real_connect();
      $this->mysqli->select_db($this->database);
    }
    return $this->mysqli;
  }

  /**
   * Helper method to check if we are connected to the database and have an active transaction.
   *
   * @param string $operationName
   *   The name of the operation that might fail.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  private function _transactionActive($operationName) {
    if (!$this->mysqli) {
      throw new DatabaseException("Cannot {$operationName}, not connected to database!");
    }
    if ($this->transaction === false) {
      throw new DatabaseException("Cannot {$operationName}, no active transaction!");
    }
    return $this;
  }

}
