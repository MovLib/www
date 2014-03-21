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
use \MovLib\Data\StreamWrapper\StreamWrapperFactory;
use \MovLib\Exception\StreamException;

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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    // Make sure the var stream wrapper is registered.
    StreamWrapperFactory::register("var");
    $this->uri = "var://cache/{$i18n->languageCode}{$kernel->requestPath}";
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
    try {
      return is_file($this->uri);
    }
    catch (StreamException $e) {
      return false;
    }
  }

  /**
   * Delete cached version of current presentation.
   *
   * @param string $uri [optional]
   *   URI of the cache file to delete, defaults to the current presentation.
   * @return this
   */
  public function delete($uri = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($uri) && (empty($uri) || !is_string($uri))) {
      throw new \InvalidArgumentException("\$uri cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    if (!$uri) {
      $uri = $this->uri;
    }

    if (is_file($uri) === true) {
      unlink($uri);
    }

    $uri = "{$uri}.gz";
    if (is_file($uri)) {
      unlink($uri);
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
      // Make sure that the complete directory structure actuallyz exists.
      $cacheDirectory = dirname($this->uri);
      if (is_dir($cacheDirectory) === false) {
        mkdir($cacheDirectory, 0777, true);
      }

      // Write the new cache file to a temporary file.
      $tmp = FileSystem::tempnam($cacheDirectory, "cache");
      file_put_contents($tmp, "{$presentation}<!--{$_SERVER["REQUEST_TIME_FLOAT"]}-->");
      $gz  = FileSystem::compress($tmp);

      // Rename is an atomic action, we want to ensure that no other process is reading the file while we're writing
      // to it.
      rename($tmp, $this->uri);
      rename($gz, "{$this->uri}.gz");
    }

    return $this;
  }

}
