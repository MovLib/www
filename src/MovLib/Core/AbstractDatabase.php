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
namespace MovLib\Core;

/**
 * Defines the base for all classes that need a database connection.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractDatabase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The active config instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * Associative array containing language specific collation strings.
   *
   * The key is the system language code and the value contains the collate string that can be used within queries. This
   * is most useful for <code>ORDER BY</code> statements, e.g.:
   *
   * <pre>SELECT * FROM `table` ORDER BY `field`{$this->collations[$this->intl->languageCode]}</pre>
   *
   * @var array
   */
  protected $collations = [
    "en" => null,
    "de" => " COLLATE utf8mb4_german2_ci",
  ];

  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\DIContainer
   */
  protected $diContainer;

  /**
   * The active file system instance.
   *
   * @var \MovLib\Core\FileSystem
   */
  protected $fs;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The active kernel instance.
   *
   * @var \MovLib\Core\Kernel
   */
  protected $kernel;

  /**
   * The active log instance.
   *
   * @var \MovLib\Core\Log
   */
  protected $log;

  /**
   * The shared MySQLi connection.
   *
   * @var null|\mysqli
   */
  private static $mysqli;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new database object.
   *
   * @todo Get rid of dependency injection container!
   * @param \MovLib\Core\DIContainer $diContainer [optional]
   *   The dependency injection container.
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer = null) {
    if ($diContainer) {
      $this->diContainer = $diContainer;
      foreach (get_object_vars($diContainer) as $property => $value) {
        if (property_exists($this, $property)) {
          $this->$property = $value;
        }
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the MySQLi instance.
   *
   * @param string $database
   *   The name of the database to connect to, defaults to <code>"movlib"</code>.
   * @return \mysqli
   *   The MySQLi instance.
   * @throws \mysqli_sql_exception
   */
  final protected function getMySQLi($database = "movlib") {
    // Check if we already have a cached instance.
    if (isset(self::$mysqli[$database])) {
      return self::$mysqli[$database];
    }

    // We don't want to check all over the place if anything returned FALSE, exceptions are much better.
    $driver = new \mysqli_driver();
    $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

    try {
      // Instantiate, connect, and select database. Note that the complete configuration is done in PHPs global ini
      // configuration file.
      self::$mysqli[$database] = new \mysqli(null, null, null, $database);
    }
    catch (\ErrorException $e) {
      if (isset(self::$mysqli[$database]->thread_id)) {
        self::$mysqli[$database]->kill(self::$mysqli[$database]->thread_id);
      }
      return $this->getMySQLi($database);
    }

    // As per recommendation in the PHP documentation, always explicitely close the connection.
    register_shutdown_function(function () {
      self::$mysqli[$databaseName]->close();
      unset(self::$mysqli[$databaseName]);
    });

    return self::$mysqli[$database];
  }

  /**
   * Decode JSON response from dynamic column.
   *
   * We store an empty string in every dynamic column when we insert into a table that contains a dynamic column. This
   * has the huge advantage that we can simply call column add later on, but if we need all the content from the column
   * an empty object is returned by the database. This utility method was made to ease the handling of this special
   * case that's contraproductive for us.
   *
   * @param string $property
   *   The property containing the JSON response.
   * @return this
   */
  final protected function jsonDecode(&...$properties) {
    $c = count($properties);
    for ($i = 0; $i < $c; ++$i) {
      if (empty($properties[$i]) || $properties[$i] == "{}") {
        $properties[$i] = null;
      }
      else {
        $properties[$i] = json_decode($properties[$i], true);
      }
    }
    return $this;
  }

}
