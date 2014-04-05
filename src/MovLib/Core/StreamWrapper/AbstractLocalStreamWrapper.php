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
use \MovLib\Exception\StreamException;

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
abstract class AbstractLocalStreamWrapper {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Stream context resource.
   *
   * @var resource
   */
  public $context;

  /**
   * The active file system intance.
   *
   * @var \MovLib\Core\FileSystem
   */
  public static $fs;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  final public function __construct() {
    $path = $this->getPath();
    if (realpath($path) === false) {
      throw new \LogicException("A stream wrapper's path must exist on the file system but '{$path}' doesn't");
    }
  }

  final public function __destruct() {
    // Do nothing, just ensure that nobody implements the destructor.
  }
  // @codeCoverageIgnoreEnd
  // @devEnd


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the external path for this stream wrapper.
   *
   * @param string $uri [optional]
   *   The URI to get the external path for.
   * @param string $cacheBuster [optional]
   *   A cache buster string that should be appended to the file's URL.
   * @return string
   *   The external path for this stream wrapper.
   */
  abstract public function getExternalPath($uri = null, $cacheBuster = null);

  /**
   * Get the canonical absolute path to the directory the stream wrapper is responsible for.
   *
   * @return string
   *   The canonical absolute path to the directory the stream wrapper is responsible for.
   */
  abstract public function getPath();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Change file group.
   *
   * @param string $realpath
   *   The file's real path.
   * @param integer|string $group
   *   The file's new group either as <code>gid</code> or string.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  final public function chgrp($realpath, $group) {
    $status = chgrp($realpath, $group);
    clearstatcache(true, $realpath);
    return $status;
  }

  /**
   * Change file mode.
   *
   * <b>IMPORTANT</b><br>
   * Never forget the leading zero for <var>$mode</var> because this makes it an octal integer.
   *
   * <b>NOTE</b><br>
   * You cannot remove special bits with this method.
   *
   * @param string $realpath
   *   The file's real path.
   * @param integer $mode
   *   The file's new mode as octal integer (always with leading zero).
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  final public function chmod($realpath, $mode) {
    $status = chmod($realpath, $mode);
    if (self::$fs->privileged) {
      if (isset(self::$fs->user)) {
        chown($this->realpath(), self::$fs->user);
      }
      if (isset(self::$fs->group)) {
        chgrp($this->realpath(), self::$fs->group);
      }
    }
    clearstatcache(true, $realpath);
    return $status;
  }

  /**
   * Change file user.
   *
   * @param string $realpath
   *   The file's real path.
   * @param integer|string $user
   *   The file's new user either as <code>uid</code> or string.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  final public function chown($realpath, $user) {
    $status = chown($realpath, $user);
    clearstatcache(true, $realpath);
    return $status;
  }

  /**
   * Close directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-closedir.php
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
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
   * Open directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-opendir.php
   * @param string $uri
   *   Absolute URI to the directory that should be opened.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  final public function dir_opendir($uri) {
    try {
      $this->uri = $uri;
      return (boolean) ($this->handle = opendir($this->realpath()));
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't open {$this->uri} directory handle", null, $e);
    }
  }

  /**
   * Read entry from directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-readdir.php
   * @return string|boolean
   *   The next filename or <code>FALSE</code> if there is no next file.
   * @throws \MovLib\Exception\StreamException
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
   * Rewind directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-rewinddir.php
   * @return boolean
   *   Always <code>TRUE</code> because there's no way to find out if the function call succeeded.
   * @throws \MovLib\Exception\StreamException
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
   * Get the name of the directory from a given URI.
   *
   * @staticvar array $dirnames
   *   Used to cache directory names.
   * @param string $uri
   *   Absolute URI of the file.
   * @return string
   *   The name of the directory from given URI.
   * @throws \MovLib\Exception\StreamException
   */
  final public function dirname($uri = null) {
    static $dirnames = [];
    try {
      if (!$uri) {
        $uri = $this->uri;
      }
      if (isset($dirnames[$uri])) {
        return $dirnames[$uri];
      }
      if (($dirnames[$uri] = dirname($this->getTarget($uri))) == ".") {
        $dirnames[$uri] = "";
      }
      $scheme = explode("://", $uri, 2)[0];
      return "{$scheme}://{$dirnames[$uri]}";
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
   * @throws \MovLib\Exception\StreamException
   */
  final protected function getTarget($uri = null) {
    static $targets = [];
    try {
      if (!$uri) {
        $uri = $this->uri;
      }
      if (isset($targets[$uri])) {
        return $targets[$uri];
      }
      return trim(explode("://", $uri, 2)[1], "\\/");
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't generate target for {$uri}", null, $e);
    }
  }

  /**
   * Create directory.
   *
   * @link http://php.net/manual/streamwrapper.mkdir.php
   * @param string $uri
   *   Absolute URI to the directory to create.
   * @param integer $mode
   *   Permission flags as octal integer.
   * @param integer $options
   *   Bit mask of <var>STREAM_REPORT_ERRORS</var> and <var>STREAM_MKDIR_RECURSIVE</var>.
   * @param boolean $recursion [internal]
   *   <code>TRUE</code> if in recursion.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  final public function mkdir($uri, $mode, $options, $recursion = false) {
    try {
      // Nothing to do if the directory already exists, but ensure correct permission mode.
      if (is_dir($uri)) {
        return chmod($uri, $mode);
      }

      // The URI is already the real path if we're in recursion.
      if ($recursion) {
        $realpath = $uri;
      }
      else {
        $this->uri = $uri;
        $realpath  = $this->realpath();
      }

      // Check if we should generate parent directories.
      if (($recursive = (boolean) ($options & STREAM_MKDIR_RECURSIVE)) === true) {
        // Realpath doesn't return the correct path if the parent directories don't exist.
        if ($recursion === false) {
          $realpath = "{$this->getPath()}/{$this->getTarget()}";
        }

        // Try to create the parent directory.
        if (is_dir(($parent = dirname($realpath)))) {
          // The recursion has to end if the parent directory exists, regardless of whether the subdirectory could be
          // created.
          if (($status = mkdir($realpath))) {
            $status = chmod($realpath, $mode);
          }

          return $recursion ? true : $status;
        }

        // If the parent directory doesn't exist and couldn't be created walk the requested directory path back up until
        // an existing directory is hit, and from there, recursively create the sub-directories. Only if that recursion
        // succeeds create the final, originally requested sub-directory.
        return $this->mkdir($parent, $mode, $options, true) && mkdir($realpath) && chmod($realpath, $mode);
      }

      // Try to create the directory and change the mode.
      return mkdir($realpath) && chmod($realpath, $mode);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't create directory {$this->uri}", null, $e);
    }
  }

  /**
   * Get the canonical absolute local path of the URI.
   *
   * @param null|string $uri [optional]
   *   Absolute URI to get the real path for, default to <code>NULL</code> and uses the URI of the current stream.
   * @return boolean|string
   *   The canonical absolute local path of the URI or <code>FALSE</code> if the path couldn't be resolved.
   * @throws \MovLib\Exception\StreamException
   */
  final public function realpath($uri = null) {
    try {
      if (!$uri) {
        $uri = $this->uri;
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
        return false;
      }

      return $realpath;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't generate real path for {$uri}", null, $e);
    }
  }

  /**
   * Rename (move) file.
   *
   * @link http://php.net/manual/streamwrapper.rename.php
   * @param string $fromURI
   *   Absolute URI of the file to rename.
   * @param string $toURI
   *   Absolute URI of the new file name.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
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
   * Remove directory.
   *
   * @link http://php.net/manual/streamwrapper.rmdir.php
   * @param string $uri
   *   Absolute URI to the directory to remove.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  final public function rmdir($uri) {
    try {
      $this->uri = $uri;
      return rmdir($this->realpath());
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't delete {$this->uri} directory", null, $e);
    }
  }

  /**
   * Retrieve the underlaying resource.
   *
   * @link http://php.net/manual/streamwrapper.stream-cast.php
   * @param integer $castAs
   *   Cast be <var>STREAM_CAST_FOR_SELECT</var> when <code>stream_select()</code> is calling <code>stream_cast()</code>
   *   or <var>STREAM_CAST_AS_STREAM</var> when <code>stream_cast()</code> is called for other uses.
   * @return boolean
   *   Always <code>FALSE</code>, indicating that this method is not supported.
   * @throws \MovLib\Exception\StreamException
   */
  final public function stream_cast($castAs) {
    return false;
  }

  /**
   * Close stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-close.php
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  final public function stream_close() {
    try {
      $status = fclose($this->handle);
      if (isset($this->context["privileged"]) && $this->context["privileged"]) {
        if (is_dir($this->uri)) {
          chmod($this->uri, FileSystem::MODE_DIR);
        }
        elseif (is_file($this->uri)) {
          chmod($this->uri, FileSystem::MODE_FILE);
        }
      }
      return $status;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't close {$this->uri} handle", null, $e);
    }
  }

  /**
   * Test for end-of-file on a file pointer.
   *
   * @link http://php.net/manual/streamwrapper.stream-eof.php
   * @return boolean
   *   <code>TRUE</code> if the read/write position is at the end of the stream and if no more data is available,
   *   <code>FALSE</code> otherwise.
   * @throws \MovLib\Exception\StreamException
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
   * Flush output.
   *
   * @link http://php.net/manual/streamwrapper.stream-flush.php
   * @return boolean
   *   <code>TRUE</code> if the cached data was successfully stored (or if there was no data to store), or
   *   <code>FALSE</code> if the data couldn't be stored.
   * @throws \MovLib\Exception\StreamException
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
  final public function stream_lock($operation) {
    try {
      return flock($this->handle, $operation);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't lock {$this->uri} handle", null, $e);
    }
  }

  /**
   * Change stream options.
   *
   * @link http://php.net/manual/streamwrapper.stream-metadata.php
   * @param string $uri
   *   Absolute URI of the file to set metadata.
   * @param integer $option
   *   One of:
   *   <ul>
   *     <li><code>STREAM_META_TOUCH</code> method was called in response to <code>touch()</code></li>
   *     <li><code>STREAM_META_OWNER_NAME</code> method was called in response to <code>chown()</code> with string</li>
   *     <li><code>STREAM_META_OWNER</code> method was called in response to <code>chown()</code></li>
   *     <li><code>STREAM_META_GROUP_NAME</code> method was called in response to <code>chgrp()</code> with string</li>
   *     <li><code>STREAM_META_GROUP</code> method was called in response to <code>chgrp()</code></li>
   *     <li><code>STREAM_META_ACCESS</code> method was called in response to <code>chmod()</code></li>
   *   </ul>
   * @param mixed $value
   *   If <var>$option</var> is:
   *   <ul>
   *     <li><code>STREAM_META_TOUCH</code> array with offset <code>0</code> being the modification time and offset
   *     <code>1</code> the access time</li>
   *     <li><code>STREAM_META_OWNER_NAME</code> the user's name as string</li>
   *     <li><code>STREAM_META_OWNER</code> the user's identifier as integer</li>
   *     <li><code>STREAM_META_GROUP_NAME</code> the group's name as string</li>
   *     <li><code>STREAM_META_GROUP</code> the group's identifier as integer</li>
   *     <li><code>STREAM_META_ACCESS</code> the desired mode as octal integer</li>
   *   </ul>
   * @return boolean
   *   <code>TRUE</code> on success and <code>FALSE</code> on failure or if the desired function is not implemented.
   * @throws \MovLib\Exception\StreamException
   */
  final public function stream_metadata($uri, $option, $value) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath();
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
   * Open stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-open.php
   * @param string $uri
   *   Absolute URI of the file to open.
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
  final public function stream_open($uri, $mode, $options, &$openedPath) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath();
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
   * Read from stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-read.php
   * @param integer $count
   *   How many bytes of data from the current position should be returned.
   * @return string|boolean
   *   The string that was read, or <code>FALSE</code> in case of error.
   * @throws \MovLib\Exception\StreamException
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
   * Seek to specific location in stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-seek.php
   * @param integer $offset
   *   The stream offset to seek to.
   * @param integer $whence [optional]
   *   Possible values:
   *   <ul>
   *     <li><code>SEEK_SET</code> (default) set position equal to <var>$offset</var> bytes</li>
   *     <li><code>SEEK_CUR</code> set position to current location plus <var>$offset</var></li>
   *     <li><code>SEEK_END</code> set position to end-of-file plus <var>$offset</var></li>
   *   </ul>
   * @return boolean
   *   <code>TRUE</code> on success and <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
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
   * Change stream options.
   *
   * @link http://php.net/manual/streamwrapper.stream-set-option.php
   * @param integer $option
   *   The option that was called on the stream.
   * @param mixed $arg1
   *   Depends on <var>$option</var>.
   * @param mixed $arg2
   *   Depends on <var>$option</var>.
   * @return boolean
   *   <code>TRUE</code> on success and <code>FALSE</code> on failure or if the desired function is not implemented.
   * @throws \MovLib\Exception\StreamException
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
   * Retrieve information about a file resource.
   *
   * @link http://php.net/manual/streamwrapper.stream-stat.php
   * @return array|boolean
   *   An array with file status, or <code>FALSE</code> in case of an error, see
   *  {@link http://at1.php.net/manual/en/function.stat.php} for a description of this array.
   * @throws \MovLib\Exception\StreamException
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
   * Retrieve the current position of a stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-tell.php
   * @return integer
   *   The current offset in bytes from the beginning of the file.
   * @throws \MovLib\Exception\StreamException
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
   * Truncate stream.
   *
   * @link http://php.net/manual/streamwrapper.stream-truncate.php
   * @param integer $newSize
   *   The new size.
   * @return boolean
   *   <code>TRUE</code> on success and <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
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
   * Write to stream.
   *
   * @param string $data
   *   The string to be written.
   * @return integer
   *   The number of bytes written.
   * @throws \MovLib\Exception\StreamException
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
   * Set access and modification time of file.
   *
   * @param string $realpath
   *   The file's real path.
   * @param null|integer $modificationTime [optional]
   *   The file's new modification time, defaults to <code>NULL</code> (current system time is used).
   * @param null|integer $accessTime [optional]
   *   The file's new access time, defaults to <code>NULL</code> (<var>$modificationTime</var> is used).
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  final public function touch($realpath, $modificationTime = null, $accessTime = null) {
    return touch($realpath, $modificationTime, $accessTime);
  }

  /**
   * Delete a file.
   *
   * @link http://php.net/manual/streamwrapper.unlink.php
   * @param string $uri
   *   Absolute URI of the file to delete.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  final public function unlink($uri) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath();
      if (file_exists($realpath)) {
        return unlink($realpath);
      }
      return true;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't unlink {$this->uri}", null, $e);
    }
  }

  /**
   * Retrieve information about a file.
   *
   * @link http://php.net/manual/streamwrapper.url-stat.php
   * @param string $uri
   *   Absolute URI of the file to delete.
   * @param integer $flags
   *   Holds additional flags set by the streams API. It can hold one or more of the following values OR'd together.
   * @return array|boolean
   *   An array with file status, or <code>FALSE</code> in case of an error, see
   *  {@link http://at1.php.net/manual/en/function.stat.php} for a description of this array.
   * @throws \MovLib\Exception\StreamException
   */
  final public function url_stat($uri, $flags) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath();
      if (file_exists($realpath)) {
        return stat($realpath);
      }
      return false;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get stats for {$this->uri}", null, $e);
    }
  }

}
