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

/**
 * Delete all directories and files within this directory.
 *
 * The behaviour of this function is equivalent to the <code>rm -rf</code> Linux command.
 *
 * @param string $directory
 *   Absolute or relative path to directory.
 * @return void
 * @since 0.0.1-dev
 */
function unlink_recursive($directory) {
  foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
    $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
  }
  rmdir($directory);
}
