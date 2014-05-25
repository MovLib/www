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
namespace MovLib\Core\Database;

use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the connection class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Connection extends \mysqli {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Connection";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array used to keep track of all transactions.
   *
   * @var array
   */
  public $transactions = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new database connection.
   *
   * @param string $database
   *   The name of the database to connect to.
   * @throws \mysqli_sql_exception
   */
  public function __construct($database) {
    // We don't want to check all over the place if anything returned FALSE, exceptions are much better.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // Configuration is done via the global php.ini file, we only need to know the name of the database.
    parent::__construct(null, null, null, $database);
  }

  /**
   * As recommended in the PHP documentation, always explicitely close the connection.
   */
  public function __destruct() {
    $this->close();
  }

  // @devStart
  // @codeCoverageIgnoreStart

  /**
   * Implements magic method <code>__clone()</code>.
   *
   * @throws \BadFunctionCallException
   *   Always thrown to prevent wrong usage of the connection class.
   */
  public function __clone() {
    throw new \BadFunctionCallException("You cannot clone a connection.");
  }

  /**
   * Implements <code>serialize()</code> callback.
   *
   * @throws \BadFunctionCallException
   *   Always thrown to prevent wrong usage of the connection class.
   */
  public function __sleep() {
    throw new \BadFunctionCallException("You cannot serialize() a connection.");
  }

  /**
   * Implements <code>unserialize()</code> callback.
   *
   * @throws \BadFunctionCallException
   *   Always thrown to prevent wrong usage of the connection class.
   */
  public function __wakeup() {
    throw new \BadFunctionCallException("You cannot unserialize() a connection.");
  }

  // @codeCoverageIgnoreEnd
  // @devEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Decode JSON string returned by <code>"COLUMN_JSON()"</code>.
   *
   * @todo Change to variadic as soon as we have NetBeans support.
   * @param mixed $property
   *   The property containing the result from the <code>"COLUMN_JSON()"</code> function.
   * @return this
   */
  public function dynColDecodeJSON(&$property) {
    if (empty($property) || $property == "{}") {
      $property = null;
    }
    else {
      $property = json_decode($property, true);
    }
    return $this;
  }

  /**
   * Build dynamic column create statement.
   *
   * The create statement should be used if you have all values that you want to store in the database present in an
   * array. The values are inserted if the evaluate to <code>TRUE</code> and omitted otherwise. Any value currently
   * present in the dynamic column will be overwritten and is lost.
   *
   * @param array $definition
   *   The dynamic column definition that will be used to create the query part. The definition has to have the
   *   following format:
   *
   *   <pre>[
   *     "dyn_col_name" => [ "key1" => "value", "key2" => "value" ],
   *     ...
   *   ]</pre>
   * @return string
   *   String containing the <code>"COLUMN_CREATE()"</code> query part based on <var>$definition</var>.
   */
  public function dynColBuildCreate(array $definition) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($definition), "The dynamic column definition cannot be empty.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // This will contain the final query part with the dynamic column create statements.
    $query = null;

    // The definition's first level is the mapping between the actual dynamic column in the table and their values.
    foreach ($definition as $name => $values) {
      // Insert separator if not in first loop.
      $query && ($query .= ",");

      // We insert an empty string into the dynamic column BLOB column if the values are empty. This allows us to SELECT
      // and UPDATE the from the column without any errors. An empty string is considered valid in the context of
      // dynamic columns, whereas NULL wouldn't be. Sadly BLOB columns don't allow for default values, that's why we
      // have to insert the empty string manually.
      if (empty($values)) {
        $part = "''";
      }
      // We have to expand all values and create the key value mapping for the creation statement of the dynamic column.
      else {
        // Initialize with NULL for easy separator decision within the loop.
        $part = null;

        // We assume that the key is valid, as it was defined by a developer, of course we have to properly escape the
        // value for insertion.
        foreach ($values as $key => $value) {
          if ($value == true) {
            $part && ($part .= ",");
            $part .= "'{$key}','{$this->real_escape_string($value)}'";
          }
        }

        // Now we can put the column create function call and the key-value-pairs together if we had any, otherwise we
        // have to fall back to an empty string.
        if ($part) {
          $part = "COLUMN_CREATE({$part})";
        }
        else {
          $part = "''";
        }
      }

      // Prefix the built part with the name of the dynamic column and the proper assignment operator.
      $query .= "`dyn_{$name}`={$part}";
    }

    // Pad the built query part with spaces to avoid incorrect embedding errors.
    return " {$query} ";
  }

  /**
   * Convinience method to fetch an object that performs all tasks to fetch it at once.
   *
   * @param string $query
   *   The query to execute, all data has to be properly escaped.
   * @param string $class
   *   The fully qualified class name.
   * @param array $args [optional]
   *   The arguments that should be passed to the constructor of the class <b>after</b> the result from the query has
   *   been exported to the object's class scope.
   * @return mixed
   *   Instance of <var>$class</var>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   *   If no result was found for <var>$query</var> a client not found exception is thrown. Usually you can let this
   *   exception fly and the kernel will take care of it.
   */
  public function fetchObject($query, $class, array $args = []) {
    if ($this->real_query($query) === false) {
      throw new NotFoundException("Failed to fetch {$class} with query: '{$query};'");
    }
    $result = $this->store_result();
    $object = $result->fetch_object($class, $args);
    $result->free();
    return $object;
  }

  /**
   * Get <code>"COLLATE"</code> SQL part for e.g. <code>"ORDER BY"</code> of strings.
   *
   * <b>EXAMPLE</b><br>
   * <pre>SELECT * FROM `table` ORDER BY `field`{$connection->collate("de")}</pre>
   *
   * @param string $languageCode
   *   The language code for the <code>"COLLATE"</code>.
   * @return null|string
   *   The <code>"COLLATE"</code> SQL part or <code>NULL</code> if none was found for <var>$languageCode</var>.
   */
  public function collate($languageCode) {
    static $collations = [ "de" => "COLLATE utf8mb4_german2_ci" ];
    if (isset($collations[$languageCode])) {
      // Pad the created query with spaces to avoid incorrect embedding.
      return " {$collations[$languageCode]} ";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Overwritten Methods


  /**
   * Starts a transaction.
   *
   * Note that this method will not set autocommit to <code>FALSE</code>, it will only start a transaction with the
   * properties specified via the flags.
   *
   * @internal
   *   This method is overwritten at this point because mysqlnd doesn't recognize MariaDB 10 and emits a warning when
   *   called. We issue the appropriate SQL statements ourselfs and benefit from the same functionality. Note that the
   *   name parameter isn't used right now.
   *
   * @todo Extend to use the name parameter.
   * @todo Should we file a bug over at MariaDB that they report a different version number (higher) or do they have a
   *       reason why it's reporting itself as being some kind of MySQL 5.5 server?
   *
   * @link http://php.net/mysqli.begin-transaction
   * @link https://dev.mysql.com/doc/refman/5.7/en/commit.html
   * @link https://mariadb.com/kb/en/start-transaction/
   * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-consistent-read.html
   * @link https://mariadb.com/kb/en/enhancements-for-start-transaction-with-consistent-snapshot/
   * @param integer $flags [optional]
   *   The transaction's properties.
   * @param string $name [optional]
   *   The transaction's name.
   * @return boolean
   *   <code>TRUE</code> if the transaction was successfully started, <code>FALSE</code> otherwise.
   * @since 5.5.0
   * @throws \mysqli_sql_exception
   *   If configured this method throws an exception upon failure.
   */
  public function begin_transaction($flags = null, $name = null) {
    $properties = null;

    if ($flags | MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT) {
      $properties .= " WITH CONSISTENT SNAPSHOT";
      if ($flags | MYSQLI_TRANS_START_READ_WRITE || $flags | MYSQLI_TRANS_START_READ_ONLY) {
        $properties .= ",";
      }
    }

    if ($flags | MYSQLI_TRANS_START_READ_WRITE) {
      $properties .= " READ WRITE";
    }
    elseif ($flags | MYSQLI_TRANS_START_READ_ONLY) {
      $properties .= " READ ONLY";
    }

    $this->real_query("START TRANSACTION{$properties}");

    // We use exceptions for everything, therefore there's no need for this.
    //
    //$result = $this->query("SELECT @@SESSION.in_transaction");
    //if ($result === false) {
    //  return false;
    //}
    //return (boolean) $result->fetch_all()[0][0];

    return true;
  }

}
