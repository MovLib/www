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
namespace MovLib\Tool;

use \MovLib\Exception\DatabaseException;

/**
 * The developer database has a pure public interface and many more methods to interact with the database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Flag indicating if a transaction is active.
   *
   * @var boolean
   */
  private $transactionActive = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
      throw new \InvalidArgumentException("Escape variable must be of type string (or number).");
    }
    return $this->connect()->real_escape_string($str);
  }

  /**
   * Execute multiple queries against the database.
   *
   * <b>IMPORTANT!</b>
   * You have to properly escape the data in the queries.
   *
   * @param string $queries
   *   Multiple queries to execute.
   * @param boolean $foreignKeyChecks [optional]
   *   Whether foreign keys should be checked or not during execution, defaults to <code>TRUE</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function queries($queries, $foreignKeyChecks = true) {
    // Obviously we can only execute string queries.
    if (!is_string($queries)) {
      $type = gettype($queries);
      throw new \InvalidArgumentException("Parameter \$queries must be of type string, {$type} given.");
    }

    // Obviously we have to have at least a single query.
    if (empty($queries)) {
      throw new \InvalidArgumentException("Parameter \$queries cannot be empty.");
    }

    // Disallow direct SET on foreign key checks, if one forgets to set it back we have huge problems.
    if (strpos($queries, "foreign_key_checks") !== false) {
      throw new \LogicException("Your queries contain 'foreign_key_checks', you shouldn't tamper with this directly because it's dangerous!");
    }

    // The proper way is to set the parameter to FALSE which will always reset the foreign key checks.
    if ($foreignKeyChecks === false) {
      $this->query("SET foreign_key_checks = 0");
    }

    // Execute the queries and directly consume them.
    $mysqli = $this->connect();
    $error  = $mysqli->multi_query($queries);
    do {
      if ($error === false) {
        if ($foreignKeyChecks === false) {
          $this->query("SET foreign_key_checks = 1");
        }
        throw new DatabaseException("Execution of multiple queries failed", $mysqli->error, $mysqli->errno);
      }
      $mysqli->use_result();
      if (($more = $mysqli->more_results())) {
        $error = $mysqli->next_result();
      }
    }
    while ($more);

    if ($foreignKeyChecks === false) {
      $this->query("SET foreign_key_checks = 1");
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
    return parent::query($query, $types, $params);
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
      throw new \InvalidArgumentException("Database name must be of type string.");
    }
    $this->database = $database;
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
    $mysqli = $this->connect();
    if (($this->transactionActive = $mysqli->commit($flags)) === false) {
      $e = new DatabaseException("Commit failed", $mysqli->error, $mysqli->errno);
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
    $mysqli = $this->connect();
    if (($this->transactionActive = $mysqli->rollback($flags)) === false) {
      throw new DatabaseException("Rollback failed", $mysqli->error, self::$mysqli->errno);
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
    $mysqli = $this->connect();
    if (($this->transactionActive = $mysqli->begin_transaction($flags)) === false) {
      throw new DatabaseException("Could not start transaction", $mysqli->error, $mysqli->errno);
    }
    return $this;
  }

}
