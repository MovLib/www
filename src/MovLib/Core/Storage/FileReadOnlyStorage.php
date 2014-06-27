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

use \MovLib\Core\Intl;

/**
 * Defines the read only file storage.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FileReadOnlyStorage implements StorageInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "FileReadOnlyStorage";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The storage's bin URI.
   *
   * @var string
   */
  protected $bin;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new read only file storage.
   *
   * @param string $directory
   *   The storage's directory within the stream wrapper.
   * @param string $languageCode [optional]
   *   The storage's ISO 639-1 alpha-2 language code, defaults to <code>NULL</code> and a language independet bin is
   *   used.
   * @param string $scheme
   *   The storage's stream wrapper scheme, defaults to <code>"cache"</code>.
   * @throws \InvalidArgumentException
   *   If <var>$languageCode</var> isn't a valid system language's code.
   */
  public function __construct($directory, $languageCode = null, $scheme = "cache") {
    // @devStart
    if (empty(Intl::$systemLanguages[$languageCode])) {
      throw new \InvalidArgumentException("The language code must be a valid system language's code.");
    }
    // @devEnd
    // Build the canonical absolute URI to the storage's bin.
    $languageCode && ($languageCode = "/{$languageCode}");
    $this->bin = "{$scheme}://{$directory}{$languageCode}";
  }

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return file_exists($this->getURI($name));
  }

  /**
   * {@inheritdoc}
   */
  public function getURI($name) {
    return "{$this->bin}/{$name}";
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() {
    $names = [];
    if (file_exists($this->bin)) {
      foreach (new \DirectoryIterator($this->bin) as $fileinfo) {
        if ($fileinfo->isFile()) {
          $names[] = $fileinfo->getPathname();
        }
      }
    }
    return $names;
  }

  /**
   * {@inheritdoc}
   */
  public function load($name) {
    try {
      return file_get_contents($this->getURI($name));
    }
    catch (StreamException $e) {
      return false;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save($name, $data) {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function writeable() {
    return false;
  }

}
