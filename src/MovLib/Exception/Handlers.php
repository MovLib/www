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

use \Exception;
use \MovLib\Data\Delayed\Logger;
use \MovLib\Exception\ErrorException;
use \MovLib\Presentation\Stacktrace;
use \ReflectionClass;

/**
 * Registers the various exception and error handlers and restores defaults upon destruction.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Handlers {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Register exception and error handlers.
   */
  public function __construct() {
    set_exception_handler([ $this, "uncaughtExceptionHandler" ]);
    set_error_handler([ $this, "errorHandler" ]);
    register_shutdown_function([ $this, "fatalErrorHandler" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Global function to convert PHP errors to exceptions.
   *
   * PHP by default mostly throws errors and not exceptions (like Java or Ruby). This user-defined error handler converts
   * these errors to exceptions and allows us to catch them and work with them. All PHP errors are runtime errors, as they
   * only appear by <i>doing</i> something. For more info on the differenciation of various exceptions and what they mean
   * have a look at the great article from Ralph Schindler (linked below).
   *
   * Please also note that this function will not convert all kinds of errors. This is due to the fact that it might not
   * even be registered when an error is raised. For instance if something goes wrong while PHP is bootstraping to start
   * our application, or while compiling this file. Of course such errors are more than fatal and should be observed with
   * another software that is capable of rescuing the PHP process itself.
   *
   * @link http://ralphschindler.com/2010/09/15/exception-best-practices-in-php-5-3
   *   Ralph Schindler: Exception best practices in PHP 5.3
   * @param int $type
   *   The error's type, one of the PHP predefined <var>E_*</var> constants.
   * @param string $message
   *   The error's message.
   * @param string $file
   *   The absolute path to the file where the error was raised.
   * @param int $line
   *   The line number within the file.
   * @throws \MovLib\Exception\ErrorException
   */
  public function errorHandler($type, $message, $file, $line) {
    throw new ErrorException($type, $message, $file, $line);
  }

  /**
   * Transforms PHP fatal errors to exceptions.
   *
   * This method is not meant to recover after a fatal error occurred. The purpose of this is to ensure that a nice
   * presentation is displayed to the user.
   *
   * @link http://stackoverflow.com/a/2146171/1251219 How do I catch a PHP Fatal Error
   * @return this
   */
  public function fatalErrorHandler() {
    if (($error = error_get_last())) {
      // We have to build our own trace, well, at least we can try with the available information.
      $error["trace"] = [
        [ "function" => __FUNCTION__, "line" => __LINE__, "file" => __FILE__ ],
        [ "function" => "<em>unknown</em>", "line" => $error["line"], "file" => $error["file"] ],
      ];

      // Please note that we HAVE TO use PHP's base exception class at this point, otherwise we can't set our own trace!
      $exception = new Exception($error["message"], $error["type"]);
      $reflectionClass = new ReflectionClass($exception);
      foreach ([ "file", "line", "trace" ] as $propertyName) {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($exception, $error[$propertyName]);
      }

      // Be sure to log this exception before attempting to render the exception presentation. We don't want to risk any
      // further errors without having sent out that mail to all developers.
      Logger::stack($exception, \MovLib\Data\Delayed\Logger::FATAL);
      Logger::run();

      // Force display of trace upon fatal errors; a user might be able to tell us how to fix the problem (open source FTW).
      $GLOBALS["movlib"]["version"] = substr($GLOBALS["movlib"]["version"], strpos($GLOBALS["movlib"]["version"], "-") + 1) . "-dev";

      try {
        exit((new Stacktrace($exception))->getPresentation());
      }
      // @todo How about some ASCII art?
      catch (Exception $e) {
        header("content-type: text/plain");
        print_r($e);
        exit();
      }
    }
    return $this;
  }

  /**
   * This is the outermost place to catch any exception that might have been forgotten somewhere.
   *
   * To ensure that no unexpected behavior crashes our software any uncaught exception will be caught at this place. An
   * error is logged and - depending on the error - a message is displayed to the user.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \Exception $exception
   *   The uncaught exception
   * @return this
   */
  public function uncaughtExceptionHandler($exception) {
    Logger::run();
    exit((new Stacktrace($exception))->getPresentation());
  }

}
