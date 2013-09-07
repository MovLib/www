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
 * Various string related utility methods.
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
    return htmlspecialchars($text, ENT_QUOTES|ENT_HTML5);
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
    return htmlentities($url, ENT_QUOTES|ENT_HTML5);
  }

  /**
   * Convert string of any form to a route usable string.
   *
   * <b>IMPORTANT!</b> This is for internal usage only, you can convert a string to route and look up the translation
   * in the database. This does not sanitize or encode the generated route!
   *
   * <b>IMPORTANT!</b> This is not meant to create complete routes. If you pass a slash (<code>/</code>) as part of the
   * string it will get encoded.
   *
   * @param string $string
   *   The string that should be converted.
   * @return string
   *   A string that can be used within the i18n route methods.
   */
  public static function convertToRoute($string) {
    return str_replace(" ", "-", mb_strtolower($string));
  }

  /**
   * Decodes HTML entities including numerical ones to regular UTF-8 bytes.
   *
   * Double-escaped entities will only be decoded once (<code>"&amp;lt;"</code> becomes <code>"&lt;"</code>, not
   * <code>"<"</code>). Be careful when using this method, as it will revert previous sanitization efforts
   * (<code>"&lt;script&gt;"</code> will become <code>"<script>"</code>).
   *
   * @param string $string
   *   The text to decode entities in.
   * @return string
   *   The input <var>$string</var>, with all HTML entities decoded once.
   */
  public static function decodeEntities($string) {
    return html_entity_decode($string, ENT_QUOTES);
  }

  /**
   * Formats text for emphasized display in a placeholder inside a sentence.
   *
   * @see \MovLib\Utility\String::checkPlain()
   * @param string $string
   *   The text to format (plain text).
   * @return string
   *   The formatted text (html).
   */
  public static function placeholder($string) {
    $string = self::checkPlain($string);
    return "<em class='placeholder'>{$string}</em>";
  }

  /**
   * Collapse all kinds of whitespace characters to a single whitespace.
   *
   * @param string $string
   *   The string to collapse.
   * @return string
   *   The collapsed string.
   */
  public static function collapseWhitespace($string) {
    return trim(preg_replace("/\s+/m", " ", $string));
  }

  /**
   * Normalize all linebreaks to *NIX style (<code>\n</code>).
   *
   * @link http://stackoverflow.com/a/7836692/1251219 How to replace different newline styles in PHP the smartest way?
   * @param string $string
   *   The string to normalize.
   * @return string
   *   The normalized string.
   */
  public static function normalizeLineFeeds($string) {
    return preg_replace("/\R/u", "\n", $string);
  }

  /**
   * Multi-byte aware wordwrap implementation.
   *
   * Please note that this function will always normalize line feeds to LF.
   *
   * @see wordwrap()
   * @link https://api.drupal.org/api/drupal/core%21vendor%21zendframework%21zend-stdlib%21Zend%21Stdlib%21StringWrapper%21AbstractStringWrapper.php/function/AbstractStringWrapper%3A%3AwordWrap/8
   * @param string $string
   *   The string to wrap.
   * @param int $width [optional]
   *   The number of characters at which the string will be wrapped, defaults to <code>75</code>.
   * @param boolean $cut [optional]
   *   If set to <code>TRUE</code>, the string is always wrapped at or before the specified width. So if you have a word
   *   that is larger than the given width, it is broken apart. Defaults to <code>FALSE</code>.
   * @return string
   *   The string wrapped at the specified length.
   */
  public static function wordwrap($string, $width = 75, $cut = false) {
    // Always remove whitespaces at beginning and end, nobody needs them.
    $string = trim(self::normalizeLineFeeds($string));
    // Use native function if we aren't dealing with a multi-byte string.
    if (strlen($string) === mb_strlen($string)) {
      return wordwrap($string, $width, "\n", $cut);
    }
    $strlen = mb_strlen($string);
    $result = "";
    $lastStart = $lastSpace = 0;
    for ($i = 0; $i < $strlen; ++$i) {
      $char = mb_substr($string, $i, 1);
      if ($char === "\n") {
        $result .= mb_substr($string, $lastStart, $i - $lastStart + 1);
        $lastStart = $lastSpace = $i + 1;
        continue;
      }
      if ($char === " ") {
        if ($i - $lastStart >= $width) {
          $result .= mb_substr($string, $lastStart, $i - $lastStart) . "\n";
          $lastStart = $i + 1;
        }
        $lastSpace = $i;
        continue;
      }
      if ($i - $lastStart >= $width && $cut === true && $lastStart >= $lastSpace) {
        $result .= mb_substr($string, $lastStart, $i - $lastStart) . "\n";
        $lastStart = $lastSpace = $i;
        continue;
      }
      if ($i - $lastStart >= $width && $lastStart < $lastSpace) {
        $result .= mb_substr($string, $lastStart, $lastSpace - $lastStart) . "\n";
        $lastStart = $lastSpace = $lastSpace + 1;
        continue;
      }
    }
    if ($lastStart !== $i) {
      $result .= mb_substr($string, $lastStart, $i - $lastStart);
    }
    return $result;
  }

}
