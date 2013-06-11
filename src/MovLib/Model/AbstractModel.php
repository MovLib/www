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
  const DEFAULT_DB = "movlib";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Total count of connections for this request.
   *
   * @var int
   */
  private static $connectionCounter = 0;

  /**
   * The MySQLi connection object for all queries.
   *
   * @var \mysqli
   */
  protected static $mysqli;

  /**
   * Total number of rows changed, deleted, or inserted by the last executed statement.
   *
   * @var int
   */
  public $affectedRows;

  /**
   * The MySQLi statement object for all queries.
   *
   * @var \mysqli_stmt
   */
  protected $stmt;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Correctly close the database connection.
   *
   * @see \MovLib\Model\AbstractModel::disconnect()
   */
  public function __destruct() {
    try {
      $this->disconnect();
    } catch (Exception $e) {
      // Do nothing at this point. If everything worked fine till this point there is no reason to exit the execution of
      // the request.
      //
      // @todo Log this exception!
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Final Methods


  /**
   * Generic delete query.
   *
   * <b>Important:</b> All where column/value pairs are concatenated with <code>AND</code>. If you have a more complex
   * query, please use the generic query method.
   *
   * <b>Usage example:</b>
   * <pre>$this->delete("users", "is", [ "id" => 42, "name" => "Smith" ]);</pre>
   *
   * <b>Resulting SQL query:</b>
   * <pre>DELETE FROM `users` WHERE `id` = 42 AND `name` = "Smith";</pre>
   *
   * @see \MovLib\Model\AbstractModel::prepareAndBind()
   * @see \MovLib\Model\AbstractModel::execute()
   * @see \MovLib\Model\AbstractModel::close()
   * @param string $table
   *   The name of the table where a record should be deleted.
   * @param string $types
   *   The type string for the <tt>WHERE</tt> clause in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $where
   *   Associative array containing column names and values for where.
   * @return $this
   * @throws \Exception
   * @throws \MovLib\Exception\DatabaseException
   */
  public final function delete($table, $types, $where) {
    $query = "DELETE FROM `{$table}` WHERE ";
    $helper = "";
    $values = [];
    foreach ($where as $column => $value) {
      $query .= "{$helper}`{$column}` = ?";
      $values[] = $value;
      $helper = " AND ";
    }
    return $this->prepareAndBind($query, $types, $values)->execute()->close();
  }

  /**
   * Get the current MySQLi instance.
   *
   * @return null|\mysqli
   */
  public final function getMySQLi() {
    return self::$mysqli;
  }

  /**
   * Get the current prepared statement instance.
   *
   * @return null|\mysqli_stmt
   */
  public final function getStmt() {
    return $this->stmt;
  }

  /**
   * Generic insert method.
   *
   * <b>Usage example:</b>
   * <pre>$this->insert("users", "ss", [ "name" => "Foobar", "mail" => "foobar@example.com" ]);</pre>
   *
   * <b>Resulting SQL query:</b>
   * <pre>INSERT INTO `users` (`name`, `mail`) VALUES ("Foobar", "foobar@example.com");</pre>
   *
   * @see \MovLib\Model\AbstractModel::prepareAndBind()
   * @see \MovLib\Model\AbstractModel::execute()
   * @see \MovLib\Model\AbstractModel::close()
   * @param string $table
   *   Name of the table where we should insert new data.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $data
   *   Associative array containing column names and values for insert.
   * @return $this
   * @throws \Exception
   * @throws \MovLib\Exception\DatabaseException
   */
  public final function insert($table, $types, $data) {
    $columns = $valueStr = $helper = "";
    $values = [];
    foreach ($data as $column => $value) {
      $columns .= "{$helper}`{$column}`";
      $valueStr .= "{$helper}?";
      $values[] = $value;
      $helper = ", ";
    }
    return $this
      ->prepareAndBind("INSERT INTO `{$table}` ({$columns}) VALUES ({$valueStr})", $types, $values)
      ->execute()
      ->close()
    ;
  }

  /**
   * Generic query with constraints.
   *
   * <b>Usage example:</b>
   * <pre>$this->query('SELECT * FROM `users` WHERE `id` = ?', 'i', [ 42 ]);</pre>
   *
   * @see \MovLib\Model\AbstractModel::prepareAndBind()
   * @see \MovLib\Model\AbstractModel::execute()
   * @see \MovLib\Model\AbstractModel::fetchAssoc()
   * @see \MovLib\Model\AbstractModel::close()
   * @param string $query
   *   The query to be executed.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values that should be bound to the <tt>WHERE</tt> parameters.
   * @return array
   *   The query result as associative array.
   * @throws \Exception
   * @throws \MovLib\Exception\DatabaseException
   */
  public final function select($query, $types, $values) {
    $this->prepareAndBind($query, $types, $values)->execute()->fetchAssoc($result)->close();
    return $result;
  }

  /**
   * Generic query without constraints.
   *
   * <b>Important:</b> If you have to bind parameters to the query, use the generic query method.
   *
   * <b>Usage Example:</b>
   * <pre>$this->queryAll("SELECT * FROM `users`");</pre>
   *
   * @see \MovLib\Model\AbstractModel::prepare()
   * @see \MovLib\Model\AbstractModel::execute()
   * @see \MovLib\Model\AbstractModel::fetchAssoc()
   * @see \MovLib\Model\AbstractModel::close()
   * @param string $query
   *   The query to be executed.
   * @return array
   *   The query result as associative array.
   * @throws \MovLib\Exception\DatabaseException
   */
  public final function selectAll($query) {
    $this->prepare($query)->execute()->fetchAssoc($result)->close();
    return $result;
  }

  /**
   * Generic update query.
   *
   * <b>Important:</b> All where column/value pairs are concatenated with <code>AND</code>. If you have a more complex
   * query, please use the generic query method.
   *
   * <b>Usage example:</b>
   * <pre>$this->update(
   *   "user",
   *   "isiis",
   *   [ "id" => 42, "name" => "foobar", "age" => 99 ],
   *   [ "id" => 1, "name" => "barfoo" ]
   * );</pre>
   *
   * <b>Resulting SQL query:</b>
   * <pre>UPDATE `user` SET `id` = 42, `name` = "foobar", `age` = 99 WHERE `id` = 1 AND `name` = "barfoo";</pre>
   *
   * @see \MovLib\Model\AbstractModel::prepareAndBind()
   * @see \MovLib\Model\AbstractModel::execute()
   * @see \MovLib\Model\AbstractModel::close()
   * @param string $table
   *   Name of the database table to update.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $set
   *   Associative array containing column names and values for <tt>SET</tt>.
   * @param array $where
   *   Associative array containing column names and values for <tt>WHERE</tt>.
   * @return $this
   * @throws \Exception
   * @throws \MovLib\Exception\DatabaseException
   */
  public final function update($table, $types, $set, $where) {
    $query = "UPDATE `{$table}` SET ";
    $comma = "";
    $values = [];
    foreach ($set as $column => $value) {
      $query .= "{$comma}`{$column}` = ?";
      $values[] = $value;
      $comma = ", ";
    }
    $query .= " WHERE ";
    $and = "";
    foreach ($where as $column => $value) {
      $query .= "{$and}`{$column}` = ?";
      $values[] = $value;
      $and = " AND ";
    }
    return $this->prepareAndBind($query, $types, $values)->execute()->close();
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Final Methods


  /**
   * Closes the prepared statement.
   *
   * @return $this
   * @throws \Exception
   *   Might throw a generic excepiton if the prepared statement is not a valid object.
   * @throws \MovLib\Exception\DatabaseException
   *   If closing the prepared statement fails.
   */
  protected final function close() {
    if ($this->stmt->close() === false) {
      throw new DatabaseException("Closing prepared statement failed.");
    }
    unset($this->stmt);
    return $this;
  }

  /**
   * Connect to default database.
   *
   * @return $this
   * @throws \Exception
   *   Might throw a generic exception if (for instance) the socket does not exist.
   * @throws \MovLib\Exception\DatabaseException
   *   If connecting to the database or selecting the database fails.
   */
  protected final function connect() {
    self::$connectionCounter++;
    if (!self::$mysqli) {
      self::$mysqli = new mysqli();
      if (self::$mysqli->real_connect() === false) {
        throw new DatabaseException("Could not connect to database server.");
      }
      if (self::$mysqli->connect_error) {
        throw new DatabaseException(self::$mysqli->error);
      }
      if (self::$mysqli->select_db(self::DEFAULT_DB) === false) {
        throw new DatabaseException(self::$mysqli->error);
      }
    }
    return $this;
  }

  /**
   * Disconnect from database.
   *
   * @return $this
   * @throws \Exception
   *   Might throw a generic exception if the mysqli variable does not contain a valid object.
   */
  protected final function disconnect() {
    self::$connectionCounter--;
    if (self::$connectionCounter === 0) {
      self::$mysqli->close();
      self::$mysqli = null;
    }
    return $this;
  }

  /**
   * Executes the previously prepared statement.
   *
   * @return $this
   * @throws \Exception
   *   Might throw a generic exception if (for instance) the prepared statement is not a valid object.
   * @throws \MovLib\Exception\DatabaseException
   *   If the execution fails (returns <code>false</code>).
   */
  protected final function execute() {
    unset($this->affectedRows);
    if ($this->stmt->execute() === false) {
      $error = $this->stmt->error;
      $errno = $this->stmt->errno;
      $this->close();
      throw new DatabaseException("Execution of statement failed with error message: {$error} ({$errno})");
    }
    $this->affectedRows = $this->stmt->affected_rows;
    return $this;
  }

  /**
   * Get the statement result as array.
   *
   * @param array|null $result
   *   The query result as numeric array containg each resulting row as associative array.
   * @return $this
   * @throws \Exception
   *   Might throw a generic exception if (for instance) the prepared statement is not a valid object.
   * @throws \MovLib\Exception\DatabaseException
   *   If fetching the result failed.
   */
  protected final function fetchAssoc(&$result) {
    if (($queryResult = $this->stmt->get_result()) === false) {
      $this->close();
      throw new DatabaseException("Get statement result failed.");
    }
    $result = [];
    while ($row = $queryResult->fetch_assoc()) {
      $result[] = $row;
    }
    $queryResult->free();
    return $this;
  }

  /**
   * Prepare a statement for execution.
   *
   * @param string $query
   *   The query to be prepared.
   * @return $this
   * @throws \Exception
   * @throws \MovLib\Exception\DatabaseException
   */
  protected final function prepare($query) {
    if (!self::$mysqli) {
      $this->connect();
    }
    if (($this->stmt = self::$mysqli->prepare($query)) === false) {
      throw new DatabaseException("Preparation of statement failed: " . self::$mysqli->error . " (" . self::$mysqli->errno . ")");
    }
    return $this;
  }

  /**
   * Prepare a statement for execution and bind parameters to it.
   *
   * @see \MovLib\Model\AbstractModel::prepare()
   * @param string $query
   *   The query to be prepared.
   * @param string $types
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $values
   *   The values to be substituted.
   * @return $this
   * @throws \Exception
   *   Might throw a generic exception if a PHP error occures. For instance if <var>$values</var> is not an array or the
   *   type count is not equal to the given values count.
   * @throws \MovLib\Exception\DatabaseException
   *   If binding the parameters to the prepared statement fails.
   */
  protected final function prepareAndBind($query, $types, $values) {
    $this->prepare($query);
    $k = count($values);
    $referencedParameters = [ $types ];
    for ($i = 0; $i < $k; ++$i) {
      $referencedParameters[$i + 1] = &$values[$i];
    }
    if (call_user_func_array([ $this->stmt, "bind_param" ], $referencedParameters) === false) {
      throw new DatabaseException("Binding parameters to prepared statement failed.");
    }
    return $this;
  }

}
