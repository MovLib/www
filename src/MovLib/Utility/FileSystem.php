<?php

/* !
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
   * <b>ATTENTION!</b> If the given path (or any of the containing directories, files or symbolic links) is not readable
   * for the PHP user this method generates a
   * {@link http://php.net/manual/en/errorfunc.constants.php <b><code>E_WARNING</code></b>} level error message. As per
   * default implementation of the functions {@link http://php.net/manual/en/function.rmdir.php <code>rmdir</code>} and
   * {@link http://php.net/manual/en/function.unlink.php <code>unlink</code>}.
   *
   * @param string $path
   *   Absolute or relative path to the directory, file or symlink.
   * @return boolean
   *   <code>TRUE</code> if all files and directories have been deleted. <code>FALSE</code> on error or if the given
   *   path is not valid.
   * @since 0.0.1-dev
   */
  static public function unlinkRecursive($path) {
    // Check if a path was given at all and if the path is on our local filesystem and exists.
    if ($path && realpath($path)) {
      if (is_dir($path)) {
        foreach (new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
          \RecursiveIteratorIterator::CHILD_FIRST
        ) as $path) {
          $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        return rmdir($path);
      }
      elseif (is_file($path) || is_link($path)) {
        return unlink($path);
      }
    }
    return FALSE;
  }

}
