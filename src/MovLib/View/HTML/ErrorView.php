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
namespace MovLib\View\HTML;

use \Exception;
use \MovLib\Entity\Language;
use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractView;

/**
 * The error view is presented to the user if something terrible happens.
 *
 * @todo Create seperate CSS for stacktrace and load on demand.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ErrorView extends AbstractView {

  /**
   * An error view expects the complete exception object to be passed along.
   *
   * @param \MovLib\Entity\Language
   *   The currently active language entity instance.
   * @param \Exception $exception
   *   The exception that caused the error.
   */
  public function __construct(Language $language, Exception $exception) {
    parent::__construct($language, "Error");
    $this->addStylesheet("/assets/css/modules/stacktrace.css");
    $this->setAlert(
      "<p>" . __("This shouldn’t have happened, but it did, an error occured while trying to handle your request.") . "</p>" .
      "<p>" . __("The error was logged and reported to the system administrators, it should be fixed in no time.") . "</p>" .
      "<p>" . __("Please try again in a few minutes.") . "</p>",
      __("We’re sorry but something went terribly wrong!"),
      "error",
      true
    );
    if (error_reporting() !== 0) {
      $this->setAlert(
        "<div class='stacktrace'>" .
          "<div class='stacktrace__title'><i class='icon icon--attention'></i> {$exception->getMessage()}</div>" .
          "<table class='stacktrace__table'>{$this->formatStacktrace($exception->getTrace())}</table>" .
        "</div>" .
        "<p class='text-center'><small>Debug information is only available if error reporting is turned on!</small></p>",
        "Stacktrace",
        "info",
        true
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getShortName() {
    return "error";
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedContent() {
    return "";
  }

  /**
   * Format the given stacktrace.
   *
   * @param array $stacktrace
   *   The stacktrace of the exception.
   * @return string
   *   The formatted stacktrace.
   */
  private function formatStacktrace(array $stacktrace) {
    $output = "";
    $stacktraceCount = count($stacktrace);
    for ($i = 0; $i < $stacktraceCount; ++$i) {
      if (isset($stacktrace[$i]["args"]) === true || empty($stacktrace[$i]["args"]) === false) {
        $argCount = count($stacktrace[$i]["args"]);
        for ($j = 0; $j < $argCount; ++$j) {
          $suffix = "";
          if (is_array($stacktrace[$i]["args"][$j])) {
            $suffix = "(" . count($stacktrace[$i]["args"][$j]) . ")";
          }
          $title = String::checkPlain(print_r($stacktrace[$i]["args"][$j], true));
          $type = gettype($stacktrace[$i]["args"][$j]);
          $stacktrace[$i]["args"][$j] = "<var class='stacktrace_var' title='{$title}'>{$type}{$suffix}</var>";
        }
        $stacktrace[$i]["args"] = "(" . implode(", ", $stacktrace[$i]["args"]) . ")";
      } else {
        $stacktrace[$i]["args"] = "";
      }
      foreach ([ "line", "class", "type", "function", "file" ] as $s) {
        if (isset($stacktrace[$i][$s]) === false) {
          $stacktrace[$i][$s] = "";
        }
      }
      $stacktrace[$i]["file"] = str_replace($_SERVER["DOCUMENT_ROOT"], "", $stacktrace[$i]["file"]);
      $output .=
        "<tr class='stacktrace__tr'>" .
          "<td class='stacktrace__td stacktrace__line-number'>{$stacktrace[$i]["line"]}</td>" .
          "<td class='stacktrace__td'>" .
            "<div class='stacktrace__line-container'>{$stacktrace[$i]["class"]}{$stacktrace[$i]["type"]}" .
              "<span class='stacktrace__function'>{$stacktrace[$i]["function"]}</span>" .
              $stacktrace[$i]["args"] .
              "<span class='stacktrace__file'>{$stacktrace[$i]["file"]}</span>" .
            "</div>" .
          "</td>" .
        "</tr>"
      ;
    }
    return $output;
  }

}
