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

/**
 * Defines the interface for PHP's {@link http://php.net/class.streamwrapper StreamWrapper boilerplate class} with a few
 * adoptions to fit our needs.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface PHPStreamWrapperInterface {
  // @codingStandardsIgnoreStart
  //
  // We have to implement all methods as they are defined in the boilerplate class, therefore we have to use snake_case
  // which isn't allowed by our coding standards. We're forced to ignore the complete interface.

  /**
   * Close directory handle.
   *
   * This method is called in response to {@link http://php.net/function.closedir <code>closedir()</code>}. Any
   * resources which were locked, or allocated, during opening and use of the directory stream should be released.
   *
   * @link http://php.net/streamwrapper.dir-closedir
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function dir_closedir();

  /**
   * Open directory handle.
   *
   * <b>NOTE</b><br>
   * The second argument as described on PHP's manual page is deprecated since 5.3.0 and was removed in 5.4.0.
   *
   * @link http://php.net/streamwrapper.dir-opendir
   * @param string $path
   *   Absolute URI to the directory that was passed to {@link http://php.net/function.opendir <code>opendir()</code>}.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function dir_opendir($path);

  /**
   * Read entry from directory handle.
   *
   * @link http://php.net/streamwrapper.dir-readdir
   * @return string|boolean
   *   The next filename or <code>FALSE</code> if there is no next file.
   */
  public function dir_readdir();

  /**
   * Rewind directory handle.
   *
   * @link http://php.net/streamwrapper.dir-rewinddir
   * @return boolean
   *   Always <code>TRUE</code> because there's no way to find out if the function call succeeded.
   */
  public function dir_rewinddir();

  /**
   * Create directory.
   *
   * <b>NOTE</b><br>
   * The last arguments (<var>$recursion</var>) isn't part of the official boilerplate code and added by us for easy
   * creation of multiple levels of directories.
   *
   * @link http://php.net/streamwrapper.mkdir
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
   */
  public function mkdir($uri, $mode, $options, $recursion = false);

  /**
   * Rename (move) file.
   *
   * @link http://php.net/streamwrapper.rename
   * @param string $fromURI
   *   Absolute URI of the file to rename.
   * @param string $toURI
   *   Absolute URI of the new file name.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function rename($fromURI, $toURI);

  /**
   * Remove directory.
   *
   * @link http://php.net/streamwrapper.rmdir
   * @param string $uri
   *   Absolute URI to the directory to remove.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function rmdir($uri);

  /**
   * Retrieve the underlaying resource.
   *
   * @link http://php.net/streamwrapper.stream-cast
   * @param integer $castAs
   *   Cast be <var>STREAM_CAST_FOR_SELECT</var> when <code>stream_select()</code> is calling <code>stream_cast()</code>
   *   or <var>STREAM_CAST_AS_STREAM</var> when <code>stream_cast()</code> is called for other uses.
   * @return boolean
   *   Always <code>FALSE</code>, indicating that this method is not supported.
   */
  public function stream_cast($castAs);

  /**
   * Close stream.
   *
   * @link http://php.net/streamwrapper.stream-close
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function stream_close();

  /**
   * Test for end-of-file on a file pointer.
   *
   * @link http://php.net/streamwrapper.stream-eof
   * @return boolean
   *   <code>TRUE</code> if the read/write position is at the end of the stream and if no more data is available,
   *   <code>FALSE</code> otherwise.
   */
  public function stream_eof();

  /**
   * Flush output.
   *
   * @link http://php.net/streamwrapper.stream-flush
   * @return boolean
   *   <code>TRUE</code> if the cached data was successfully stored (or if there was no data to store), or
   *   <code>FALSE</code> if the data couldn't be stored.
   */
  public function stream_flush();

  /**
   * Advisory file locking.
   *
   * @link http://php.net/streamwrapper.stream-lock
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
   */
  public function stream_lock($operation);

  /**
   * Change stream options.
   *
   * @link http://php.net/streamwrapper.stream-metadata
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
   */
  public function stream_metadata($uri, $option, $value);

  /**
   * Open stream.
   *
   * @link http://php.net/streamwrapper.stream-open
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
   */
  public function stream_open($uri, $mode, $options, &$openedPath);

  /**
   * Read from stream.
   *
   * @link http://php.net/streamwrapper.stream-read
   * @param integer $count
   *   How many bytes of data from the current position should be returned.
   * @return string|boolean
   *   The string that was read, or <code>FALSE</code> in case of error.
   */
  public function stream_read($count);

  /**
   * Seek to specific location in stream.
   *
   * @link http://php.net/streamwrapper.stream-seek
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
   */
  public function stream_seek($offset, $whence = SEEK_SET);

  /**
   * Change stream options.
   *
   * @link http://php.net/streamwrapper.stream-set-option
   * @param integer $option
   *   The option that was called on the stream.
   * @param mixed $arg1
   *   Depends on <var>$option</var>.
   * @param mixed $arg2
   *   Depends on <var>$option</var>.
   * @return boolean
   *   <code>TRUE</code> on success and <code>FALSE</code> on failure or if the desired function is not implemented.
   */
  public function stream_set_option($option, $arg1, $arg2);

  /**
   * Retrieve information about a file resource.
   *
   * @link http://php.net/streamwrapper.stream-stat
   * @return array|boolean
   *   An array with file status, or <code>FALSE</code> in case of an error, see
   *  {@link http://php.net/function.stat} for a description of this array.
   */
  public function stream_stat();

  /**
   * Retrieve the current position of a stream.
   *
   * @link http://php.net/streamwrapper.stream-tell
   * @return integer
   *   The current offset in bytes from the beginning of the file.
   */
  public function stream_tell();

  /**
   * Truncate stream.
   *
   * @link http://php.net/streamwrapper.stream-truncate
   * @param integer $newSize
   *   The new size.
   * @return boolean
   *   <code>TRUE</code> on success and <code>FALSE</code> on failure.
   */
  public function stream_truncate($newSize);

  /**
   * Write to stream.
   *
   * @link http://php.net/streamwrapper.stream-write
   * @param string $data
   *   The string to be written.
   * @return integer
   *   The number of bytes written.
   */
  public function stream_write($data);

  /**
   * Delete a file.
   *
   * @link http://php.net/streamwrapper.unlink
   * @param string $uri
   *   Absolute URI of the file to delete.
   * @return boolean
   *   <code>TRUE</code> on success or <code>FALSE</code> on failure.
   */
  public function unlink($uri);

  /**
   * Retrieve information about a file.
   *
   * @link http://php.net/streamwrapper.url-stat
   * @param string $uri
   *   Absolute URI of the file to delete.
   * @param integer $flags
   *   Holds additional flags set by the streams API. It can hold one or more of the following values OR'd together.
   * @return array|boolean
   *   An array with file status, or <code>FALSE</code> in case of an error, see
   *  {@link http://at1.php.net/manual/en/function.stat.php} for a description of this array.
   */
  public function url_stat($uri, $flags);

  // @codingStandardsIgnoreEnd
}
