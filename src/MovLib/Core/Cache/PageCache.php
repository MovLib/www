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
namespace MovLib\Core\Cache;

/**
 * Defines the page cache object.
 *
 * The page cache allows to store complete presentation page's on disk for nginx.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PageCache implements CacheInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "PageCache";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The cache's compressor to compress cached pages.
   *
   * @var \MovLib\Core\Compressor\CompressorInterface
   */
  protected $compressor;

  /**
   * The cache's key of the current page.
   *
   * @var string
   */
  protected $key;

  /**
   * The cache's storage to store cached pages.
   *
   * @var \MovLib\Core\Storage\StorageInterface
   */
  protected $storage;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new page cache object.
   *
   * @param \MovLib\Core\Compressor\CompressorInterface $compressor
   *   The page cache's compressor to compress the pages.
   * @param \MovLib\Core\Storage\StorageInterface $storage
   *   The page cache's storage to store the pages.
   * @param string $key [optional]
   *   Set cache key for the page that should be cached.
   */
  public function __construct(\MovLib\Core\Compressor\CompressorInterface $compressor, \MovLib\Core\Storage\StorageInterface $storage, $key = null) {
    $this->compressor = $compressor;
    $this->storage    = $storage;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function delete($key = null) {
    $this->setKey($key);
    $this->storage->delete($this->key);
    $this->storage->delete($this->compressor->getURI($this->key));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    foreach ($keys as $key) {
      $this->delete($key);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    // Nothing to do!
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key = null) {
    $this->setKey($key);
    return $this->storage->load($this->key);
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    $pages = [];
    foreach ($keys as $key) {
      $pages[] = $this->get($key);
    }
    return $pages;
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    $this->storage->deleteAll();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function set($data, $key = null, $expire = CacheInterface::CACHE_PERMANENT) {
    $this->setKey($key);
    $this->storage->save($this->key, $data);
    $this->compressor->compressFile($this->storage->getURI($this->key));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setKey($key) {
    if (isset($key)) {
      // @devStart
      if (empty($key)) {
        throw new \InvalidArgumentException("A page cache's key cannot be empty.");
      }
      // @devEnd
      $this->key = $key;
    }
    // @devStart
    elseif ($this->key === null) {
      throw new \BadMethodCallException("No page cache key to work with.");
    }
    // @devEnd
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data, $expire = CacheInterface::CACHE_PERMANENT) {
    foreach ($data as $key => $page) {
      $this->set($page, $key);
    }
    return $this;
  }

}
