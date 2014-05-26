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

use \MovLib\Core\Database\Database;

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
   * @var \MovLib\Core\Container
   */
  protected $container;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new database object.
   *
   * @todo Get rid of dependency injection container!
   * @param \MovLib\Core\Container $container [optional]
   *   The dependency injection container.
   */
  public function __construct(\MovLib\Core\Container $container = null) {
    if ($container) {
      $this->container = $container;
      foreach (get_object_vars($container) as $property => $value) {
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
   * @deprecated
   * @param string $database
   *   The name of the database to connect to, defaults to <code>"movlib"</code>.
   * @return \mysqli
   *   The MySQLi instance.
   * @throws \mysqli_sql_exception
   */
  final protected function getMySQLi($database = "movlib") {
    return Database::getConnection($database);
  }

}
