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

use \Monolog\Formatter\LineFormatter;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Handler\FingersCrossedHandler;
use \Monolog\Handler\FirePHPHandler;
use \Monolog\Handler\NativeMailerHandler;
use \Monolog\Logger;
use \Monolog\Processor\IntrospectionProcessor;

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
final class Log {

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
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function alert($message, array $context = []) {
    return self::log(Logger::ALERT, $message, $context);
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
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function critical($message, array $context = []) {
    return self::log(Logger::CRITICAL, $message, $context);
  }

  /**
   * Detailed debug information.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function debug($message, array $context = []) {
    return self::log(Logger::DEBUG, $message, $context);
  }

  /**
   * System is unusable.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function emergency($message, array $context = []) {
    return self::log(Logger::EMERGENCY, $message, $context);
  }

  /**
   * Runtime error that doesn't require immediate action but should typically be logged and monitored.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function error($message, array $context = []) {
    return self::log(Logger::ERROR, $message, $context);
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
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function info($message, array $context = []) {
    return self::log(Logger::INFO, $message, $context);
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
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function log($level, $message, array $context = []) {
    global $kernel;
    static $logger = null;

    // Instantiate logger if we have no instance yet.
    if (!$logger) {

      // CRITICAL, ALERT, and EMERGENCY always trigger sending of email.
      $handlers = [
        new NativeMailerHandler(
          $kernel->emailDevelopers, "IMPORTANT! {$kernel->siteName} is experiencing problems!", $kernel->emailFrom, Logger::CRITICAL
        ),
        new FingersCrossedHandler((new ErrorLogHandler())
          ->setFormatter(new LineFormatter("%channel% %level_name%: %message% %context% %extra%\n", null, true))
        )
      ];

      // DEBUG, INFO, and NOTICE are sent to the client's browser if not in production and executed via php-fpm.
      if ($kernel->production === false && $kernel->fastCGI === true) {
        $handlers[] = new FirePHPHandler(Logger::DEBUG, false);
      }

      // Instantiate the new logger and store it in the static variable of this method for later usage.
      $logger = new Logger($kernel->hostname, $handlers, [ new IntrospectionProcessor(Logger::WARNING) ]);
    }

    // Log the message as requested.
    return $logger->log($level, $message, $context);
  }

  /**
   * Normal but significant events.
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function notice($message, array $context = []) {
    return self::log(Logger::NOTICE, $message, $context);
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
   * @return boolean
   *   Whether the record has been processed.
   */
  public static function warning($message, array $context = []) {
    return self::log(Logger::WARNING, $message, $context);
  }

}
