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
   *   HTML safe version of <var>$text</var>, or an empty string if <var>$text</var> is not valid UTF-8.
   */
  public static function checkPlain($text) {
    return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
  }

  /**
   * Encodes special characters in a URL for display as HTML.
   *
   * Also validates strings as UTF-8.
   *
   * @param string $url
   *   The URL to be checked or processed.
   * @return string
   *   HTML safe version of <var>$url</var>, or an empty string if <var>$url</var> is not valid UTF-8.
   */
  public static function checkUrl($url) {
    return htmlentities($url, ENT_QUOTES, "UTF-8");
  }

  /**
   * Formats a string to HTML display by replacing variable placeholders.
   *
   * This function replaces variable placeholders in a string with the requested values and escapes the values so they
   * can be safely displayed as HTML. It should be used on any unknown text that is intended to be printed to an HTML
   * page (especially text that may have come from untrusted users, since in that case it prevents cross-site scripting
   * and other security problems).
   *
   * <b>Usage example:</b>
   * <pre>String::format("Hello @world! Hello %world! Hello !world!", [ "@world" => "W<>orld", "%world" => "W<>orld", "!world" => "W<>orld" ]);</pre>
   * Output:
   * <pre>Hello W&amp;lt;&amp;gt;ld! Hello <em class='placeholder'>W&amp;lt;&amp;gt;rld</em>! Hello W<>orld!</pre>
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

  /**
   * Shorten given string to given length and optionally append a string.
   *
   * <b>Usage example:</b>
   * The following will return “<em>extre…</em>”:
   * <pre>String::shorten("extremely long string", 5, __("…"));</pre>
   *
   * @param string $string
   *   The strong to shorten.
   * @param int $length
   *   The length the string shall not exceed.
   * @param string $append
   *   String to append if the string exceeds <var>$length</var>.
   * @return string
   *   The string as is if it does not exceed <var>$length</var>, otherwise the shortened string with <var>$append</var>
   *   appended to the end of the string.
   */
  public static function shorten($string, $length, $append = "") {
    if (mb_strlen($string) > $length) {
      return mb_substr($string, 0, $length) . $append;
    }
    return $string;
  }

}
