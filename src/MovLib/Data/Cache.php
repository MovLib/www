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
namespace MovLib\Data;

use \MovLib\Data\FileSystem;

/**
 * Interact with the persistent disc cache.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cache {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Absolute path to the cache file of the current request.
   *
   * @var string
   */
  protected $cacheFile;

  /**
   * Indicates whether this request is cacheable or not.
   *
   * @var boolean
   */
  public $cacheable = true;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new cache instance.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->cacheFile = "{$kernel->documentRoot}/var/cache/{$i18n->languageCode}{$kernel->requestPath}";
    if ($kernel->requestPath == "/") {
      $this->cacheFile .= $_SERVER["PRESENTER"];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Check if the current request has a cached version.
   *
   * @return boolean
   *   <code>TRUE</code> if a cached version exists, otherwise <code>FALSE</code>.
   */
  public function cached() {
    return is_file($this->cacheFile);
  }

  /**
   * Delete cached version of current presentation.
   *
   * @param string $cacheFile [optional]
   *   Absolute path to the cache file that should be deleted, defaults to the current cache file property of this
   *   instance. Note that the compressed version of the cached file is deleted as well.
   * @return this
   */
  public function delete($cacheFile = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($cacheFile) && (empty($cacheFile) || !is_string($cacheFile))) {
      throw new \InvalidArgumentException("\$cacheFile cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (!$cacheFile) {
      $cacheFile = $this->cacheFile;
    }
    if (is_file($cacheFile) === true) {
      try {
        FileSystem::delete($cacheFile);
        FileSystem::delete("{$cacheFile}.gz");
      }
      catch (\RuntimeException $e) {
        Log::error($e);
      }
    }
    return $this;
  }

  /**
   * Save new cached version of current presentation.
   *
   * @global \MovLib\Data\User\Session $session
   * @param string $presentation
   *   The complete presentation to cache.
   * @return this
   */
  public function save($presentation) {
    global $session;
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($presentation)) {
      return $this;
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if ($this->cacheable === true && $session->isAuthenticated === false && $this->cached() === false) {
      try {
        FileSystem::createDirectory(dirname($this->cacheFile));
        FileSystem::putContent($this->cacheFile, "{$presentation}<!--{$_SERVER["REQUEST_TIME_FLOAT"]}-->", LOCK_EX);
        FileSystem::compress($this->cacheFile);
      }
      catch (\RuntimeException $e) {
        Log::error($e);
      }
    }
    return $this;
  }

}
