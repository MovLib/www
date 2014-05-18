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

/**
 * @todo Description of functions
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/**
 * Encode URL path preserving slashes.
 *
 * @param string $path
 *   The URL path to encode.
 * @return string
 *   The encoded URL path.
 */
function url_encode_path($path) {
  if (empty($path) || $path == "/") {
    return $path;
  }
  return str_replace("%2F", "/", rawurlencode($path));
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
function sanitize_filename($filename) {
  // @devStart
  // @codeCoverageIgnoreStart
  assert(!empty($filename) && is_string($filename), "\$filename cannot be empty and must be of type string.");
  // @codeCoverageIgnoreEnd
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
