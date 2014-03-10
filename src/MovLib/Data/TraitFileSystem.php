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

/**
 * Provides methods for interacting with the file system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitFileSystem {

  /**
   * Delete given file or directory.
   *
   * @param string $path
   *   Absolute path to the file or directory to delete.
   * @param string $flags [optional]
   *   Flags that should be passed to the <code>rm</code> command. See <code>man rm</code> for more information.
   * @return $this
   */
  protected final function fsDelete($path, $flags = null) {
    if (!empty($flags) && $flags[0] != "-") {
      $flags = "-{$flags}";
    }
    $this->fsRealpath($path);
    exec("rm {$flags} '{$path}'", $output, $status);
    if ($status !== 0) {
      $output = str_replace("\t", "    ", implode("\n", $output));
      throw new \RuntimeException("{$output}\n\nCouldn't delete '{$path}'");
    }
    return $this;
  }

  /**
   * Get system user and group if unknown and in non FastCGI context.
   *
   * We can't be certain that this process is running witht he proper user and group if we aren't executed via php-fpm
   * because a CLI script could be executed as root or via sudo.
   *
   * <b>Note:</b> Methods using this helper method should check if they are not running via php-fpm before calling this
   * method to avoid unnecessary function calls which are expensive in PHP; this doesn't matter in CLI mode.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param null|string $user
   *   The possibly passed user name.
   * @param null|string $group
   *   The possibly passed group name.
   * @return $this
   */
  protected final function fsGetSystemUserAndGroup(&$user, &$group) {
    global $kernel;
    if (!$user && !$group && isset($kernel->configuration->user) && isset($kernel->configuration->group)) {
      $user  = $kernel->configuration->user;
      $group = $kernel->configuration->group;
    }
    return $this;
  }

  /**
   * Change file mode, owner, and group.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $path
   *   Absolute path of the file to change.
   * @param integer $mode [optional]
   *   The desired mode of the file, defaults to <code>2664</code>.
   * @param string $user [optional]
   *   The desired user of the file, defaults to <code>NULL</code> (user won't be changed).
   * @param string $group [optional]
   *   The desired group of the file, defaults to <code>NULL</code> (group won't be changed).
   * @return $this
   * @throws \Exception
   * @throws \RuntimeException
   */
  protected final function fsChangeMode($path, $mode = 2664, $user = null, $group = null) {
    global $kernel;
    // @devStart
    // @codeCoverageIngoreStart
    if (empty($path)) {
      throw new \InvalidArgumentException("\$filename cannot be empty");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->fsRealpath($path);
    if (chmod($path, $mode) === false) {
      throw new \RuntimeException("Couldn't update file mode of '{$path}'");
    }
    if ($kernel->fastCGI === false) {
      $this->fsGetSystemUserAndGroup($user, $group);
    }
    if (isset($user) && chown($path, $user) === false) {
      throw new \RuntimeException("Couldn't update user of '{$path}'");
    }
    if (isset($group) && chgrp($path, $group) === false) {
      throw new \RuntimeException("Couldn't update group of '{$path}'");
    }
    return $this;
  }

  /**
   * Create new directory including all parent directories as needed.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $directory
   *   Absolute path to the directory that should be created.
   * @param integer $mode [optional]
   *   The desired mode of the file, defaults to <code>2664</code>.
   * @param string $user [optional]
   *   The desired user of the file, defaults to <code>NULL</code> (user won't be changed).
   * @param string $group [optional]
   *   The desired group of the file, defaults to <code>NULL</code> (group won't be changed).
   * @return $this
   * @throws \Exception
   * @throws \RuntimeException
   */
  protected final function fsCreateDirectory($directory, $mode = 2774, $user = null, $group = null) {
    global $kernel;
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($directory)) {
      throw new \InvalidArgumentException("\$directory cannot be empty");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->fsRealpath($directory);
    if (is_dir($directory) === false) {
      exec("mkdir --mode={$mode} --parents --verbose '{$directory}", $output, $status);
    }
    if ($status !== 0) {
      $output = str_replace("\t", "    ", $output);
      throw new \RuntimeException("{$output}\n\nCouldn't create directory '{$directory}'");
    }
    if ($kernel->fastCGI === false) {
      $this->fsGetSystemUserAndGroup($user, $group);
    }
    if ($user && $group) {
      $this->fsChangeMode($directory, $mode, $user, $group);
    }
    return $this;
  }

  /**
   * Read entire file into string.
   *
   * @param string $path
   *   Absolute path of the file to read.
   * @return string
   *   The file's content as string.
   * @throws \Exception
   * @throws \RuntimeException
   */
  protected final function fsGetContents($path) {
    $this->fsRealpath($path);
    if (($content = file_get_contents($path)) === false) {
      throw new \RuntimeException;
    }
    return $content;
  }

  /**
   * Write string to file.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $path
   *   Absolute path of the file to write.
   * @param string|array $data
   *   The data to write. Can be either a string, an array or a stream resource. If data is a stream resource, the
   *   remaining buffer of that stream will be copied to the specified file. This is similar with using
   *   {@link http://php.net/function.stream-copy-to-stream.php <code>stream_copy_to_stream()</code>}. You can
   *   also specify the data parameter as a single dimension array. This is equivalent to
   *   <code>file_put_contents($filename, implode('', $array))</code>.
   * @param integer $flags
   *   The value of flags can be any combination of the following flags, joined with the binary <code>|</code> operator.
   *
   *   <b>Available falgs</b>
   *   <table>
   *     <tr>
   *       <th>Flag</th>
   *       <th>Description</th></tr>
   *     <tr>
   *       <td><var>FILE_USE_INCLUDE_PATH</var></td>
   *       <td>Search for <var>$path</var> in the include directory. See
   *       {@link http://php.net/ini.core.php#ini.include-path <code>include_path</code>} for more information.
   *       </td>
   *     </tr>
   *     <tr>
   *       <td><var>FILE_APPEND</var></td>
   *       <td>If file <var>$path</var> already exists, append the data to the file instead of overwriting it.</td>
   *     </tr>
   *     <tr>
   *       <td><var>LOCK_EX</var></td>
   *       <td>Acquire an exclusive lock on the file while proceeding to the writing.</td>
   *     </tr>
   *   </table>
   * @param integer $mode [optional]
   *   The desired mode of the file, defaults to <code>2664</code>.
   * @param string $user [optional]
   *   The desired user of the file, defaults to <code>NULL</code> (user won't be changed).
   * @param string $group [optional]
   *   The desired group of the file, defaults to <code>NULL</code> (group won't be changed).
   * @return $this
   * @throws \Exception
   * @throws \RuntimeException
   */
  protected final function fsPutContents($path, $data, $flags = 0, $mode = 2664, $user = null, $group = null) {
    $this->fsRealpath($path);
    if (file_put_contents($path, $data, $flags) === false) {
      throw new \RuntimeException;
    }
    $this->fsChangeMode($path, $mode, $user, $group);
    return $this;
  }

  /**
   * Canonicalize path.
   *
   * @param string $path
   *   The path being checked. Whilst a path must be supplied, the value can be blank or <code>NULL</code> in these
   *   cases, the value is interpreted as the current directory.
   * @return $this
   * @throws \RuntimeException
   */
  protected final function fsRealpath(&$path) {
    if (($path = realpath($path)) === false) {
      throw new \RuntimeException;
    }
    return $this;
  }

  /**
   * Sanitizes a filename, replacing whitespace with dashes and transforming the string to lowercase.
   *
   * Removes special characters that are illegal in filenames on certain operating systems and special characters
   * requiring special escaping to manipulate at the command line. Replaces spaces and consecutive dashes with a single
   * dash. Trims period, dash und underscore from beginning and end of filename.
   *
   * @param string $filename
   *   The filename to be sanitized.
   * @return string
   *   The sanitized filename.
   */
  protected final function fsSanitizeName($filename) {
    // Remove special characters which aren't allowed in many operating systems from the file's name.
    $filename = str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename);

    // Replace whitespace characters with dashes.
    $filename = preg_replace("/[\s-]+/", "-", $filename);

    // Remove certain punctuation from the beginning and end of the file's name.
    $filename = trim($filename, ".-_");

    // We are always using lowercased file names.
    $filename = mb_strtolower($filename);

    return $filename;
  }

  /**
   * Create a symboli link.
   *
   * @param string $target
   *   Absolute path to the target directory or file.
   * @param string $link
   *   Absolute path to the symbolic link.
   * @param boolean $force [optional]
   *   Whether to override existing destination or not, defaults to <code>TRUE</code> (override).
   * @return $this
   * @throws \RuntimeException
   */
  protected final function fsSymlink($target, $link, $force = true) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (is_string($target) === false || is_string($link) === false) {
      throw new \InvalidArgumentException("\$target and \$link must be of type string");
    }
    if (empty($target) || empty($link)) {
      throw new \InvalidArgumentException("\$target and \$link cannot be empty");
    }
    if (file_exists($target) === false) {
      throw new \InvalidArgumentException("\$target must be a valid path: '{$target}'");
    }
    if (is_bool($force) === false) {
      throw new \InvalidArgumentException("\$force must be of type boolean");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $force = $force === true ? "f" : null;
    $this->fsRealpath($target);
    exec("ln -{$force}s '{$target}' '{$link}'", $output, $status);
    if ($status !== 0) {
      $output = str_replace("\t", "    ", implode("\n", $output));
      throw new \RuntimeException("{$output}\n\nCouldn't create symbolic link from '{$target}' to '{$link}'");
    }
    return $this;
  }

}
