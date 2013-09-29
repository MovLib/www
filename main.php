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
 *
function __autoload($class) {
  $class = strtr($class, "\\", "/");
  require "{$_SERVER["DOCUMENT_ROOT"]}/src/{$class}.php";
}
 * @todo We should get rid of Composer asap because it slows down our site. But right now we need it for HTMLPurifier
 *       and we shure need other external stuff soon to meet our deadline. This is okay for now and we'll compensate
 *       the performance penalty with proper caching.
 */

// Use Composer for simplified autoloading of vendor stuff and our own stuff.
$composerAutoloader = require "{$_SERVER["DOCUMENT_ROOT"]}/vendor/autoload.php";
$composerAutoloader->add("MovLib", "{$_SERVER["DOCUMENT_ROOT"]}/src");

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
    // We have to build our own trace, well, at least we can try with the available information.
    $error["trace"] = [
      [ "function" => __FUNCTION__, "line" => __LINE__, "file" => __FILE__ ],
      [ "function" => "<em>unknown</em>", "line" => $error["line"], "file" => $error["file"] ],
    ];

    // Please note that we HAVE TO use PHP's base exception class at this point, otherwise we can't set our own trace!
    $exception = new \Exception($error["message"], $error["type"]);
    $reflectionClass = new \ReflectionClass($exception);
    foreach ([ "file", "line", "trace" ] as $propertyName) {
      $reflectionProperty = $reflectionClass->getProperty($propertyName);
      $reflectionProperty->setAccessible(true);
      $reflectionProperty->setValue($exception, $error[$propertyName]);
    }

    // Be sure to log this exception before attempting to render the exception presentation. We don't want to risk any
    // further errors without having sent out that mail to all developers.
    \MovLib\Data\Delayed\Logger::stack($exception, \MovLib\Data\Delayed\Logger::FATAL);
    \MovLib\Data\Delayed\Logger::run();

    // Force display of trace upon fatal errors; a user might be able to tell us how to fix the problem (open source FTW).
    $GLOBALS["movlib"]["version"] = substr($GLOBALS["movlib"]["version"], strpos($GLOBALS["movlib"]["version"], "-") + 1) . "-dev";

    try {
      exit((new \MovLib\Presentation\Stacktrace($exception))->getPresentation());
    }
    // @todo How about some ASCII art?
    catch (\Exception $e) {
      header("content-type: text/plain");
      print_r($e);
      exit();
    }
  }
}

// Check for possible fatal errors that are not catchable otherwise.
register_shutdown_function("error_fatal_handler");

