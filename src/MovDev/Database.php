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
namespace MovDev;

use \InvalidArgumentException;
use \MovLib\Exception\DatabaseException;
use \mysqli;

/**
 * The developer database has a pure public interface and many more methods to interact with the database.
 *
 * @property \mysqli $mysqli The current MySQLi instance.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   *
   */
  private $database;

  /**
   * The current MySQLi instance.
   *
   * @var \mysqli
   */
  public $mysqli;

  /**
   * Flag indicating if a transaction is active.
   *
   * @var boolean
   */
  private $transactionActive = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new developer database object.
   *
   * @param string $database
   *   The name of the database to connect to.
   * @throws \InvalidArgumentException
   */
  public function __construct($database = "movlib") {
    $this->setDatabase($database);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Establish connection to database.
   *
   * @return \mysqli
   * @throws \InvalidArgumentException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function connect() {
    if (!isset($this->mysqli)) {
      try {
        $this->mysqli = new mysqli();
        $this->mysqli->real_connect();
      }
      catch (\ErrorException $e) {
        $this->mysqli->kill($this->mysqli->thread_id);
        $this->mysqli->real_connect();
      }
      if ($this->mysqli->connect_error) {
        throw new DatabaseException("Connecting to database server failed", $mysqli->error, $mysqli->errno);
      }
      if ($this->mysqli->select_db($this->database) === false) {
        throw new DatabaseException("Selecting database '{$this->database}' failed", $mysqli->error, $mysqli->errno);
      }
    }
    return $this->mysqli;
  }

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
    if (!is_string($str) && !is_numeric($str)) {
      throw new InvalidArgumentException("Escape variable must be of type string (or integer).");
    }
    return $this->connect()->real_escape_string($str);
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
    if (!is_string($queries)) {
      throw new InvalidArgumentException("Queries must be of type string.");
    }
    $error = $this->connect()->multi_query($queries);
    do {
      if ($error === false) {
        throw new DatabaseException("Execution of multiple queries failed", $this->mysqli->error, $this->mysqli->errno);
      }
      $this->mysqli->use_result();
      if (($more = $this->mysqli->more_results())) {
        $error = $this->mysqli->next_result();
      }
    }
    while ($more);
    return $this;
  }

  /**
   * Generic query method.
   *
   * @param string $query
   *   The query to be executed.
   * @param string $types [optional]
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $params [optional]
   *   The parameters to bind.
   * @return \mysqli_stmt
   * @throws \MovLib\Exception\DatabaseException
   */
  public function query($query, $types = null, array $params = null) {
    if (!is_string($query)) {
      throw new InvalidArgumentException("Query must be of type string.");
    }
    /* @var $stmt \mysqli_stmt */
    if (($stmt = $this->connect()->prepare($query)) === false) {
      throw new DatabaseException("Preparation of statement failed", $this->mysqli->error, $this->mysqli->errno);
    }
    if ($types && $params) {
      if (!is_string($types) || empty($params)) {
        throw new InvalidArgumentException("Types must be of type string and params of type array (not empty).");
      }
      $refParams = [ $types ];
      $c         = count($params);
      for ($i = 0, $j = 1; $i < $c; ++$i, ++$j) {
        $refParams[$j] =& $params[$i];
      }
      if (call_user_func_array([ $stmt, "bind_param" ], $refParams) === false) {
        throw new DatabaseException("Binding parameters to prepared statement failed", $stmt->error, $stmt->errno);
      }
    }
    if ($stmt->execute() === false) {
      throw new DatabaseException("Execution of prepared statement failed", $stmt->error, $stmt->errno);
    }
    return $stmt;
  }

  /**
   * Set the database to which this instance should connect to.
   *
   * @param string $database
   *   The name of the database.
   * @throws \InvalidArgumentException
   */
  public function setDatabase($database) {
    if (!is_string($database)) {
      throw new InvalidArgumentException("Database name must be of type string.");
    }
    $this->database = $database;
  }

  /**
   * Whetever a transaction is active or not.
   *
   * @return boolean
   *   <code>TRUE</code> if a transaction is active, otherwise <code>FALSE</code>.
   */
  public function transactionActive() {
    return $this->transactionActive;
  }

  /**
   * Commit current transaction.
   *
   * @param int $flags [optional]
   *   A bitmask of <var>MYSQLI_TRANS_COR_*</var> constants, defaults to <var>MYSQLI_TRANS_COR_AND_NO_CHAIN</var>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function transactionCommit($flags = MYSQLI_TRANS_COR_AND_NO_CHAIN) {
    if (!isset($this->mysqli) || $this->transactionActive === false) {
      throw new DatabaseException("No active transaction, nothing to commit.");
    }
    if (($this->transactionActive = $this->mysqli->commit($flags)) === false) {
      $e = new DatabaseException("Commit failed", $this->mysqli->error, $this->mysqli->errno);
      $this->transactionRollback();
      throw $e;
    }
    return $this;
  }

  /**
   * Rollback current transaction.
   *
   * @param int $flags [optional]
   *   A bitmask of <var>MYSQLI_TRANS_COR_*</var> constants, defaults to <var>MYSQLI_TRANS_COR_AND_NO_CHAIN</var>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function transactionRollback($flags = MYSQLI_TRANS_COR_AND_NO_CHAIN) {
    if (!isset($this->mysqli) || $this->transactionActive === false) {
      throw new DatabaseException("No active transaction, nothing to rollback.");
    }
    if (($this->transactionActive = $this->mysqli->rollback($flags)) === false) {
      throw new DatabaseException("Rollback failed", $this->mysqli->error, $this->mysqli->errno);
    }
    return $this;
  }

  /**
   * Start a transaction.
   *
   * Executes a SQL native <code>START TRANSACTION</code> against the database and establishes a connection if not
   * connection is available.
   *
   * @param int $flags [optional]
   *   One of the <var>MYSQLI_TRANS_START_*</var> constants, defaults to <var>MYSQLI_TRANS_START_READ_WRITE</var>.
   * @return this
   * @throws \MovLib\Data\DatabaseException
   */
  public function transactionStart($flags = MYSQLI_TRANS_START_READ_WRITE) {
    if (($this->transactionActive = $this->connect()->begin_transaction($flags)) === false) {
      throw new DatabaseException("Could not start transaction", $this->mysqli->error, $this->mysqli->errno);
    }
    return $this;
  }

}
