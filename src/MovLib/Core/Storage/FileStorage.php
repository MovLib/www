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
namespace MovLib\Core\Storage;

use \MovLib\Core\FileSystem;

/**
 * Defines the file storage object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class FileStorage extends FileReadOnlyStorage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "FileStorage";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    $uri = $this->getURI($name);
    if (file_exists($uri)) {
      if (is_dir($uri)) {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uri, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $fileinfo) {
          if ($fileinfo->isDir()) {
            rmdir($fileinfo->getPathname());
          }
          else {
            unlink($fileinfo->getPathname());
          }
        }
        return rmdir($uri);
      }
      else {
        return unlink($uri);
      }
    }
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    return $this->delete($this->bin);
  }

  /**
   * {@inheritdoc}
   */
  public function save($name, $data) {
    $uri = $this->getURI($name);
    mkdir(dirname($uri), FileSystem::MODE_DIR, true);
    return file_put_contents($uri, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function writeable() {
    return true;
  }

}
