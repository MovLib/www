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
 * Defines the interface local stream wrappers.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface LocalStreamWrapperInterface extends PHPStreamWrapperInterface {

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
  public function chgrp($realpath, $group);

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
  public function chmod($realpath, $mode);

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
  public function chown($realpath, $user);

  /**
   * Get the name of the directory from a given URI.
   *
   * @param string $uri
   *   Absolute URI of the file.
   * @return string
   *   The name of the directory from given URI.
   */
  public function dirname($uri = null);

  /**
   * Get the canonical absolute path to the directory the stream wrapper is responsible for.
   *
   * @return string
   *   The canonical absolute path to the directory the stream wrapper is responsible for.
   */
  public function getPath();

  /**
   * Get the stream wrapper's scheme.
   *
   * @return string
   *   The stream wrapper's scheme.
   */
  public function getScheme();

  /**
   * Get the absolute stream resource URI.
   *
   * @return string
   *   The absolute stream resource URI.
   */
  public function getURI();

  /**
   * Get the canonical absolute local path of the URI.
   *
   * @param null|string $uri [optional]
   *   Absolute URI to get the real path for, default to <code>NULL</code> and uses the URI of the current stream.
   * @return boolean|string
   *   The canonical absolute local path of the URI or <code>FALSE</code> if the path couldn't be resolved.
   */
  public function realpath($uri = null);

  /**
   * Register the stream wrapper with it's scheme.
   *
   * @internal
   *   PHP's stream wrapper implementation doesn't allow for dependency injection. We therefore are forced to create a
   *   work around. The register method will register the stream wrapper globally, the passed in <var>$fileSystem</var>
   *   will be export to a static class property and persist over multiple instantiations of the registered stream
   *   wrapper.
   * @param \MovLib\Core\FileSystem $fileSystem
   *   File system instance.
   * @return this
   */
  public function register(\MovLib\Core\FileSystem $fileSystem);

  /**
   * Set the absolute stream resource URI.
   *
   * @param string $uri
   *   The absolute URI to set.
   * @return this
   */
  public function setURI($uri);

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
   */
  public function touch($realpath, $modificationTime = null, $accessTime = null);

}
