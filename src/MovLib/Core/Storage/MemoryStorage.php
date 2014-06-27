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
 * Defines the memory storage.
 *
 * The memory storage will only store data for a single request or process, this is very useful for unit tests.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class MemoryStorage implements StorageInterface {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Bin used to store the data in memory.
   *
   * @var array
   */
  protected $bin = [];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    if (isset($this->bin[$name])) {
      unset($this->bin[$name]);
    }
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    $this->bin = [];
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return isset($this->bin[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function getURI($name) {
    return $name;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    return array_keys($this->bin);
  }

  /**
   * {@inheritdoc}
   */
  public function load($name) {
    if (isset($this->bin[$name])) {
      return $this->bin[$name];
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function save($name, $data) {
    $this->bin[$name] = $data;
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function writeable() {
    return true;
  }

}
