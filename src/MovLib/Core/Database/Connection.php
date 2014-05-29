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
   * Decode a dynamic column that was fetched with <code>COLUMN_JSON()</code>.
   *
   * @param mixed $property
   *   The property containing the result from the <code>COLUMN_JSON()</code> call.
   * @return this
   */
  public function dynamicDecode(&$property) {
    // If the value of the property is empty or contains an empty JSON object, use NULL. This makes sure that we have
    // the same value within all properties which is important for serialization.
    if (empty($property) || $property == "{}") {
      $property = null;
    }
    // Otherwise we decode the string.
    else {
      $property = json_decode($property, true);

      // Throw an exception in case of error.
      if (($code = json_last_error()) !== JSON_ERROR_NONE) {
        throw new \ErrorException(json_last_error_msg(), $code);
      }
    }
    return $this;
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
