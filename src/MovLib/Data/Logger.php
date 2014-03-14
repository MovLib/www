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
namespace MovLib\Data;

use \Monolog\Logger;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Handler\BrowserConsoleHandler;

/**
 * PSR-3 compatible static logger class.
 *
 * @link http://www.php-fig.org/psr/psr-3/
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Logger {

  /**
   * Action must be taken immediately.
   *
   * <b>EXAMPLE</b><br>
   * Entire website down, database, unavailable, etc. This should trigger the SMS alerts and wake you up.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function alert($message, array $context = []) {
    $this->log(LOG_ALERT, $message, $context);
  }

  /**
   * Critical conditions.
   *
   * <b>EXAMPLE</b><br>
   * Application component unavailable, unexpeced exception.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function critical($message, array $context = []) {
    $this->log(LOG_CRIT, $message, $context);
  }

  /**
   * Detailed debug information.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function debug($message, array $context = []) {
    $this->log(LOG_DEBUG, $message, $context);
  }

  /**
   * System is unusable.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function emergency($message, array $context = []) {
    $this->log(LOG_EMERG, $message, $context);
  }

  /**
   * Runtime error that doesn't require immediate action but should typically be logged and monitored.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function error($message, array $context = []) {
    $this->log(LOG_ERR, $message, $context);
  }

  /**
   * Interesting events.
   *
   * <b>EXAMPLE</b><br>
   * User logs in, SQL logs.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function info($message, array $context = []) {
    $this->log(LOG_INFO, $message, $context);
  }

  /**
   * Log a message with arbitraty level.
   *
   * @global \MovLib\Kernel $kernel
   * @staticvar \Monolog\Logger $logger
   *   Used to cache the logger instance in use.
   * @param mixed $level
   *   The default log methods make use of PHP's predefined <var>LOG_*</var> constants, but this method allows you to
   *   use any log level.
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function log($level, $message, array $context = []) {
    global $kernel;
    static $logger = null;
    if (!$logger) {
      $logger = new Logger($kernel->hostname, [ new ErrorLogHandler($messageType, $level) ]);
    }
    static $monolog = null;
    if (!$monolog) {
      $monolog = new Logger($kernel->hostname, [ new ErrorLogHandler() ]);
      if ($kernel->production === true && $kernel->fastCGI === true) {
        $monolog->pushHandler(new BrowserConsoleHandler());
      }
    }
    $message .= "\n";
    foreach ($context as $key => $value) {
      $message .= "    {$key}: {$value}\n";
    }
    error_log($message);
  }

  /**
   * Normal but significant events.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function notice($message, array $context = []) {
    $this->log(LOG_NOTICE, $message, $context);
  }

  /**
   * Exceptional occurrences that are not errors.
   *
   * <b>EXAMPLE</b><br>
   * Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   */
  public static function warning($message, array $context = []) {
    $this->log(LOG_WARNING, $message, $context);
  }

}
