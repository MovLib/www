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
namespace MovLib\Exception;

use \MovLib\Data\Log;

/**
 * A database exception might be thrown if any database action fails.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DatabaseException extends \mysqli_sql_exception {

  /**
   * Instantiate new DatabaseException.
   *
   * @param string $message
   *   The message explaining the problem.
   * @param string $mysqliError [optional]
   *   The error string from MySQLi.
   * @param int $mysqliErrno [optional]
   *   The error number from MySQLi.
   * @param int $code [optional]
   *   The exception code.
   * @param \Exception $previous [optional]
   *   The previous exception.
   */
  public function __construct($message, $mysqliError = "none", $mysqliErrno = -1, $code = 0, $previous = null) {
    parent::__construct("{$message}: {$mysqliError} ({$mysqliErrno})", $code, $previous);

    // Always log any database exception at warning level for the fingers crossed logger.
    Log::warning($this);
  }

}
