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
namespace MovLib\Presentation;

use \MovLib\Exception\DebugException;
use \MovLib\Configuration;

/**
 * The stacktrace presentation is used if everything else fails.
 *
 * This class shall not extend any other class, the only allowed dependency is the <code>DebugException</code> which is
 * a custom MovLib exception to dissect variables.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Stacktrace {

  /**
   * String buffer used to collect the stacktrace.
   *
   * @param string
   */
  private $stacktrace = "";

  /**
   * Instantiate new stacktrace presentation page.
   *
   * @param \Exception $exception
   *   The exception that should be presented. Any instance that inherits from PHP's built in exception class is okay.
   */
  public function __construct($exception) {
    http_response_code(500);
    if (strpos($GLOBALS["movlib"]["version"], "-dev") !== false) {
      // Debug exception don't need the stack formatted.
      if ($exception instanceof DebugException) {
        $stacktrace = "<tr class='stacktrace__tr'><td><pre>{$exception}</pre></td></tr>";
      }
      else {
        $stacktrace = $this->formatStacktrace($exception->getTrace());
      }
      $exceptionName = get_class($exception);
      $this->stacktrace =
        "<div class='alert alert--info' role='alert'>" .
          "<div class='container'>" .
            "<h4 class='alert__title'>Stacktrace for {$exceptionName}</h4>" .
            "<div class='stacktrace'>" .
              "<div class='stacktrace__title'><i class='icon icon--attention'></i> {$exception->getMessage()}</div>" .
              "<table class='stacktrace__table'>{$stacktrace}</table>" .
            "</div>" .
          "</div>" .
        "</div>"
      ;
    }
  }

  /**
   * Format the given stacktrace.
   *
   * @param array $trace
   *   The array returned by the getter.
   * @return string
   *   The formatted stacktrace.
   */
  private function formatStacktrace($trace) {
    $stacktrace = "";
    $c = count($trace);
    for ($i = 0; $i < $c; ++$i) {
      if (!empty($trace[$i]["args"])) {
        $argCount = count($trace[$i]["args"]);
        for ($j = 0; $j < $argCount; ++$j) {
          $suffix = "";
          if (is_array($trace[$i]["args"][$j])) {
            $suffix = "(" . count($trace[$i]["args"][$j]) . ")";
          }
          $type = gettype($trace[$i]["args"][$j]);
          $title = htmlspecialchars(print_r($trace[$i]["args"][$j], true), ENT_QUOTES|ENT_HTML5);
          $trace[$i]["args"][$j] = "<var class='stacktrace_var' title='{$title}'>{$type}{$suffix}</var>";
        }
        $trace[$i]["args"] = implode(", ", $trace[$i]["args"]);
      }
      else {
        $trace[$i]["args"] = "";
      }

      foreach ([ "line", "class", "type", "function", "file" ] as $s) {
        if (!isset($trace[$i][$s])) {
          $trace[$i][$s] = "";
        }
      }
      $trace[$i]["file"] = str_replace($_SERVER["DOCUMENT_ROOT"], "", $trace[$i]["file"]);

      $stacktrace .=
        "<tr class='stacktrace__tr'>" .
          "<td class='stacktrace__td stacktrace__line-number'>{$trace[$i]["line"]}</td>" .
          "<td class='stacktrace__td'>" .
            "<div class='stacktrace__line-container'>{$trace[$i]["class"]}{$trace[$i]["type"]}" .
              "<span class='stacktrace__function'>{$trace[$i]["function"]}</span>({$trace[$i]["args"]})" .
              "<span class='stacktrace__file'>{$trace[$i]["file"]}</span>" .
            "</div>" .
          "</td>" .
        "</tr>"
      ;
    }
    return $stacktrace;
  }

  /**
   * Get presentation as string.
   *
   * @todo Combine CSS files.
   * @global \MovLib\Configuration $config
   * @return string
   *   The presentation as string.
   */
  public function getPresentation() {
    global $config;
    if (!$config) {
      $config = new Configuration();
    }
    return
      "<!doctype html>" .
      "<html dir='ltr' lang='en'>" .
        "<head>" .
          "<title>Internal Server Error — MovLib</title>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/base.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/grid.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/generic.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/header.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/content.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/footer.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/icons.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/alert.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/layout/buttons.css'>" .
          "<link rel='stylesheet' href='//{$config->domainStatic}/asset/css/modules/stacktrace.css'>" .
          "<link rel='icon' type='image/svg+xml' href='//{$config->domainStatic}/asset/img/logo/vector.svg'>" .
          "<link rel='icon' type='image/png' sizes='256x256' href='//{$config->domainStatic}/asset/img/logo/256.png'>" .
          "<link rel='icon' type='image/png' sizes='128x128' href='//{$config->domainStatic}/asset/img/logo/128.png'>" .
          "<link rel='icon' type='image/png' sizes='64x64' href='//{$config->domainStatic}/asset/img/logo/64.png'>" .
          "<link rel='icon' type='image/png' sizes='32x32' href='//{$config->domainStatic}/asset/img/logo/32.png'>" .
          "<link rel='icon' type='image/png' sizes='24x24' href='//{$config->domainStatic}/asset/img/logo/24.png'>" .
          "<link rel='icon' type='image/png' sizes='16x16' href='//{$config->domainStatic}/asset/img/logo/16.png'>" .
          "<meta name='viewport' content='width=device-width,initial-scale=1.0'>" .
        "</head>" .
        "<body class='stacktrace-body' id='stacktrace'>" .
          "<header id='header'>" .
            "<div class='container'>" .
              "<div id='header__logo'>" .
                "<img alt='MovLib logo' height='42' id='logo' src='//{$config->domainStatic}/asset/img/logo/vector.svg' width='42'> MovLib" .
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
                $this->stacktrace .
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

}
