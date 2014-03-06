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
 * Provides methods for interacting with the file system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitFileSystem {


  // ------------------------------------------------------------------------------------------------------------------- Methods

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
  protected final function sanitizeFilename($filename) {
    // Remove special characters which aren't allowed in many operating systems from the file's name.
    $filename = str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename);

    // Replace whitespace characters with dashes.
    $filename = preg_replace("/[\s-]+/", "-", $filename);

    // Remove certain punctuation from the beginning and end of the file's name.
    $filename = trim($filename, ".-_");

    // We are always using lowercased file names.
    $filename = mb_strtolower($filename);

    return $filename;
  }

  /**
   * Read entire file into string.
   *
   * @see file_put_contents()
   * @param string $filename
   *   Absolute path of the file to read.
   * @return string
   *   The file's content as string.
   * @throws \Exception
   * @throws \RuntimeException
   */
  protected final function fileGetContents($filename) {
    if (($content = file_get_contents($filename)) === false) {
      throw new \RuntimeException;
    }
    return $content;
  }

  /**
   * Write string to file.
   *
   * @see file_put_contents()
   * @global \MovLib\Kernel $kernel
   * @param string $filename
   * @param string|array $data
   * @param integer $flags
   * @param integer $mode [optional]
   *   The umask that should be applied to the file, defaults to <code>2664</code>.
   * @param string $user [optional]
   *   The user who should own the file.
   * @param string $group [optional]
   *   The group who should own the file.
   * @return $this
   * @throws \Exception
   * @throws \RuntimeException
   */
  protected final function filePutContents($filename, $data, $flags = 0, $mode = 2664, $user = null, $group = null) {
    global $kernel;

    // Try to write the data to file.
    if (file_put_contents($filename, $data, $flags) === false) {
      throw new \RuntimeException;
    }

    // Ensure that the correct file mode is set.
    chmod($filename, $mode);

    // We can't be certain that the file has the correct user and group if we aren't running as php-fpm process.
    if ($kernel->fastCGI === false) {
      if (empty($user) && !empty($kernel->systemUser)) {
        $user = $kernel->systemUser;
      }
      if (!empty($user)) {
        chown($filename, $user);
      }
      if (empty($group) && !empty($kernel->systemGroup)) {
        $group = $kernel->systemGroup;
      }
      if (!empty($group)) {
        chgrp($filename, $group);
      }
    }

    return $this;
  }

}
