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
namespace MovLib\Core\StreamWrapper;

use \MovLib\Core\FileSystem;

/**
 * Base class for all local stream wrappers.
 *
 * This class provides a complete stream wrapper implementation. URIs such as "<scheme>://<target>" are expanded to
 * normal file system paths such a <code>"/document/root/tmp"</code> and then PHP file system functions are invoked.
 *
 * <b>NOTE</b><br>
 * Method names have to be written in snake-case because they have to follow the interface defined by PHP. We cannot
 * rename them!
 *
 * <b>NOTE</b><br>
 * The code is based on the Drupal stream wrapper impelentation and (as always) a lot of other Drupal code.
 *
 * @link http://php.net/manual/class.streamwrapper.php
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractLocalStreamWrapper implements LocalStreamWrapperInterface {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractLocalStreamWrapper";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * The canonical absolute root path.
   *
   * @var string
   */
  public static $root;

  /**
   * The file's owning user.
   *
   * @var null|string
   */
  public static $user;

  /**
   * The file's owning group.
   *
   * @var null|string
   */
  public static $group;


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
  public $uri;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  final public function chgrp($realpath, $group) {
    $status = chgrp($realpath, $group);
    clearstatcache(true, $realpath);
    return $status;
  }

  /**
   * {@inheritdoc}
   */
  final public function chmod($realpath, $mode) {
    $status = chmod($realpath, $mode);
    if (static::$user) {
      $this->chown($realpath, static::$user);
    }
    if (static::$group) {
      $this->chgrp($realpath, static::$group);
    }
    clearstatcache(true, $realpath);
    return $status;
  }

  /**
   * {@inheritdoc}
   */
  final public function chown($realpath, $user) {
    $status = chown($realpath, $user);
    clearstatcache(true, $realpath);
    return $status;
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function dir_closedir() {
    try {
      return (boolean) (closedir($this->handle));
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't close {$this->uri} directory handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function dir_opendir($uri) {
    try {
      $this->uri = $uri;
      return (boolean) ($this->handle = opendir($this->realpath($uri)));
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't open {$this->uri} directory handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function dir_readdir() {
    try {
      return readdir($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't read entry from {$this->uri} directory handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function dir_rewinddir() {
    try {
      rewinddir($this->handle);
      return true;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't rewind {$this->uri} directory handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @staticvar array $dirnames
   *   Used to cache directory names.
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function dirname($uri = null) {
    try {
      $uri || ($uri = $this->uri);
      if (($dirname = dirname($this->getTarget($uri))) === ".") {
        $dirname = null;
      }
      return "{$this::getScheme()}://{$dirname}}";
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get directory name of {$uri}", null, $e);
    }
  }

  /**
   * Get the canonical absolute writable local path of the URI.
   *
   * @param string $uri [optional]
   *   Absolute URI of the file.
   * @return string
   *   The canonical absolute writable local path of the URI.
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final protected function getTarget($uri = null) {
    try {
      $uri || ($uri = $this->uri);
      return trim(explode("://", $uri, 2)[1], "\\/");
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't generate target for {$uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  final public function getURI() {
    return $this->uri;
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function mkdir($uri, $mode, $options) {
    try {
      $this->uri = $uri;
      $recursive = (boolean) ($options & STREAM_MKDIR_RECURSIVE);

      // Realpath fails if $uri has multiple levels of directories that don't exist yet.
      $realpath = $recursive === true ? "{$this->getPath()}/{$this->getTarget($uri)}" : $this->realpath($uri);

      // Do nothing if the directory already exists but ensure correct permission mode.
      if (is_dir($realpath)) {
        return $this->chmod($realpath, $mode);
      }

      // If recursive create each non-existent parent directory.
      if ($recursive) {
        // Split the path into it's individual parts.
        $parts = explode(DIRECTORY_SEPARATOR, $realpath);

        // We are working with an absolute path, thus the first part is empty because it starts with a slash.
        array_shift($parts);

        // Don't handle the last directory in the following loop.
        array_pop($parts);

        // Start from the file system's root directory.
        $path = DIRECTORY_SEPARATOR;

        // Go through all parts and create the directory if necessary.
        foreach ($parts as $part) {
          // Append the next part to the full path.
          $path .= $part;

          // Only attempt to create the directory if it doesn't exist yet.
          if (file_exists($path) === false) {
            if (mkdir($path, $mode, false) === false) {
              return false;
            }
            if ($this->chmod($path, $mode) === false) {
              return false;
            }
          }

          // Append the directory separator to the path for the next iteration.
          $path .= DIRECTORY_SEPARATOR;
        }
      }

      // Don't check if the top-level directory already exists, as this condition must cause this function to fail.
      if (mkdir($realpath, $mode, false) === false) {
        return false;
      }

      return $this->chmod($realpath, $mode);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't create directory {$this->uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @staticvar array $realpaths
   *   Used to cache realpaths.
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function realpath($uri = null) {
    static $realpaths = [];
    try {
      $uri || ($uri = $this->uri);

      if (isset($realpaths[$uri])) {
        return $realpaths[$uri];
      }

      // Buld canonical absolute local file path.
      $basepath = $this->getPath();
      $filepath = "{$basepath}/{$this->getTarget($uri)}";

      // File doesn't exist yet if realpath returns FALSE.
      if (($realpath = realpath($filepath)) === false) {
        $realpath = realpath(dirname($filepath)) . "/" . basename($filepath);
      }

      // Make sure we have a canonical absolute path by now.
      if ($realpath === false || strpos($realpath, $basepath) !== 0) {
        $realpaths[$uri] = false;
      }
      else {
        $realpaths[$uri] = $realpath;
      }

      return $realpaths[$uri];
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't generate real path for {$uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function rename($fromURI, $toURI) {
    try {
      return rename($this->realpath($fromURI), $this->realpath($toURI));
    }
    catch (\Exception $e) {
      throw new StreamException("Couldn't rename (move) file from {$fromURI} to {$toURI}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  final public static function register($root, $user = null, $group = null) {
    // @devStart
    if (is_dir($root) === false) {
      throw new \InvalidArgumentException("The root path must exists on disk.");
    }
    // @devEnd
    static::$root = $root;
    $user && (static::$user = $user);
    $group && (static::$group = $group);
    return stream_wrapper_register(static::getScheme(), static::class);
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function rmdir($uri) {
    try {
      $this->uri = $uri;
      $realpath = $this->realpath($uri);
      if (is_dir($realpath)) {
        chmod($realpath, 0700);
        return rmdir($realpath);
      }
      return true;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't delete {$this->uri} directory", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  final public function setURI($uri) {
    $this->uri = $uri;
    return $this;
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_cast($castAs) {
    return false;
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_close() {
    try {
      $status = fclose($this->handle);
      $this->chmod($this->realpath(), FileSystem::MODE_FILE);
      return $status;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't close {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_eof() {
    try {
      return feof($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Failed to determine end-of-file from {$this->uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_flush() {
    try {
      return fflush($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't flush {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_lock($operation) {
    try {
      return flock($this->handle, $operation);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't lock {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_metadata($uri, $option, $value) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath($uri);
      switch ($option) {
        case STREAM_META_TOUCH:
          return $this->touch($realpath, isset($value[0]) ? $value[0] : null, isset($value[1]) ? $value[1] : null);

        case STREAM_META_OWNER_NAME:
        case STREAM_META_OWNER:
          return $this->chown($realpath, $value);

        case STREAM_META_GROUP_NAME:
        case STREAM_META_GROUP:
          return $this->chgrp($realpath, $value);

        case STREAM_META_ACCESS:
          return $this->chmod($realpath, $value);
      }
      return false;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Metadata failed on {$this->uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_open($uri, $mode, $options, &$openedPath) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath($uri);
      $status    = (boolean) ($this->handle = fopen($realpath, $mode));
      if ($status === true && $options & STREAM_USE_PATH) {
        $openedPath = $realpath;
      }
      return $status;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't open {$uri} stream handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_read($count) {
    try {
      return fread($this->handle, $count);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't read from {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_seek($offset, $whence = SEEK_SET) {
    try {
      // fseek returns 0 on success and -1 on a failure.
      // stream_seek 1 on success and 0 on a failure.
      return !fseek($this->handle, $offset, $whence);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't seek in {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_set_option($option, $arg1, $arg2) {
    try {
      switch ($option) {
        case STREAM_OPTION_BLOCKING:
          return stream_set_blocking($this->handle, $arg1);

        case STREAM_OPTION_WRITE_BUFFER:
          return stream_set_write_buffer($this->handle, $arg1, $arg2);

        case STREAM_OPTION_READ_TIMEOUT:
          return stream_set_timeout($this->handle, $arg1, $arg2);
      }
      return false;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't set options on {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_stat() {
    try {
      return fstat($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get stats for {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_tell() {
    try {
      return ftell($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get current position of {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_truncate($newSize) {
    try {
      return ftruncate($this->handle, $newSize);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't truncate {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function stream_write($data) {
    try {
      return fwrite($this->handle, $data);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't write to {$this->uri} handle", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function touch($realpath, $modificationTime = null, $accessTime = null) {
    return touch($realpath, $modificationTime, $accessTime) && $this->chmod($realpath, FileSystem::MODE_FILE);
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function unlink($uri) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath($uri);
      if (file_exists($realpath)) {
        chmod($realpath, 0700);
        return unlink($realpath);
      }
      return true;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't unlink {$this->uri}", null, $e);
    }
  }

  /**
   * {@inheritdoc}
   * @throws \MovLib\Core\StreamWrapper\StreamException
   */
  final public function url_stat($uri, $flags) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath($uri);
      if ($realpath !== false && file_exists($realpath)) {
        return stat($realpath);
      }
      return false;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get stats for {$this->uri}", null, $e);
    }
  }

}
