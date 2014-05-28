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

use \Monolog\Formatter\HtmlFormatter;
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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Log";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Logger system in use.
   *
   * @var \Monolog\Logger
   */
  protected $logger;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new logger.
   *
   * @param \MovLib\Core\Config $config
   *   Active global configuration instance.
   * @param string $name
   *   The log entry's name, use hostname for HTTP and process title's for CLI.
   * @param boolean $http
   *   Whether this logger is executing in HTTP context or not.
   * @throws \ErrorException
   */
  public function __construct(\MovLib\Core\Config $config, $name, $http) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($name), "Log name cannot be empty.");
    assert(is_string($name), "Log name must be of type string.");
    assert(is_bool($http), "\$http must be of type boolean.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    $mailer = new NativeMailerHandler(
      $config->emailDevelopers,
      "IMPORTANT! {$config->sitename} is experiencing problems!",
      $config->emailFrom,
      Logger::ERROR,
      true,
      2048
    );
    $mailer->setContentType("text/html");
    $mailer->setFormatter(new HtmlFormatter());

    // DEBUG upwards will be logged to the error log.
    $errorLog = new ErrorLogHandler();
    $errorLog->setFormatter(new LineFormatter("%channel% %level_name%: %message% %context% %extra%\n", null, true));

    // Always use the fingers crossed handler to ensure that we have as much information as possible.
    $handlers = [ $mailer, $errorLog ];

    // DEBUG, INFO, and NOTICE are sent to the client's browser if not in production and executed via php-fpm.
    if ($http && !$config->production) {
      $handlers[] = new FirePHPHandler(Logger::DEBUG);
      $errorLog->setLevel(Logger::INFO);
    }

    // Instantiate the new logger and store it in the static variable of this method for later usage.
    $this->logger = new Logger($name, $handlers);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Action must be taken immediately.
   *
   * <b style="color:red">SEVERITY 550</b>
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
  public function alert($message, array $context = []) {
    return $this->logger->addRecord(Logger::ALERT, $message, $context);
  }

  /**
   * Critical conditions.
   *
   * <b style="color:red">SEVERITY 500</b>
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
  public function critical($message, array $context = []) {
    return $this->logger->addRecord(Logger::CRITICAL, $message, $context);
  }

  /**
   * Detailed debug information.
   *
   * <b style="color:red">SEVERITY 100</b>
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public function debug($message, array $context = []) {
    return $this->logger->addRecord(Logger::DEBUG, $message, $context);
  }

  /**
   * System is unusable.
   *
   * <b style="color:red">SEVERITY 600</b>
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public function emergency($message, array $context = []) {
    return $this->logger->addRecord(Logger::EMERGENCY, $message, $context);
  }

  /**
   * Runtime error that doesn't require immediate action but should typically be logged and monitored.
   *
   * <b style="color:red">SEVERITY 400</b>
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public function error($message, array $context = []) {
    return $this->logger->addRecord(Logger::ERROR, $message, $context);
  }

  /**
   * Interesting events.
   *
   * <b style="color:red">SEVERITY 200</b>
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
  public function info($message, array $context = []) {
    return $this->logger->addRecord(Logger::INFO, $message, $context);
  }

  /**
   * Log a message with arbitraty level.
   *
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
  public function log($level, $message, array $context = []) {
    return $this->logger->addRecord($level, $message, $context);
  }

  /**
   * Normal but significant events.
   *
   * <b style="color:red">SEVERITY 250</b>
   *
   * @param string|object $message
   *   Either a string or an object implementing a <code>__toString()</code> method.
   * @param array $context [optional]
   *   The context array can contain arbitrary data, the only thing that is important is, that you put possibly thrown
   *   exceptions into the <code>"exception"</code> key.
   * @return boolean
   *   Whether the record has been processed.
   */
  public function notice($message, array $context = []) {
    return $this->logger->addRecord(Logger::NOTICE, $message, $context);
  }

  /**
   * Exceptional occurrences that are not errors.
   *
   * <b style="color:red">SEVERITY 300</b>
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
  public function warning($message, array $context = []) {
    return $this->logger->addRecord(Logger::WARNING, $message, $context);
  }

}
