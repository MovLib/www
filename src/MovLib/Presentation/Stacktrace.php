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
namespace MovLib\Presentation;

/**
 * The stacktrace presentation is used if everything else fails.
 *
 * This class shall not extend any other class, the only allowed dependency is the <code>DebugException</code> which is
 * a custom MovLib exception to dissect variables.
 *
 * @todo Extend abstract page and translate (as soon as base stuff is stable).
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Stacktrace {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The exception to format.
   *
   * @var \Exception
   */
  protected $exception;

  /**
   * The title.
   *
   * This is the name of the exception for which we generated the stacktrace.
   *
   * @see Stacktrace::__construct()
   * @var string
   */
  protected $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new stacktrace presentation page.
   *
   * @param \Exception $exception
   *   The exception that should be presented. Any instance that inherits from PHP's built in exception class is okay.
   * @param boolean $fatal [optional]
   *   If set to <code>TRUE</code> title will say <i>Fatal Error</i> instead of the name of the exception, defaults to
   *   <code>FALSE</code>.
   */
  public function __construct($exception, $fatal = false) {
    $this->exception = $exception;
    $this->title     = $fatal === true ? "Fatal Error" : get_class($exception);
  }

  /**
   * Get presentation as string.
   *
   * @todo Combine CSS files.
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The presentation as string.
   */
  public function getPresentation() {
    global $kernel;
    http_response_code(500);
    return
      "<!doctype html>" .
      "<html dir='ltr' lang='en'>" .
        "<head>" .
          "<title>Internal Server Error — {$kernel->siteName}</title>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/base.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/grid.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/generic.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/header.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/content.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/footer.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/icons.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/alert.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/layout/buttons.css'>" .
          "<link rel='stylesheet' href='//{$kernel->domainStatic}/asset/css/modules/stacktrace.css'>" .
          "<link rel='icon' type='image/svg+xml' href='//{$kernel->domainStatic}/asset/img/logo/vector.svg'>" .
          "<link rel='icon' type='image/png' sizes='256x256' href='//{$kernel->domainStatic}/asset/img/logo/256.png'>" .
          "<link rel='icon' type='image/png' sizes='128x128' href='//{$kernel->domainStatic}/asset/img/logo/128.png'>" .
          "<link rel='icon' type='image/png' sizes='64x64' href='//{$kernel->domainStatic}/asset/img/logo/64.png'>" .
          "<link rel='icon' type='image/png' sizes='32x32' href='//{$kernel->domainStatic}/asset/img/logo/32.png'>" .
          "<link rel='icon' type='image/png' sizes='24x24' href='//{$kernel->domainStatic}/asset/img/logo/24.png'>" .
          "<link rel='icon' type='image/png' sizes='16x16' href='//{$kernel->domainStatic}/asset/img/logo/16.png'>" .
          "<meta name='viewport' content='width=device-width,initial-scale=1.0'>" .
        "</head>" .
        "<body class='stacktrace-body' id='stacktrace'>" .
          "<header id='header'>" .
            "<div class='container'>" .
              "<div id='header__logo'>" .
                "<img alt='{$kernel->siteName} logo' height='42' id='logo' src='//{$kernel->domainStatic}/asset/img/logo/vector.svg' width='42'> {$kernel->siteName}" .
              "</div>" .
            "</div>" .
          "</header>" .
          "<div class='stacktrace-content' id='content' role='main'>" .
            "<div id='content__header'>" .
              "<div class='container'>" .
                "<h1 class='title' id='content__header__title'>Internal Server Error</h1>" .
              "</div>" .
              "<div id='alerts'>" .
                "<div class='alert alert--error' role='alert'>" .
                  "<div class='container'>" .
                    "<h4 class='alert__title'>An unexpected condition which prevented us from fulfilling the request was encountered.</h4>" .
                    "<p>This error was reported to the system administrators, it should be fixed in no time. Please try again in a few minutes.</p>" .
                  "</div>" .
                "</div>" .
                "<div class='alert alert--info' role='alert'>" .
                  "<div class='container'>" .
                    "<h4 class='alert__title'>Stacktrace for {$this->title}</h4>" .
                    "<div class='stacktrace'>" .
                      "<div class='stacktrace__title'><i class='icon icon--attention'></i> {$this->exception->getMessage()}</div>" .
                      "<table class='stacktrace__table'>{$this->formatStacktrace($this->exception->getTrace())}</table>" .
                    "</div>" .
                  "</div>" .
                "</div>" .
              "</div>" .
            "</div>" .
          "</div>" .
          "<footer id='footer'>" .
            "<div class='container'>" .
              "<small style='text-align:center'>Stacktrace is only available if release type is <em class='placeholder'>dev</em>!</small>" .
            "</div>" .
          "</footer>" .
        "</body>" .
      "</html>"
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format the stacktrace entry's function arguments.
   *
   * @param array $stacktrace
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's formatted function arguments.
   */
  protected function formatFunctionArguments(array &$stacktrace) {
    if (empty($stacktrace["args"])) {
      return "";
    }
    foreach ($stacktrace["args"] as $delta => $arg) {
      $suffix                     = is_array($arg) ? "(" . count($arg) . ")" : null;
      $type                       = gettype($arg);
      try {
        $title = htmlspecialchars(print_r($arg, true), ENT_QUOTES | ENT_HTML5);
      }
      // There are some properties that might issue an error (e.g. with mysqli the famous "property access not allowed
      // yet" warnings.
      catch (\ErrorException $e) {
        $title = "WARNING: Property access not allowed yet.";
      }
      $stacktrace["args"][$delta] = "<var class='stacktrace_var' title='{$title}'>{$type}{$suffix}</var>";
    }
    return implode(", ", $stacktrace["args"]);
  }

  /**
   * Format the stacktrace entry's class name.
   *
   * @param array $stacktrace
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's formatted class name.
   */
  protected function formatClassName(array &$stacktrace) {
    if (empty($stacktrace["class"])) {
      return "";
    }
    return (string) $stacktrace["class"];
  }

  /**
   * Format the stacktrace entry's file name.
   *
   * @global \MovLib\Kernel $kernel
   * @param array $stacktrace
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's formatted file name.
   */
  protected function formatFileName(array &$stacktrace) {
    global $kernel;
    if (empty($stacktrace["file"])) {
      return "<em>unknown</em>";
    }
    return str_replace([ $kernel->documentRoot, "/src/" ], "", $stacktrace["file"]);
  }

  /**
   * Format the stacktrace entry's line number.
   *
   * @param array $stacktrace
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's formatted line number.
   */
  protected function formatLineNumber(array &$stacktrace) {
    if (empty($stacktrace["line"])) {
      return "0";
    }
    return (string) $stacktrace["line"];
  }

  /**
   * Format the given stacktrace.
   *
   * @global \MovLib\Kernel $kernel
   * @param array $stacktrace
   *   The array returned by the getter.
   * @return string
   *   The formatted stacktrace.
   */
  protected function formatStacktrace(array $stacktrace) {
    $formatted = null;
    $c         = count($stacktrace);
    for ($i = 0; $i < $c; ++$i) {
      $line       = $this->formatLineNumber($stacktrace[$i]);
      $class      = $this->formatClassName($stacktrace[$i]);
      $function   = $this->formatFunction($stacktrace[$i]);
      $type       = $this->formatFunctionType($stacktrace[$i]);
      $args       = $this->formatFunctionArguments($stacktrace[$i]);
      $file       = $this->formatFileName($stacktrace[$i]);
      $formatted .=
        "<tr class='stacktrace__tr'>" .
          "<td class='stacktrace__td stacktrace__line-number'>{$line}</td>" .
          "<td class='stacktrace__td'>" .
            "<div class='stacktrace__line-container'>{$class}{$type}" .
              "<span class='stacktrace__function'>{$function}</span>({$args})" .
              "<span class='stacktrace__file'>{$file}</span>" .
            "</div>" .
          "</td>" .
        "</tr>"
      ;
    }
    return $formatted;
  }

  /**
   * Format the stacktrace entry's function name.
   *
   * @param array $stacktrce
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's function name.
   */
  protected function formatFunction(array &$stacktrce) {
    if (empty($stacktrce["function"])) {
      return "<em>unknown</em>";
    }
    return (string) $stacktrce["function"];
  }

  /**
   * Format the stacktrace entry's function type.
   *
   * @param array $stacktrace
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's formatted function type.
   */
  protected function formatFunctionType(array &$stacktrace) {
    if (empty($stacktrace["type"])) {
      return "";
    }
    if ($stacktrace["type"] == "dynamic") {
      return "->";
    }
    if ($stacktrace["type"] == "static") {
      return "::";
    }
    return (string) $stacktrace["type"];
  }

}