/**
* This is the outermost place to catch any exception that might have been forgotten somewhere.
*
* To ensure that no unexpected behaviour crashes our software any uncaught exception will be caught at this place. An
* error is logged and, depending on the error, a message is displayed to the user.
*
* @global \MovLib\Data\I18n $i18n
* @param \Exception $exception
* The uncaught exception.
*/
function uncaught_exception_handler($exception) {
  \MovLib\Data\Delayed\Logger::run();
  exit((new \MovLib\Presentation\Stacktrace($exception))->getPresentation());
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
 */
function error_all_handler($type, $message, $file, $line) {
  throw new \MovLib\Exception\ErrorException($type, $message, $file, $line);
}

// Do not pass an error type for the all handler, as PHP will invoke it for any and every error this way.
set_error_handler("error_all_handler");

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
  // Instantiate new session object and try to resume any existing session.
  $session = new \MovLib\Data\Session();

  // Instantiate global i18n object with the current display language.
  $i18n = new \MovLib\Data\I18n();

  // Instantiate the presenter as set in the nginx route and try to render the presentation.
  $presenter = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
  $presentation = (new $presenter())->getPresentation();
}
// A presentation can throw a redirect exception for different reasons. An error might have happended that needs the
// user to be redirected to a different page, or an action was successfully completed and the user should go to a
// different page to continue. No matter what, the execution of the presentation rendering process has to be terminated
// right away and the redirect has to be executed. Of course any delayed methods should be executed as usual. Throwing
// an exception to redirect is the absolutely right way to handle this kind of action. The presentation couldn't comply
// with it's promise to generate a page, additionally a redirect is producing a side effect and shouldn't be part of our
// object oriented code.
catch (\MovLib\Exception\RedirectException $e) {
  header("Location: {$e->route}", true, $e->status);
  $title = [ 301 => "Moved Permanently", 302 => "Moved Temporarily", 303 => "See Other" ];
  // @todo Do we really have to send this response ourself or is nginx handling this?
  $presentation = "<html><head><title>{$e->status} {$title[$e->status]}</title></head><body bgcolor=\"white\"><center><h1>{$e->status} {$title[$e->status]}</h1></center><hr><center>nginx/{$_SERVER["SERVER_VERSION"]}</center></body></html>";
}
// A presentation can throw a unauthorized exception if the current page needs a valid signed in user. This is another
// exception that has to be in our main application and can't reside in our object oriented code. The current process
// has to be terminated right away, delayed methods have to executed and this kind of code has side effects.
catch (\MovLib\Exception\UnauthorizedException $e) {
  // Ensure any active session is destroyed.
  $session->destroy();

  // We have to ensure that the login page is going to render the form without any further validation, therefor we have
  // to reset the request method to GET because we don't know (and don't want to check) the current request method.
  $_SERVER["REQUEST_METHOD"] = "GET";

  // The rest is straight forward, set headers, init presentation, ...
  // http://stackoverflow.com/a/1088127/1251219
  http_response_code(401);
  header("WWW-Authenticate: MovLib location=\"{$i18n->r("/users/login")}\"");

  $login = new \MovLib\Presentation\Users\Login();
  $login->alerts .= $e->getMessage();
  $presentation = $login->getPresentation();
}
// A presentation can throw a client exception for various client errors including "not found", "gone", "forbidden" and
// "bad request". This type of exception has to stop the execution of the main application immediately and present an
// error page to the user, which includes side effects. The delayed methods still have to be executed.
catch (\MovLib\Exception\Client\AbstractClientException $e) {
  http_response_code($e->status);
  $page = new MovLib\Presentation\Page($e->title);
  $page->alerts .= $e->alert;
  $presentation = $page->getPresentation();
}
// Because of the finally block many exception thrown at this point are not passed to the custom uncaught exception
// handler we defined before. I don't have hard evidence that the finally block is the reason, but this problem first
// was observed after introducing it. Catching the most basic exception type and passing it to our function solves this.
//
// Catch software generated exceptions.
catch (\MovLib\Exception\AbstractException $e) {
  uncaught_exception_handler($e);
}
// Catch PHP generated exceptions and be sure to log them as an error.
catch (\Exception $e) {
  \MovLib\Data\Delayed\Logger::stack($e, \MovLib\Data\Delayed\Logger::ERROR);
  uncaught_exception_handler($e);
}
// This is always executed, no matter what happens!
finally {
  // If we have a session, we have to shut it down correctly.
  if ($session) {
    $session->shutdown();
  }

  // Render the presentation.
  echo $presentation;

  // This makes sure that the output that was generated until this point will be returned to nginx for delivery.
  fastcgi_finish_request();

  $responseEnd = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
  if ($responseEnd > 1.0) {
    \MovLib\Data\Delayed\Logger::stack(
      "RESPONSE {$responseEnd}s <{$_SERVER["SERVER"]}{$_SERVER["REQUEST_URI"]}>",
      \MovLib\Data\Delayed\Logger::SLOW
    );
  }

  // Execute each delayed run method after sending the generated output to the user.
  foreach ($delayed as $classes) {
    foreach ($classes as $class => $method) {
      $class::{$method}();
    }
  }

  $delayedEnd = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
  if ($delayedEnd > 5.0) {
    \MovLib\Data\Delayed\Logger::stack(
      "DELAYED {$responseEnd}s <{$_SERVER["SERVER"]}{$_SERVER["REQUEST_URI"]}>",
      \MovLib\Data\Delayed\Logger::SLOW
    );
  }

  // The logger is always executed at last!
  \MovLib\Data\Delayed\Logger::run();
}
