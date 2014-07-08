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
   * Connections to the memory cache servers.
   *
   * @var array
   */
  protected static $connections = [];

  /**
   * Memcached default options.
   *
   * @var array
   */
  protected static $options = [
    \Memcached::OPT_BINARY_PROTOCOL      => true,
    \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
  ];


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The current memory cache server connection.
   *
   * @var \Memcached
   */
  protected $connection;

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
   * @param string $languageCode [optional]
   *   The ISO 639-1 alpha-2 language code of the Memcached server to use, defaults to <code>NULL</code> and the
   *   language independent server is used.
   * @param string $key [optional]
   *   The cache key, defaults to <code>NULL</code> and no key is set.
   * @throws \MemcachedException
   *   If setting the default options or servers of a newly created memcached instance fails.
   */
  public function __construct($languageCode = null, $key = null) {
    // Create a new connection if we have none for this language.
    if (empty(self::$connections[$languageCode])) {
      self::$connections[$languageCode] = new \Memcached($languageCode ?: "_");
      $socket = $languageCode ? "-{$languageCode}" : null;
      if (self::$connections[$languageCode]->setOptions(self::$options) === false || self::$connections[$languageCode]->addServer("/run/memcached/server{$socket}.sock", 0) === false) {
        // @codeCoverageIgnoreStart
        // This exception isn't easily created, we'd have to execute PHPUnit with root privileges and stop the server.
        throw new \MemcachedException(self::$connections[$languageCode]->getResultMessage(), self::$connections[$languageCode]->getResultCode());
        // @codeCoverageIgnoreEnd
      }
    }
    $this->connection = self::$connections[$languageCode];
    $this->key        = $key;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * {@inheritdoc}
   */
  public function decrement($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1) {
    $this->setKey($key);
    if (($result = $this->connection->decrement($this->key, $by, $default, $this->getExpiration($expire))) === false) {
      throw new CacheException($this->connection->getResultMessage(), $this->connection->getResultCode());
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key = null) {
    $this->setKey($key);
    if ($this->connection->delete($this->key) === false && ($code = $this->connection->getResultCode()) !== \Memcached::RES_NOTFOUND) {
      throw new CacheException($this->connection->getResultMessage(), $code);
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
    // Garbage collection is automatically performed by Memcached!
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key = null) {
    $this->setKey($key);
    if (($data = $this->connection->get($this->key)) === false) {
      // We want to return NULL in case the key doesn't exist.
      if (($code = $this->connection->getResultCode()) === \Memcached::RES_NOTFOUND) {
        return;
      }
      throw new CacheException($this->connection->getResultMessage(), $code);
    }
    return $data;
  }

  /**
   * Get the timestamp for the given expire time.
   *
   * @return mixed
   *   The timestamp for the given expire time or <var>CacheInterface::CACHE_PERMANENT</var>.
   */
  protected function getExpiration($expire) {
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
    $data = [];
    foreach ($keys as $key) {
      if (($value = $this->get($key))) {
        $data[$key] = $value;
      }
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key = null, $default = 0, $expire = CacheInterface::CACHE_PERMANENT, $by = 1) {
    $this->setKey($key);
    if (($result = $this->connection->increment($key, $by, $default, $this->getExpiration($expire))) === false) {
      throw new CacheException($this->connection->getResultMessage(), $this->connection->getResultCode());
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    $this->key = null;
    if ($this->connection->flush() === false) {
      throw new CacheException($this->connection->getResultMessage(), $this->connection->getResultCode());
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function set($data, $key = null, $expire = CacheInterface::CACHE_PERMANENT) {
    $this->setKey($key);
    if ($this->connection->set($this->key, $data, $this->getExpiration($expire)) === false) {
      throw new CacheException($this->connection->getResultMessage(), $this->connection->getResultCode());
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
  public function setMultiple(array $data, $expire = CacheInterface::CACHE_PERMANENT) {
    foreach ($data as $key => $value) {
      $this->set($value, $key, $expire);
    }
    return $this;
  }

}
