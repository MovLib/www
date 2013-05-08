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
namespace MovLib\Model;

use \Exception;
use \MovLib\Exception\DatabaseException;
use \mysqli;

/**
 * Base class for all models.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractModel {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Default database name.
   *
   * @var string
   */
  const DEFAULT_DB = 'movlib';


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The MySQLi connection object for all queries.
   *
   * @var \mysqli
   */
  private $mysqli;

  /**
   * The MySQLi statement object for all queries.
   *
   * @var \mysqli_stmt
   */
  private $stmt;


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Initialize new model base instance and connect to default database.
   *
   * @throws \MovLib\Exception\DatabaseException
   */
  public function __construct() {
    $this->connect();
  }

  /**
   * Correctly close the database connection.
   */
  public function __destruct() {
    $this->disconnect();
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Closes the prepared statement.
   *
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   *   Only raises the exception if closing of a valid prepared statement instance failed. If the prepared statement is
   *   not a valid instance any reference will be unset and this method simply returns.
   */
  protected final function close() {
    // Check if we can call close and if we can, call it.
    if (is_callable([ $this->stmt, 'close' ]) && $this->stmt->close() === false) {
      throw new DatabaseException('Closing prepared statement failed.');
    }
    unset($this->stmt);
    return $this;
  }

  /**
   * Connect to default database.
   *
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function connect() {
    try {
      $this->mysqli = new mysqli();
      if ($this->mysqli->real_connect() === false) {
        throw new DatabaseException('Could not connect to default database server.');
      }
      if ($this->mysqli->connect_error) {
        throw new DatabaseException("Database connect error with message: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
      if ($this->mysqli->select_db(self::DEFAULT_DB) === false) {
        throw new DatabaseException('Could not use default database: ' . self::DEFAULT_DB);
      }
    } catch (Exception $e) {
      throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
    }
    return $this;
  }

  /**
   * Generic delete query.
   *
   * <b>Usage example:</b>
   * <pre>$this->delete(
   *   'user',                        // Table name
   *   'id = ? AND name = ? LIMIT 1', // Where clause
   *   'is',                          // Where types
   *   [ 1, 'Foobar' ]                // Where values
   * );</pre>
   *
   * @param string $table
   *   The name of the table where a record should be deleted.
   * @param string $whereClause
   *   The identifying string that precedes the SQL WHERE clause.
   * @param array $setValues
   *   The type string for where in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $whereValues
   *   The values that identify the row.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function delete($table, $whereClause, $whereTypes, array $whereValues) {
    return $this
      ->prepareAndBind(sprintf('DELETE FROM %s WHERE %s', $table, $whereClause), $whereTypes, $whereValues)
      ->execute()
      ->close()
    ;
  }

  /**
   * Disconnect from database.
   *
   * @return \MovLib\Model\AbstractModel
   */
  protected final function disconnect() {
    if (is_callable([ $this->mysqli, 'close' ])) {
      $this->mysqli->close();
    }
    unset($this->mysqli);
    return $this;
  }

  /**
   * Executes the previously prepared statement.
   *
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function execute() {
    try {
      if (is_callable([ $this->stmt, 'execute' ]) === false) {
        $this->close();
        throw new DatabaseException('No valid prepared statement instance.');
      }
      if ($this->stmt->execute() === false) {
        $error = $this->stmt->error;
        $errno = $this->stmt->errno;
        $this->close();
        throw new DatabaseException("Execution of statement failed with error message: $error ($errno)");
      }
    } catch (Exception $e) {
      throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
    }
    return $this;
  }

  /**
   * Get the statement result as associative array.
   *
   * @param array|null $result
   *   The query result as associative array or <code>null</code> if empty result. If the result consists of only one
   *   row, then only a single array representing this row is returned.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function fetchAssoc(&$result) {
    try {
      if (is_callable([ $this->stmt, 'get_result' ]) === false) {
        $this->close();
        throw new DatabaseException('No valid prepared statement instance.');
      }
      if (($queryResult = $this->stmt->get_result()) === false) {
        $this->close();
        throw new DatabaseException('Get statement result failed.');
      }
      if ($queryResult->num_rows === 1) {
        $result = $queryResult->fetch_assoc();
      }
      else {
        $result = [];
        while ($row = $queryResult->fetch_assoc()) {
          $result[] = $row;
        }
      }
      $queryResult->free();
    } catch (Exception $e) {
      throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
    }
    return $this;
  }

  /**
   * Get the current MySQLi instance.
   *
   * @return null|\mysqli
   */
  protected final function getMySQLi() {
    return $this->mysqli;
  }

  /**
   * Get the current prepared statement instance.
   *
   * @return null|\mysqli_stmt
   */
  protected final function getStmt() {
    return $this->stmt;
  }

  /**
   * Generic insert method.
   *
   * @param string $table
   *   Name of the table where we should insert new data.
   * @param array $columns
   *   Names of the columns where we should insert new data.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values that should be inserted.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function insert($table, array $columns, $types, array $values) {
    return $this
      ->prepareAndBind(
        sprintf(
          'INSERT INTO %s (%s) VALUES (%s)',
          $table,
          implode(',', $columns),
          implode(',', array_fill(0, count($values), '?'))
        ),
        $types,
        $values
      )
      ->execute()
      ->close()
    ;
  }

  /**
   * Prepare a statement for execution.
   *
   * @param string $query
   *   The query to be prepared.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function prepare($query) {
    if (($this->stmt = $this->mysqli->prepare($query)) === false) {
      $this->close();
      throw new DatabaseException("Preparation of statement failed: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    return $this;
  }

  /**
   * Prepare a statement for execution and bind parameters to it.
   *
   * @param string $query
   *   The query to be prepared.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values to be substituted.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function prepareAndBind($query, $types, array $values) {
    try {
      if (($typeCount = strlen($types)) !== ($valueCount = count($values))) {
        throw new DatabaseException("Wrong parameter count, expected $typeCount but received $valueCount.");
      }
      $this->prepare($query);
      foreach ($values as $delta => $value) {
        $values[$delta] = &$value;
      }
      array_unshift($values, $types);
      if (call_user_func_array([ $this->stmt, 'bind_param' ], $values) === false) {
        throw new DatabaseException('Binding parameters to prepared statement failed.');
      }
    } catch (Exception $e) {
      throw new DatabaseException($e->getMessage(), $e->getCode(), $e);
    }
    return $this;
  }

  /**
   * Generic query with constraints.
   *
   * @param string $query
   *   The query to be executed.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values that should be inserted.
   * @return array
   *   The query result as associative array.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function query($query, $types, array $values) {
    $this
      ->prepareAndBind($query, $types, $values)
      ->execute()
      ->fetchAssoc($result)
      ->close()
    ;
    return $result;
  }

  /**
   * Generic query without constraints.
   *
   * @param string $query
   *   The query to be executed.
   * @return array
   *   The query result as associative array.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function queryAll($query) {
    $this
      ->prepare($query)
      ->execute()
      ->fetchAssoc($result)
      ->close()
    ;
    return $result;
  }

  /**
   * Generic update query.
   *
   * <b>Usage example:</b>
   * <pre>$this->update(
   *   'user',                 // Table name
   *   'is',                   // Set types
   *   [ 42, 'Foo' ],          // Set values
   *   'id = ? AND name = ?',  // Where clause
   *   'is',                   // Where types
   *   [ 1, 'Bar' ]            // Where values
   * );</pre>
   *
   * @param string $table
   *   Name of the database table to update.
   * @param string $setTypes
   *   The type string for set in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $setValues
   *   The values that should be set.
   * @param string $whereClause
   *   The identifying string that precedes the SQL WHERE clause.
   * @param array $setValues
   *   The type string for where in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $whereValues
   *   The values that identify the row.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function update($table, $setTypes, array $setValues, $whereClause, $whereTypes, array $whereValues) {
    return $this
      ->prepareAndBind(
        sprintf(
          'UPDATE %s SET %s = ? WHERE %s',
          $table,
          implode(' = ?,', $setValues),
          $whereClause
        ),
        $setTypes . $whereTypes,
        array_merge($setValues, $whereValues)
      )
      ->execute()
      ->close()
    ;
  }

}
