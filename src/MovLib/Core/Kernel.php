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
use \MovLib\Core\Container;
use \MovLib\Core\FileSystem;
use \MovLib\Core\HTTP\Container as HTTPContainer;
use \MovLib\Core\HTTP\Request;
use \MovLib\Core\HTTP\Response;
use \MovLib\Core\HTTP\Session;
use \MovLib\Core\Intl;
use \MovLib\Core\Log;
use \MovLib\Exception\ClientException\ClientExceptionInterface;
use \MovLib\Mail\Mailer;
use \MovLib\Presentation\Error\InternalServerError;

/**
 * The MovLib kernel.
 *
 * The kernel takes care of bootstraping and instantiating the correct objects to handle a CLI or HTTP request.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Kernel {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Kernel";
  // @codingStandardsIgnoreEnd

  /**
   * The canonical absolute version string.
   *
   * @var string
   */
  const VERSION = "0.0.1-dev";

  /**
   * The version as zero-padded integer string.
   *
   * @var string
   */
  const VERSION_ID = "000010";

  /**
   * The major version part.
   *
   * @var string
   */
  const VERSION_MAJOR = "0";

  /**
   * The minor version part.
   *
   * @var string
   */
  const VERSION_MINOR = "0";

  /**
   * The release version part.
   *
   * @var string
   */
  const VERSION_RELEASE = "1";

  /**
   * The extra version part.
   *
   * @var string
   */
  const VERSION_EXTRA = "dev";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this kernel is in CLI context or not.
   *
   * @var boolean
   */
  public $cli = false;

  /**
   * Used to collect methods that should be executed after the response was sent to the client.
   *
   * @see ::delayMethodCall
   * @see ::executeDelayedMethods
   * @var array
   */
  protected $delayedMethods = [];

  /**
   * Dependency injection container.
   *
   * @var \MovLib\Core\Container|\MovLib\Core\HTTP\Container
   */
  protected $container;

  /**
   * Whether this kernel is in HTTP context or not.
   *
   * @var boolean
   */
  public $http = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Boot kernel.
   *
   * @param string $documentRoot
   *   The real document root path.
   * @param string $logName
   *   The name for the log entries.
   * @param string $language
   *   The system language's ISO 639-1 alpha-2 code.
   * @return this
   */
  protected function boot($documentRoot, $logName, $language) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(
      !empty($documentRoot) && is_string($documentRoot) && !is_link($documentRoot) && is_dir($documentRoot) && realpath($documentRoot) !== false,
      "\$documentRoot cannot be empty, must be of type string and point to an existing directory."
    );
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Include PHP core extending procedural functions.
    require __DIR__ . "/functions.php";

    // Build absolute path to the serialized config file and use it if present, if not fall back to the default config.
    $serializedConfig = $documentRoot . Config::PATH;
    if (file_exists($serializedConfig)) {
      $this->container->config = unserialize(file_get_contents($serializedConfig));
    }
    else {
      $this->container->config = new Config();
    }

    // @devStart
    // @codeCoverageIgnoreStart
    // @todo REMOVE ME as soon as we have no coming soon page!
    $this->container->config->hostname = "alpha.movlib.org";
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->container->kernel = $this;
    $this->container->log    = new Log($this->container->config, $logName, $this->http);
    $this->container->fs     = new FileSystem($documentRoot, $this->container->config->hostnameStatic);
    $this->container->intl   = new Intl($language);

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
    // @devStart
    // @codeCoverageIgnoreStart
    assert_options(ASSERT_BAIL, true);
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->cli       = true;
    $this->container = new Container();
    $this->boot($documentRoot, "{$basename}-cli", Intl::DEFAULT_CODE);
    $this->container->fs->setProcessOwner($this->container->config->user, $this->container->config->group);
    $application = new Application($this->container, $basename);
    $application->setAutoExit(false);
    $application->run();
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
    set_error_handler([ $this, "errorHandler" ]);
    set_exception_handler([ $this, "exceptionHandler" ]);
    register_shutdown_function([ $this, "fatalErrorHandler" ]);
    ini_set("display_errors", false);

    $this->http                = true;
    $this->container           = new HTTPContainer();
    $this->boot($documentRoot, $_SERVER["SERVER_NAME"], $_SERVER["LANGUAGE_CODE"]);
    $this->container->request  = new Request($this->container->intl);
    $this->container->response = new Response($this->container->request, $this->container->config->hostname);
    $this->container->session  = new Session($this->container);

    // Try to initialize the session and presentation and send it to the client.
    try {
      $this->container->session->resume();
      $presenterClass = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
      $this->container->presenter = new $presenterClass($this->container);
      $this->container->presenter->init();
      $response = $this->container->presenter->getPresentation($this->container->presenter->getContent());
    }
    // Client exception's are exception's that display a fully rendered page in HTTP context, catch them separately.
    catch (ClientExceptionInterface $clientException) {
      // A client exception might throw another client exception (redirects) that we have to catch.
      try {
        $response = $clientException->getPresentation($this->container);
      }
      catch (ClientExceptionInterface $clientException) {
        $response = $clientException->getPresentation($this->container);
      }
    }
    // Any other exception is an error, but the base system booted nicely therefore we try to display a nice looking
    // error page including a stack trace. MovLib is open source and we don't use any passwords anywhere, therefore we
    // don't have to keep the stacktrace a secret. Who knows, maybe someone can will directly create a pull request at
    // GitHub that fixes the issue (*dreaming*).
    catch (\Exception $exception) {
      $this->container->presenter = new InternalServerError($this->container);
      $this->container->presenter->init()->setException($exception);
      $response = $this->container->presenter->getPresentation($this->container->presenter->getContent());
    }

    if ($this->container->config->production === true) {
      echo $response;
      if (fastcgi_finish_request() === false) {
        $this->container->log->error("FastCGI finish request failed.");
      }
    }

    $this->container->session->shutdown();
    $this->bench("response", 0.75);
