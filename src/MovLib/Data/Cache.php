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

use \MovLib\Data\StreamWrapper\StreamWrapperFactory;
use \MovLib\Data\FileSystem;
use \MovLib\Data\Log;

/**
 * Interact with the persistent disc cache.
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
   * Indicates whether this request is cacheable or not.
   *
   * @var boolean
   */
  public $cacheable = true;

  /**
   * URI to the cache file of the current request.
   *
   * @var string
   */
  protected $uri;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new cache instance.
   *
   */
  public function __construct() {
    $this->uri = "dr://var/cache/{$i18n->languageCode}{$kernel->requestPath}";
    if ($kernel->requestPath == "/") {
      $this->uri .= $_SERVER["PRESENTER"];
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
    return is_file($this->uri);
  }

  /**
   * Delete cached version of current presentation.
   *
   * @param string $uri [optional]
   *   URI of the cache file to delete, defaults to the current presentation.
   * @return this
   */
  public function delete($uri = null) {
    if (!$uri) {
      $uri = $this->uri;
    }
    unlink($uri);
    unlink("{$uri}.gz");
    return $this;
  }

  /**
   * Save new cached version of current presentation.
   *
   * @param string $presentation
   *   The complete presentation to cache.
   * @return this
   */
  public function save($presentation) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($presentation)) {
      return $this;
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    if ($this->cacheable === true && $session->isAuthenticated === false && $this->cached() === false) {
      try {
        // Make sure that the complete directory structure actually exists.
        $dir = dirname($this->uri);
        mkdir($dir, 0775, true);

        // Write the new cache file to a temporary file, this ensures that nginx isn't delivering the file while we're
        // writing to it.
        $uri = StreamWrapperFactory::create($this->uri)->realpath();
        $tmp = tempnam($dir, "cache");
        file_put_contents($tmp, "{$presentation}<!--{$_SERVER["REQUEST_TIME_FLOAT"]}-->");
        $gz  = FileSystem::compress($tmp);

        // Rename is an atomic action.
        rename($tmp, $uri);
        rename($gz, "{$uri}.gz");
      }
      catch (\Exception $e) {
        Log::error($e);
      }
    }

    return $this;
  }

}
