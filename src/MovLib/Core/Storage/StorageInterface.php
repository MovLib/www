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

/**
 * Defines the storage interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface StorageInterface {

  /**
   * Delete virtual file from storage.
   *
   * @param string $name
   *   The virtual file's name.
   * @return boolean
   *   <code>TRUE</code> if virtual file was deleted, <code>FALSE</code> on failure or if not allowed.
   */
  public function delete($name);

  /**
   * Delete all virtual files.
   *
   * @return boolean
   *   <code>TRUE</code> if all virtual files were deleted, <code>FALSE</code> on failure or if not allowed.
   */
  public function deleteAll();

  /**
   * Check if virtual file exists.
   *
   * @param string $name
   *   The virtual file's name to check.
   * @return boolean
   *   <code>TRUE</code> if the virtual file exists, <code>FALSE</code> otherwise.
   */
  public function exists($name);

  /**
   * Get virtual file's URI.
   *
   * <b>NOTE</b><br>
   * The file might not exist!
   *
   * @param string $name
   *   The virtual file's name.
   * @return string
   *   The virtual file's URI.
   */
  public function getURI($name);

  /**
   * List all virtual file names.
   *
   * @return array
   *   Array containing all virtual file names.
   */
  public function listAll();

  /**
   * Load virtual file's content.
   *
   * @param string $name
   *   The virtual file's content.
   * @return mixed
   *   The virtual file's content, <code>FALSE</code> if the file doesn't exist.
   */
  public function load($name);

  /**
   * Save data to virtual file.
   *
   * @param string $name
   *   The virtual file's name to save to.
   * @param mixed $data
   *   The virtual file's data to save.
   * @return boolean
   *   <code>TRUE</code> if successfully saved, <code>FALSE</code> on failure or it not allowed.
   */
  public function save($name, $data);

  /**
   * Whether this storage is writeable or not.
   *
   * @return boolean
   *   <code>TRUE</code> if this storage is writeable, <code>FALSE</code> otherwise.
   */
  public function writeable();

}
