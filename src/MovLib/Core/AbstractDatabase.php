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
   * <pre>SELECT * FROM `table` ORDER BY `field`{$db->collations[$i18n->languageCode]}</pre>
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
   * Used for caching of prepared statements.
   *
   * @var \mysqli_stmt
   */
  protected $stmtCache;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new database object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   The dependency injection container.
   */
  final public function __construct(\MovLib\Core\DIContainer $diContainer) {
    $this->diContainer = $diContainer;
    $this->config      = $diContainer->config;
    $this->fs          = $diContainer->fs;
    $this->intl        = $diContainer->intl;
    $this->kernel      = $diContainer->kernel;
    $this->log         = $diContainer->log;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the MySQLi instance.
   *
   * @staticvar null|\mysqli $mysqli
   *   Used to cache the MySQLi instance across all concrete classes.
   * @return \mysqli
   *   The MySQLi instance.
   * @throws \mysqli_sql_exception
   */
  final protected function getMySQLi() {
    static $mysqli;

    // Check if we already have a cached instance.
    if ($mysqli) {
      // @devStart
      // @codeCoverageIgnoreStart
      $this->log->debug("Getting MySQLi", $this->getMySQLiDebugConnectionStats($mysqli));
      // @codeCoverageIgnoreEnd
      // @devEnd
      return $mysqli;
    }

    // @devStart
    // @codeCoverageIgnoreStart
    assert(strpos(static::class, "\\Data\\") !== false, "Only data classes are allowed to extend the database class!");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We don't want to check all over the place if anything returned FALSE, exceptions are much better.
    $driver = new \mysqli_driver();
    $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

    // Instantiate, connect, and select database.
    $mysqli = new \mysqli(ini_get("mysqli.default_host"), null, null, $this->config->database);

    // As per recommendation in the PHP documentation, always explicitely close the connection.
    register_shutdown_function(function () use ($mysqli) { $mysqli->close(); });

    // @devStart
    // @codeCoverageIgnoreStart
    $this->log->debug(
      "Successfully connected (this message should only appear ONCE per request).",
      $this->getMySQLiDebugConnectionStats($mysqli)
    );
    // @codeCoverageIgnoreEnd
    // @devEnd

    return $mysqli;
  }

  // @devStart
  // @codeCoverageIgnoreStart
  private function getMySQLiDebugConnectionStats(\mysqli $mysqli) {
    $connectionStats = null;
    foreach ($mysqli->get_connection_stats() as $key => $value) {
      if (in_array($key, [ "connection_reused", "reconnect", "pconnect_success", "active_connections", "active_persistent_connections" ])) {
        $connectionStats[$key] = $value;
      }
    }
    return $connectionStats;
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * Generic query method.
   *
   * @deprecated Use {@see AbstractDatabase::getMySQLi} and work directly with the native instance. It's WAY faster than
   *   this method and allows you to implement whatever you want without restrictions. Don't forget that you HAVE TO use
   *   prepared statements whenever you deal with user supplied data, but you're good to go for normal query's if the
   *   data you deal with is set by another developer. Normal query's are often much faster.
   * @param string $query
   *   The query to be executed.
   * @param string $types [optional]
   *   The type string in <code>\mysqli_stmt::bind_param</code> syntax.
   * @param array $params [optional]
   *   The parameters to bind.
   * @return \mysqli_stmt
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function query($query, $types = null, array $params = null) {
    $mysqli = $this->getMySQLi();
    $stmt = $mysqli->prepare($query);
    if ($types && $params) {
      $refParams = [ $types ];
      $c         = count($params);
      for ($i = 0, $j = 2; $i < $c; ++$i, ++$j) {
        $refParams[$j] =& $params[$i];
      }
      call_user_func_array([ $stmt, "bind_param" ], $refParams);
    }
    $stmt->execute();
    return $stmt;
  }

}
