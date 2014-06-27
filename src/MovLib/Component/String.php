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
namespace MovLib\Component;

/**
 * Defines the static string class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class String {

  /**
   * Transform string to camelCase.
   *
   * @param string $string
   *   The string to transform.
   * @param string $delimiter [optional]
   *   The delimiter string that separates the words, defaults to empty string (<code>" "</code>).
   * @return string
   *   The <var>$string</var> in camelCase.
   */
  final public static function camelCase($string, $delimiter = " ") {
    // We use the PascalCase method to do the heavy lifting for us.
    $string = self::pascalCase($string, $delimiter);

    // We only have to lowercase the first character, binary safe of course.
    return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
  }

  /**
   * Transform string to PascalCase.
   *
   * @param string $string
   *   The string to transform.
   * @param string $delimiter [optional]
   *   The delimiter string that separates the words, defaults to empty string (<code>" "</code>).
   * @return string
   *   The <var>$string</var> in PascalCase.
   */
  final public static function pascalCase($string, $delimiter = " ") {
    // Replace the delimiter with the default delimiter if it differs.
    if ($delimiter !== " ") {
      $string = str_replace($delimiter, " ", $string);
    }

    // Now we can convert each first word character to uppercase.
    $string = mb_convert_case($string, MB_CASE_TITLE);

    // And finally remove all delimiters that are supported by mb_convert_case().
    return str_replace([ " ", "_" ], "", $string);
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
   * @throws \InvalidArgumentException
   *   If <var>$filename</var> is invalid.
   */
  final public static function sanitizeFilename($filename) {
    // @devStart
    if (empty($filename)) {
      throw new \InvalidArgumentException("A file's name cannot be empty.");
    }
    if (!is_string($filename)) {
      throw new \InvalidArgumentException("A file's name must be of type string.");
    }
    // @devEnd

    // Remove characters which aren't allowed in filenames.
    $filename = str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename);

    // Replace whitespace characters with dashes.
    $filename = preg_replace("/[\s-]+/", "-", $filename);

    // Remove characters which aren't allowed at the beginning and end of a filename.
    $filename = trim($filename, ".-_");

    // Always lowercase all filenames for better compatibility.
    return mb_strtolower($filename);
  }

}
