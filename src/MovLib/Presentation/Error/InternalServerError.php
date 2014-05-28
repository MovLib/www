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
namespace MovLib\Presentation\Error;

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
final class InternalServerError extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InternalServerError";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The thrown exception.
   *
   * @var \Exception
   */
  protected $exception;


  // ------------------------------------------------------------------------------------------------------------------- Layout Methods


  /**
   * {@inheritdoc}
   * @param \Exception $exception
   *   The thrown exception that lead to the internal server error.
   */
  public function init() {
    $this->response->cacheable = false;
    http_response_code(500);
    $this->initPage($this->intl->t("Internal Server Error"));
    $this->initBreadcrumb();
    $this->stylesheets[] = "stacktrace";
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->alertError(
      $this->intl->t("Uh oh… that shouldn’t have happened."),
      "<p>{$this->intl->t("It appears you’ve found a glitch in the system.")}</p><p>{$this->intl->t(
        "This incident has been reported to the developers and should be fixed in no time. Please try again in a few " .
        "minutes. More technical details about this error can be found below. Don’t worry if you don’t understand what " .
        "it says down there, it will help us to solve the issue."
      )}</p>"
    );

    if (!($this->exception instanceof \Exception)) {
      $this->exception = new \RuntimeException("No exception was set for the internal server error.");
    }
    $tableHeader = $this->formatExceptionMessage($this->exception);
    if (($previous = $this->exception->getPrevious())) {
      $tableHeader .= "<br>{$this->formatExceptionMessage($previous)}";
    }
    return "<div class='c'>{$this->calloutInfo(
      "<table id='stacktrace-details'>" .
        "<thead>{$tableHeader}</thead>" .
        "<tbody>{$this->formatStacktrace($this->exception->getTrace())}</tbody>" .
      "</table>",
      $this->intl->t("Stacktrace for {0}", $this->placeholder("\\" . get_class($this->exception)))
    )}</div>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Helper Methods


  /**
   * Format exception message for stacktrace title.
   *
   * @param \Exception $e
   *   The exception to format the message.
   * @return string
   *   The exception's message formatted for the stacktrace title.
   */
  protected function formatExceptionMessage(\Exception $e) {
    return "<tr><th class='tar'><span class='ico ico-info'></span></th><th class='tal'>{$this->intl->t(
      "{exception_message}{class} on line {line,number}", [
        "exception_message" => nl2br($e->getMessage(), false),
        "class"             => "<br>" . str_replace([ $this->fs->documentRoot, $this->config->documentRoot ], "dr:/", $e->getFile()),
        "line"              => $e->getLine(),
      ]
    )}</th></tr>";
  }

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
   * @param array $stacktrace
   *   The stacktrace entry.
   * @return string
   *   The stacktrace entry's formatted file name.
   */
  protected function formatFileName(array &$stacktrace) {
    if (empty($stacktrace["file"])) {
      return "<em>unknown</em>";
    }
    // Remove symbolic link document root and/or realpath document root from paths and replace with proper scheme.
    return str_replace([ $this->fs->documentRoot, $this->config->documentRoot ], "dr:/", $stacktrace["file"]);
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
    if (empty($stacktrace["line"]) || $stacktrace["line"] == 0) {
      return "<i>?</i>";
    }
    return (string) $stacktrace["line"];
  }

  /**
   * Format the given stacktrace.
   *
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

  /**
   * Set the exception that was thrown.
   *
   * @param \Exception $exception
   *   The exception that was thrown.
   * @return this
   */
  public function setException(\Exception $exception) {
    $this->exception = $exception;
    return $this;
  }

}
