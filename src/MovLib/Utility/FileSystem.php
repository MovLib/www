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

use \FilesystemIterator;
use \MovLib\Exception\FileSystemException;
use \MovLib\Utility\HTTP;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

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
   * Get absolute URI to static file from the assets.
   *
   * @param string $path
   *   The path within the assets directory.
   * @return string
   *   Absolute URI to the file.
   */
  public static function asset($path) {
    return "https://" . HTTP::SERVER_NAME_STATIC . "/{$path}";
  }

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
   * Delete directory, file or symbolic link.
   *
   * The behaviour of this method is equivalent to the <tt>rm -rf</tt> Linux command.
   *
   * If the passed path is a valid local directory this method will delete the contents of the directory before
   * deleting the directory itself. The default {@link http://php.net/manual/en/function.rmdir.php <code>rmdir</code>}
   * function only deletes a directory if it is empty.
   *
   * If the passed path is a local symbolic link only the symbolic link will be deleted. If the symbolic link points to
   * a valid local directory or file, both will stay untouched.
   *
   * @todo Should the exception be more specific about which file could not be deleted? Or is it sufficient and the
   *       developer has to go to the directory, file or symbolic link himself to check what the problem is?
   * @param string $path
   *   Absolute or relative path to the directory, file or symlink.
   * @return boolean
   *   <code>true</code> if all files and directories have been deleted. <code>false</code> on error or if the given
   *   path is not valid.
   * @throws \MovLib\Exception\FileSystemException
   *   If any of the delete actions fails (e.g. wrong permissions).
   * @since 0.0.1-dev
   */
  public static function unlinkRecursive($path) {
    $return = false;
    $exceptionMessage = "Could not delete given directory, file or symbolic link: '{$path}'";
    // Check if a path was given at all and if the path is on our local filesystem and exists.
    if (empty($path) === false && realpath($path) === true && file_exists($path) === true) {
      if (is_dir($path) === true) {
        /* @var $tmpPath \DirectoryIterator */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $tmpPath) {
          if ($tmpPath->isDir() === true) {
            $return = rmdir($tmpPath->getPathname());
          }
          elseif ($tmpPath->isFile() === true || $tmpPath->isLink() === true) {
            $return = unlink($tmpPath->getPathname());
          }
          // Check each iteration if something went wrong.
          if ($return === false) {
            throw new FileSystemException($exceptionMessage);
          }
        }
        // Above loop will not delete the directory itself, this is the last step.
        $return = rmdir($path);
      }
      elseif (is_file($path) === true || is_link($path) === true) {
        $return = unlink($path);
      }
    }
    if ($return === false) {
      throw new FileSystemException($exceptionMessage);
    }
    return $return;
  }

}
