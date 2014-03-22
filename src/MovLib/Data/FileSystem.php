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

use \MovLib\Data\StreamWrapper\StreamWrapperFactory;
use \MovLib\Exception\FileSystemException;

/**
 * Static methods to interact with the local filesystem.
 *
 * <b>NOTE</b><br>
 * We use strings for file modes because they are much easier to validate and handle in contrast to octal numbers. The
 * problem with the octal numbers is the fact that most people don't know that they have to include a leading zero. By
 * using strings this is no problem, because we can easily pass the modes around, preserving any leading zero, and
 * validate them with regular expressions or pass them directly to shell calls.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class FileSystem {

  /**
   * Compress file with GZIP.
   *
   * @param string $uri
   *   URI or path to the file that should be compressed.
   * @return string
   *   URI or path of the compressed file.
   * @throws \MovLib\Exception\FileSystemException
   */
  final public static function compress($uri) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($uri) || !is_string($uri)) {
      throw new \InvalidArgumentException("\$source cannot be empty and must be of type string");
    }
    if (!is_file($uri) || !is_readable($uri)) {
      throw new \LogicException("'{$uri}' file is not readable for compression");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    try {
      $scheme = parse_url($uri, PHP_URL_SCHEME);
      if ($scheme) {
        $realpath = StreamWrapperFactory::create($uri)->realpath();
      }
      else {
        $realpath = realpath($uri);
      }

      // Try to compress the file.
      Shell::execute("zopfli --gzip --ext 'gz' {$realpath}");
      $uriCompressed = "{$uri}.gz";

      // Make sure that the modification time is exactly the same (as recommended in the nginx docs).
      if (touch($uriCompressed, filemtime($uri)) === false) {
        // @codeCoverageIgnoreStart
        throw new \Exception;
        // @codeCoverageIgnoreEnd
      }
    }
    catch (\Exception $e) {
      throw new FileSystemException("Couldn't compress '{$uri}'", null, $e);
    }

    return $uriCompressed;
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
  final public static function sanitizeFilename($filename) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($filename) || is_string($filename) === false) {
      throw new \InvalidArgumentException("\$filename cannot be empty and must be of type string");
    }
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

}
