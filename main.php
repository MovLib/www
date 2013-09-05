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

/**
 * Main PHP script serving all page requests within the MovLib application.
 *
 * The main script contains all bootstrap functionaility that every script needs. We do not include another file for the
 * bootstrap process, simply to keep things easy to understand. But, this file should only contains procedural PHP
 * extensions and no object-oriented code. Additionally developers should think long and hard if the function they are
 * going to implement is really needed by every request. If not, move it to some other place that is more appropriate
 * (like a static class that is automatically loaded if a script needs a method from it).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 * @since 0.0.1-dev
 */

// Parse global configuration and ensure it's available globally.
$GLOBALS["movlib"] = parse_ini_file("{$_SERVER["DOCUMENT_ROOT"]}/conf/movlib.ini");

/**
 * Ultra fast class autoloader.
 *
 * @param string $class
 *   Fully qualified class name (automatically passed to this magic function by PHP).
 */
function __autoload($class) {
  $class = strtr($class, "\\", "/");
  require "{$_SERVER["DOCUMENT_ROOT"]}/src/{$class}.php";
}

/**
 * This is the outermost place to catch any exception that might have been forgotten somewhere.
 *
 * To ensure that no unexpected behaviour crashes our software any uncaught exception will be caught at this place. An
 * error is logged and, depending on the error, a message is displayed to the user.
 *
 * @global \MovLib\Model\I18nModel $i18n
 * @param \Exception $exception
 *   The uncaught exception.
 */
function uncaught_exception_handler($exception) {
  global $i18n;
  $i18n = $i18n ?: new \MovLib\Model\I18nModel();
  $presenter = new \MovLib\Presenter\ExceptionPresenter($exception);
  \MovLib\Utility\DelayedLogger::run();
  exit($presenter);
}

// Set the default exception handler.
set_exception_handler("uncaught_exception_handler");

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
function error_all_handler($type, $message, $file, $line) {
  $exception = new \MovLib\Exception\ErrorException($message, null, $type);
  $exception->setFile($file)->setLine($line);
  throw $exception;
}

// Do not pass an error type for the all handler, as PHP will invoke it for any and every error this way.
set_error_handler("error_all_handler");

/**
 * Transform PHP fatal errors to exceptions.
 *
 * This function is not meant to recover after a fatal error occurred. The purpose of this is to ensure that a nice
 * error view is displayed to the user.
 *
 * @link http://stackoverflow.com/a/2146171/1251219 How do I catch a PHP Fatal Error
 */
function error_fatal_handler() {
  if ($error = error_get_last()) {
    $exception = new \Exception($error["message"], $error["type"]);
    $reflection = new \ReflectionClass("Exception");

    $trace = $reflection->getProperty("trace");
    $trace->setAccessible(true);
    $trace->setValue($exception, [
      [ "function" => __FUNCTION__, "line" => __LINE__, "file" => __FILE__ ],
      [ "function" => "<em>unknown</em>", "line" => $error["line"], "file" => $error["file"] ],
    ]);

    $file = $reflection->getProperty("file");
    $file->setAccessible(true);
    $file->setValue($exception, $error["file"]);

    $line = $reflection->getProperty("line");
    $line->setAccessible(true);
    $line->setValue($exception, $error["line"]);

    uncaught_exception_handler($exception);
  }
}

// Check for possible fatal errors that are not catchable otherwise.
register_shutdown_function("error_fatal_handler");

// Array to collect class names and function names which will be executed after the response was sent to the user.
$delayed = [];

/**
 * Register new delayed class to be called after the response has been sent to the user.
 *
 * @global array $delayed
 * @param string $class
 *   Absolute class name (use the magic <var>__CLASS__</var> constant).
 * @param int $weight [optional]
 *   Defines when this class should be called. This is important if your class relys on other delayed classes. The
 *   weight must not be negative! Defaults to 50, the lower the earlier the execution.
 * @param string $method [optional]
 *   The name of the method that should be called, defaults to <i>run</i>.
 */
function delayed_register($class, $weight = 50, $method = "run") {
  global $delayed;
  $delayed[$weight][$class] = $method;
}

try {
  // Instantiate session for the client requesting the page. A real session will only be generated if we know this user.
  // By default we don't start a session, nor do we send out any cookies to the client. We wait until the client is doing
  // anything where we really need a session.
  $user = new \MovLib\Model\SessionModel();

  // Instantiate global i18n object with the current display language.
  $i18n = new \MovLib\Model\I18nModel();

  // Start the rendering process.
  $presenter = "\\MovLib\\Presenter\\{$_SERVER["PRESENTER"]}Presenter";
  echo new $presenter();
}
// If the request requires user authentication or there was a problem during initializing the session, return
// appropriate HTTP headers and display the login page with an alert that tells the user that this request requires
// authentication. This is the most outter scope were we can catch and construct this, not pretty, but effective and
// easy to use for us developers.
catch (\MovLib\Exception\UnauthorizedException $e) {
  // If the exception was thrown in the session model no i18n instance is present, but we need it.
  if (!$i18n) {
    $i18n = new \MovLib\Model\I18nModel();
  }
  // We have to ensure that the login page is going to render the form without any further validation, therefor we have
  // to reset the request method to GET because we don't know (and don't want to check) the current request method.
  $_SERVER["REQUEST_METHOD"] = "GET";
  // The rest is straight forward, set headers, init presenter, render view ...
  http_response_code(401);
  // http://stackoverflow.com/a/1088127/1251219
  header("WWW-Authenticate: MovLib location=\"{$i18n->r("/user/login")}\"");
  $presenter = new \MovLib\Presenter\User\UserLoginPresenter();
  $presenter->view->addAlert(new \MovLib\View\HTML\Alert($e->getMessage(), [
    "block" => true,
    "title" => $e->getTitle(),
    "severity" => \MovLib\View\HTML\Alert::SEVERITY_ERROR,
  ]));
  echo $presenter;
}

// This makes sure that the output that was generated until this point will be returned to nginx for delivery.
fastcgi_finish_request();

// Ensure that the session gets written to our session database (encountered some odd situations where they weren't;
// this seems to solve this, although I never found out what is really causing the problem, it apparently is tightly
// bound to the above call to fastcgi_finish_request().
if (session_status() === PHP_SESSION_ACTIVE) {
  session_write_close();
}

// Execute each delayed run method after sending the generated output to the user.
foreach ($delayed as $classes) {
  foreach ($classes as $class => $method) {
    $class::{$method}();
  }
}

// The logger is always executed at last!
\MovLib\Utility\DelayedLogger::run();
