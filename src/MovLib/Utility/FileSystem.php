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

use \MovLib\Exception\FileSystemException;

/**
 * The <b>FileSystem</b> class provides several static utility methods to work with the local filesystem.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class FileSystem {

  /**
   * Get the operating systems specific temporary path.
   *
   * The behaviour of the default PHP function <code>sys_get_temp_dir()</code> is inconsistent regarding the last
   * character returned. This method makes sure that the path never ends with the operating systems specific directory
   * separator.
   *
   * @staticvar string $tmpDir
   *   Used to store the absolute path, to make sure the check regarding the last character does not have to be made over
   *   and over again.
   * @return string
   *   The absolute path to the operating systems specific temporary path (no trailing directory separator).
   */
  public static function getTemporaryDirectory() {
    static $tmpDir = false;
    if ($tmpDir === false) {
      $tmpDir = rtrim(sys_get_temp_dir(), "/");
    }
    return $tmpDir;
  }

  /**
    * Extends the PHP platform with a recursive glob function.
    *
    * @see glob()
    * @link http://www.php.net/manual/en/function.glob.php#87221
    * @param string $pattern
    *   The pattern. No tilde expansion or parameter substitution is done.
    * @param string $path
    *   Absolute path to the directory in which should be searched for files that match the given pattern.
    * @return array
    *   Returns an array containing the matched files/directories, an empty array if no file matched or <tt>FALSE</tt> on error.
    */
   public static function rglob($pattern, $path) {
     $files = glob($path . $pattern);
     foreach (glob("{$path}*", GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT) as $p) {
       $files = array_merge($files, rglob($pattern, $p));
     }
     return $files;
   }

  /**
   * Create a temporary copy of a file.
   *
   * @param string $source
   *   Absolute path or URL to the source file of which a temporary copy should be created.
   * @param string $fileExtension
   *   [optional] Overwrite the file extension for the temporary file, or specify one in the first place if the source
   *   path does not have an extension. Do not prepend the string with a dot, this is added automatically.
   * @return string
   *   The absolute path to the temporary copy.
   * @throws \MovLib\Exception\FileSystemException
   *   If the copy operation fails.
   * @since 0.0.1-dev
   */
  public static function temporaryCopy($source, $fileExtension = false) {
    if ($fileExtension !== false) {
      $fileExtension = pathinfo($source, PATHINFO_EXTENSION);
    }
    if (empty($fileExtension) === false) {
      $fileExtension = ".{$fileExtension}";
    }
    $tmpFilePath = FileSystem::getTemporaryDirectory() . "/" . uniqid() . $fileExtension;
    if (copy($source, $tmpFilePath) === false) {
      $source = basename($source);
      throw new FileSystemException("Could not create a temporary copy of '{$source}'.");
    }
    return $tmpFilePath;
  }

  /**
   * Recursive deletion of given path.
   *
   * @param string $path
   *   Absolute or relative path to the directory, file or symlink.
   * @return boolean
   *   <code>TRUE</code> if all files and directories have been deleted, otherwise <code>FALSE</code>.
   */
  public static function unlinkRecursive($path) {
    exec("rm -r {$path}", $output, $status);
    return $status !== 0;
  }

}
