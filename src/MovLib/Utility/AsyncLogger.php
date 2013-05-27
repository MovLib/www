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
namespace MovLib\Utility;

use \MovLib\Utility\AsyncAbstractWorker;
use \Stackable;

/**
 * Asynchronous logger facility.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AsyncLogger extends AsyncAbstractWorker {

  /**
   * Fatal errors will be written to <tt>logs/fatal.log</tt>.
   *
   * @var string
   */
  const LEVEL_FATAL = "fatal";

  /**
   * Errors will be written to <tt>logs/error.log</tt>.
   *
   * @var string
   */
  const LEVEL_ERROR = "error";

  /**
   * Warnings will be written to <tt>logs/warning.log</tt>.
   *
   * @var string
   */
  const LEVEL_WARNING = "warning";

  /**
   * Info will be written to <tt>logs/info.log</tt>.
   *
   * @var string
   */
  const LEVEL_INFO = "info";

  /**
   * Debug will be written to <tt>logs/debug.log</tt>.
   *
   * @var string
   */
  const LEVEL_DEBUG = "debug";

  /**
   * Maximum size a log file can grow to in Bytes. Defaults to 64 MB.
   *
   * @var int
   */
  const MAX_LOG_SIZE = 67108864;

  /**
   * Log a simple message.
   *
   * @param string $message
   *   The message to log.
   * @param string $level
   *   The log level, defaults to <var>AsyncLogger::LEVEL_WARNING</var>.
   * @return $this
   */
  public static function log($message, $level = self::LEVEL_WARNING) {
    /* @var $instance AsyncLogger */
    $instance = self::getInstance();
    $instance->stack((new AsyncLoggerStackable($level))->constructFromMessage($message));
    return $instance;
  }

  /**
   * Log a exception.
   *
   * @param \Exception $exception
   *   The exception to log.
   * @param string $level
   *   The log level, defaults to <var>AsyncLogger::LEVEL_WARNING</var>.
   * @return $this
   */
  public static function logException($exception, $level = self::LEVEL_WARNING) {
    /* @var $instance AsyncLogger */
    $instance = self::getInstance();
    $instance->stack((new AsyncLoggerStackable($level))->constructFromException($exception));
    return $instance;
  }

}

/**
 * Single asynchronous log entry.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AsyncLoggerStackable extends Stackable {

  /**
   * Log level, defines in which file the log entry goes.
   *
   * @var string
   */
  private $level;

  /**
   * The log message itself.
   *
   * @var string
   */
  private $message;

  /**
   * Create new log entry.
   *
   * @param string $level
   *   Filename of the log file.
   */
  public function __construct($level) {
    $this->level = $level;
  }

  /**
   * Log an exception.
   *
   * @param \Exception $exception
   * @return $this
   */
  public function constructFromException($exception) {
    $this->message = " [{$exception->getFile()}:{$exception->getLine()}]: {$exception->getMessage()}";
    return $this;
  }

  /**
   * Log a simple message.
   *
   * @param string $message
   *   The message to log.
   * @return $this
   */
  public function constructFromMessage($message) {
    $this->message = ": $message";
    return $this;
  }

  /**
   * Write log entry.
   */
  public function run() {
    // We have no access to the $_SERVER variable in this thread.
    file_put_contents("/var/www/logs/{$this->level}.log", date("Y-m-d H:i:s") . $this->message . PHP_EOL, FILE_APPEND);
  }

}
