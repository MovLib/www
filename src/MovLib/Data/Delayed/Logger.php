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
namespace MovLib\Data\Delayed;

use \MovLib\Data\Delayed\Mailer;
use \MovLib\Presentation\Email\MovDevEmail;

/**
 * Delayed log facility.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Logger {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Default log entry type.
   *
   * @var int
   */
  const LOGTYPE_DEFAULT = 0;

  /**
   * Exception log entry type.
   *
   * @var int
   */
  const LOGTYPE_EXCEPTION = 1;

  /**
   * Maximum size a log file can grow to in Bytes. Defaults to 64 MB.
   *
   * @var int
   */
  const MAX_LOG_SIZE = 67108864;


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * Array used to collect all log entries.
   *
   * @var array
   */
  private static $logEntries = [];


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Writes all log entries from the stack to the appropriate log file on the filesystem.
   */
  public static function run() {
    $logEntries = [];
    // Generate log entries from stack (unpacking of nested arrays is only available in PHP 5.5+).
    foreach (self::$logEntries as list($type, $date, $logEntry, $level)) {
      $logEntries[$level] = "";
      switch ($type) {
        case self::LOGTYPE_EXCEPTION:
          $logEntries[$level] .= "{$date} [{$logEntry->getFile()}:{$logEntry->getLine()}]: {$logEntry->getMessage()}\n";
          break;

        default:
          $logEntries[$level] .= "{$date}: {$logEntry}\n";
          break;
      }
    }
    // Write all log entries to the appropriate log file. If the maximum log filesize is reached simply overwrite the
    // existing log file. Otherwise append the entries to the log.
    foreach ($logEntries as $level => $logEntry) {
      self::logNow($logEntry, $level);
    }
  }

  /**
   * Log a simple message.
   *
   * @see \MovLib\Data\Delayed\Logger::stack()
   * @param string $message
   *   The message to log.
   * @param string $level
   *   The log level, defaults to <var>E_WARNING</var>.
   */
  public static function log($message, $level = E_WARNING) {
    self::stack($message, $level);
  }

  /**
   * Log a simple message now, this means that this entry will not be written with a delay, it's in real-time!
   *
   * This method is used internally by this class, but can be utilized for debugging. This is also the reason why the
   * default level is set to <var>E_NOTICE</var>.
   *
   * @param string $logEntry
   *   The entry to log.
   * @param string $level
   *   The log level, defaults to <var>E_NOTICE</var>. <b>HINT:</b> If you pass along a string as level, you can log
   *   to a completely different log file. This is very useful for creating dedicated debug log files. Example usage
   *   can be found in the \MovLib\Data\Delayed\Mailer class.
   */
  public static function logNow($logEntry, $level = E_NOTICE) {
    $logFile = $level;
    if (!is_string($level)) {
      // @see http://www.php.net/manual/en/errorfunc.constants.php
      switch ($level) {
        case E_ERROR:
        case E_PARSE:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
          $logFile = "error";
          // Send email to developers upon logging of entries with high levels, there must be something that needs a fix.
          $preLogEntry = htmlspecialchars($logEntry, ENT_QUOTES);
          (new Mailer())->send(new MovDevEmail(
            "IMPORTANT! An error message was just logged.",
            "A critical error was just logged, here is the log entry:\n\n{$logEntry}",
            "<p>A critical error was just logged, here is the log entry:</p><pre>{$preLogEntry}</pre>"
          ));
          break;

        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
          $logFile = "warning";
          break;

        default:
          $logFile = "notice";
          break;
      }
    }
    $logFile = "{$_SERVER["DOCUMENT_ROOT"]}/logs/{$logFile}.log";
    if (!is_file($logFile)) {
      touch($logFile);
    }
    if (filesize($logFile) >= self::MAX_LOG_SIZE) {
      exec("tail -n 100 {$logFile} > {$logFile}");
    }
    file_put_contents($logFile, $logEntry, FILE_APPEND);
  }

  /**
   * Log an exception.
   *
   * @see \MovLib\Data\Delayed\Logger::stack()
   * @param \Exception $exception
   *   The exception to log.
   * @param string $level
   *   The log level, defaults to <var>E_WARNING</var>.
   */
  public static function logException($exception, $level = E_WARNING) {
    self::stack($exception, $level, self::LOGTYPE_EXCEPTION);
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Static Methods


  /**
   * Add log entry to stack.
   *
   * @param mixed $message
   *   The log entry's message or object containing the message.
   * @param string $level
   *   The log entry's severity level.
   * @param int $type [optional]
   *   The log entry's type, defaults to <var>AsyncLogger::LOGTYPE_DEFAULT</var>.
   * @return this
   */
  private static function stack($message, $level, $type = self::LOGTYPE_DEFAULT) {
    self::$logEntries[] = [ $type, date("Y-m-d H:i:s"), $message, $level ];
  }

}