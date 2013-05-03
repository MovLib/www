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

  /** Default absolute path to database sockets */
  const SOCKET_PATH = '/var/run/mysqld/';

  /** Default socket name */
  const SOCKET_NAME = '/mysqld.sock';

  /** Name of the language independet database */
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
    return new mysqli(
      ini_get('mysqli.default_host'),
      ini_get('mysqli.default_user'),
      ini_get('mysqli.default_pw'),
      $database,
      ini_get('mysqli.default_port'),
      AbstractModel::SOCKET_PATH . $socketFolderName . AbstractModel::SOCKET_NAME
    );
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
