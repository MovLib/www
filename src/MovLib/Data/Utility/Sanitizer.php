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
namespace MovLib\Data\Utility;

use \Normalizer;

/**
 * The sanitizer class provides several utility methods to sanitize user input.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait Sanitizer {

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
  public static function decodeHtmlEntities($string) {
    return html_entity_decode($string, ENT_QUOTES|ENT_HTML5);
  }

  /**
   * Encodes <b>all</b> special HTML characters. Most useful for URLs in HTML documents.
   *
   * @param string $text
   *   The plain-text string to process.
   * @return string
   *   The <var>$text</var> with encoded HTML special characters.
   */
  public static function encodeHtmlEntities($text) {
    return htmlentities($text, ENT_QUOTES|ENT_HTML5);
  }

  /**
   * Encodes special HTML characters.
   *
   * <b>Always</b> use this method before displaying any plain-text string to the user.
   *
   * @param string $text
   *   The plain-text string to process.
   * @return string
   *   The <var>$text</var> with encoded HTML special characters.
   */
  public static function encodeSpecialHtmlEntities($text) {
    return htmlspecialchars($text, ENT_QUOTES|ENT_HTML5);
  }

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
    $sanitized = str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename);
    $sanitized = preg_replace("/[\s-]+/", "-", $sanitized);
    $sanitized = trim($sanitized, ".-_");
    $sanitized = mb_strtolower($sanitized);
    return $sanitized;
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
  public static function lineFeeds($string) {
    return preg_replace("/\R/u", "\n", $string);
  }

  /**
   * Sanitize plain text string.
   *
   * The following actions are performed:
   * <ul>
   *   <li>utf-8 is normalized to NFC</li>
   *   <li>HTML entities are decoded (twice)</li>
   *   <li>all HTML tags are stripped</li>
   *   <li>tabs and extra whitespace is removed</li>
   *   <li>octets are stripped</li>
   * </ul>
   *
   * @param string $raw
   *   The string to sanitize.
   * @return string
   *   The sanitized plain text string.
   */
  public static function plainText($raw, $preserveLineFeeds = false) {
    $sanitized = Normalizer::normalize($raw);
    $sanitized = self::lineFeeds($sanitized);
    $sanitized = self::decodeHtmlEntities(self::decodeHtmlEntities($sanitized));
    if ($preserveLineFeeds === true) {
      $sanitized = str_replace("\n", "~~~~n~~~~", $sanitized);
    }
    $sanitized = strip_tags($sanitized);
    $sanitized = self::collapseWhitespace($sanitized);
    $sanitized = filter_var($sanitized, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    if ($preserveLineFeeds === true) {
      $sanitized = str_replace("~~~~n~~~~", "\n", $sanitized);
    }
    return $sanitized;
  }

}
