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

use \MovLib\Console\Application;
use \MovLib\Core\Config;
use \MovLib\Core\DIContainer;
use \MovLib\Core\FileSystem;
use \MovLib\Core\HTTP\DIContainerHTTP;
use \MovLib\Core\HTTP\Request;
use \MovLib\Core\HTTP\Response;
use \MovLib\Core\HTTP\Session;
use \MovLib\Core\Intl;
use \MovLib\Core\Log;
use \MovLib\Mail\Mailer;

/**
 * The MovLib kernel.
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
   * Dependency injection container.
   *
   * @var \MovLib\Core\DIContainer
   */
  protected $diContainer;

  // @devStart
  // @codeCoverageIgnoreStart
  /**
   * Whether the kernel has already booted or not.
   *
   * @var boolean
   */
  protected $booted = false;
  // @codeCoverageIgnoreEnd
  // @devEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Boot kernel.
   *
   * @param string $documentRoot
   *   The real document root path.
   * @return this
   */
  protected function boot($documentRoot) {
    // @devStart
    // @codeCoverageIgnoreStart
    if ($this->booted) {
      throw new \LogicException("Kernel already booted!");
    }
    $this->booted = true;
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->diContainer->kernel = $this;

    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($documentRoot) || !is_string($documentRoot) || is_link($documentRoot) || realpath($documentRoot) === false) {
      throw new \InvalidArgumentException("\$documentRoot cannot be empty, must be of type string and point to an existing directory.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Build absolute path to the serialized config file and use it if present, if not fall back to the default config.
    $serializedConfig = $documentRoot . Config::PATH;
    if (file_exists($serializedConfig)) {
      $this->diContainer->config = unserialize(file_get_contents($serializedConfig));
    }
    else {
      $this->diContainer->config = new Config();
    }
    // @devStart
    // @codeCoverageIgnoreStart
    // @todo REMOVE ME as soon as we have no coming soon page!
    $this->diContainer->config->hostname = "alpha.movlib.org";
    // @codeCoverageIgnoreEnd
    // @devEnd

    return $this;
  }

  /**
   * Boot kernel to CLI mode.
   *
   * @param string $documentRoot
   *   The real document root path.
   * @param string $basename
   *   The base name of the invoked symbolic link.
   * @return this
   */
  public function bootCLI($documentRoot, $basename) {
    $this->diContainer       = new DIContainer();
    $this->boot($documentRoot);
    $this->diContainer->log  = new Log("{$this->diContainer->config->siteName}CLI", $this->diContainer->config, false);
    $this->diContainer->fs   = new FileSystem($documentRoot, $this->diContainer->log);
    $this->diContainer->intl = new Intl($this->diContainer->config->defaultLocale, $this->diContainer->config->defaultLocale, $this->diContainer->config->locales);
    $this->diContainer->fs->setProcessOwner($this->diContainer->config->user, $this->diContainer->config->group);
    (new Application($this->diContainer, $basename))->run();
    $this->shutdown();
    return $this;
  }

  /**
   * Boot kernel to HTTP mode.
   *
   * <b>NOTE</b><br>
   * The HTTP kernel shuts down automatically!
   *
   * @param string $documentRoot
   *   The real document root path.
   * @return this
   */
  public function bootHTTP($documentRoot) {
    $this->diContainer = new DIContainerHTTP();
    $this->boot($documentRoot);
    set_error_handler([ $this, "errorHandler" ]);
    set_exception_handler([ $this, "exceptionHandler" ]);
    register_shutdown_function([ $this, "fatalErrorHandler" ]);
    ini_set("display_errors", false);

    $this->diContainer->log      = new Log($_SERVER["SERVER_NAME"], $this->diContainer->config, true);
    $this->diContainer->fs       = new FileSystem($documentRoot, $this->diContainer->log);
    $this->diContainer->intl     = new Intl($_SERVER["LANGUAGE_CODE"], $this->diContainer->config->defaultLocale, $this->diContainer->config->locales);
    $this->diContainer->request  = new Request($this->diContainer->intl);
    $this->diContainer->response = new Response($this->diContainer->config, $this->diContainer->request);
    $this->diContainer->session  = new Session($this->diContainer->log, $this->diContainer->response, $this->diContainer->request->remoteAddress, $this->diContainer->config->timeZone);

    // @todo NOT GOOD! We have to move this somewhere save and find a solution for the dynamic translation.
    $this->diContainer->config->siteSlogan            = $this->diContainer->intl->t($this->diContainer->config->siteSlogan);
    $args                                             = [ "sitename" => $this->diContainer->config->siteName, "slogan" => $this->diContainer->config->siteSlogan ];
    $this->diContainer->config->siteNameAndSlogan     = $this->diContainer->intl->t($this->diContainer->config->siteNameAndSlogan, $args);
    $this->diContainer->config->siteNameAndSloganHTML = $this->diContainer->intl->t($this->diContainer->config->siteNameAndSloganHTML, $args);

    $this->diContainer->session->resume();
    $presenterClass = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
    $this->diContainer->presenter = new $presenterClass($this->diContainer);
    $this->diContainer->presenter->init();
    echo $this->diContainer->presenter->getPresentation();

    // @devStart
    // @codeCoverageIgnoreStart
    $this->diContainer->log->debug("Response Time: " . (microtime(true) - $this->diContainer->request->timeFloat));
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (fastcgi_finish_request() === false) {
      $this->diContainer->log->error("FastCGI finish request failure");
    }
    $this->diContainer->session->shutdown();
    $this->bench("response", 0.75);
//    $this->diContainer->response->cache($presentation);
    $this->shutdown();
    $this->bench("shutdown", 0.5);

    return $this;
  }

  /**
   * Benchmark code execution against the request's start time.
   *
   * @param string $what
   *   Short description of what is being benchmarked.
   * @param float $target
   *   The target time to meet, if current microtime is above the target a log entry will be created.
   * @return this
   */
  protected function bench($what, $target) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($what) || !is_string($what)) {
      throw new \InvalidArgumentException("\$what cannot be empty and must be of type string.");
    }
    if (empty($target) || !is_numeric($target)) {
      throw new \InvalidArgumentException("\$target cannot be empty and must be of type number.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (($time = microtime(true) - $this->diContainer->request->timeFloat) > $target) {
      // We don't use the logger at this point, because we always want these events to be logged.
      error_log("Slow {$what} with {$time} for {$this->diContainer->request->uri}");
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
   * @param \Exception $exception
   *   The exception that wasn't caught.
   */
  public function exceptionHandler($exception) {
    // Make sure we don't send a header after some content was already sent to the client because that would just
    // trigger another error.
    if (!headers_sent()) {
      header("Content-Type: text/plain");
    }

    // Make sure we actually have a logger available.
    if (isset($this->diContainer->log) && $this->diContainer->log instanceof Log) {
      $this->diContainer->log->emergency($exception);
    }
    // If not things use PHP native functions, the root user of this server should have email forwarding set up.
    else {
      error_log($exception);
      mail("root", "EMERGENCY! MovLib is experiencing problems!", $exception);
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
    fastcgi_finish_request();
    exit();
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
        $this->error($e);
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

      $this->exceptionHandler($exception);
    }
  }

  /**
   * Shut the kernel down.
   *
   * @return this
   */
  protected function shutdown() {
    $this->executeDelayedMethods();
    $this->diContainer->fs->deleteRegisteredFiles();
    return $this;
  }

}
