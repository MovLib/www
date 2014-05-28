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
namespace MovLib\Core;

/**
 * Represents connection to memcached server.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Memcached {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Memcached";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The default expiration time (in seconds).
   *
   * @var integer
   */
  const DEFAULT_EXPIRATION = 3600;

  /**
   * The maximum amount an event can be executed from an IP.
   *
   * @var integer
   */
  const FLOODING_IP_MAX = 50;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The active logger instance.
   *
   * @var \MovLib\Core\Log
   */
  protected $log;

  /**
   * The current persistent memcached connection.
   *
   * @var \Memcached
   */
  protected static $memcached;

  /**
   * Numeric array containing all memcached options.
   *
   * @var array
   */
  protected static $options = [
    \Memcached::OPT_BINARY_PROTOCOL      => true,
    \Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
  ];

  /**
   * Numeric array containing all available memcached servers.
   *
   * @var array
   */
  protected static $servers = [
    [ "/run/memcached/server1.sock", 0 ],
  ];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Memcached connection.
   *
   * @internal
   *   No clue how it's possible to get to this exception via PHPUnit, setting to ignore because it seems this will
   *   never happen (and if it does, we'll know how to test it).
   * @param \MovLib\Core\Log $logger
   *   The active logger instance.
   */
  public function __construct(\MovLib\Core\Log $log) {
    $this->log = $log;
    if (!self::$memcached) {
      self::$memcached = new \Memcached("_");
      if (self::$memcached->setOptions(self::$options) === false || self::$memcached->addServers(self::$servers) === false) {
        // @codeCoverageIgnoreStart
        $e = new \MemcachedException(self::$memcached->getResultMessage(), self::$memcached->getResultCode());
        $this->log->critical($e);
        throw $e;
        // @codeCoverageIgnoreEnd
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Delete key from memcached.
   *
   * @param mixed $key
   *   The key to delete.
   * @return $this
   * @throws \MemcachedException
   */
  public function delete($key) {
    if (self::$memcached->delete($key) === false && ($code = self::$memcached->getResultCode()) !== \Memcached::RES_NOTFOUND) {
      // @codeCoverageIgnoreStart
      $e = new \MemcachedException(self::$memcached->getResultMessage(), $code);
      $this->log->critical($e);
      throw $e;
      // @codeCoverageIgnoreEnd
    }
    return $this;
  }

  /**
   * Retrieve a cache item.
   *
   * @param string $key
   *   The key of the item to retrieve.
   * @return mixed
   *   The item that was previously stored under <var>$key</var> or <code>FALSE</code> if the <var>$key</var> wasn't
   *   found.
   * @throws \MemcachedException
   */
  public function get($key) {
    $value = self::$memcached->get($key);
    if ($value === false && ($code = self::$memcached->getResultCode()) !== \Memcached::RES_NOTFOUND) {
      // @codeCoverageIgnoreStart
      $e = new \MemcachedException(self::$memcached->getResultMessage(), $code);
      $this->log->critical($e);
      throw $e;
      // @codeCoverageIgnoreEnd
    }
    return $value;
  }

  /**
   * Increment numeric item's value.
   *
   * @param mixed $key
   *   They key of the item to increment.
   * @param integer $initialValue [optional]
   *   The value to set the item to if it doesn't currently exist, defaults to <code>1</code>.
   * @param integer $expiration [optional]
   *   The expiration time in seconds, defaults to {@see Memcached::DEFAULT_EXPIRATION}.
   * @param integer $incrementBy [optional]
   *   The amount by which to increment the item's value, defaults to <code>1</code>.
   * @return integer
   *   The new item's value on success or <code>FALSE</code> on failure.
   * @throws \MemcachedException
   */
  public function increment($key, $initialValue = 1, $expiration = self::DEFAULT_EXPIRATION, $incrementBy = 1) {
    $value = self::$memcached->increment($key, $incrementBy, $initialValue, $expiration);
    if ($value === false) {
      // @codeCoverageIgnoreStart
      $e = new \MemcachedException(self::$memcached->getResultMessage(), self::$memcached->getResultCode());
      $this->log->critical($e);
      throw $e;
      // @codeCoverageIgnoreEnd
    }
    return $value;
  }

  /**
   * Check if an event is executed too often.
   *
   * @param mixed $key
   *   The key to identify the executed event.
   * @param integer $max
   *   The maximum amount of times this event can be executed.
   * @param integer $expiration [optional]
   *   The expiration time in seconds, defaults to {@see Memcached::DEFAULT_EXPIRATION}.
   * @return boolean
   *   Returns <code>TRUE</code> if amount exceeds <var>$max</var>, otherwise <code>FALSE</code>.
   * @throws \MemcachedException
   */
  public function isFlooding($key, $max, $expiration = self::DEFAULT_EXPIRATION) {
    return ($this->increment($key, 1, $expiration) > $max);
  }

  /**
   * Check if this IP attempted to execute an event too many times.
   *
   * @param string $remoteAddress
   *   The remote address to check.
   * @param string $event
   *   The event identifier (e.g. presentation identifier, <code>$presentation->id</code>).
   * @return this
   * @throws \MemcachedException
   */
  public function isRemoteAddressFlooding($remoteAddress, $event) {
    if ($this->isFlooding("{$event}{$remoteAddress}", self::FLOODING_IP_MAX) === true) {
      $e = new \MemcachedException("Flooding: too many attempts to invoke event from remote address.");
      $this->log->warning($e, [ "remoteAddress" => $remoteAddress ]);
      throw $e;
    }
    return $this;
  }

  /**
   * Set new memcached entry.
   *
   * @param mixed $key
   *   The entry's key.
   * @param mixed $value
   *   The entry's value.
   * @param integer $expiration [optional]
   *   The expiration time in seconds, defaults to {@see Memcached::DEFAULT_EXPIRATION}.
   * @return this
   * @throws \MemcachedException
   */
  public function set($key, $value, $expiration = self::DEFAULT_EXPIRATION) {
    if (self::$memcached->set($key, $value, $expiration) === false) {
      // @codeCoverageIgnoreStart
      $e = new \MemcachedException(self::$memcached->getResultMessage(), self::$memcached->getResultCode());
      $this->log->critical($e);
      throw $e;
      // @codeCoverageIgnoreEnd
    }
    return $this;
  }

}
