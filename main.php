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
 * @copyright (c) 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 * @since 0.0.1-dev
 */

/**
 * ASCII end of transmission
 *
 * @var string
 */
define("PHP_EOT", chr(4));

/**
 * The name of the website.
 *
 * @var string
 */
define("SITENAME", "MovLib");

/**
 * Ultra fast class autoloader.
 *
 * @param string $class
 *   Fully qualified class name (automatically passed to this magic function by PHP).
 * @return void
 */
function __autoload($class) {
  require $_SERVER["DOCUMENT_ROOT"] . "/src/" . strtr($class, "\\", DIRECTORY_SEPARATOR) . ".php";
}

/**
 * Particular gettext function for a message within a specific context.
 *
 * @link http://www.gnu.org/software/gettext/manual/html_node/Contexts.html
 * @see gettext
 * @param string $msgctxt
 *   The message's context identifier. Do not use the class name or full sentences as context. Try to use jQuery like
 *   selectors like <code>html head title</code> or <code>input[type="search"]</code> as they are very unlikely to
 *   change. Another good example of a context which is used very often is <code>route</code> for URLs.
 * @param string $msgid
 *   The message that should be translated.
 * @return string
 *   The translated message.
 */
function pgettext($msgctxt, $msgid) {
  /* @var $msgctxtid string */
  $msgctxtid = $msgctxt . PHP_EOT . $msgid;
  /* @var $translation string */
  $translation = _($msgctxtid);
  if (strcmp($translation, $msgctxtid) === 0) {
    return $msgid;
  }
  return $translation;
}

/**
 * Plural particular gettext function for a message within a specific context.
 *
 * @link http://www.gnu.org/software/gettext/manual/html_node/Contexts.html
 * @link http://www.gnu.org/software/gettext/manual/html_node/Plural-forms.html
 * @see ngettext
 * @see pgettext
 * @param string $msgctxt
 *   The message"s context identifier. Do not use the class name or full sentences as context. Try to use jQuery like
 *   selectors like <code>html head title</code> or <code>input[type="search"]</code> as they are very unlikely to
 *   change. Another good example of a context which is used very often is <code>route</code> for URLs.
 * @param string $msgid1
 *   The message to use if the count is <em>1</em>.
 * @param string $msgid2
 *   The message to use if the count is <em>>1</em>.
 * @param int $num
 *   The count that defines the plural form to use.
 * @return string
 *   The translated message.
 */
function npgettext($msgctxt, $msgid1, $msgid2, $num) {
  /* @var $msgctxtid1 string */
  $msgctxtid1 = $msgctxt . PHP_EOT . $msgid1;
  /* @var $msgctxtid2 string */
  $msgctxtid2 = $msgctxt . PHP_EOT . $msgid2;
  /* @var $translation string */
  $translation = n_($msgctxtid1, $msgctxtid2, $num);
  if (strcmp($translation, $msgctxtid1) === 0) {
    return $msgid1;
  }
  if (strcmp($translation, $msgctxtid2) === 0) {
    return $msgid2;
  }
  return $translation;
}

/**
 * Gettext with optional context support.
 *
 * @see gettext
 * @see pgettext
 * @param string $msgid
 *   The message that should be translated.
 * @param string $msgctxt
 *   [optional] Context of this translation.
 * @return string
 *   The translated string.
 */
function __($msgid, $args = [], $msgctxt = "") {
  if (empty($msgctxt)) {
    $msgid = gettext($msgid);
  }
  $msgid = pgettext($msgctxt, $msgid);
  if (empty($args)) {
    return $msgid;
  }
  return \MovLib\Utility\String::format($msgctxt, $args);
}

/**
 * Plural version of gettext with optional context support.
 *
 * @see ngettext
 * @see npgettext
 * @param string $msgid1
 *   The message to use if the count is <em>1</em>.
 * @param string $msgid2
 *   The message to use if the count is <em>&gt;1</em>.
 * @param int $n
 *   The count that defines the plural form to use.
 * @param string $msgctxt
 *   [optional] Context of this translation.
 * @return string
 *   The translated string
 */
function n__($msgid1, $msgid2, $n, $msgctxt = "") {
  if (empty($msgctxt)) {
    return ngettext($msgid1, $msgid2, $n);
  }
  return npgettext($msgctxt, $msgid1, $msgid2, $n);
}

function route($path, $args = []) {
  return vprintf(__($path, null, "route"), $args);
}

