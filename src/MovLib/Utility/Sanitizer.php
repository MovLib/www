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
namespace MovLib\Utility;

use \MovLib\Utility\String;

/**
 * The sanitizer class provides several utility methods to sanitize user input.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Sanitizer {

  /**
   * Sanitizes a filename, replacing whitespace with dashes and transforming the string to lowercase.
   *
   * Removes special characters that are illegal in filenames on certain operating systems and special characters
   * requiring special escaping to manipulate at the command line. Replaces spaces and consecutive dashes with a single
   * dash. Trims period, dash und underscore from beginning and end of filename.
   *
   * @param string $filename
   *   The filename to be sanitized.
   * @return string
   *   The sanitized filename.
   */
  public static function filename($filename) {
    return mb_strtolower(trim(preg_replace("/[\s-]+/", "-", str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename)), ".-_"));
  }

  /**
   * Sanitize a string from user input.
   *
   * The following actions are performed:
   * <ul>
   *   <li>checks for invalid UTF-8</li>
   *   <li>converts special HTML characters</li>
   *   <li>strips all tags</li>
   *   <li>removes tabs and extra white space</li>
   *   <li>strips octets</li>
   * </ul>
   *
   * @param string $string
   *   The string to sanitize.
   * @param boolean $preserveLinefeeds
   *   [Optional] Set this to <code>TRUE</code> if you want to preserve linefeeds, this will also normalize all line-
   *   feeds to *nix style (\n). Default is <code>FALSE</code>.
   * @return string
   *   The sanitized string.
   */
  public static function string($string, $preserveLinefeeds = false) {
    settype($string, "string");
    if ($preserveLinefeeds === true) {
      $string = nl2br($string, false);
    }
    $string = trim(String::collapseWhitespace(String::checkPlain(strip_tags(filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)))));
    if ($preserveLinefeeds === true) {
      $string = str_replace("<br>", "\n", $string);
    }
    return $string;
  }

  /**
   * Escape single quotes in string.
   *
   * We are using single quotes within our HTML, therefor any single quotes within attributes have to escaped.
   *
   * @param string $string
   *   The string to escape single quotes.
   * @return string
   *   The string with escape single quotes.
   */
  public static function escapeSingleQuotes(&$string) {
    return strtr($string, "'", "&#39;");
  }

}
