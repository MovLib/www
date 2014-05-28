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
 * Defines the database class.
 *
 * This class cannot be instantiated and should not be extended. It's a simple factory to get an active connection to
 * the database. It's not a singleton, not global and contains a single static method that simply returns an active
 * connection to the database without any dependencies or state.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class Database {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Database";
  // @codingStandardsIgnoreEnd

  /**
   * Established database connection.
   *
   * @var array
   */
  protected static $connections;

  /**
   * Get active database connection.
   *
   * @param string $database [optional]
   *   The name of the database to get a connection to, defaults to <code>"movlib"</code>.
   * @return \MovLib\Core\Database\Connection
   *   Active database connection.
   */
  final public static function getConnection($database = "movlib") {
    // Return connection if we already established one before.
    if (isset(self::$connections[$database])) {
      return self::$connections[$database];
    }

    // Try to establish a new connection to the desired database. Note that the complete configuration for the default
    // database is done in the global php.ini configuration. We only have to select the database which is hardcoded for
    // now because we only use a single database.
    try {
      self::$connections[$database] = new Connection($database);
    }
    // Connecting to the database may fail for various reasons, most common is a closed socket with persistent
    // connections. The database may have restarted or we've reached a timeout. Doesn't matter, we'll try to connect
    // again.
    catch (\ErrorException $e) {
      // We have to kill any possibly still active thread before attempting to connect again.
      if (isset(self::$connections[$database]->thread_id)) {
        self::$connections[$database]->kill(self::$connections[$database]->thread_id);
      }

      // Try again once, if this doesn't solve the issue let the exception fly.
      self::$connections[$database] = new Connection($database);
    }

    return self::$connections[$database];
  }

}
