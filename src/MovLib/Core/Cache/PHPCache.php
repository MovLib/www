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
 * Defines the PHP cache object.
 *
 * The PHP cache allows to store arbitrary data structures that are automatically serialized and unserialized. This
 * cache features very good performance if PHP's OPCache is activated because frequently used data will be stored in
 * memory.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PHPCache implements CacheInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "PHPCache";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Current cache item's key.
   *
   * @var sring
   */
  protected $key;

  /**
   * Storage to store the cached items.
   *
   * @var \MovLib\Core\Storage\StorageInterface
   */
  protected $storage;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new disk cache object.
   *
   * @param \MovLib\Core\Storage\StorageInterface $storage
   *   Storage to store the cached items.
   * @param string $key [optional]
   *   The cache item's key, defaults to <code>NULL</code>. This allows you to preconfigure the key.
   * @throws \InvalidArgumentException
   *   If the passed language code is not a valid system language's code.
   */
  public function __construct(\MovLib\Core\Storage\StorageInterface $storage, $key = null) {
    $this->storage = $storage;
    $key && $this->setKey($key);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function delete($key = null) {
    $this->setKey($key);
    if ($this->storage->delete($this->key) === false) {
      throw new CacheException("Couldn't delete cache item for key: {$this->key}");
    }
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
    foreach ($this->storage->listAll() as $key) {
      $this->getCacheItem($key);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key = null) {
    if (($item = $this->getCacheItem($key))) {
      return $item->data;
    }
  }

  /**
   * Get cache item.
   *
   * @see ::get
   * @param string $key [optional]
   *   The cache item's key to get the data for, defaults to <code>NULL</code> and the current key will be used.
   * @return \MovLib\Core\Cache\CacheItem|null
   *   The cache item or <code>NULL</code> if no data was found.
   * @throws \BadMethodCallException
   *   If <var>$key</var> is <code>NULL</code> and no current key is available.
   * @throws \MovLib\Core\Cache\CacheException
   *   If getting fails.
   */
  public function getCacheItem($key = null) {
    $this->setKey($key);
    if (($item = $this->storage->load($this->key))) {
      $item = unserialize($item);
      if ($item->expired() === true) {
        $this->delete();
      }
      else {
        return $item;
      }
    }
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
    $items = [];
    foreach ($keys as $key) {
      if (($data = $this->get($key))) {
        $items[$key] = $data;
      }
    }
    return $items;
  }

  /**
   * Get cache items.
   *
   * @see ::getMultiple
   * @param array $keys
   *   An array of cache keys to get the data for.
   * @return array
   *   Array containing the data for the cached keys.
   * @throws \MovLib\Core\Cache\CacheException
   *   If getting fails.
   */
  public function getMultipleCacheItems(array $keys) {
    $items = [];
    foreach ($keys as $key) {
      if (($item = $this->getCacheItem($key))) {
        $items[$key] = $item;
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    if ($this->storage->deleteAll() === false) {
      throw new CacheException("Cache purge failed.");
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function set($data, $key = null, $expire = CacheInterface::CACHE_PERMANENT) {
    $this->setKey($key);
    if ($this->storage->save($this->key, serialize(new CacheItem($this->key, $data, $expire))) === false) {
      throw new CacheException("Saving data failed.");
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setKey($key) {
    if (isset($key)) {
      // @devStart
      if (empty($key)) {
        throw new \InvalidArgumentException("A cache item's key cannot be empty.");
      }
      if (\MovLib\Component\String::sanitizeFilename($key) !== $key) {
        throw new \InvalidArgumentException("The cache item's key contains invalid characters.");
      }
      // @devEnd
      $this->key = $key;
    }
    // @devStart
    elseif ($this->key === null) {
      throw new \BadMethodCallException("No cache item key is set to work with.");
    }
    // @devEnd
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $items, $expire = CacheInterface::CACHE_PERMANENT) {
    foreach ($items as $key => $data) {
      $this->set($data, $key, $expire);
    }
    return $this;
  }

}
