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
namespace MovLib\Data\StreamWrapper;

use \MovLib\Data\Log;
use \MovLib\Exception\StreamException;

/**
 * Stream wrapper base class for local files.
 *
 * This class provides a complete stream wrapper implementation. URIs such as "<scheme>://<target>" are expanded to
 * normal file system paths such a <code>"/document/root/tmp"</code> and then PHP file system functions are invoked.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractLocalStreamWrapper {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Stream context resource.
   *
   * @var resource
   */
  public $context;

  /**
   * Generic resource handle.
   *
   * @var null|resource
   */
  public $handle;

  /**
   * Instance URI (stream).
   *
   * @var string
   */
  protected $uri;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the canonical absolute path to the directory the stream wrapper is responsible for.
   *
   * @return string
   *   The canonical absolute path to the directory the stream wrapper is responsible for.
   */
  abstract function getPath();


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct() {
    $path = $this->getPath();
    if (realpath($path) === false) {
      throw new \LogicException("A stream wrapper's path must exist on the file system but '{$path}' doesn't");
    }
  }
  // @codeCoverageIgnoreEnd
  // @devEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the canonical absolute local path of the URI.
   *
   * @return string
   *   The canonical absolute local path of the URI.
   * @throws \MovLib\Exception\StreamException
   */
  public function realpath() {
    // Build relative path to file from URI.
    list(, $target) = explode("://", $this->uri, 2);

    // Remove erroneous leading or trailing, forward-slashes and backslashes.
    $target = trim($target, "\\/");

    // Buld canonical absolute local file path.
    $basepath = $this->getPath();
    $filepath = "{$basepath}/{$target}";

    // File doesn't exist yet if realpath returns FALSE.
    if (($realpath = realpath($filepath)) === false) {
      $realpath = realpath(dirname($filepath)) . "/" . basename($filepath);
    }

    // Make sure we have a canonical absolute path by now.
    if ($realpath === false || strpos($realpath, $basepath) === false) {
      throw new StreamException("Couldn't determine canonical absolute local path for '{$this->uri}'");
    }

    return $realpath;
  }

  /**
   * Advisory file locking.
   *
   * @link http://php.net/manual/streamwrapper.stream-lock.php
   * @param integer $operation
   *   One of the following:
   *   <ul>
   *     <li><code>LOCK_SH</code> to acquire a shared lock (reader).</li>
   *     <li><code>LOCK_EX</code> to acquire an exclusive lock (writer).</li>
   *     <li><code>LOCK_UN</code> to release a lock (shared or exclusive).</li>
   *     <li><code>LOCK_NB</code> if you don't want flock() to block while locking (not supported on Windows).</li>
   *   </ul>
   * @return boolean
   *   Always <code>TRUE</code>.
   * @throws \MovLib\Exception\StreamException
   */
  public function stream_lock($operation) {
    static $operations = [ LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB ];
    try {
      if (in_array($operation, $operations)) {
        return flock($this->handle, $operation);
      }
      return true;
    }
    catch (\Exception $e) {
      throw new StreamException("Couldn't lock URI '{$this->uir}'", null, $e);
    }
  }

  /**
   * Open stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-open.php
   * @param string $uri
   *   The URI of the file to open.
   * @param string $mode
   *   The file mode (<code>"r"</code>, <code>"wb"</code>, etc.).
   * @param integer $options
   *   Bit mask of <var>STREAM_USE_PATH</var> and <var>STREAM_REPORT_ERRORS</var>.
   * @param string $openedPath
   *   String containing the actually opened path.
   * @return boolean
   *   <code>TRUE</code> is file was opened successfully, otherwise <code>FALSE</code>.
   * @throws \MovLib\Exception\StreamException
   */
  public function stream_open($uri, $mode, $options, &$openedPath) {
    try {
      $this->uri = $uri;
      $filepath  = $this->realpath();
      $return    = (boolean) ($this->handle = fopen($filepath, $mode));

      if ($return === true && ($options & STREAM_USE_PATH)) {
        $openedPath = $filepath;
      }

      return $return;
    }
    catch (\Exception $e) {
      if ($options & STREAM_REPORT_ERRORS) {
        throw new StreamException("Couldn't open local stream '{$uri}'");
      }
      Log::warning($e);
    }
  }

}
