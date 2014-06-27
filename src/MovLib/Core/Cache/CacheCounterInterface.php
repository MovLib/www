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
 * Defines the cache counter interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface CacheCounterInterface extends CacheInterface {

  /**
   * Increment cache item's numeric data.
   *
   * @param string $key [optional]
   *   The cache item's key to increment, defaults to <code>NULL</code> and the current key is used.
   * @param mixed $default [optional]
   *   The cache item's default data if not set, defaults to <code>0</code>.
   * @param integer $expire [optional]
   *   The cache item's expiration time in seconds from now, defaults to <code>CacheInterface::CACHE_PERMANENT</code>.
   * @param mixed $by [optional]
   *   How much to add to the cache item's existing value, defaults to <code>1</code>.
   * @return mixed
   *   The cache item's new value.
   * @throws \MovLib\Core\Cache\CacheException
   *   If incrementing fails.
   */
  public function increment($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1);

  /**
   * Decrement cache item's numeric data.
   *
   * @param string $key [optional]
   *   The cache item's key to decrement, defaults to <code>NULL</code> and the current key is used.
   * @param mixed $default [optional]
   *   The cache item's default data if not set, defaults to <code>0</code>.
   * @param integer $expire [optional]
   *   The cache item's expiration time in seconds from now, defaults to <code>CacheInterface::CACHE_PERMANENT</code>.
   * @param mixed $by [optional]
   *   How much to substracted from the cache item's existing value, defaults to <code>1</code>.
   * @return mixed
   *   The cache item's new value.
   * @throws \MovLib\Core\Cache\CacheException
   *   If decrementing fails.
   */
  public function decrement($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1);

}
