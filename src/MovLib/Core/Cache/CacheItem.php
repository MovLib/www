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

use \MovLib\Component\DateTime;

/**
 * Defines the cache item object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CacheItem {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CacheItem";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The cache item's creation date and time.
   *
   * @var \MovLib\Component\DateTime
   */
  public $created;

  /**
   * The cache item's data.
   *
   * @var mixed
   */
  public $data;

  /**
   * The cache item's expiration date and time.
   *
   * @var \MovLib\Component\DateTime|null
   */
  protected $expire = CacheInterface::CACHE_PERMANENT;

  /**
   * The cache item's unique key.
   *
   * @var string
   */
  public $key;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new cache item.
   *
   * @param string $key
   *   The cache item's unique key.
   * @param mixed $data
   *   The cache item's data.
   * @param integer $expire [optional]
   *   The cache item's expire time in seconds from now, defaults to <code>CacheInterface::CACHE_PERMANENT</code>.
   */
  public function __construct($key, $data, $expire = CacheInterface::CACHE_PERMANENT) {
    $this->key     = $key;
    $this->data    = $data;
    $this->created = new DateTime();
    if ($expire !== CacheInterface::CACHE_PERMANENT) {
      $this->expire = (new DateTime())->modify("{$expire} seconds");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Whether this cache item is expired or not.
   *
   * @return boolean
   *   <code>TRUE</code> if expired otherwise <code>FALSE</code>.
   */
  public function expired() {
    // Note that using the native date and time object is enough at this point.
    if ($this->expire === CacheInterface::CACHE_PERMANENT) {
      return false;
    }
    return (boolean) ($this->expire <= new \DateTime());
  }

}
