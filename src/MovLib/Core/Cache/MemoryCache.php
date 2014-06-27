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
 * Defines the memory cache object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class MemoryCache implements CacheCounterInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "MemoryCache";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * Current persistent memcached connection.
   *
   * @var \Memcached
   */
  protected static $memcached;

  /**
   * Memcached default options.
   *
   * @var array
   */
  protected static $options = [
    \Memcached::OPT_BINARY_PROTOCOL      => true,
    \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
  ];

  /**
   * Memcached servers.
   *
   * @var array
   */
  protected static $servers = [
    [ "/run/memcached/server.sock", 0 ],
  ];


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The current cache key in use.
   *
   * @var null|string
   */
  protected $key;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new memory cache.
   *
   * @param string $key [optional]
   *   The cache key, defaults to <code>NULL</code> and no key is set.
   * @param string $languageCode [optional]
   *   The ISO 639-1 alpha-2 language code of the Memcached server to use, defaults to <code>NULL</code> and the
   *   language independent server is used.
   * @throws \MemcachedException
   *   If setting the default options or servers of a newly created memcached instance fails.
   */
  public function __construct($key = null, $languageCode = null) {
    $this->key = $key;
    if (!self::$memcached) {
      self::$memcached = new \Memcached("_");
      $languageCode && ($languageCode = "-{$languageCode}");
      if (self::$memcached->setOptions(self::$options) === false || self::$memcached->addServers([ "/run/memcached/server{}.sock", 0 ]) === false) {
        // @codeCoverageIgnoreStart
        // This exception isn't easily created, we'd have to execute PHPUnit with root privileges and stop the server.
        throw new \MemcachedException(self::$memcached->getResultMessage(), self::$memcached->getResultCode());
        // @codeCoverageIgnoreEnd
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * {@inheritdoc}
   */
  public function decrement($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1) {
    return $this->setKey($key)->exec("decrement", $this->key, $by, $default, $this->getExpiration($expire));
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key = null) {
    $this->setKey($key)->exec("delete", $this->key);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    $this->exec("deleteMulti", $keys);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    // Garbage collection is automatically performed by Memcached!
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key = null) {
    return $this->setKey($key)->exec("get", $this->key);
  }

  /**
   * Get the timestamp for the given expire time.
   *
   * @return mixed
   *   The timestamp for the given expire time or <var>CacheInterface::CACHE_PERMANENT</var>.
   */
  public function getExpiration($expire) {
    if ($expire === CacheInterface::CACHE_PERMANENT) {
      return CacheInterface::CACHE_PERMANENT;
    }
    $expiration = (new \DateTime())->modify("+{$expire} seconds");
    return $expiration->getTimestamp();
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
    return $this->exec("getMulti", $keys);
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1) {
    return $this->setKey($key)->exec("increment", $this->key, $by, $default, $this->getExpiration($expire));
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    $this->key = null;
    $this->exec("flush");
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function set($data, $key = null, $expire = CacheInterface::CACHE_PERMANENT) {
    $this->setKey($key)->exec("set", $this->key, $data, $this->getExpiration($expire));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setKey($key) {
    if ($key === null) {
      if ($this->key === null) {
        throw new \BadMethodCallException("No cache item key is set to work with.");
      }
    }
    else {
      $this->key = $key;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data, $expire = CacheInterface::CACHE_PERMANENT) {
    $this->exec("setMulti", $data, $this->getExpiration($expire));
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Execute memcached method and throw exception if it fails.
   *
   * @param string $method
   *   The name of the method to execute.
   * @param mixed $args [variadic]
   *   Arguments that should be passed to the method.
   * @return mixed
   *   The return value from the called method.
   * @throws \MovLib\Core\Cache\CacheException
   *   If the method returns <code>FALSE</code> or a {@see \MemcachedException} is thrown. Note that the return code
   *   is checked and even if the code is {@see \Memcached::RES_NOTFOUND} no exception is thrown.
   */
  protected function exec($method, ...$args) {
    try {
      $result = self::$memcached->$method(...$args);
      if ($result === false && ($code = self::$memcached->getResultCode()) !== \Memcached::RES_NOTFOUND) {
        throw new CacheException(self::$memcached->getResultMessage(), self::$memcached->getResultCode());
      }
      return $result;
    }
    catch (\MemcachedException $e) {
      throw new CacheException(self::$memcached->getResultMessage(), self::$memcached->getResultCode(), $e);
    }
  }

}
