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
 * Static methods to interact with the local filesystem.
 *
 * <b>NOTE</b><br>
 * We use strings for file modes because they are much easier to validate and handle in contrast to octal numbers. The
 * problem with the octal numbers is the fact that most people don't know that they have to include a leading zero. By
 * using strings this is no problem, because we can easily pass the modes around, preserving any leading zero, and
 * validate them with regular expressions or pass them directly to shell calls.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class FileSystem {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Default mode for directories.
   *
   * @var string
   */
  const DIRECTORY_MODE = "0775";

  /**
   * Default mode for files.
   *
   * @var string
   */
  const FILE_MODE = "0664";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Change file mode.
   *
   * <b>NOTE</b><br>
   * You cannot remove any special modes like set user or group ID with this method. You'll have to execute a shell
   * command with <code>"u-s"</code> or <code>"g-s"</code> to get rid of them.
   *
   * @param string $path
   *   The file's absolute path to change mode.
   * @param null|string $mode [optional]
   *   The file's new mode. If <code>NULL</code> is passed (default) {@see FileSystem::DIRECTORY_MODE} or
   *   {@see FileSystem::FILE_MODE} are applied.
   * @throws \ErrorException
   */
  public static function changeMode($path, $mode = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("The path cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::withinDocumentRoot($path);

    // Use default modes if no mode was passed.
    if (!isset($mode)) {
      if (is_dir($path)) {
        $mode = self::DIRECTORY_MODE;
      }
      else {
        $mode = self::FILE_MODE;
      }
    }

    if (file_exists($path) === true) {
      try {
        if (chmod($path, self::validateMode($mode)) === false) {
          // @codeCoverageIgnoreStart
          throw new \RuntimeException("Couldn't change mode of '{$path}'");
          // @codeCoverageIgnoreEnd
        }
      }
      catch (\Exception $e) {
        throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
      }
    }
  }

  /**
   * Change file mode recursively.
   *
   * <b>NOTE</b><br>
   * This is an expensive method, usually you'll only need it in CLI context and an exception is thrown if you attempt
   * to use this method in FastCGI context.
   *
   * <b>NOTE</b><br>
   * You cannot remove any special modes like set user or group ID with this method. You'll have to execute a shell
   * command with <code>"u-s"</code> or <code>"g-s"</code> to get rid of them.
   *
   * @param string $path
   *   The directory's absolute path to change mode.
   * @param string $fileMode [optional]
   *   The file's mode, defaults to {@see FileSystem::FILE_MODE}.
   * @param string $directoryMode [optional]
   *   The directory's mode, defaults to {@see FileSystem::DIRECTORY_MODE}.
   * @throws \RuntimeException
   */
  public static function changeModeRecursive($path, $fileMode = FileSystem::FILE_MODE, $directoryMode = FileSystem::DIRECTORY_MODE) {
    // @devStart
    // @codeCoverageIgnoreStart
    global $kernel;
    if ($kernel->fastCGI === true) {
      throw new \LogicException("This method shouldn't be used in FastCGI context");
    }
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("The path cannot be empty and must be of type string");
    }
    $realpath = self::getRealPath($path);
    if (is_dir($realpath) === false) {
      throw new \InvalidArgumentException("Recursively changing the mode only makes sense on directories");
    }
    foreach ([ "fileMode", "directoryMode" ] as $param) {
      self::validateMode(${$param});
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::withinDocumentRoot($path);
    try {
      Shell::execute("find '{$path}' -follow -type f -exec chmod {$fileMode} {} \;");
      Shell::execute("find '{$path}' -follow -type d -exec chmod {$directoryMode} {} \;");
    }
    catch (\Exception $e) {
      throw new \RuntimeException("Couldn't change modes of '{$path}'", null, $e);
    }
  }

  /**
   * Change file owernship.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $path
   *   The file's absolute path to change owner.
   * @param null|string $user [optional]
   *   The file's new owning user name, defaults to <code>NULL</code> which will apply the kernel user.
   * @param null|string $group [optional]
   *   The file's new owning group name, defaults to <code>NULL</code> which will apply the kernel group.
   * @param boolean $recursive [optional]
   *   Whether to change owernship recursively, only makes sense with directories.
   * @throws \ErrorException
   */
  public static function changeOwner($path, $user = null, $group = null, $recursive = false) {
    global $kernel;
    // We can't simply change the ownership of a file if we aren't root or executed via sudo. First of all we can't
    // change the user at all at that point, because we aren't that user and don't have the privileges to give him or
    // her files. In case of the group it's pretty similar, we can't assume that our current user is part of that group
    // and attempting to change the group to a group we aren't part of would also result in an error.
    //
    // This method is mainly meant for the CLI application which is often executed via sudo and we have to enable
    // ourselfs for full control over the ownership in those cases.
    if (posix_getuid() !== 0) {
      return;
    }
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("Path cannot be empty and must be of type string");
    }
    foreach ([ "user", "group" ] as $param) {
      if (${$param} && (empty(${$param}) || is_string(${$param}) === false)) {
        throw new \InvalidArgumentException("\${$param} cannot be empty and must be of type string");
      }
    }
    if (is_bool($recursive) === false) {
      throw new \InvalidArgumentException("\$recursive must be of type boolean");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (!isset($user)) {
      $user = $kernel->systemUser;
    }
    if (!isset($group)) {
      $group = $kernel->systemGroup;
    }
    if ($recursive === true) {
      try {
        Shell::execute("chown --preserve-root --recursive --verbose {$user}:{$group} '{$path}'");
      }
      catch (\Exception $e) {
        throw new \ErrorException("Couldn' change ownership of '{$path}' and its childs", null, $e);
      }
    }
    elseif (chown($path, $user) === false || chgrp($path, $group) === false) {
      // @codeCoverageIgnoreStart
      // chown() and/or chgrp() may emit E_ERROR which is automatically transformed to \ErrorException, this is only to
      // make sure that an \ErrorException is always thrown if chown() and/or chgrp() return FALSE.
      throw new \ErrorException("Couldn't change ownership of '{$path}'");
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   * Create local directory.
   *
   * @param string $path
   *   The directory's absolute path.
   * @param boolean $parents [optional]
   *   Create parent directories as needed, defaults to <code>TRUE</code>.
   * @param string $mode [optional]
   *   The directory's mode, defaults to {@see FileSystem::DIRECTORY_MODE}.
   * @param null|string $user [optional]
   *   The directory's owning user, defaults to <code>NULL</code>.
   *
   *   If no user name is passed and the current process isn't running via php-fpm the user from the global
   *   configuration will be applied to the directory.
   * @param null|string $group [optional]
   *   The directory's owning group, defaults to <code>NULL</code>.
   *
   *   If no group name is passed and the current process isn't running via php-fpm the group from the global
   *   configuration will be applied to the directory.
   * @return string
   *   <var>$path</var>
   * @throws \ErrorException
   */
  public static function createDirectory($path, $parents = true, $mode = FileSystem::DIRECTORY_MODE, $user = null, $group = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("Path cannot be empty and must be of type string");
    }
    if (is_bool($parents) === false) {
      throw new \InvalidArgumentException("\$parents must be of type boolean");
    }
    self::validateMode($mode);
    // @codeCoverageIgnoreEnd
    // @devEnd

    self::withinDocumentRoot($path);

    // We want the call to mkdir() fail if the path already exists but isn't a directory to make sure that the caller
    // doesn't assume working with a directory while it is a file or link in reality. We don't want the call to this
    // method to fail if the directory already exists, that's why we check with is_dir().
    if (is_dir($path) === false && mkdir($path, self::validateMode($mode), $parents) === false) {
      // @codeCoverageIgnoreStart
      // mkdir() may emit E_ERROR which is automatically transformed to \ErrorException, this is only to make sure that
      // an \ErrorException is always thrown if mkdir() returns FALSE.
      throw new \ErrorException("Couldn't create directory '{$path}'");
      // @codeCoverageIgnoreEnd
    }
    self::changeOwner($path, $user, $group);

    return $path;
  }

  /**
   * Create symbolic link.
   *
   * @param string $target
   *   The symbolic link's absolute target path.
   * @param string $link
   *   The symoblic link's absolute path.
   * @param boolean $force [optional]
   *   Whether to override existing destination or not, defaults to <code>FALSE</code> (do not override).
   * @throws \ErrorException
   */
  public static function createSymbolicLink($target, $link, $force = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "target", "link" ] as $param) {
      if (empty(${$param}) || is_string(${$param}) === false) {
        throw new \InvalidArgumentException("\${$param} cannot be empty and must be of type string");
      }
    }
    if (is_file($target) === false && is_dir($target) === false) {
      throw new \InvalidArgumentException("\$target must be a regular file or directory");
    }
    if (realpath($target) != $target) {
      throw new \InvalidArgumentException("\$target must be absolute: {$target}");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::withinDocumentRoot($target);
    self::withinDocumentRoot($link);
    if ($force === true) {
      Shell::execute("ln --force --symbolic --verbose '{$target}' '{$link}'");
    }
    elseif (symlink($target, $link) === false) {
      // @codeCoverageIgnoreStart
      // symlink() sometimes emits and E_ERROR which is automatically translated to an \ErrorException, therefore we
      // throw an \ErrorException as well at this point and allow callers to catch a single exception type.
      throw new \ErrorException("Couldn't create symbolic link from {$target} to {$link}");
      // @codeCoverageIgnoreEnd
    }
  }

  /**
   * Delete local file.
   *
   * <b>NOTE</b><br>
   * This method will silently return if the file doesn't exist.
   *
   * @param string $path
   *   Absolute path of the file to delete.
   * @param boolean $recursive [optional]
   *   Whether to recursively delete, only makes sense if the file is a directory or link. Defaults to
   *   <code>FALSE</code>.
   * @param boolean $force [optional]
   *   Whether to force deletion, only makes sense if the file is a directory and should be deleted even if is non-empty.
   *   Defaults to <code>FALSE</code>.
   * @throws \Exception
   * @throws \RuntimeException
   */
  public static function delete($path, $recursive = false, $force = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("\$path cannot be empty and must be of type string");
    }
    foreach ([ "recursive", "force" ] as $param) {
      if (is_bool(${$param}) === false) {
        throw new \InvalidArgumentException("\${$param} must be of type boolean");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::withinDocumentRoot($path);
    if (file_exists($path) === true) {
      if ($recursive === false) {
        if (is_dir($path) === true) {
          if (rmdir($path) === false) {
            throw new \RuntimeException("Couldn't delete '{$path}'");
          }
        }
        elseif (unlink($path) === false) {
          throw new \RuntimeException("Couldn't delete '{$path}'");
        }
      }
      else {
        $force = ($force === true) ? "--force" : null;
        Shell::execute("rm --recursive {$force} '{$path}'");
      }
    }
  }

  /**
   * Delete the directory and all its parent directories.
   *
   * @param string $directory
   *   Absolute path to the directory from which the deletion process should start.
   * @throws \RuntimeException
   */
  public static function deleteDirectories($directory) {
    Shell::execute("rmdir --ignore-fail-on-non-empty --parent '{$directory}'");
  }

  /**
   * Get file's content.
   *
   * @param string $path
   *   The file's absolute path to get the content from.
   * @return string
   *   The file's content.
   * @throws \RuntimeException
   */
  public static function getContent($path) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("\$path cannot be empty and must be of type string");
    }
    if (file_exists($path) === false) {
      throw new \InvalidArgumentException("The desired file to get the content from doesn't exist");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    self::withinDocumentRoot($path);
    try {
      $content = file_get_contents($path);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    if ($content === false) {
      throw new \RuntimeException("Couldn't get content of '{$path}'");
    }

    return $content;
  }

  /**
   * Get the file's permissions.
   *
   * <b>NOTE</b><br>
   * This method will always return a string with exactly four characters.
   * <ol>
   *   <li>The first character specifies the special mode and is either 0 (no special mode), 2 (set group ID), or
   *   4 (set user ID).</li>
   *   <li>The second character specifies the mode for the owning user.</li>
   *   <li>The third character specifies the mode for the owning group.</li>
   *   <li>The fourth character specifies the mode for the rest of the world.</li>
   * </ol>
   * See <code>man chmod</code> for more information.
   *
   * @param string $path
   *   The file's absolute path to get the permissions for.
   * @return string
   *   The file's permissions.
   */
  public static function getPermissions($path) {
    clearstatcache();
    $mode   = decoct(fileperms($path));
    $length = strlen($mode);
    if ($length > 4) {
      $mode = substr($mode, $length - 4);
    }
    return $mode;
  }

  /**
   * Decode JSON string from file.
   *
   * @see FileSystem::getContent()
   * @param string $path
   *   The file's absolute path to get the JSON from.
   * @param boolean $assoc [optional]
   *   <code>TRUE</code> to return associative array, <code>FALSE</code> (default) to return object.
   * @param integer $depth [optional]
   *   Maximum recursion depth, defaults to <code>512</code>.
   * @param integer $options [optional]
   *   Bitmask of <var>JSON_*</var> decode options.
   * @return array|object
   *   The decoded JSON string from the file either as object or array (depending on <var>$assoc</var>).
   * @throws \RuntimeException
   */
  public static function getJSON($path, $assoc = false, $depth = 512, $options = 0) {
    $json = json_decode(self::getContent($path), $assoc, $depth, $options);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException(json_last_error_msg());
    }
    return $json;
  }

  /**
   * Resolves the absolute path of a local file.
   *
   * <b>Note:</b> Only use this method with local files!
   *
   * An exception is thrown if PHP's built-in <code>realpath()</code> returned <code>FALSE</code> and the file actually
   * does exist or if the current process isn't running in CLI mode and attempting to resolve a file outside the
   * document root.
   *
   * @param string $path
   *   The local file to get the absolute path to.
   * @return string
   *   The absolute path of the local file.
   * @throws \RuntimeException
   */
  public static function getRealPath($path) {
    // Let PHP resolve the absolute path to the file.
    $realpath = realpath($path);

    // Check if PHP was able to do so.
    if ($realpath === false) {
      // realpath() will return FALSE if the file doesn't exist, which isn't really an error for us. Therefore we have
      // to check if the file really doesn't exist, this on the other hand means that some other error occurred and we
      // throw an exception in this case.
      if (file_exists($path) === true) {
        throw new \RuntimeException("Path is invalid: {$path}");
      }
      // @todo Should we attempt to canonicalize the path ourselfs at this point?
    }
    // Only use the realpath result if it isn't of type boolean.
    else {
      return $realpath;
    }

    return $path;
  }

  /**
   * Find files and directories in path optionally filtered by extension(s).
   *
   * <b>IMPORTANT</b><br>
   * Avoid using complicated <code>FileSystem::glob()</code> or <code>glob()</code> calls because they use a special
   * kind of <i>regular expression</i>. It's wiser to use the {@see \MovLib\Data\FileSystem::globRegExp} for that
   * because everyone knows how to use PCRE regular expressions. This is the reason why this method refers to the first
   * parameter as <i>path</i> instead of <i>pattern</i>.
   *
   * @see \MovLib\Data\FileSystem::globRecursive()
   * @see \MovLib\Data\FileSystem::globRegExp()
   * @param string $path
   *   Absolute path to the directory to search in. It's basically the same as the first parameter to PHP's built-in
   *   <code>glob()</code> function, read the <b>IMPORTANT</b> note in this methods description why you shouldn't use
   *   it like that.
   * @param mixed $extension [optional]
   *   Either <code>NULL</code> (default) which will treat <var>$path</var> exactly like PHP's built-in first parameter
   *   of <code>glob()</code>, or a string containing a single extension (e.g. <code>"php"</code>), or a numeric array
   *   containing multiple extensions (e.g. <code>[ "php", "inc" ]</code>) which will automatically add the
   *   <var>GLOB_BRACE</var> flag to the <var>$flags</var> and format the pattern accordingly.
   * @param integer $flags [optional]
   *   Same flags as PHP's built-in <code>glob()</code> function accepts, defaults to <var>GLOB_NOSORT</var> which you
   *   should always use to improve performance unless you really need a sorted array. Also note that
   *   <var>GLOB_BRACE</var> is automatically added to the flags if <var>$extensions</var> is of type array. Flags are
   *   combined with the binary or (<code>|</code>) operator.
   * @return array
   *   Array containing the matched files.
   * @throws \RuntimeException
   */
  public static function glob($path, $extension = null, $flags = GLOB_NOSORT) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || !is_string($path)) {
      throw new \InvalidArgumentException("\$pattern cannot be empty and must be of type string");
    }
    if (empty($extension) && (!is_string($extension) || !is_array($extension))) {
      throw new \InvalidArgumentException("\$extensions cannot be empty and must be of type string or array");
    }
    if (!is_integer($flags)) {
      throw new \InvalidArgumentException("\$flags must be of type integer, use the GLOB_* constants");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      if (isset($extension)) {
        if ($extension === (array) $extension) {
          $extensions = implode(",", $extension);
          $path   .= "/*.{{$extensions}}";
          $flags     |= GLOB_BRACE;
        }
        else {
          $path .= "/*.{$extension}";
        }
      }
      $matches = glob($path, $flags);
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    if ($matches === false) {
      throw new \RuntimeException("Globbing with pattern '{$path}' failed");
    }
    return (array) $matches;
  }

  /**
   * Move a file.
   *
   * <b>NOTE</b><br>
   * If <var>$to</var> exists, it will be overwritten.
   *
   * @param string $from
   *   Absolute path of the file to move.
   * @param string $to
   *   Absolute path of the file's destination.
   * @throws \RuntimeException
   */
  public static function move($from, $to) {
    try {
      $status = rename(rtrim($from, "/"), rtrim($to, "/"));
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    if ($status === false) {
      throw new \RuntimeExcepiton("Couldn't move file from '{$from}' to '{$to}'");
    }
  }

  /**
   * Write content to file.
   *
   * @param string $path
   *   The file's absolute path.
   * @param null|string $content [optional]
   *   The content to write, defaults to <code>NULL</code> which is the same as calling <code>touch()</code>.
   * @param integer $flags [optional]
   *   A combination of the following flags, you can join flags with the binary or operator <code>|<code>:
   *   <table>
   *     <tr><th>Flag</th><th>Description</th></tr>
   *     <tr><td><code>FILE_APPEND</code></td>Append content to file rather than overwriting existing file content.<td></td>
   *     <tr><td><code>LOCK_EX</code></td>Acquire an exclusive lock on the file while writing<td></td>
   *   </table>
   * @param null|integer $modificationTime [optional]
   *   The file's modification time, defaults to <code>NULL</code> (current time).
   * @param null|integer $accessTime [optional]
   *   The file's access time, defaults to <code>NULL</code> (<var>$modificationTime</var> or current time).
   * @throws \RuntimeException
   */
  public static function putContent($path, $content = null, $flags = 0, $modificationTime = null, $accessTime = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || is_string($path) === false) {
      throw new \InvalidArgumentException("\$path cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::withinDocumentRoot($path);
    if (file_put_contents($path, "{$content}", $flags) === false) {
      throw new \RuntimeException("Couldn't create and/or write to file {$path}");
    }
    if ($modificationTime && touch($path, $modificationTime, $accessTime ?: $modificationTime) === false) {
      throw new \RuntimeException("Couldn't change modification and/or access time of tile {$path}");
    }
  }

  /**
   * Encode and write JSON data to file.
   *
   * @see FileSystem::putContent()
   * @param string $path
   *   The file's absolute path.
   * @param mixed $data
   *   The data to JSON encode and write to file. Can be any type except a resource.
   * @param integer $flags [optional]
   *   A combination of the following flags, you can join flags with the binary or operator <code>|<code>:
   *   <table>
   *     <tr><th>Flag</th><th>Description</th></tr>
   *     <tr><td><code>FILE_APPEND</code></td>Append content to file rather than overwriting existing file content.<td></td>
   *     <tr><td><code>LOCK_EX</code></td>Acquire an exclusive lock on the file while writing<td></td>
   *   </table>
   * @param integer $options [optional]
   *   The JSON encode options, {@link http://php.net/json.constants.php}.
   * @param null|integer $modificationTime [optional]
   *   The file's modification time, defaults to <code>NULL</code> (current time).
   * @param null|integer $accessTime [optional]
   *   The file's access time, defaults to <code>NULL</code> (<var>$modificationTime</var> or current time).
   * @return string
   *   <var>$data</var> encoded as JSON.
   * @throws \RuntimeException
   */
  public static function putJSON($path, $data, $flags = 0, $options = JSON_UNESCAPED_UNICODE, $modificationTime = null, $accessTime = null) {
    $json = json_encode($data, $options);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException(json_last_error_msg());
    }
    self::putContent($path, $json, $flags, $modificationTime, $accessTime);
    return $json;
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
  public static function sanitizeFilename($filename) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($filename) || is_string($filename) === false) {
      throw new \InvalidArgumentException("\$filename cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Remove characters which aren't allowed in filenames.
    $filename = str_replace([ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", '"', "&", "$", "#", "*", "(", ")", "|", "~" ], "", $filename);

    // Replace whitespace characters with dashes.
    $filename = preg_replace("/[\s-]+/", "-", $filename);

    // Remove characters which aren't allowed at the beginning and end of a filename.
    $filename = trim($filename, ".-_");

    // Always lowercase all filenames for better compatibility.
    return mb_strtolower($filename);
  }

  /**
   * Validate file mode.
   *
   * <b>NOTE</b><br>
   * A valid mode has at least four characters and
   *
   * @param string $mode
   *   The mode to validate.
   * @return integer
   *   The validated mode as octal integer.
   * @throws \InvalidArgumentException
   */
  public static function validateMode($mode) {
    // Mode is mandatory.
    if (empty($mode)) {
      throw new \InvalidArgumentException("A file's mode cannot be empty");
    }

    // Make sure that we are dealing with an octal number.
    if (is_string($mode) === false) {
      throw new \InvalidArgumentException("A file's mode must be of type string");
    }

    // Make sure that the format of the given octal number is correct and only contains valid values.
    if (preg_match("/0?[024][0-7]{3}/", $mode) !== 1) {
      throw new \InvalidArgumentException(
        "Given file mode '{$mode}' is invalid. A file's mode may contain up to five characters, where the first " .
        "character has to be 0. The second character (or first if the leading zero is omitted) is the sticky bit " .
        "and can therefore only have 0, 1, or 2. The next three characters are the actual permission bits and " .
        "have to be between 0 and 7. See `man chown` for more information"
      );
    }

    return intval($mode, 8);
  }

  /**
   * Check if given path is within the document root.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $path
   *   Absolute path to check.
   * @throws \LogicException
   */
  public static function withinDocumentRoot($path) {
    global $kernel;
    if ($kernel->fastCGI === true && strpos($path, $kernel->documentRoot) === false) {
      throw new \LogicException("\$path cannot be outside document root");
    }
  }

}
