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

/**
 * Description of String
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class String {

  /**
   * Encodes special characters in a plain-text string for display as HTML.
   *
   * Also validates strings as UTF-8.
   *
   * @param string $text
   *   The text to be checked or processed.
   * @return string
   *   HTML safe version of <code>$text</code>, or an empty string if <code>$text</code> is not valid UTF-8.
   */
  public static function checkPlain($text) {
    return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
  }

  /**
   * Formats a string to HTML display by replacing variable placeholders.
   *
   * This function replaces variable placeholders in a string with the requested values and escapes the values so they
   * can be safely displayed as HTML. It should be used on any unknown text that is intended to be printed to an HTML
   * page (especially text that may have come from untrusted users, since in that case it prevents cross-site scripting
   * and other security problems).
   *
   * @link http://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!String.php/function/String%3A%3Aformat/8
   * @see \MovLib\Utility\String::checkPlain()
   * @see \MovLib\Utility\String::placeholder()
   * @param string $string
   *   String containing placeholders.
   * @param array $args
   *   An associative array of replacements to make. Occurrences in $string of any key in $args are replaced with the
   * corresponding value, after optional sanitization and formatting. The type of sanitization and formatting depends on
   * the first character of the key:
   *   <ul>
   *     <li><b>@variable:</b> Excaped to HTML using <code>String::checkPlain()</code>. Use this as the default choice
   *     for anything displayed on a page on the site.</li>
   *     <li><b>%variable:</b> Escaped to HTML and formatted using <code>String::placeholder()</code>, which makes it
   *     display as <em>emphasized</em> text.</li>
   *     <li><b>!variable:</b> Inserted as is, with no sanitization or formatting. Only use this for text that has
   *     already been prepared for HTML display (for example, user-supplied text that has already been run through
   *     <code>String::checkPlain()</code> previously, or is expected to contain some limited HTML tags and has already
   *     been run through <code>String::filterXss()</code> previously).</li>
   *   </ul>
   * @return string
   *   The formatted string with placeholders inserted, or <tt>FALSE</tt> if no args specified.
   */
  public static function format($string, $args = []) {
    foreach ($args as $key => $value) {
      switch ($key[0]) {
        case "@":
          $args[$key] = self::checkPlain($value);
          break;

        case "%":
          $args[$key] = self::placeholder($value);
          break;

        case "!":
          // Pass-through!
      }
    }
    return strtr($string, $args);
  }

  /**
   * Formats text for emphasized display in a placeholder inside a sentence.
   *
   * Used automatically by <code>String::format()</code>.
   *
   * @param string $string
   *   The text to format (plain-text).
   * @return string
   *   The formatted text (html).
   */
  public static function placeholder($string) {
    $string = self::checkPlain($string);
    return "<em class='placeholder'>{$string}</em>";
  }

}
