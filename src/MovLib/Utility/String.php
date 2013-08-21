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
 * @todo Description of String
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class String {

  /**
   * Encodes data with base 36.
   *
   * @link https://en.wikipedia.org/wiki/Base_36
   * @param mixed $data
   *   The data to encode.
   * @return string
   *   The encoded data, as a string or <code>FALSE</code> on failure.
   */
  public static function base36encode($data) {
    return base_convert($data, 10, 36);
  }

  /**
   * Decode data with base 36.
   *
   * @link https://en.wikipedia.org/wiki/Base_36
   * @param string $data
   *   The encoded data.
   * @return mixed
   *   Returns the original data or <code>FALSE</code> on failure.
   */
  public static function base36decode($data) {
    return base_convert($data, 36, 10);
  }

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
   * Shorten given string to given length and optionally append a string.
   *
   * <b>Usage example:</b>
   * The following will return “<em>extre…</em>”:
   * <pre>String::shorten("extremely long string", 5, __("…"));</pre>
   *
   * @param string $string
   *   The string to shorten.
   * @param int $length
   *   The length the string shall not exceed.
   * @param string $append
   *   [Optional] String to append if the string exceeds <var>$length</var>. Defaults to an empty string.
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
   * Multi-byte aware implementation of PHP's wordwrap function.
   *
   * @see wordwrap()
   * @param string $string
   *   The string to wrap.
   * @param int $width
   *   The number of characters at which the string will be wrapped.
   * @param string $break
   *   [Optional] The line is broken with this string. Defaults to <var>PHP_EOL</var>.
   * @param boolean $cut
   *   [Optional] If set to <code>TRUE</code>, the string is always wrapped at or before the specified width. So if
   *   you have a word that is larger than the given width, it is broken apart. Defaults to <code>FALSE</code>.
   * @return string
   *   The string wrapped at the specified length.
   */
  public static function wordwrap($string, $width, $break = PHP_EOL, $cut = false) {
    // Always remove whitespaces at beginning and end, nobody needs them.
    $string = trim($string);
    // Check if we are dealing with a multi-byte string, if not use native function.
    if (strlen($string) === mb_strlen($string)) {
      return wordwrap($string, $width, $break, $cut);
    }
    // We'll use the $cut variable to access the correct regular expression in the following array.
    settype($cut, "int");
    // Do not create these strings in each loop.
    $regExp = [
      "#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){{$width}}#",
      "#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){{$width},}\b#U"
    ];
    $words = explode(" ", $string);
    $string = "";
    $c = count($words);
    for ($i = 0; $i < $c; ++$i) {
      $stringLength = mb_strlen($words[$i]);
      $cutLength = ceil($stringLength / $width);
      $newWord = "";
      for ($j = 0; $j < $cutLength; ++$j) {
        preg_match($regExp[$cut], $words[$i], $matches);
        $newString = $matches[0];
        $newWord .= $newString . $break;
        $words[$i] = mb_substr($words[$i], mb_strlen($newString));
      }
      $string .= " {$newWord}{$words[$i]}";
    }
    return $string;
  }

}
