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
namespace MovLib\Data;

/**
 * Static class providing various methods to work with HTML.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class HTML {

  /**
   * Get the raw HTML string.
   *
   * @param string $text
   *   The encoded HTML string that should be decoded.
   * @return string
   *   The raw HTML string.
   */
  final public static function decode($text) {
    return htmlspecialchars_decode($text, ENT_QUOTES | ENT_HTML5);
  }

  /**
   * Decodes all HTML entities including numerical ones to regular UTF-8 bytes.
   *
   * Double-escaped entities will only be decoded once (<code>"&amp;lt;"</code> becomes <code>"&lt;"</code>, not
   * <code>"<"</code>). Be careful when using this function, as it will revert previous sanitization efforts
   * (<code>"&lt;script&gt;"</code> will become <code>"<script>"</code>).
   *
   * @param string $text
   *   The text to decode entities in.
   * @return string
   *   <var>$text</var> with all HTML entities decoded.
   */
  final public static function decodeEntities($text) {
    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
  }

  /**
   * Encode special characters in a plain-text string for display as HTML.
   *
   * <b>Always</b> use this method before displaying any plain-text string to the user.
   *
   * @param string $text
   *   The plain-text string to process.
   * @return string
   *   <var>$text</var> with encoded HTML special characters.
   */
  final public static function encode($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
  }

}
