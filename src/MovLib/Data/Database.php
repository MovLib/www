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
namespace MovLib\Data;

use \Exception;
use \MovLib\Exception\DatabaseException;
use \MovLib\Data\Delayed\Logger;
use \mysqli;
use \ReflectionFunction;

/**
 * Base class for all database related classes.
 *
 * Implements the most basic methods to query the database and handles connecting and disconnecting globally for all
 * models. We ensure that we only have a single database connection per request in this class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * TTL value for records in the temporary table that are deleted on a daily basis.
   *
   * @var int
   */
  const TMP_TTL_DAILY = "@daily";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing the connect count for each database.
   *
   * @var array
   */
  private static $connectionCounter = [];

  /**
   * Name of the database to which this instance is connected.
   *
   * @var string
   */
  protected $database;

  /**
   * Associative array containing a single MySQLi instance for each database.
   *
   * @var \mysqli
   */
  private static $mysqli = [];

  /**
   * Total number of rows changed, deleted, or inserted by the last executed statement.
   *
   * @var int
   */
  public $affectedRows = 0;

  /**
   * The MySQLi statement object for all queries.
   *
   * @var \mysqli_stmt
   */
  protected $stmt;

  /**
   * Used to cache the reference to <code>stmt_bind_param()</code> function, which allow us to invoke the function with
   * an array of parameters. Using a shared reflection function among all instances of this function is faster than
   * calling <code>call_user_func_array()</code>.
   *
   * @var \ReflectionFunction
   */
  private static $stmtBindParam;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Correctly close the database connections.
   */
  public function __destruct() {
    try {
      $this->disconnect();
    } catch (Exception $e) {
      // Do nothing! If everything worked fine till this point there is no reason to exit the execution of the request.
      Logger::stack($e);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Final Methods


  /**
   * Generic delete, insert, or update query method.
   *
   * @param string $query
   *   The query to be executed.
   * @param string $types [optional]
   *   The type string in <code>\mysqli_stmt::bind_param()</code> syntax.
   * @param array $params [optional]
   *   The parameters to bind.
   * @param boolean $closeStmt [optional]
   *   Flag indicating if the <code>\mysqli_stmt</code> instance should be closed or not.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function query($query, $types = null, array $params = null, $closeStmt = true) {
    $this->prepareAndExecute($query, $types, $params);
    if ($closeStmt === true) {
      $this->close();
    }
    return $this;
  }

  /**
   * Generic select query method.
   *
   * @param string $query
   *   The query to be executed.
   * @param string $types [optional]
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $params [optional]
   *   The parameters to bind.
   * @return array
   *   The query result as associative array.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function select($query, $types = null, array $params = null) {
    $this->prepareAndExecute($query, $types, $params);
    if (($queryResult = $this->stmt->get_result()) === false) {
      $error = $this->stmt->error;
      $errno = $this->stmt->errno;
      $this->close();
      throw new DatabaseException("Get statement result failed: {$error} ({$errno})");
    }
    $this->close();
    $result = [];
    while ($row = $queryResult->fetch_assoc()) {
      $result[] = $row;
    }
    $queryResult->free();
    return $result;
  }

  /**
   * Set new record in the temporary table.
   *
   * @param mixed $data
   *   The data to store.
   * @param string $ttl [optional]
   *   The time to life for this record, defaults to <var>\MovLib\Data\Database::TMP_TTL_DAILY</var>.
   * @return string
   *   The key (hash) of the newly added record.
   */
  protected function tmpSet($data, $ttl = self::TMP_TTL_DAILY) {
    $hash = hash("sha512", openssl_random_pseudo_bytes(1024));
    $this->query("INSERT INTO `tmp` (`key`, `data`, `ttl`) VALUES (?, ?)", "ss", [ $hash, serialize($data), $ttl ]);
    return $hash;
  }

  /**
   * Get record from the temporary table.
   *
   * @param string $key
   *   The key (hash) of the record.
   * @return null|mixed
   *   The data that was previously stored with this hash or <code>NULL</code> if no record was found for the key.
   */
  protected function tmpGetAndDelete($key) {
    $data = $this->select("SELECT `data` FROM `tmp` WHERE `key` = ? LIMIT 1", "s", [ $key ]);
    if (!empty($data[0])) {
      $data = unserialize($data[0]["data"]);
      $this->query("DELETE FROM `tmp` WHERE `key` = ?", "s", [ $key ]);
    }
    return $data;
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods


  /**
   * Closes the prepared statement.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  private function close() {
    if ($this->stmt->close() === false) {
      $error = $this->stmt->error;
      $errno = $this->stmt->errno;
      throw new DatabaseException("Closing prepared statement failed: {$error} ({$errno})");
    }
    unset($this->stmt);
    return $this;
  }

  /**
   * Connect to database.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  private function connect() {
    if (!$this->database) {
      $this->database = $GLOBALS["movlib"]["default_database"];
    }
    if (!self::$stmtBindParam) {
      self::$stmtBindParam = new ReflectionFunction("mysqli_stmt_bind_param");
    }
    if (!isset(self::$connectionCounter[$this->database])) {
      self::$connectionCounter[$this->database] = 0;
    }
    self::$connectionCounter[$this->database]++;
    if (!isset(self::$mysqli[$this->database])) {
      $mysqli = new mysqli();
      if ($mysqli->real_connect() === false || $mysqli->connect_error) {
        $error = $mysqli->error;
        $errno = $mysqli->errno;
        throw new DatabaseException("Connecting to database server failed: {$error} ({$errno})");
      }
      if ($mysqli->select_db($GLOBALS["movlib"]["default_database"]) === false) {
        $error = $mysqli->error;
        $errno = $mysqli->errno;
        throw new DatabaseException("Selecting database failed: {$error} ({$errno})");
      }
      self::$mysqli[$this->database] = $mysqli;
    }
    return $this;
  }

  /**
   * Disconnect from database.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  private function disconnect() {
    // No need to disconnect if we have no connection to this database.
    if (isset(self::$connectionCounter[$this->database])) {
      // Decrement connection counter for this database connection, if there are no instances left using this database
      // connection (equality to zero) disconnect.
      if (--self::$connectionCounter[$this->database] === 0) {
        if (self::$mysqli[$this->database]->close() === false) {
          $error = self::$mysqli[$this->database]->error;
          $errno = self::$mysqli[$this->database]->errno;
          throw new DatabaseException("Disconnecting from database server failed: {$error} ({$errno})");
        }
        // Make sure the array offset is removed entirely from the array. This ensures that any new instance that needs
        // a connection to this database has to establish a new connection.
        unset(self::$mysqli[$this->database]);
      }
    }
    return $this;
  }

  /**
   * Prepare a statement for execution.
   *
   * @param string $query
   *   The query to be executed.
   * @param string $types [optional]
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $params [optional]
   *   The parameters to bind.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  private function prepareAndExecute($query, $types = null, array $params = null) {
    if (!isset(self::$mysqli[$this->database])) {
      $this->connect();
    }
    if (($this->stmt = self::$mysqli[$this->database]->prepare($query)) === false) {
      $error = self::$mysqli[$this->database]->error;
      $errno = self::$mysqli[$this->database]->errno;
      throw new DatabaseException("Preparation of statement failed: {$error} ({$errno})");
    }
    if ($types && $params) {
      $c = count($params);
      $refParams = [ $this->stmt, $types ];
      for ($i = 0; $i < $c; ++$i) {
        $refParams[$i + 2] = &$params[$i];
      }
      if (self::$stmtBindParam->invokeArgs($refParams) === false) {
        $error = $this->stmt->error;
        $errno = $this->stmt->errno;
        throw new DatabaseException("Binding parameters to prepared statement failed: {$error} ({$errno})");
      }
    }
    $this->affectedRows = 0;
    if ($this->stmt->execute() === false) {
      $error = $this->stmt->error;
      $errno = $this->stmt->errno;
      $this->close();
      throw new DatabaseException("Execution of prepared statement failed: {$error} ({$errno})");
    }
    $this->affectedRows = $this->stmt->affected_rows;
    return $this;
  }

}
