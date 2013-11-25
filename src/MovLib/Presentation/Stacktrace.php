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

use \MovLib\Presentation\Partial\Alert;

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
class Stacktrace extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new stacktrace presentation page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param \Exception $exception
   *   The exception that should be presented. Any instance that inherits from PHP's built in exception class is okay.
   * @param boolean $fatal [optional]
   *   If set to <code>TRUE</code> title will say <i>Fatal Error</i> instead of the name of the exception, defaults to
   *   <code>FALSE</code>.
   */
  public function __construct($exception, $fatal = false) {
    global $i18n, $kernel;
    http_response_code(500);
    $this->init($i18n->t("Internal Server Error"));
    $kernel->stylesheets[] = "stacktrace";

    $this->alerts .= new Alert(
      $i18n->t("This error was reported to the system administrators, it should be fixed in no time. Please try again in a few minutes."),
      $i18n->t("An unexpected condition which prevented us from fulfilling the request was encountered."),
      Alert::SEVERITY_ERROR
    );

    $this->alerts .= new Alert(
      "<div id='stacktrace-details'>" .
        "<div class='title'><i class='icon icon--attention'></i> {$exception->getMessage()}</div>" .
        "<table>{$this->formatStacktrace($exception->getTrace())}</table>" .
      "</div>",
      $i18n->t("Stacktrace for {0}", [ $this->placeholder($fatal === true ? "Fatal Error" : get_class($exception)) ]),
      Alert::SEVERITY_INFO
    );
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
      $suffix = is_array($arg) ? "(" . count($arg) . ")" : null;
      $type   = gettype($arg);
      try {
        $title = htmlspecialchars(print_r($arg, true), ENT_QUOTES | ENT_HTML5);
      }
      // There are some properties that might issue an error (e.g. with mysqli the famous "property access not allowed
      // yet" warnings.
      catch (\ErrorException $e) {
        $title = "WARNING: Property access not allowed yet.";
      }
      if (!empty($title)) {
        $title = " title='{$title}'";
      }
      $stacktrace["args"][$delta] = "<var{$title}>{$type}{$suffix}</var>";
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
        "<tr>" .
          "<td class='line-number'>{$line}</td>" .
          "<td>{$class}{$type}<span class='function'>{$function}</span>({$args})<span class='file'>{$file}</span></td>" .
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
