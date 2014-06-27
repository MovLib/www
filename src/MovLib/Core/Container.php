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

use \MovLib\Core\Cache\MemoryCache;
use \MovLib\Core\Cache\PHPCache;
use \MovLib\Core\Cache\PageCache;
use \MovLib\Core\Compressor\ZopfliCompressor;
use \MovLib\Core\Storage\FileStorage;

/**
 * Defines the dependency injection container object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Container {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Container";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Active global configuration instance.
   *
   * @var \MovLib\Core\Config
   */
  public $config;

  /**
   * Active file system instance.
   *
   * @var \MovLib\Core\FileSystem
   */
  public $fs;

  /**
   * Active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  public $intl;

  /**
   * Active kernel instance.
   *
   * @var \MovLib\Core\Kernel
   */
  public $kernel;

  /**
   * Active logger instance.
   *
   * @var \MovLib\Core\Log
   */
  public $log;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get compressor.
   *
   * @return \MovLib\Core\Compressor\CompressorInterface
   *   Compressor.
   */
  public function getCompressor() {
    return new ZopfliCompressor();
  }

  /**
   * Get new page cache for the current language.
   *
   * @param string $key [optional]
   *   The page cache item's key to set, default to <code>NULL</code>.
   * @return \MovLib\Core\Cache\CacheInterface
   *   The page cache.
   */
  public function getPageCache($key = null) {
    return new PageCache($this->getCompressor(), new FileStorage("page", $this->intl->code), $key);
  }

  /**
   * Get new persistent cache for the current language.
   *
   * @param string $key [optional]
   *   The cache item's key to set, defaults to <code>NULL</code>.
   * @return \MovLib\Core\Cache\CacheInterface
   *   New persistent cache.
   */
  public function getPersistentCache($key = null) {
    return new PHPCache(new FileStorage("php", $this->intl->code), $key);
  }

  /**
   * Get new volatile cache instance for the current language.
   *
   * @param string $key [optional]
   *   The cache item's key to set, defaults to <code>NULL</code>
   * @return \MovLib\Core\Cache\CacheCounterInterface
   *   New volatile cache.
   */
  public function getVolatileCache($key = null) {
    return new MemoryCache($this->intl->code, $key);
  }

}
