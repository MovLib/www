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
namespace MovLib;

use \MovLib\Data\I18n;
use \MovLib\Data\Mailer;
use \MovLib\Data\User\Session;
use \MovLib\Exception\Client\ErrorForbiddenException;
use \MovLib\Presentation\Email\FatalErrorEmail;
use \MovLib\Presentation\Stacktrace;

/**
 * The kernel provides the most basic properties and methods that are needed by any part of the system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Kernel {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The name of the default database.
   *
   * @var string
   */
  public $databaseDefault = "movlib";

  /**
   * The name of the localize database.
   *
   * @var string
   */
  public $databaseLocalize = "movlib_localize";

  /**
   * Numeric array containing all delayed emails.
   *
   * @var array
   */
  protected $delayedEmails;

  /**
   * Numeric array containing all delayed methods.
   *
   * @var array
   */
  protected $delayedMethods;

  /**
   * The absolute path to the document root, e.g. <code>"/var/www"</code>.
   *
   * @var string
   */
  public $documentRoot = "/var/www";

  /**
   * The API domain, without scheme or trailing slash, e.g. <code>"api.movlib.org"</code>.
   *
   * @var string
   */
  public $domainAPI = "api.movlib.org";

  /**
   * The default domain, without scheme or trailing slash, e.g. <code>"movlib.org"</code>.
   *
   * @var string
   */
  public $domainDefault = "alpha.movlib.org";

  /**
   * The localize domain, without scheme or trailing slash, e.g. <code>"localize.movlib.org"</code>.
   *
   * @var string
   */
  public $domainLocalize = "localize.movlib.org";

  /**
   * The static domain, without scheme or trailing slash, e.g. <code>"static.movlib.org"</code>.
   *
   * @var string
   */
  public $domainStatic = "alpha.movlib.org";

  /**
   * The developer mailinglist email address.
   *
   * @var string
   */
  public $emailDevelopers = "developers@movlib.org";

  /**
   * The default from address for emails.
   *
   * @var string
   */
  public $emailFrom = "noreply@movlib.org";

  /**
   * The webmaster email address.
   *
   * @var string
   */
  public $emailWebmaster = "webmaster@movlib.org";

  /**
   * The host name of the current request.
   *
   * @var string
   */
  public $hostname = "movlib.org";

  /**
   * The password cost for hashing the user passwords.
   *
   * @var string
   */
  public $passwordCost = 13;

  /**
   * The user name (for file permissions etc.).
   *
   * @var string
   */
  public $phpUser = "movdev";

  /**
   * The group name (for file permissions etc.).
   *
   * @var string
   */
  public $phpGroup = "www-data";

  /**
   * Flag indicating if the website is in production mode or not.
   *
   * @var boolean
   */
  public $production = false;

  /**
   * The current request's protocol (either <code>"HTTP/1.0"</code> or <code>"HTTP/1.1"</code>).
   *
   * @var string
   */
  public $protocol = "HTTP/1.1";

  /**
   * The client's remote address.
   *
   * @var string
   */
  public $remoteAddress = "127.0.0.1";

  /**
   * The current request method.
   *
   * @var string
   */
  public $requestMethod = "GET";

  /**
   * The currently request URI.
   *
   * @var string
   */
  public $requestURI = "/";

  /**
   * The server scheme (<code>"http"</code> / <code>"https"</code>).
   *
   * @var string
   */
  public $scheme = "https";

  /**
   * The site name, e.g. <code>"MovLib"</code>.
   *
   * @var string
   */
  public $siteName = "MovLib";

  /**
   * The site slogan, e.g. <code>"the free movie library"</code>.
   *
   * @var string
   */
  public $siteSlogan = "the free movie library";

  /**
   * Numeric array containing the system locales.
   *
   * @see \MovLib\Data\SystemLanguage
   * @see \MovLib\Data\SystemLanguages
   * @var array
   */
  public $systemLanguages = [ "de" => "de_AT", "en" => "en_US" ];

  /**
   * The HTTP user agent string.
   *
   * @var string
   */
  public $userAgent = false;

  /**
   * The version string.
   *
   * @var string
   */
  public $version = "0.0.1-dev";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Bootstrap process.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $kernel, $i18n, $session;

    // Export ourself to global scope and allow any layer to access the kernel's public properties.
    $kernel = $this;

    // Transform all PHP errors to exceptions.
    set_error_handler([ $this, "errorHandler" ]);

    // Catch fatal errors and ensure that something is displayed to the client.
    register_shutdown_function([ $this, "fatalErrorHandler" ]);

    try {
      // Initialize environment properties based on variables passed in by nginx.
      $this->documentRoot  = $_SERVER["DOCUMENT_ROOT"];
      $this->hostname      = $_SERVER["SERVER_NAME"];
      $this->protocol      = $_SERVER["SERVER_PROTOCOL"];
      $this->remoteAddress = filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_REQUIRE_SCALAR);
      $this->requestMethod = $_SERVER["REQUEST_METHOD"];
      $this->requestURI    = $_SERVER["REQUEST_URI"];
      $this->scheme        = $_SERVER["SCHEME"];
      $this->userAgent     = filter_var($_SERVER["HTTP_USER_AGENT"], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

      // Configure the autoloader.
      spl_autoload_register([ $this, "autoload" ], true);

      // Always create an I18n instance for translating any kind of presentation.
      $i18n = new I18n();

      // If either the client's IP address or user agent string are invalid or empty abort execution.
      if ($this->remoteAddress === false || $this->userAgent === false) {
        $e403 = new ErrorForbiddenException();
        $e403->alert->message =
          "<p>{$i18n->t("IP address or user agent string is invalid or empty.")}</p>" .
          "<p>{$i18n->t(
            "Please note that you have to submit your IP address and user agent string to identify yourself as being " .
            "human; should you have privacy concerns read our {0}Privacy Policy{1}.",
            [ "<a href='{$i18n->r("/privacy-policy")}'>" , "</a>" ]
          )}</p>"
        ;
        throw $e403;
      }

      // If we have a valid IP address and user agent string initialize a session for the client.
      $session = new Session();

      // Try to create presentation based on the presenter set by nginx.
      $presentationClass = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
      $presentation      = (new $presentationClass())->getPresentation();
    }
    catch (ClientException $e) {
      foreach ($e->headers as $header) {
        header($header);
      }
      $presentation = $e->presentation;
    }
    catch (\Exception $e) {
      $presentation = (new Stacktrace($e))->getPresentation();
    }
    finally {
      // This allows us to lazy start anonymous sessions and send cookies right before sending the response.
      $session->shutdown();

      // Render the presentation.
      echo $presentation;

      // Special function that is only available with php-fpm, this sends the previously rendered presentation to the
      // client but execution of this script will continue below this function call.
      fastcgi_finish_request();

      // Calculate execution time for response generation and log if it took too long.
      $responseEnd = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
      if ($responseEnd > 0.1) {
        error_log("SLOW: Response took too long to generate with {$responseEnd} seconds for URI {$this->scheme}://{$this->hostname}{$this->requestURI}");
      }

      // Execute each delayed method.
      if ($this->delayedMethods) {
        foreach ($this->delayedMethods as list($callable, $params)) {
          call_user_func_array($callable, $params);
        }
      }

      // Send all delayed emails.
      if ($this->delayedEmails) {
        new Mailer($this->delayedEmails);
      }

      // Calculate time for response and delayed generation and log if it took too long.
      $delayedEnd = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
      if ($delayedEnd > 5.0) {
        error_log("SLOW: Delayed took too long to execute with {$delayedEnd} seconds for URI {$this->scheme}://{$this->hostname}{$this->requestURI}");
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Class autoloader.
   *
   * The default autoloader doesn't honor any external libraries and only loads MovLib classes. This is intentional
   * because there should be no dependencies. Other parts of the application should use the composer provided autoloader.
   *
   * @param string $class
   *   Fully qualified class name (automatically passed to this magic method by PHP).
   * @throws \ErrorException
   */
  public function autoload($class) {
    $class = strtr($class, "\\", "/");
    require "{$this->documentRoot}/src/{$class}.php";
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
  public function delayMethodCall($callable, array $params = null) {
    $this->delayedMethods[] = [ $callable, $params ];
    return $this;
  }

  /**
   * Transforms PHP errors to PHP's ErrorException.
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
   * @throws \ErrorException
   */
  public function errorHandler($severity, $message, $file, $line) {
    throw new \ErrorException($message, $severity, 0, $file, $line);
  }

  /**
   * Transform fatal errors to exceptions.
   *
   * This isn't meant to recover after a fatal error occurred. The purpose of this is to ensure that a nice presentation
   * is displayed to the client, including any information that might be helpful in resolving this problem.
   *
   * @link http://stackoverflow.com/a/2146171/1251219 How do I catch a PHP Fatal Error
   */
  public function fatalErrorHandler() {
    if (($error = error_get_last())) {
      $line = __LINE__ - 2;

      // Let xdebug provide the stack if available (not the best, but better than none).
      if (function_exists("xdebug_get_function_stack")) {
        $error["trace"]            = array_reverse(xdebug_get_function_stack());
        $error["trace"][0]["line"] = $line;
      }
      // We have to build our own trace, well, at least we can try with the available information.
      else {
        $error["trace"] = [
          [ "function" => __FUNCTION__, "line" => $line, "file" => __FILE__ ],
          [ "function" => "<em>unknown</em>", "line" => $error["line"], "file" => $error["file"] ],
        ];
      }

      // Please note that we HAVE TO use PHP's base exception class at this point, otherwise we can't set our own trace!
      $exception       = new \Exception($error["message"], $error["type"]);
      $reflectionClass = new \ReflectionClass($exception);
      foreach ([ "file", "line", "trace" ] as $propertyName) {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($exception, $error[$propertyName]);
      }

      // Send an email to all developers.
      (new Mailer())->send(new FatalErrorEmail($exception));

      // Display internal server error page to client.
      exit((new Stacktrace($exception, true))->getPresentation());
    }
  }

  /**
   * Send email after response was sent to the client.
   *
   * @param string $email
   *   The full class name of the email that should be sent.
   * @param array $args [optional]
   *   The arguments that should be passed to the constructor of the email.
   * @return this
   */
  public function sendEmail($email, array $args = null) {
    $this->delayedEmails[] = [ $email, $args ];
    return $this;
  }

}
