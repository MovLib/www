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
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/ movlib.org
 * @since 0.0.1-dev
 */

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
$composerAutoloader->add("MovLib", "src/");

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
  $GLOBALS["movlib"] = parse_ini_file("{$_SERVER["DOCUMENT_ROOT"]}/conf/movlib.ini");
  $config       = new \MovLib\Configuration();
  $handlers     = new \MovLib\Exception\Handlers();
  $session      = new \MovLib\Data\User\Session();
  $i18n         = new \MovLib\Data\I18n();
  $presenter    = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
  $presentation = (new $presenter())->getPresentation();
}
// A presentation can throw a client exception for various client errors including "not found", "gone", "forbidden" and
// "bad request". This type of exception has to stop the execution of the main application immediately and present an
// error page to the user, which includes side effects. The delayed methods still have to be executed.
catch (\MovLib\Exception\Client\AbstractErrorException $e) {
  $presentation = $e->presentation->getPresentation();
}
// A presentation can throw a redirect exception for different reasons. An error might have happended that needs the
// user to be redirected to a different page, or an action was successfully completed and the user should go to a
// different page to continue. No matter what, the execution of the presentation rendering process has to be terminated
// right away and the redirect has to be executed. Of course any delayed methods should be executed as usual. Throwing
// an exception to redirect is the absolutely right way to handle this kind of action. The presentation couldn't comply
// with it's promise to generate a page, additionally a redirect is producing a side effect and shouldn't be part of our
// object oriented code.
catch (\MovLib\Exception\Client\AbstractRedirectException $e) {
  header($e->locationHeader);
  $presentation = $e->presentation;
}
// A presentation can throw a unauthorized exception if the current page needs a valid signed in user. This is another
// exception that has to be in our main application and can't reside in our object oriented code. The current process
// has to be terminated right away, delayed methods have to executed and this kind of code has side effects.
catch (\MovLib\Exception\Client\UnauthorizedException $e) {
  header($e->authenticateHeader);
  $presentation = $e->presentation->getPresentation();
}
// Because of the finally block many exception thrown at this point are not passed to the custom uncaught exception
// handler we defined before. I don't have hard evidence that the finally block is the reason, but this problem first
// was observed after introducing it. Catching the most basic exception type and passing it to our function solves this.
//
// Catch software generated exceptions.
catch (\MovLib\Exception\AbstractException $e) {
  $handlers->uncaughtExceptionHandler($e);
}
// Catch PHP errors, warnings, etc.
catch (\ErrorException $e) {
  $handlers->errorHandler($e->getSeverity(), $e->getMessage(), $e->getFile(), $e->getLine());
}
// Catch PHP generated exceptions and be sure to log them as an error.
catch (\Exception $e) {
  \MovLib\Data\Delayed\Logger::stack($e, \MovLib\Data\Delayed\Logger::ERROR);
  $handlers->uncaughtExceptionHandler($e);
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
