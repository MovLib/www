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

use \mysqli;
use \mysqli_stmt;
use \MovLib\Exception\DatabaseException;

/**
 *
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

  /** Name of the language independent database */
  const COMMON_DB = 'common';

  /**
   *
   *
   * @var string
   */
  protected $defaultTable;

  /**
   *
   *
   * @var string
   */
  protected $languageCode;

  /**
   *
   *
   * @var array
   */
  private $socketPool = [];

  /**
   *
   *
   * @param string $languageCode
   * @param string $defaultTable
   */
  public function __construct($languageCode, $defaultTable) {
    $this->languageCode = $languageCode;
    $this->defaultTable = $defaultTable;
  }

  public function __destruct() {
    foreach ($this->socketPool as $socketName => $socket) {
      $socket->close();
    }
  }

  /**
   * Retrieve a database connection for the specified parameters.
   *
   * @param string $database
   *   The database name.
   * @param string $table
   *   The table name.
   * @return \mysqli
   *   The requested connection.
   */
  private function getConnection($database, $table) {
    /* @var $socketFolderName string */
    $socketFolderName = $database . '_' . $table;
    if (array_key_exists($socketFolderName, $this->socketPool)) {
      return $this->socketPool[$socketFolderName];
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
      throw new DatabaseException('Database connection on socket folder: '
        . $socketFolderName . ' failed with message: ' . $connection->error . ' (' . $connection->errno . ')');
    }
    $this->socketPool[$socketFolderName] = $connection;
    return $connection;
  }

  private function bindParam(mysqli_stmt &$stmt, $types, array $values) {
    array_unshift($values, $types);
    for ($i = 0; $i < count($values); $i++) {
      $values[$i] = &$values[$i];
    }
    call_user_func_array([ $stmt, 'bind_param' ], $values);
  }

    /**
   * A basic delete query.
   *
   * @param string $where
   * @param string $database
   * @param string $table
   * @throws DatabaseException
   */
  public function delete($where, $database = AbstractModel::COMMON_DB, $table = false) {
    $table = $table ?: $this->defaultTable;
    $conn = $this->getConnection($database, $table);
    $query = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
    $stmt = $conn->prepare($query);
    $result = $stmt->execute();
    $stmt->close();
    if ($result === false) {
      throw new DatabaseException('Delete failed on socket: ' . $database . '_' . $table . ' with message: '
        . $conn->error . ' (' . $conn->errno . ')');
    }
  }

  /**
   * Basic insert query method.
   *
   * @param array $columnNames
   * @param type $types
   * @param array $values
   * @param type $database
   * @param type $table
   * @throws DatabaseException
   */
  public function insert(array $columnNames, $types, array $values, $database = AbstractModel::COMMON_DB, $table = false) {
    $table = $table ?: $this->defaultTable;
    /* @var $mysqli \mysqli */
    $conn = $this->getConnection($database, $table);
    /* @var $query mixed */
    $query = [];
    foreach ($values as $delta => $value) {
      $query[] = '?';
    }
    /* @var $stmt \mysqli_stmt */
    $stmt = $conn->prepare(sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $table, implode('`, `', $columnNames), implode(', ', $query)));
    $this->bindParam($stmt, $types, $values);
    $result = $stmt->execute();
    $stmt->close();
    if ($result === false) {
      throw new DatabaseException("Insert failed on socket: {$database}_{$table} with message: {$conn->error} ({$conn->errno})");
    }
  }

  /**
   * A basic query without constraints.
   *
   * @param string $query
   *   The query to process.
   * @param string $database
   * @param string $table
   *
   * @return array
   *   The query result as associative array.
   */
  public function queryAll($query, $database = AbstractModel::COMMON_DB, $table = false) {
    $result = [];
    $conn = $this->getConnection($database, $table ?: $this->defaultTable);
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $queryResult = $stmt->get_result();
    while ( $row = $queryResult->fetch_assoc()) {
      $result[] = $row;
    }
    $queryResult->close();
    $stmt->close();
    return $result;
  }

  /**
   * A basic query with constraints.
   *
   * @param string $query
   * @param string $types
   *   The query parameter types in mysqli->prepare() syntax.
   * @param array $args
   *   The query parameters to be substituted.
   * @param string $database
   * @param string $table
   *
   * @return array
   *   The query result as associative array.
   */
  public function query($query, $types, array $args, $database = AbstractModel::COMMON_DB, $table = false) {
    $result = [];
    $conn = $this->getConnection($database, $table ?: $this->defaultTable);
    $stmt = $conn->prepare($query);
    $this->bindParam($stmt, $types, $args);
    $stmt->execute();
    $queryResult = $stmt->get_result();
    while ($row = $queryResult->fetch_assoc()) {
      $result[] = $row;
    }
    $queryResult->close();
    $stmt->close();
    return $result;
  }

  /**
   * A basic update query
   *
   * @param array $columns
   * @param string $types
   *   The query parameter types in mysqli->prepare() syntax.
   * @param array $values
   * @param string $where
   * @param string $database
   * @param string $table
   * @throws DatabaseException
   */
  public function update(array $columns, $types, array $values, $where, $database = AbstractModel::COMMON_DB, $table = false) {
    $table = $table ?: $this->defaultTable;
    $conn = $this->getConnection($database, $table);
    $query = sprintf('UPDATE `%s` SET `%s` = ?', $table, implode('` = ?, `', $columns));
    if (isset($where)) {
      $query .= " WHERE $where";
    }
    $stmt = $conn->prepare($query);
    $this->bindParam($stmt, $types, $values);
    $result = $stmt->execute();
    $stmt->close();
    if ($result === false) {
      throw new DatabaseException('Update failed on socket: ' . $database . '_' . $table . ' with message: '
        . $conn->error . ' (' . $conn->errno . ')');
    }
  }

  /**
   * Retrieve the version of the MySQL server that the MySQLi extension is connected to.
   *
   * @param string $database
   *   [optional] The database name.
   * @param string $table
   *   [optional] The table name.
   * @return string
   *   String representing the server version.
   */
  public function serverInfo($database = AbstractModel::COMMON_DB, $table = false) {
    return $this->getConnection($database, $table ?: $this->defaultTable)->server_info;
  }

}