/**
 * This is the outermost place to catch any exception that might have been forgotten somewhere.
 *
 * To ensure that no unexpected behaviour crashes our software, any uncaught exception will be caught at this place. An
 * error is logged to the syslog and, depending on the error, a message is displayed to the user.
 *
 * @link http://www.php.net/manual/en/function.set-exception-handler.php
 * @param \Exception $e
 *   The base exception class from PHP from which every exception derives. This ensures that we are able to catch
 *   absolutely every exception that might arise.
 */
function uncaught_exception_handler($e) {
  exit((new \MovLib\Presenter\ExceptionPresenter($e))->getOutput());
}

// Set the default exception handler.
set_exception_handler("uncaught_exception_handler");

/**
 * Global function to convert PHP errors to exceptions.
 *
 * PHP by default mostly throws errors and not exceptions (like Java or Ruby). This user-defined error handler converts
 * these errors to exceptions and allows us to catch them and work with them. All PHP errors are runtime errors, as they
 * only appear by <em>doing</em> something. For more info on the differenciation of various exceptions and what they
 * mean have a look at the great article from Ralph Schindler (linked below).
 *
 * Please also note that this function will not convert all kinds of errors. This is due to the fact that it might not
 * even be registered when an error is raised. For instance if something goes wrong while PHP is bootstraping to start
 * our application, or while compiling this file. Of course such errors are more than fatal and should be observed with
 * another software that is capable of rescuing the PHP process itself.
 *
 * @link http://ralphschindler.com/2010/09/15/exception-best-practices-in-php-5-3
 * @link http://www.php.net/manual/en/function.set-error-handler.php
 * @param int $errno
 *   The level of error that was raised.
 * @param string $errstr
 *   The error message that describes what went wrong.
 * @param string $errfile
 *   The filename that the error was raised in.
 * @param int $errline
 *   The line number the error was raised at.
 */
function error_all_handler($errno, $errstr, $errfile, $errline) {
  uncaught_exception_handler(
    (new \MovLib\Exception\ErrorException($errstr, $errno))->setFile($errfile)->setLine($errline)
  );
}

// Do not pass an error type for the all handler, as PHP will invoke it for any and every error this way.
set_error_handler("error_all_handler");

/**
 * Transform PHP fatal errors to exceptions.
 *
 * This function is not meant to recover after a fatal error occurred. The purpose of this is to ensure that a nice
 * error view is displayed to the user.
 *
 * @link http://stackoverflow.com/a/2146171/1251219
 */
function error_fatal_handler() {
  if (($err = error_get_last()) !== null) {
    $exception = new \Exception($err["message"], $err["type"]);

    $reflectionClass = new \ReflectionClass("Exception");

    $trace = $reflectionClass->getProperty("trace");
    $trace->setAccessible(true);
    $trace->setValue($exception, [
      [ "function" => __FUNCTION__, "line" => __LINE__, "file" => __FILE__ ],
      [ "function" => "<em>unknown</em>", "line" => $err["line"], "file" => $err["file"] ],
    ]);

    $file = $reflectionClass->getProperty("file");
    $file->setAccessible(true);
    $file->setValue($exception, $err["file"]);

    $line = $reflectionClass->getProperty("line");
    $line->setAccessible(true);
    $line->setValue($exception, $err["line"]);

    uncaught_exception_handler($exception);
  }
}

// Check for possible fatal errors that are not catchable otherwise.
register_shutdown_function("error_fatal_handler");

/*DEBUG{{{*/
$t = microtime(true);
/*}}}DEBUG*/
$presenter = "\\MovLib\\Presenter\\" . $_SERVER["PRESENTER"] . "Presenter";
echo (new $presenter())->getOutput();
/*DEBUG{{{*/
$t = microtime(true) - $t;
$t = sprintf("%.6f", $t - intval($t));
echo
  "<p class='text-center'><code>{ generated in {$t}&nbsp;s | loaded in <span id='js-pageload'>0.000</span>&nbsp;s }" .
  "<script>
    window.onload = function () {
      if (window.performance == null) {
        return;
      }
      document.getElementById('js-pageload').innerHTML = ((window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart) / 1000) % 60;
    };
  </script>"
;
/*}}}DEBUG*/

// This makes sure that the output that was generated until this point will be returned to nginx for delivery. If
// any of our async methods is still working, they can finish their work in the background and the client does not have
// to wait for them to finish their work.
fastcgi_finish_request();
