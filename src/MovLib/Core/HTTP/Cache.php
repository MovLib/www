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
namespace MovLib\Core\HTTP;

use \MovLib\Core\Log;

/**
 * Defines the interface to the persistent disk cache for presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Cache {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Cache";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * URI to the cache file of the current presentation.
   *
   * @var string
   */
  protected $uri;

  /**
   * URI to the compressed cache file of the current presentation.
   *
   * @var string
   */
  protected $uriCompressed;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new cache instance.
   *
   */
  public function __construct() {
    $this->uri = "dr://var/cache/{$i18n->languageCode}{$request->path}";
    if ($request->path == "/") {
      $this->uri .= $_SERVER["PRESENTER"];
    }
    $this->uriCompressed = "{$this->uri}.gz";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Whether a cached version for this request/response exists or not.
   *
   * @return boolean
   *   <code>TRUE</code> if a cached version exists, <code>FALSE</code> otherwise.
   */
  public function isCache() {
    return is_file($this->uri);
  }

  /**
   * Delete the cache entry for this request/response.
   *
   * $return this
   */
  public function delete() {
    if (is_file($this->uri)) {
      unlink($this->uri);
    }
    if (is_file($this->uriCompressed)) {
      unlink($this->uriCompressed);
    }
    return $this;
  }

  /**
   * Delete multiple cache entries.
   *
   * @return this
   */
  public function deleteMultiple() {
    // @todo It has to be possible to delete multiple cache entries after resources change that are used globally. For
    //       instance if one creates a movie, almost all cache files for listings that list movies have to be purged
    //       from disk.
    //
    //       This method should call another method that is executed after the response was sent to the client (delayed).
    return $this;
  }

  /**
   * Save given presentation to persistent cache.
   *
   * @param string $presentation
   *   The presentation to cache.
   * @return this
   */
  public function save($presentation) {
    if ($response->cacheable && $_SERVER["REQEUEST_METHOD"] == "GET" && !$session->isAuthenticated && !is_file($this->uri)) {
      try {
        $tmp           = "dr://tmp/" . basename($this->uri) . $_SERVER["REQUEST_TIME_FLOAT"];
        $dir           = dirname($this->uri);
        mkdir($dir, 0775, true);
        file_put_contents($tmp, "{$presentation}<!--{$_SERVER["REQUEST_TIME_FLOAT"]}-->");
        $tmpCompressed = $fs->compress($tmp);
        rename($tmp, $this->uri);
        rename($tmpCompressed, $this->uriCompressed);
      }
      catch (\Exception $e) {
        Log::error($e);
      }
    }
    return $this;
  }

}