//    $this->container->response->cache($presentation);
    $this->shutdown();
    $this->bench("shutdown", 0.5);

    if ($this->container->config->production === false) {
      echo $response;
    }

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
    assert(is_string($what), "Short description of bench must be of type string.");
    assert(!empty($what), "Short description of bench cannot be empty.");
    assert(is_numeric($target), "Target time of bench must be of type integer or float.");
    assert($target > 0, "Target time has to be greater than zero.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (($time = microtime(true) - $this->container->request->timeFloat) > $target) {
      $this->container->log->info("SLOW {$what}", [
        "time" => $time,
        "uri"  => $this->container->request->uri,
      ]);
    }
    return $this;
  }

  /**
   * Execute given method on shutdown of kernel.
   *
   * Note that you cannot control at which point your method will be executed, as they are all simply executed in the
   * order they were stacked.
   *
   * @todo Should we add the possibility to add a weight to a delayed method?
   * @param string $name
   *   A globally unique name for the delayed method. This name allows you and others to identify the method within the
   *   stack and remove them if necessary.
   * @param mixed $object
   *   The object implementing the method.
   * @param string $method
   *   The name of the method that should be called, visibility doesn't matter. The kernel acts as simple mediator and
   *   doesn't change the state of anything. We use reflection to make the methods accessible, performance is not an
   *   issue at that point because the response has already been sent to the client.
   * @param array $params [optional]
   *   Additional parameters that should be passed to the delayed method. Parameters won't be changed by the kernel and
   *   are passed as is.
   * @return this
   */
  public function delayMethodCall($name, $object, $method, array $params = []) {
    $this->delayedMethods[$name] = [ $object, $method, $params ];
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
   * @param boolean $title [optional]
   *   The title that should be displayed.
   */
  public function exceptionHandler($exception, $title = "UNCAUGHT EXCEPTION!") {
    // Make sure we don't send a header after some content was already sent to the client because that would just
    // trigger another error.
    if (!headers_sent()) {
      header("Content-Type: text/plain");
    }

    // Make sure we actually have a logger available.
    if (isset($this->container->log) && $this->container->log instanceof Log) {
      $this->container->log->emergency($exception);
    }
    // If not things use PHP native functions, the root user of this server should have email forwarding set up.
    else {
      error_log($exception);
      mail("root", "EMERGENCY! MovLib is experiencing problems!", $exception);
    }

    $pad = str_repeat(" ", (118 - strlen($title)) / 2);
    $title = "{$pad}{$title}{$pad}";
    $pad = strlen($title) - 118;
    if ($pad < 0) {
      $title .= str_repeat(" ", -$pad);
    }

    // ASCII art source: http://www.chris.com/ASCII/index.php?art=animals/insects/other
    echo <<<EOT
# -------------------------------------------------------------------------------------------------------------------- #
#                                                                                                                      #
#{$title}#
#                                                                                                                      #
#                                 Please report @ https://github.com/MovLib/www/issues                                 #
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

      $this->exceptionHandler($exception, "UNRECOVERABLE FATAL ERROR!");
    }
  }

  /**
   * Remove a previously delayed method from the stack.
   *
   * @param string $name
   *   The globally unique name of the delayed method that should be removed.
   * @return this
   */
  public function removeDelayedMethod($name) {
    if (isset($this->delayedMethods[$name])) {
      unset($this->delayedMethods[$name]);
    }
    return $this;
  }

  /**
   * Shut the kernel down.
   *
   * @return this
   */
  protected function shutdown() {
    // You'll notice that we're setting all methods to be accessible. Although this might seem like it's breaking the
    // encapsulation of the objects. But the object is actually calling itself, the kernel is only used as a collector
    // for the delayed methods. I also thought about using the observer pattern, but re-implementing the management of
    // the various methods seemed to be more work than this little hack. I might revisit this at a later stage and
    // change it.
    foreach ($this->delayedMethods as list($object, $method, $params)) {
      $method = new \ReflectionMethod($object, $method);
      $method->setAccessible(true);
      $method->invokeArgs($object, $params);
    }

    if ($this->http === true) {
      (new Mailer())->sendEmailStack($this->container);
    }

    $this->container->fs->deleteRegisteredFiles($this->container->log);

    return $this;
  }

}
