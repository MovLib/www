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
 * Defines the null cache object.
 *
 * This object is exactly what you'd expect, a <code>/dev/null</code> equivalent if you want, it simply does nothing.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class NullCache implements CacheCounterInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "NullCache";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function decrement($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1) {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key = null) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key = null) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1) {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function set($data, $key = null, $expire = CacheInterface::CACHE_PERMANENT) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setKey($key) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data, $expire = CacheInterface::CACHE_PERMANENT) {
    return $this;
  }

}
