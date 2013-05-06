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

use \MovLib\Exception\DatabaseException;
use \MovLib\Model\ModelInterface;
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
abstract class AbstractModel implements ModelInterface {

  /**
   * The default table to be used by the model.
   *
   * @var string
   */
  protected $defaultTable;

  /**
   * The MySQLi statement object for all queries.
   *
   * @var \mysqli_stmt
   */
  protected $stmt;

  /**
   * Initialize new model base instance.
   *
   * @param string $defaultTable
   *   The default table to be used by the model.
   */
  public function __construct($defaultTable) {
    $this->defaultTable = $defaultTable;
  }

  /**
   * Retrieve a database connection for the specified parameters.
   *
   * @staticvar array $connectionPool
   *   The connection pool
   * @param string $database
   *   The database name.
   * @param string $table
   *   The table name.
   * @return \mysqli
   *   The requested MySQLi connection.
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function getConnection(&$database, &$table) {
    static $connectionPool = [];
    $database = $database ?: $_SERVER['LANGUAGE_CODE'];
    $table = $table ?: $this->defaultTable;
    $socketFolderName = $database . '_' . $table;
    if (array_key_exists($socketFolderName, $connectionPool)) {
      return $connectionPool[$socketFolderName];
    }
    // Use default values from PHP configuration for now. Can be dynamically changed if needed.
    $connection = new mysqli(
      ini_get('mysqli.default_host'),
      'movlib',
      ini_get('mysqli.default_pw'),
      $database,
      ini_get('mysqli.default_port'),
      $_SERVER['DB_SOCKET_PATH'] . "/$socketFolderName/" . $_SERVER['DB_SOCKET_NAME']
    );
    if ($connection->connect_error) {
      throw new DatabaseException("Database connection on socket folder: $socketFolderName failed with message: {$connection->error} ({$connection->errno})");
    }
    $connectionPool[$socketFolderName] = $connection;
    return $connection;
  }

  /**
   * Binds the parameters to a prepared MySQLi statement object.
   *
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The parameters to be bound.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  private function bindParam($types, array $values) {
    array_unshift($values, $types);
    for ($i = 0; $i < count($values); $i++) {
      $values[$i] = &$values[$i];
    }
    if (is_callable([ $this->stmt, 'bind_param' ]) && call_user_func_array([ $this->stmt, 'bind_param' ], $values) === false) {
      throw new DatabaseException('Bind param failed.');
    }
    return $this;
  }

  /**
   * Prepare a statement.
   *
   * @param string $query
   *   The query to be prepared.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function prepare($query, $database = false, $table = false) {
    if (($this->stmt = $this->getConnection($database, $table)->prepare(str_replace('{table}', $table, $query))) === false) {
      throw new DatabaseException('Preparation of statement failed.');
    }
    return $this;
  }

  /**
   * Prepare a statement and bind parameters to it.
   *
   * @param string $query
   *   The query to be prepared.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values to be substituted.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function prepareAndBind($query, $types, array $values, $database = false, $table = false) {
    return $this->prepare($query, $database, $table)->bindParam($types, $values);
  }

  /**
   * Executes the previously prepared statement.
   *
   * @see \MovLib\Model\AbstractModel::stmt
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function execute() {
    if (is_callable([ $this->stmt, 'execute' ]) && $this->stmt->execute() === false) {
      throw new DatabaseException("Execution of statement failed with error message: {$this->stmt->error} ({$this->stmt->errno})");
    }
    return $this;
  }

  /**
   * Get the statement result as an associative array.
   *
   * @param array|null $result
   *   The query result as associative array or <code>null</code> if empty result. If the result consists of only one
   *   row, then only a single array representing this row is returned.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function getStmtResult(&$result) {
    if (is_callable([ $this->stmt, 'get_result' ]) && ($queryResult = $this->stmt->get_result()) === false) {
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
    return $this;
  }

  /**
   * Closes the statement.
   *
   * @see \MovLib\Model\AbstractModel::stmt
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function closeStmt() {
    if (is_callable([ $this->stmt, 'close' ]) && $this->stmt->close() === false) {
      throw new DatabaseException('Closing of statement failed.');
    }
    unset($this->stmt);
    return $this;
  }

  /**
   * A basic delete query.
   *
   * @param string $where
   *   The where clause without the <tt>WHERE</tt> keyword.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  public function delete($where, $database = false, $table = false) {
    return $this->prepare(sprintf('DELETE FROM {table} WHERE %s', $where), $database, $table)->execute()->closeStmt();
  }

  /**
   * Basic insert query method.
   *
   * @param array $columnNames
   *   The insert column names.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values to be inserted.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insert(array $columnNames, $types, array $values, $database = false, $table = false) {
    return $this->prepareAndBind(
      sprintf('INSERT INTO {table} (%s) VALUES (%s)', implode(',', $columnNames), implode(',', array_fill(0, count($values), '?'))),
      $types, $values, $database, $table
    )->execute()->closeStmt();
  }

  /**
   * A basic query without constraints.
   *
   * @param string $query
   *   The query to process.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return array
   *   The query result as associative array.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function queryAll($query, $database = false, $table = false) {
    $this->prepare($query, $database, $table)->execute()->getStmtResult($result)->closeStmt();
    return $result;
  }

  /**
   * A basic query with constraints.
   *
   * @param string $query
   *   The query to be executed.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $args
   *   The query parameters to be substituted.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return array
   *   The query result as associative array.
   * @throws \MovLib\Exception\DatabaseException
   */
  public function query($query, $types, array $args, $database = false, $table = false) {
    $this->prepareAndBind($query, $types, $args, $database, $table)->execute()->getStmtResult($result)->closeStmt();
    return $result;
  }

  /**
   * A basic update query
   *
   * @param array $columns
   *   The colums to be updated.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values to be updated.
   * @param string $where
   *   The where clause without the <tt>WHERE</tt> keyword.
   * @param string $database
   *   [optional] The database name, defaults to <var>$_SERVER['LANGUAGE_CODE']</var>.
   * @param string $table
   *   [optional] The table, defaults to <var>AbstractModel::defaultTable</var>.
   * @return \MovLib\Model\AbstractModel
   * @throws \MovLib\Exception\DatabaseException
   */
  public function update(array $columns, $types, array $values, $where, $database = false, $table = false) {
    return $this->prepareAndBind(
      sprintf('UPDATE {table} SET %s = ? WHERE %s', implode(' = ?,', $columns), $where),
      $types, $values, $database, $table
    )->execute()->closeStmt();
  }

}
