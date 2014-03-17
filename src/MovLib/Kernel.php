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

use \MovLib\Data\Cache;
use \MovLib\Data\Database;
use \MovLib\Data\I18n;
use \MovLib\Data\Log;
use \MovLib\Data\Mailer;
use \MovLib\Data\User\Session;
use \MovLib\Exception\AbstractClientException;
use \MovLib\Presentation\Error\Forbidden;
use \MovLib\Presentation\Error\Unauthorized;
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
   * Alert messages that should be displayed to the user on the next pageview.
   *
   * @var string
   */
  public $alerts;

  /**
   * Associative array containing the cache buster strings for the various assets.
   *
   * @var array
   */
  public $cacheBusters = [
    "css" => [ /*####css-cache-buster####*/ ],
    "jpg" => [ /*####jpg-cache-buster####*/ ],
    "js"  => [ /*####js-cache-buster####*/ ],
    "png" => [ /*####png-cache-buster####*/ ],
    "svg" => [ /*####svg-cache-buster####*/ ],
  ];

  /**
   * Numeric array containing all delayed emails.
   *
   * @var null|array
   */
  protected $delayedEmails;

  /**
   * Numeric array containing all delayed methods.
   *
   * @var null|array
   */
  protected $delayedMethods;

  /**
   * The absolute path to the document root, e.g. <code>"/var/www"</code>.
   *
   * @var string
   */
  public $documentRoot;

  /**
   * The API domain, without scheme or trailing slash, e.g. <code>"api.movlib.org"</code>.
   *
   * @var string
   */
  public $domainAPI = "api";

  /**
   * The default domain, without scheme or trailing slash, e.g. <code>"movlib.org"</code>.
   *
   * @var string
   */
  public $domainDefault;

  /**
   * The localize domain, without scheme or trailing slash, e.g. <code>"localize.movlib.org"</code>.
   *
   * @var string
   */
  public $domainLocalize = "localize";

  /**
   * The secure tools domain, without scheme or trailing slash, e.g. <code>"secure.tools.movlib.org"</code>.
   *
   * @var string
   */
  public $domainSecureTools = "secure.tools";

  /**
   * The static domain, without scheme or trailing slash, e.g. <code>"static.movlib.org"</code>.
   *
   * @var string
   */
  public $domainStatic;

  /**
   * The tools domain, without scheme or trailing slash, e.g. <code>"tools.movlib.org"</code>.
   *
   * @var string
   */
  public $domainTools = "tools";

  /**
   * The developer mailinglist email address.
   *
   * @var string
   */
  public $emailDevelopers;

  /**
   * The default from address for emails.
   *
   * @var string
   */
  public $emailFrom;

  /**
   * The webmaster email address.
   *
   * @var string
   */
  public $emailWebmaster;

  /**
   * Whether this request is handled via FastCGI or not.
   *
   * @var boolean
   */
  public $fastCGI = true;

  /**
   * The host name of the current request.
   *
   * @var string
   */
  public $hostname;

  /**
   * Whether this request is secure or not.
   *
   * @var boolean
   */
  public $https = true;

  /**
   * Numeric array containing all JavaScript module names that should be loaded with this presentation.
   *
   * @var array
   */
  public $javascripts = [];

  /**
   * Associative array to collect JavaScript settings for this presentation.
   *
   * @var array
   */
  public $javascriptSettings = [];

  /**
   * The password options.
   *
   * The current default password is {@see PASSWORD_BCRYPT} which supports <i>salt</i> and <i>cost</i>. We don't use
   * <i>salt</i> because we want PHP to generate a random one. The cost should be set to something around half a second.
   *
   * @var array
   */
  public $passwordOptions = [ "cost" => 12 ];

  /**
   * Absolute path to the persistent disk cache for presentations.
   *
   * @var string
   */
  public $pathCache = "/cache";

  /**
   * Absolute path to the PHP translation files.
   *
   * @var string
   */
  public $pathTranslations = "/private/translation";

  /**
   * Flag indicating if the website is in production mode or not.
   *
   * @var boolean
   */
  public $production;

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
   * The requested URI path.
   *
   * This variable contains only the requested path without the query part.
   *
   * @see Kernel::$requestURI
   * @var string
   */
  public $requestPath = "/";

  /**
   * The requested URI path and query.
   *
   * The name is misleading and this has historical reasons. This string actually contains the path and query parts of
   * the requested URI.
   *
   * @see Kernel::$requestPath
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
   * The site name including the slogan and punctuation, e.g. <code>"MovLib, the free movie library."</code>.
   *
   * @var string
   */
  public $siteNameAndSlogan = "MovLib, the free movie library.";

  /**
   * The site name including the slogan, punctuation and HTML, e.g. <code>"MovLib <small>the <em>free</em> movie
   * library.</small>"</code>.
   *
   * @var string
   */
  public $siteNameAndSloganHTML = "MovLib <small>the <em>free</em> movie library.";

  /**
   * The site slogan, e.g. <code>"the free movie library"</code>.
   *
   * @var string
   */
  public $siteSlogan = "the free movie library";

  /**
   * Numeric array containing all CSS module names that should be loaded with this presentation.
   *
   * @var array
   */
  public $stylesheets = [];

  /**
   * Numeric array containing the system locales.
   *
   * @var array
   */
  public $systemLanguages = [ "de" => "de_AT", "en" => "en_US" ];

  /**
   * PHP's POSIX group name.
   *
   * @var string
   */
  public $systemGroup;

  /**
   * PHP's POSIX user name.
   *
   * @var string
   */
  public $systemUser;

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
   * @global \MovLib\Data\Cache $cache
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @param string $documentRoot
   *   The absolute path to the current working directory (not the symbolic link).
   */
  public function __construct($documentRoot) {
    global $cache, $db, $i18n, $kernel, $session;

    // Export ourself to global scope and allow any layer to access the kernel's public properties.
    $kernel = $this;

    // Transform ALL PHP errors to exceptions.
    set_error_handler([ $this, "errorHandler" ], -1);

    // Catch fatal errors and ensure that something is displayed to the client.
    $this->fastCGI = isset($_SERVER["FCGI_ROLE"]);
    if ($this->fastCGI === true) {
      register_shutdown_function([ $this, "fatalErrorHandler" ]);
    }

    try {
      // Initialize environment properties based on variables passed in by nginx.
      $this->documentRoot      = $documentRoot;
      $this->domainDefault     = $_ENV["DOMAIN_DEFAULT"];
      $this->domainAPI         = "{$this->domainAPI}.{$this->domainDefault}";
      $this->domainLocalize    = "{$this->domainLocalize}.{$this->domainDefault}";
      $this->domainSecureTools = "{$this->domainSecureTools}.{$this->domainDefault}";
      $this->domainStatic      = $_ENV["DOMAIN_STATIC"];
      $this->domainTools       = "{$this->domainTools}.{$this->domainDefault}";
      $this->emailDevelopers   = $_ENV["EMAIL_DEVELOPERS"];
      $this->emailFrom         = $_ENV["EMAIL_FROM"];
      $this->emailWebmaster    = $_ENV["EMAIL_WEBMASTER"];
      $this->hostname          = $_SERVER["SERVER_NAME"];
      $this->https             = isset($_SERVER["HTTPS"]);
      $this->pathCache         = "{$this->documentRoot}{$this->pathCache}/{$_SERVER["LANGUAGE_CODE"]}";
      $this->pathTranslations  = "{$this->documentRoot}{$this->pathTranslations}";
      $this->production        = (boolean) $_ENV["PRODUCTION"];
      $this->protocol          = $_SERVER["SERVER_PROTOCOL"];
      // @todo If we're ever going to use proxy servers this code has to be changed!
      //       https://github.com/komola/ZendFramework/blob/master/Controller/Request/Http.php#L1054
      $this->remoteAddress     = filter_var($_SERVER["REMOTE_ADDR"], FILTER_VALIDATE_IP, FILTER_REQUIRE_SCALAR);
      $this->requestMethod     = $_SERVER["REQUEST_METHOD"];
      $this->requestPath       = $_SERVER["REQUEST_PATH"];
      $this->requestURI        = $_SERVER["REQUEST_URI"];
      $this->scheme            = $_SERVER["SCHEME"];
      $this->systemGroup       = $_ENV["SYSTEM_GROUP"];
      $this->systemUser        = $_ENV["SYSTEM_USER"];
      $this->userAgent         = filter_var($_SERVER["HTTP_USER_AGENT"], FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_STRIP_LOW);

      // @devStart
      // @codeCoverageIgnoreStart
      $this->domainDefault = "alpha.{$this->domainDefault}";
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Configure fast autoloader.
      //spl_autoload_register([ $this, "autoload" ], true);

      // Configure Composer autoloader.
      try {
        require "{$this->documentRoot}/vendor/autoload.php";
      }
      catch (\ErrorException $e) {
        // Only react on real problems, the vendor supplied stuff often raises DEPRECATED or STRICT errors we don't
        // care about (because we can't fix them).
        switch ($e->getSeverity()) {
          case E_ERROR:
          case E_WARNING:
            throw $e;
        }
      }

      // Prepare global database connection.
      $db = new Database();

      // Always create an I18n instance for translating any kind of presentation.
      $i18n = new I18n();

      // Translate the slogan variations but not the site name itself.
      $this->siteNameAndSlogan     = $i18n->t("{0}, the free movie library.", [ $this->siteName ]);
      $this->siteNameAndSloganHTML = $i18n->t("{0} {1}the {2}free{3} movie library.{4}", [ $this->siteName, "<small>", "<em>", "</em>", "</small>" ]);
      $this->siteSlogan            = $i18n->t("the free movie library");

      // If either the client's IP address or user agent string are invalid or empty abort execution.
      if ($this->remoteAddress === false || $this->userAgent === false) {
        $e = new Forbidden(
          "<p>{$i18n->t("IP address or user agent string is invalid or empty.")}</p>" .
          "<p>{$i18n->t(
            "Please note that you have to submit your IP address and user agent string to identify yourself as being " .
            "human; should you have privacy concerns read our {privacy_policy}.",
            [ "privacy_policy" => "<a href='{$i18n->r("/privacy-policy")}'>{$i18n->t("Privacy Policy")}</a>" ]
          )}</p>"
        );
        Log::warning($e);
        throw $e;
      }

      // If we have a valid IP address and user agent string initialize a session for the client.
      $session = new Session();

      // Only check user authorization, see nginx configuration for details on this.
      if ($_SERVER["PRESENTER"] == "CheckAuthorization") {
        if ($session->isAuthenticated === true) {
          $path = str_replace("upload/private", "private/upload", $this->requestPath);
          switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case "jpg":
              $type = "image/jpeg";
              break;

            case "png":
              $type = "image/png";
              break;
          }
          header("Content-Type: image/jpeg");
          header("X-Accel-Redirect: {$path}");
          exit();
        }
        throw new Unauthorized;
      }

      // Instantiate the cache if we are serving a presentation.
      $cache            = new Cache();
      $cache->cacheable = $_SERVER[ "REQUEST_METHOD" ] == "GET";

      // Try to get the presentation.
      $presentation = "\\MovLib\\Presentation\\{$_SERVER["PRESENTER"]}";
      $presentation = (new $presentation())->getPresentation();
    }
    catch (AbstractClientException $clientException) {
      Log::notice($clientException);
      $presentation = $clientException->getPresentation();
    }
    catch (\Exception $e) {
      try {
        $presentation = (new Stacktrace($e))->getPresentation();
        // Log after trying to fetch the exception, if above code results in an exception things are worse than we
        // thought.
        Log::alert($e);
      }
      catch (\Exception $e) {
        Log::emergency($e);
        header("Content-Type: text/plain; charset=utf-8");
        exit("==== FATAL ERROR ====\n\n{$e}");
      }
    }
    finally {
      // Set alert messages for next page view. Instead of storing alert messages on our server we send them to the
      // client, this will only increase network traffic by a few bytes. Plus the alert message is stored until the user
      // closes the agent, it's very unlikely that such an alert is still from interest on the next user agent session.
      if ($this->alerts) {
        $this->cookieCreate("alerts", "{$this->alerts}");
      }

      // This allows us to lazy start anonymous sessions and send cookies right before sending the response.
      if ($session) {
        $session->shutdown();
      }

      // @devStart
      // @codeCoverageIgnoreStart
      Log::debug("Response Time: " . (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]));
      // @codeCoverageIgnoreEnd
      // @devEnd

      // @devStart
      // @codeCoverageIgnoreStart
      if (empty($presentation)) {
        header("Content-Type: test/plain; charset=utf-8");
        echo "==== PRESENTATION IS EMPTY ====";
      }
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Render the presentation.
      echo $presentation;

      // Special function that is only available with php-fpm, this sends the previously rendered presentation to the
      // client but execution of this script will continue below this function call.
      if ($this->fastCGI === true) {
        fastcgi_finish_request();
      }

      // Calculate execution time for response generation and log if it took too long.
      if (($responseEnd = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) > 0.75) {
        Log::notice("Slow Response", [
          "time" => $responseEnd,
          "uri"  => "{$this->scheme}://{$this->hostname}{$this->requestURI}",
        ]);
      }

      // Can we cache this presentation?
      $cache->save($presentation);

      // Execute each delayed method.
      if ($this->delayedMethods) {
        foreach ($this->delayedMethods as list($callable, $params)) {
          try {
            call_user_func_array($callable, (array) $params);
          }
          catch (\Exception $e) {
            Log::error($e);
          }
        }
      }

      // Send all delayed emails.
      if ($this->delayedEmails) {
        try {
          new Mailer($this->delayedEmails);
        }
        catch (\Exception $e) {
          Log::critical($e);
        }
      }

      // Calculate time for response and delayed generation and log if it took too long.
      if (($delayedEnd = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) > 5.0) {
        Log::info("Slow Delayed", [
          "time" => $delayedEnd,
          "uri"  => "{$this->scheme}://{$this->hostname}{$this->requestURI}",
        ]);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get absolute URL for an asset file.
   *
   * @staticvar array $cache
   *   Used to cache the URLs that are built during a single request.
   * @param string $name
   *   The filename (or path) of the asset file for which the URL should be built. What you have to pass with this
   *   parameter depends on the asset type you need. CSS and JS files are <b>always</b> only referred by their name.
   *   This is because all CSS and JS files that are dynamically included reside in the module sub-directory of their
   *   asset directory. If you need an image on the other side the name must include the absolute path within the img
   *   directory in the asset directory (without leading slash). Don't include the trailing dot nor the asset's
   *   extension here!
   * @param string $extension
   *   The asset's file extension (e.g. <code>"css"</code>).
   * @return string
   *   The absolute URL (including scheme and hostname) of the asset.
   */
  public function getAssetURL($name, $extension) {
    static $cache = [];

    // If we have no cached URL for this asset build the URL.
    if (!isset($cache[$extension][$name])) {
      // CSS and JS assets are always in the same directory as their extension plus the module sub-directory (other
      // assets of this type aren't includable during normal execution, with the exception of the files that are named
      // MovLib), images have many different extensions and their directory doesn't match up with that.
      $dir = "img";
      if ($extension == "css" || $extension == "js") {
        $dir = $extension;
        if ($name != "MovLib") {
          $dir .= "/module";
        }
      }

      // @devStart
      // @codeCoverageIgnoreStart
      if (!isset($this->cacheBusters[$extension][$name])) {
        $this->cacheBusters[$extension][$name] = md5_file("{$this->documentRoot}/public/asset/{$dir}/{$name}.{$extension}");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Add the absolute URL to our URL cache and we're done.
      $cache[$extension][$name] = "//{$this->domainStatic}/asset/{$dir}/{$name}.{$extension}?{$this->cacheBusters[$extension][$name]}";
    }

    return $cache[$extension][$name];
  }

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
//  public function autoload($class) {
//    $class = strtr($class, "\\", "/");
//    require "{$this->documentRoot}/src/{$class}.php";
//  }

  /**
   * Create cookie.
   *
   * @param string $id
   *   The cookie's unique identifier.
   * @param mixed $value
   *   The cookie's value.
   * @param integer $expire [optional]
   *   The cookie's time to life.
   * @param boolean $httpOnly [optional]
   *   Whether this cookie should be http only (not accessible for JavaScript) or not.
   * @return this
   * @throws \LogicException
   */
  public function cookieCreate($id, $value, $expire = 0, $httpOnly = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    Log::debug("Creating Cookie", [ "id" => $id, "value" => $value ]);
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      if (setcookie($id, $value, $expire, "/", $this->domainDefault, $this->https, $httpOnly) === false) {
        // @codeCoverageIgnoreStart
        throw new \Exception;
        // @codeCoverageIgnoreEnd
      }
    }
    catch (\Exception $e) {
      $e = new \LogicException("Couldn't create cookie", null, $e);
      Log::error($e);
      throw $e;
    }
    return $this;
  }

  /**
   * Delete cookie(s).
   *
   * @param string|array $ids
   *   The cookie's unique identifier(s).
   * @return this
   * @throws \LogicException
   */
  public function cookieDelete($ids) {
    foreach ((array) $ids as $id) {
      // @devStart
      // @codeCoverageIgnoreStart
      Log::debug("Deleting Cookie", [ "id" => $id ]);
      // @codeCoverageIgnoreEnd
      // @devEnd
      $this->cookieCreate($id, "", 1);
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
  public function delayMethodCall($callable, array $params = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (is_callable($callable) === false) {
      throw new \InvalidArgumentException("\$callable cannot be empty and must be of type callable");
    }
    if (isset($params) && !is_array($params)) {
      throw new \InvalidArgumentException("\$params cannot be empty and must be of type array");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
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
   * Transform fatal errors to exceptions.
   *
   * This isn't meant to recover after a fatal error occurred. The purpose of this is to ensure that a nice presentation
   * is displayed to the client, including any information that might be helpful in resolving this problem.
   *
   * @link http://stackoverflow.com/a/2146171/1251219 How do I catch a PHP Fatal Error
   */
  public function fatalErrorHandler() {
    if (($error = error_get_last())) {
      try {
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
        $properties      = [ "file", "line", "trace" ];
        $i               = 3;
        while ($i--) {
          $reflectionProperty = $reflectionClass->getProperty($properties[$i]);
          $reflectionProperty->setAccessible(true);
          $reflectionProperty->setValue($exception, $error[$properties[$i]]);
        }

        // Display internal server error page to client.
        $presentation = (new Stacktrace($exception, true))->getPresentation();
      }
      catch (\Exception $e) {
        header("Content-Type: text/plain; charset=utf-8");
        $presentation = (string) $e;
      }

      // Log this error, send an email to all developers, and display the error to the user.
      Log::emergency($exception);
      exit($presentation);
    }
  }

  /**
   * Send email after response was sent to the client.
   *
   * @param \MovLib\Presentation\Email\AbstractEmail $email
   *   The email to send.
   * @return this
   */
  public function sendEmail($email) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($email) || !($email instanceof \MovLib\Presentation\Email\AbstractEmail)) {
      throw new \InvalidArgumentException("\$email cannot be empty and must be of type \\MovLib\\Presentation\\Email\\AbstractEmail");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->delayedEmails[] = $email;
    return $this;
  }

}
