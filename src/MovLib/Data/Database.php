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
namespace MovLib\Data;

use \MovLib\Exception\DatabaseException;

/**
 * Base class for all database related classes.
 *
 * Implements the most basic methods to query the database and handles connecting and disconnecting globally for all
 * models. We ensure that we only have a single database connection per request in this class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing a single MySQLi instance for each database.
   *
   * @var \array
   */
  protected static $connections = [];

  /**
   * Associative array containing language specific collation strings.
   *
   * The key is the system language code and the value contains the collate string that can be used within queries. This
   * is most useful for <code>ORDER BY</code> statements, e.g.:
   *
   * <pre>SELECT * FROM `table` ORDER BY `field`{$db->collations[$i18n->languageCode]}</pre>
   *
   * @var array
   */
  public $collations = [ "en" => null, "de" => " COLLATE utf8mb4_german2_ci" ];

  /**
   * Name of the database to which this instance is connected.
   *
   * @var string
   */
  protected $database = "movlib";

  /**
   * The current MySQLi connection.
   *
   * @var \mysqli
   */
  protected $mysqli;

  /**
   * Used to cache the reference to <code>stmt_bind_param()</code> function, which allow us to invoke the function with
   * an array of parameters. Using a shared reflection function among all instances of this function is faster than
   * calling <code>call_user_func_array()</code>.
   *
   * @var \ReflectionFunction
   */
  protected static $stmtBindParam;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new database.
   *
   * @param string $database [optional]
   *   The database to connect to.
   */
  public function __construct($database = "movlib") {
    $this->database = $database;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Connect to database.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  final protected function connect() {
    if (!isset(self::$connections[$this->database])) {
      // A cached reflection function is faster than call_user_func_array()!
      if (!self::$stmtBindParam) {
        self::$stmtBindParam = new \ReflectionFunction("mysqli_stmt_bind_param");
      }
      $mysqli = new \mysqli();
      if ($mysqli->real_connect() === false || $mysqli->connect_error) {
        throw new DatabaseException("Connecting to database server failed", $mysqli->error, $mysqli->errno);
      }
      if ($mysqli->select_db($this->database) === false) {
        throw new DatabaseException("Selecting database '{$this->database}' failed", $mysqli->error, $mysqli->errno);
      }
      self::$connections[$this->database] = $mysqli;
    }
    if (!$this->mysqli) {
      $this->mysqli = self::$connections[$this->database];
    }
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
    if (!$this->mysqli) {
      $this->connect();
    }
    /* @var $stmt \mysqli_stmt */
    if (($stmt = $this->mysqli->prepare($query)) === false) {
      throw new DatabaseException("Preparation of statement failed", $this->mysqli->error, $this->mysqli->errno);
    }
    if ($types && $params) {
      $refParams = [ $stmt, $types ];
      $c         = count($params);
      for ($i = 0, $j = 2; $i < $c; ++$i, ++$j) {
        $refParams[$j] =& $params[$i];
      }
      if (self::$stmtBindParam->invokeArgs($refParams) === false) {
        throw new DatabaseException("Binding parameters to prepared statement failed", $stmt->error, $stmt->errno);
      }
    }
    if ($stmt->execute() === false) {
      throw new DatabaseException("Execution of prepared statement failed", $stmt->error, $stmt->errno);
    }
    return $stmt;
  }

  /**
   * Whether a transaction is active or not.
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
    if ($this->transactionActive === false) {
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
    if ($this->transactionActive === false) {
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
    if (!$this->mysqli) {
      $this->connect();
    }
    if (($this->transactionActive = $this->mysqli->begin_transaction($flags)) === false) {
      throw new DatabaseException("Could not start transaction", $this->mysqli->error, $this->mysqli->errno);
    }
    return $this;
  }

}
