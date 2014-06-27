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
 * Defines the cache interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface CacheInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The cache key and its associated data never expires.
   *
   * @var null
   */
  const CACHE_PERMANENT = null;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete cache item.
   *
   * @param string $key [optional]
   *   The cache item's key to delete, defaults to <code>NULL</code> and the current key will be used.
   * @return this
   * @throws \BadMethodCallException
   *   If <var>$key</var> is <code>NULL</code> and no key is set.
   * @throws \MovLib\Core\Cache\CacheException
   *   If deletion fails.
   */
  public function delete($key = null);

  /**
   * Delete multiple cache items.
   *
   * @param array $keys
   *   An array of cache keys to delete.
   * @return this
   * @throws \MovLib\Core\Cache\CacheException
   *   If deletion fails.
   */
  public function deleteMultiple(array $keys);

  /**
   * Collect garbage.
   *
   * The concrete class should delete all expired cache items.
   *
   * @return this
   * @throws \MovLib\Core\Cache\CacheException
   *   If garbage collection fails.
   */
  public function garbageCollection();

  /**
   * Get cached item's data.
   *
   * @param string $key [optional]
   *   The cache item's key to get the data for, defaults to <code>NULL</code> and the current key will be used.
   * @return mixed
   *   The cache item's data or <code>NULL</code> if no data was found.
   * @throws \BadMethodCallException
   *   If <var>$key</var> is <code>NULL</code> and no current key is available.
   * @throws \MovLib\Core\Cache\CacheException
   *   If getting fails.
   */
  public function get($key = null);

  /**
   * Get the current cache key.
   *
   * @return null|string
   *   The current cache key or <code>NULL</code> if no cache key is set.
   */
  public function getKey();

  /**
   * Get multiple cache items data.
   *
   * @param array $keys
   *   An array of cache keys to get the data for.
   * @return array
   *   Array containing the data for the cached keys.
   * @throws \MovLib\Core\Cache\CacheException
   *   If getting fails.
   */
  public function getMultiple(array $keys);

  /**
   * Purge the complete cache.
   *
   * @return this
   * @throws \MovLib\Core\Cache\CacheException
   *   If purging fails.
   */
  public function purge();

  /**
   * Set cache item's data.
   *
   * @param mixed $data
   *   The cache item's data to set.
   * @param string $key [optional]
   *   The cache item's key to set the data for, defaults to <code>NULL</code> and the current key will be used.
   * @param integer $expire [optional]
   *   The cache item's expiration time in seconds from now, defaults to <code>CacheInterface::CACHE_PERMANENT</code>.
   * @return this
   * @throws \BadMethodCallException
   *   If <var>$key</var> is <code>NULL</code> and no key is set.
   * @throws \MovLib\Core\Cache\CacheException
   *   If setting fails.
   */
  public function set($data, $key = null, $expire = CacheInterface::CACHE_PERMANENT);

  /**
   * Set the cache key.
   *
   * @param string $key
   *   The cache key to set.
   * @return this
   * @throws \InvalidArgumentException
   *   If <var>$key</var> is not valid.
   */
  public function setKey($key);

  /**
   * Set multiple cache items.
   *
   * @param array $data
   *   An array of cache item key-data pairs to cache.
   * @param integer $expire [optional]
   *   The cache item's expiration time in seconds from now, defaults to <code>CacheInterface::CACHE_PERMANENT</code>.
   * @return this
   * @throws \MovLib\Core\Cache\CacheException
   *   If setting fails.
   */
  public function setMultiple(array $data, $expire = CacheInterface::CACHE_PERMANENT);

}
