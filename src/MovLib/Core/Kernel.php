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

use \MovLib\Core\Config;
use \MovLib\Core\Database;
use \MovLib\Core\FileSystem;
use \MovLib\Core\HTTP\Cache;
use \MovLib\Core\HTTP\Request;
use \MovLib\Core\HTTP\Response;
use \MovLib\Core\HTTP\Session;
use \MovLib\Core\I18n;
use \MovLib\Core\Log;
use \MovLib\Presentation\Stacktrace;

/**
 * The kernel is the core of MovLib itself.
 *
 * This class is responsible for building the base system that is available to all components of the MovLib software.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Kernel {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Used to collect methods that should executed after the response was sent to the client.
   *
   * @see Kernel::delayMethodCall()
   * @see Kernel::executeDelayedMethods()
   * @var array
   */
  protected $delayedMethods = [];

  /**
   * Whether this kernel is handling a CLI process or not.
   *
   * @var boolean
   */
  public $cli = false;

  /**
   * Whether this kernel is handling a HTTP request/response or not.
   *
   * @var boolean
   */
  public $http = false;

  /**
   * Whether this kernel is executed in privileged mode (root or sudo) or not.
   *
   * @var boolean
   */
  public $privileged = false;

  /**
   * Whether executed via Windows or not, only used in console context.
   *
   * @var boolean
   */
  public $windows = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new MovLib kernel.
   *
   * @global \MovLib\Core\HTTP\Cache $cache
   * @global \MovLib\Core\Config $config
   * @global \MovLib\Core\Database $db
   * @global \MovLib\Core\FileSystem $fs
   * @global \MovLib\Core\I18n $i18n
   * @global \MovLib\Core\Kernel $kernel
   * @global \MovLib\Core\HTTP\Request $request
   * @global \MovLib\Core\HTTP\Response $response
   * @global \MovLib\Core\HTTP\Session $session
   * @param string $documentRoot
   *   The real document root path.
   */
  public function boot($documentRoot) {
    global $config, $db, $fs, $i18n;

    // Determine the environment before attempting to boot.
    $this->http = !($this->cli = PHP_SAPI == "cli");

    // Transform all PHP errors to exceptions.
    set_error_handler([ $this, "errorHandler" ]);

    // Always try to create a nice output, for developers and clients, in HTTP mode. NOTE: must be registered before
    // any booting starts because any booting might fail!
    if ($this->http) {
      set_exception_handler([ $this, "exceptionHandler" ]);
      register_shutdown_function([ $this, "fatalErrorHandler" ]);
      ini_set("display_errors", false);
    }

    $fs = new FileSystem($documentRoot);
    $fs->registerStreamWrappers();

    $config = file_exists(Config::URI) ? unserialize(file_get_contents(Config::URI)) : new Config();
    if (empty($_SERVER["LANGUAGE_CODE"])) {
      $_SERVER["LANGUAGE_CODE"] = $config->defaultLanguageCode;
    }
    // @devStart
    // @codeCoverageIgnoreStart
    // @todo REMOVE ME as soon as we have no coming soon page!
    $config->hostname = "alpha.movlib.org";
    // @codeCoverageIgnoreEnd
    // @devEnd

    $db   = new Database($config->database);
    $i18n = new I18n($_SERVER["LANGUAGE_CODE"], $config->defaultLocale, $config->locales, $db);

    $config->siteSlogan            = $i18n->t($config->siteSlogan);
    $args                          = [ "sitename" => $config->siteName, "slogan" => $config->siteSlogan ];
    $config->siteNameAndSlogan     = $i18n->t($config->siteNameAndSlogan, $args);
    $config->siteNameAndSloganHTML = $i18n->t($config->siteNameAndSloganHTML, $args);

    // Determine if we're booting for a HTTP request.
    if ($this->http) {
      // Instantiate HTTP objects.
      global $cache, $request, $response, $session;
      $request  = new Request();
      $cache    = new Cache();
      $session  = new Session();
      $response = new Response();

      // From here it's save to disable the display errors feature from PHP.
      $session->resume();
      $presentation = $response->respond($config->siteName);
      $session->shutdown();

      // @devStart
      // @codeCoverageIgnoreStart
      Log::debug("Response Time: " . (microtime(true) - $request->timeFloat));
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Send the response to the client.
      echo $presentation;
      if (fastcgi_finish_request() === false) {
        Log::error("FastCGI finish request failure");
      }

      // Shutdown the system.
      $this->bench("response", 0.75);
      $cache->store($presentation);
    }
    // Check if we're booting for a CLI request.
    else {
      // Binaries might be executed via privileged user accounts (root or sudo) and is even required for very few
      // commands. It's very important to check for this because files might have the wrong owners otherwise.
      $this->privileged = posix_getuid() === 0;

      // It's important to differentiate between our default environment (Linux) and Windows in console context.
      $this->windows = defined("PHP_WINDOWS_VERSION_MAJOR");
    }

    $this->executeDelayedMethods();
    $fs->deleteRegisteredFiles();

    if ($this->http) {
      $this->bench("shutdown", 0.5);
    }
  }

  /**
   * Benchmark code execution against the request's start time.
   *
   * @global \MovLib\Core\HTTP\Request $request
   * @param string $what
   *   Short description of what is being benchmarked.
   * @param float $target
   *   The target time to meet, if current microtime is above the target a log entry will be created.
   * @return this
   */
  protected function bench($what, $target) {
    global $request;
    if (($time = microtime(true) - $request->timeFloat) > $target) {
      // We don't use the logger at this point, because we always want these events to be logged.
      error_log("Slow {$what} with {$time} for {$request->uri}");
    }
    return $this;
  }

  /**
   * Execute given callable after response was sent to the client.
   *
   * @param callable $callable
   *   The callable to execute delayed.
   * @param array $params [optional]
   *   Parameters that should be passed to <var>$callable</var>.
   * @return this
   */
  public function delayMethodCall(callable $callable, array $params = null) {
    $this->delayedMethods[] = [ $callable, $params ];
    return $this;
  }

  /**
   * Transforms PHP errors to PHP's {@see \ErrorException}.
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
   * @param int $severity
   *   The error's type, one of the PHP predefined <var>E_*</var> constants.
   * @param string $message
   *   The error's message.
   * @param string $file
   *   The absolute path to the file where the error was raised.
   * @param int $line
   *   The line number within the file.
   * @throws \ErrorException
   */
  public function errorHandler($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, 0, $file, $line);
  }

  /**
   * Used to catch uncaught exceptions.
   *
   * @global \MovLib\Core\Config $config
   * @param \Exception $exception
   *   The exception that wasn't caught.
   */
  public function exceptionHandler($exception) {
    global $config;
    Log::critical($exception);
    exit((new Stacktrace($config->siteName, $exception))->getPresentation());
  }

  /**
   * Execute all delayed methods.
   *
   * @return this
   */
  protected function executeDelayedMethods() {
    foreach ($this->delayedMethods as list($callable, $params)) {
      try {
        call_user_func_array($callable, (array) $params);
      }
      catch (Exception $e) {
        Log::error($e);
      }
    }
    return $this;
  }

  /**
   * Transform fatal errors to exceptions.
   *
   * This has nothing to do with recovering from a fatal error. The purpose is to ensure that a nice presentation is
   * displayed to the client, including any information that might be helpful in resolving the fatal error.
   *
   * We use an anonymous function at this point because we don't want anyone to execute this function other that
   * PHP itself.
   *
   * @link http://stackoverflow.com/a/2146171/1251219
   */
  public function fatalErrorHandler() {
    if (($error = error_get_last())) {
      $line = __LINE__ - 2;

      // Let xdebug provide the stack if available.
      if (function_exists("xdebug_get_function_stack")) {
        $error["trace"] = array_reverse(xdebug_get_function_stack());
        $error["trace"][0]["line"] = $line;
      }
      // We have to build our own trace.
      else {
        $error["trace"] = [
          [ "function" => __FUNCTION__, "line" => $line, "file" => __FILE__ ],
          [ "function" => "<em>unknown</em>", "line" => $error["line"], "file" => $error["file"] ],
        ];
      }

      // Please note that we have to use PHP's base exception class at this point, otherwise we can't set our own trace.
      $exception = new \Exception($error["message"], $error["type"]);
      $reflector = new \ReflectionClass($exception);
      foreach ([ "file", "line", "trace" ] as $propertyName) {
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($exception, $error[$propertyName]);
      }

      // Use PHP's native logger to log this exception.
      error_log($exception);
      if (!headers_sent()) {
        header("Content-Type: text/plain");
      }

      // ASCII art source: http://www.chris.com/ASCII/index.php?art=animals/insects/other
      echo <<<EOT
# -------------------------------------------------------------------------------------------------------------------- #
#                                                                                                                      #
#                   UNRECOVERABLE FATAL ERROR! Please report @ https://github.com/MovLib/www/issues                    #
#                                                                                                                      #
# -------------------------------------------------------------------------------------------------------------------- #

{$exception}


                 \   /
                 .\-/.
             /\  () ()  /\
            /  \ /~-~\ /  \
                y  Y  V
          ,-^-./   |   \,-^-.  Don't Bug
         /    {    |    }    \   Me!!!
               \   |   /
               /\  A  /\
              /  \/ \/  \
             /           \ ∫VaMp FiNaL / crw
EOT;
      if (function_exists("fastcgi_finish_request")) {
        fastcgi_finish_request();
      }
    }
  }

}
