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
namespace MovLib\View\HTML\Error;

use \MovLib\Utility\DelayedLogger;
use \MovLib\Utility\String;
use \MovLib\View\HTML\AlertView;

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
class ExceptionView extends AlertView {

  /**
   * An error view expects the complete exception object to be passed along.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter that created the view instance.
   * @param \Exception $exception
   *   The exception that caused the error.
   */
  public function __construct($presenter, $exception) {
    global $i18n;
    parent::__construct($presenter, $i18n->t("Internal Server Error"));
    http_response_code(500);
    $this->stylesheets[] = "modules/stacktrace.css";
    $this->setAlert(
      "<p>{$i18n->t("An unexpected condition which prevented us from fulfilling the request was encountered.")}</p>" .
      "<p>{$i18n->t("This error was reported to the system administrators, it should be fixed in no time. Please try again in a few minutes.")}</p>",
      self::ALERT_SEVERITY_ERROR,
      true
    );
    /*DEBUG{{{*/
    $this->setAlert(
      [
        "title" => "Stacktrace",
        "message" =>
          "<div class='stacktrace'>" .
            "<div class='stacktrace__title'><i class='icon icon--attention'></i> {$exception->getMessage()}</div>" .
            "<table class='stacktrace__table'>{$this->formatStacktrace($exception->getTrace())}</table>" .
          "</div>" .
          "<p class='text-center'><small>Debug information is only available if debugging is activated during bootstrap phase!</small></p>"
      ],
      self::ALERT_SEVERITY_INFO,
      true
    );
    /*}}}DEBUG*/
    DelayedLogger::logException($exception, E_RECOVERABLE_ERROR);
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
      if (isset($stacktrace[$i]["args"]) || !empty($stacktrace[$i]["args"])) {
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
        $stacktrace[$i]["args"] = implode(", ", $stacktrace[$i]["args"]);
      } else {
        $stacktrace[$i]["args"] = "";
      }
      foreach ([ "line", "class", "type", "function", "file" ] as $s) {
        if (!isset($stacktrace[$i][$s])) {
          $stacktrace[$i][$s] = "";
        }
      }
      $stacktrace[$i]["file"] = str_replace($_SERVER["HOME"], "", $stacktrace[$i]["file"]);
      $output .=
        "<tr class='stacktrace__tr'>" .
          "<td class='stacktrace__td stacktrace__line-number'>{$stacktrace[$i]["line"]}</td>" .
          "<td class='stacktrace__td'>" .
            "<div class='stacktrace__line-container'>{$stacktrace[$i]["class"]}{$stacktrace[$i]["type"]}" .
              "<span class='stacktrace__function'>{$stacktrace[$i]["function"]}</span>({$stacktrace[$i]["args"]})" .
              "<span class='stacktrace__file'>{$stacktrace[$i]["file"]}</span>" .
            "</div>" .
          "</td>" .
        "</tr>"
      ;
    }
    return $output;
  }

}
