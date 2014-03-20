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
  public $uri;


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


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the web accessible URL of the resource.
   *
   * This method should return a URL that can be embedded in a web page and accessed from a browser. For example, the
   * external URL of <code>"asset://img/logo/vecotr.svg"</code> might be <code>"https://movlib.org/asset/img/logo/vector.svg"</code>.
   *
   * @return string
   *   The web accessible URL of the resource.
   */
  abstract public function getExternalURL();

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
   * @param integer|string $group
   *   The file's new group either as <code>gid</code> or string.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function chgrp($group) {
    try {
      $realpath = $this->realpath();
      $return   = chgrp($realpath, $group);
      clearstatcache(true, $realpath);
      return $return;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't change file group of '{$this->uri}'", null, $e);
    }
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
   * @param integer $mode
   *   The file's new mode as octal integer (always with leading zero).
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function chmod($mode) {
    try {
      $realpath = $this->realpath();
      $return   = chmod($realpath, $mode);
      clearstatcache(true, $realpath);
      return $return;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't change file mode of '{$this->uri}'", null, $e);
    }
  }

  /**
   * Change file user.
   *
   * @param integer|string $user
   *   The file's new user either as <code>uid</code> or string.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function chown($user) {
    try {
      $realpath = $this->realpath();
      $return   = chown($realpath, $mode);
      clearstatcache(true, $realpath);
      return $return;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't change file user of '{$this->uri}'", null, $e);
    }
  }

  /**
   * Close directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-closedir.php
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function dir_closedir() {
    try {
      closedir($this->handle);
      return (boolean) $this->handle;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't close directory handle of '{$this->uri}'", null, $e);
    }
  }

  /**
   * Open directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-opendir.php
   * @param string $uri
   *   Absolute URI to the directory that should be opened.
   * @param integer $options [unused]
   *   Whether or not to enforce <code>safe_mode</code>.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function dir_opendir($uri, $options) {
    try {
      $this->uri    = $uri;
      $this->handle = opendir($this->realpath());
      return (boolean) $this->handle;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't open directory handle of '{$this->uri}'", null, $e);
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
  public function dir_readdir() {
    try {
      return readdir($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't read next directory entry of '{$this->url}'", null, $e);
    }
  }

  /**
   * Rewind directory handle.
   *
   * @link http://php.net/manual/streamwrapper.dir-rewinddir.php
   * @return boolean
   *   Always <code>TRUE</code>.
   * @throws \MovLib\Exception\StreamException
   */
  public function dir_rewinddir() {
    try {
      rewinddir($this->handle);
      return true;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't rewind directory of '{$this->url}'", null, $e);
    }
  }

  /**
   * Get the name of the directory from a given URI.
   *
   * @param string $uri
   *   Absolute URI of the file.
   * @return string
   *   The name of the directory from given URI.
   */
  public function dirname($uri = null) {
    $scheme  = parse_url($uri, PHP_URL_SCHEME);
    $dirname = dirname($this->getTarget($uri));
    if ($dirname == ".") {
      $dirname = "";
    }
    return "{$scheme}://{$dirname}";
  }

  /**
   * Get the canonical absolute writable local path of the URI.
   *
   * @param string $uri [optional]
   *   Absolute URI of the file.
   * @return string
   *   The canonical absolute writable local path of the URI.
   */
  protected function getTarget($uri = null) {
    if (!$uri) {
      $uri = $this->uri;
    }

    // Build relative path to file from URI.
    list(, $target) = explode("://", $this->uri, 2);

    // Remove erroneous leading or trailing, forwardslashes and backslashes.
    return trim($target, "\\/");
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
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function mkdir($uri, $mode, $options) {
    try {
      $this->uri = $uri;
      $recursive = (boolean) ($options & STREAM_MKDIR_RECURSIVE);
      if ($recursive === true) {
        $path = "{$this->getPath()}/{$this->getTarget()}";
      }
      else {
        $path = $this->realpath();
      }
      return mkdir($path, $mode, $recursive);
    }
    catch (\ErrorException $e) {
      $e = new \StreamException("Couldn't create directory '{$uri}'", null, $e);
      if ($options & STREAM_REPORT_ERRORS) {
        throw $e;
      }
      Log::warning($e);
    }
  }

  /**
   * Get the canonical absolute local path of the URI.
   *
   * @param null|string $uri [optional]
   *   Absolute URI to get the real path for, default to <code>NULL</code> and uses the URI of the current stream.
   * @return string
   *   The canonical absolute local path of the URI.
   * @throws \MovLib\Exception\StreamException
   */
  public function realpath($uri = null) {
    // Buld canonical absolute local file path.
    $basepath = $this->getPath();
    $filepath = "{$basepath}/{$this->getTarget($uri)}";

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
  public function rename($fromURI, $toURI) {
    try {
      return rename($fromURI, $toURI);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't rename URI from '{$fromURI}' to '{$toURI}'");
    }
  }

  /**
   * Remove directory.
   *
   * @link http://php.net/manual/streamwrapper.rmdir.php
   * @param string $uri
   *   Absolute URI to the directory to remove.
   * @param integer $options
   *   Bit mask of <var>STREAM_REPORT_ERRORS</var>.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function rmdir($uri, $options) {
    try {
      $this->uri = $uri;
      return rmdir($this->realpath());
    }
    catch (\ErrorException $e) {
      $e = new StreamException("Couldn't delete directory '{$uri}'", null, $e);
      if ($options & STREAM_REPORT_ERRORS) {
        throw $e;
      }
      Log::warning($e);
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
   */
  public function stream_cast($castAs) {
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
  public function stream_close() {
    try {
      return fclose($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't close stream of '{$this->url}'", null, $e);
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
  public function stream_eof() {
    try {
      return feof($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't test for end-of-file on stream of '{$this->url}'", null, $e);
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
  public function stream_flush() {
    try {
      return fflush($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't test for end-of-file on stream of '{$this->url}'", null, $e);
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
  public function stream_lock($operation) {
    static $operations = [ LOCK_SH, LOCK_EX, LOCK_UN, LOCK_NB ];
    try {
      if (in_array($operation, $operations)) {
        return flock($this->handle, $operation);
      }
      return true;
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't lock URI '{$this->uir}'", null, $e);
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
  public function stream_metadata($uri, $option, $value) {
    $this->uri = $uri;

    switch ($option) {
      case STREAM_META_TOUCH:
        return $this->touch($value[0], $value[1]);

      case STREAM_META_OWNER_NAME:
      case STREAM_META_OWNER:
        return $this->chown($value);

      case STREAM_META_GROUP_NAME:
      case STREAM_META_GROUP:
        return $this->chgrp($value);

      case STREAM_META_ACCESS:
        return $this->chmod($value);
    }

    return false;
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
    catch (\ErrorException $e) {
      $e = new StreamException("Couldn't open local stream '{$uri}'");
      if ($options & STREAM_REPORT_ERRORS) {
        throw $e;
      }
      Log::warning($e);
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
  public function stream_read($count) {
    try {
      return fread($this->handle, $count);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't read from stream '{$this->uri}'", null, $e);
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
  public function stream_seek($offset, $whence = SEEK_SET) {
    try {
      // fseek returns 0 on success and -1 on a failure.
      // stream_seek 1 on success and 0 on a failure.
      return !fseek($this->handle, $offset, $whence);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't seek to specific location in stream of '{$this->uri}'", null, $e);
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
   *   Always <code>FALSE</code> indicating that this method is not supported.
   * @throws \MovLib\Exception\StreamException
   */
  public function stream_set_option($option, $arg1, $arg2) {
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

  /**
   * Retrieve information about a file resource.
   *
   * @link http://php.net/manual/streamwrapper.stream-stat.php
   * @return array|boolean
   *   An array with file status, or <code>FALSE</code> in case of an error, see
   *  {@link http://at1.php.net/manual/en/function.stat.php} for a description of this array.
   * @throws \MovLib\Exception\StreamException
   */
  public function stream_stat() {
    try {
      return fstat($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get stats for stream of '{$this->uri}'", null, $e);
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
  public function stream_tell() {
    try {
      return ftell($this->handle);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't get stats for stream of '{$this->uri}'", null, $e);
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
  public function stream_truncate($newSize) {
    try {
      return ftruncate($this->handle, $newSize);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't truncate stream '{$this->url}'", null, $e);
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
  public function stream_write($data) {
    try {
      return fwrite($this->handle, $data);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't write to stream '{$this->url}'", null, $e);
    }
  }

  /**
   * Set access and modification time of file.
   *
   * @param null|integer $modificationTime [optional]
   *   The file's new modification time, defaults to <code>NULL</code> (current system time is used).
   * @param null|integer $accessTime [optional]
   *   The file's new access time, defaults to <code>NULL</code> (<var>$modificationTime</var> is used).
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   * @throws \MovLib\Exception\StreamException
   */
  public function touch($modificationTime = null, $accessTime = null) {
    try {
      return touch($this->realpath(), $modificationTime, $accessTime);
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't touch '{$this->uri}'", null, $e);
    }
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
  public function unlink($uri) {
    try {
      $this->uri = $uri;
      return unlink($this->realpath());
    }
    catch (\ErrorException $e) {
      throw new StreamException("Couldn't unlink '{$this->uri}'", null, $e);
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
  public function url_stat($uri, $flags) {
    try {
      $this->uri = $uri;
      $realpath  = $this->realpath();
      return stat($realpath);
    }
    catch (\ErrorException $e) {
      $e = new StreamException("Couldn't stat '{$this->uri}'", null, $e);
      if (($flags & STREAM_URL_STAT_QUIET) || !file_exists($realpath)) {
        Log::notice($e);
      }
      else {
        throw $e;
      }
    }
  }

}
