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
namespace MovLib\Core;

use \MovLib\Core\Log;
use \MovLib\Exception\FileSystemException;

/**
 * Global File System
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class FileSystem {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Default binary mode.
   *
   * @var integer
   */
  const MODE_BIN = 0774;

  /**
   * Default directory mode.
   *
   * @var integer
   */
  const MODE_DIR = 0775;

  /**
   * Default file mode.
   *
   * @var integer
   */
  const MODE_FILE = 0664;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * The real document root path.
   *
   * @var string
   */
  public $documentRoot;

  /**
   * List of files that should be deleted on shutdown.
   *
   * @var array
   */
  protected static $registeredFiles = [];

  /**
   * Scheme class mapping for available stream wrappers.
   *
   * @var array
   */
  protected static $streamWrappers = [
    "dr"     => "\\MovLib\\Core\\StreamWrapper\\DocumentRootStreamWrapper",
    "asset"  => "\\MovLib\\Core\\StreamWrapper\\AssetStreamWrapper",
    "upload" => "\\MovLib\\Core\\StreamWrapper\\UploadStreamWrapper",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new file system.
   *
   * @param string $documentRoot
   *   Real document root path.
   */
  public function __construct($documentRoot) {
    $this->documentRoot = $documentRoot;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Compress file.
   *
   * @global \MovLib\Core\Config $config
   * @global \MovLib\Core\Kernel $kernel
   * @param string $uri
   *   URI of the file to compress.
   * @return string
   *   URI of the compressed file.
   * @throws \MovLib\Exception\FileSystemException
   */
  public function compress($uri) {
    global $kernel;
    try {
      $realpath = $this->realpath($uri);
      Shell::execute("zopfli --ext 'gz' --gzip --verbose '{$realpath}'");
      $urigz = "{$uri}.gz";
      touch($urigz, filemtime($uri));
      if ($kernel->privileged === true) {
        global $config;
        chown($urigz, $config->user);
        chgrp($urigz, $config->group);
      }
      return $urigz;
    }
    catch (ShellException $e) {
      throw new FileSystemException("Couldn't compress '{$uri}'", null, $e);
    }
  }

  /**
   * Delete all files that were registered for deletion.
   *
   * @return this
   */
  public function deleteRegisteredFiles() {
    foreach (self::$registeredFiles as $uri => $recursive) {
      try {
        if ($recursive === true) {
          /* @var $fileinfo \SplFileInfo */
          foreach ($this->getRecursiveIterator($uri) as $fileinfo) {
            if ($fileinfo->isDir()) {
              rmdir($fileinfo->getPathname());
            }
            else {
              unlink($fileinfo->getPathname());
            }
          }
        }
        if (is_dir($uri)) {
          rmdir($uri);
        }
        else {
          unlink($uri);
        }
      }
      catch (\Exception $e) {
        Log::error($e);
      }
    }
    return $this;
  }

  /**
   * Get a recursive iterator to iterate through directories.
   *
   * The return iterator is suitable for usage with a <code>foreach</code> loop and will return childs first and skipt
   * dots. Essentially it's perfect for recursive deletion of files and directories or to find files together with a
   * {@see \RegexIterator}.
   *
   * <b>EXAMPLE</b><br>
   * <pre>
   * global $fs;
   * foreach (new \RegexIterator($fs->getRecursiveIterator("dr://src"), "/\.php$/") as $fileinfo) {
   *   touch($fileinfo->getPathname());
   * }
   * </pre>
   *
   * @param string $uri
   *   URI of the start directory to recursively iterate through.
   * @return \RecursiveIteratorIterator
   */
  public function getRecursiveIterator($uri) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($uri) || !is_string($uri)) {
      throw new \InvalidArgumentException("\$uri cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uri, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
  }

  /**
   * Get a stream wrapper instance for the given URI.
   *
   * @param string $uri
   *   The URI to get the instance for.
   * @return \MovLib\Core\StreamWrapper\AbstractLocalStreamWrapper
   *   A stream wrapper instance for the given URI.
   * @throws \MovLib\Exception\FileSystemException
   */
  public function getStreamWrapper($uri) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($uri) || !is_string($uri)) {
      throw new \InvalidArgumentException("\$uri cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      $scheme           = explode("://", $uri, 2)[0];
      $instance         = new self::$streamWrappers[$scheme]();
      $instance->scheme = $scheme;
      $instance->uri    = $uri;
      return $instance;
    }
    catch (\ErrorException $e) {
      throw new FileSystemException("No stream wrapper available to handle '{$uri}'.", null, $e);
    }
  }

  /**
   * Get the canonical absolute path of a URI.
   *
   * @param string $uri
   *   The URI to get the path for.
   * @return boolean|string
   *   The canonical absolute path of the URI or <code>FALSE</code> if the path couldn't be resolved.
   */
  public function realpath($uri) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($uri) || !is_string($uri)) {
      throw new \InvalidArgumentException("\$uri cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Make sure that we're really dealing with URI at this point and use PHP's built-in realpath if we aren't.
    if (strpos($uri, "://") === false) {
      $realpath = realpath($uri);

      // PHP's realpath will return FALSE for files that don't exist, we try to resolve at least the parent directory
      // in this case, if this fails as well return FALSE.
      if ($realpath === false) {
        $realpath = realpath(dirname($uri));
      }
    }
    else {
      $realpath = $this->getStreamWrapper($uri)->realpath();
    }

    return $realpath;
  }

  /**
   * Register file for deletion on shutdown.
   *
   * @param string $uri
   *   URI of the file that should be deleted on shutdown.
   * @param boolean $force
   *   If set to <code>TRUE</code> and the given URI points to a directory, everything within the directory will be
   *   deleted including the directory itself. Defaults to <code>FALSE</code>, which means that the deletion of a non-
   *   empty directory will fail.
   * @return this
   */
  public function registerFileForDeletion($uri, $force = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($uri) || !is_string($uri)) {
      throw new \InvalidArgumentException("\$uri cannot be empty and must be of type string.");
    }
    if (!is_bool($force)) {
      throw new \InvalidArgumentException("\$force must be of type boolean.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::$registeredFiles[$uri] = $force;
    return $this;
  }

  /**
   * Instantiate new file system.
   *
   * @staticvar boolean $registered
   *   Whether stream wrappers were already registered or not.
   * @return this
   */
  public function registerStreamWrappers() {
    static $registered = false;
    if ($registered === false) {
      foreach (self::$streamWrappers as $scheme => $class) {
        if (stream_wrapper_register($scheme, $class) === false) {
          Log::warning(
            "Couldn't register '{$class}' as stream wrapper for scheme '{$scheme}://'. This might be because another " .
            "stream wrapper is already registered for this scheme."
          );
        }
      }
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
  public function sanitizeFilename($filename) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($filename) || !is_string($filename)) {
      throw new \InvalidArgumentException("\$filename cannot be empty and must be of type string.");
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
   * Create a symbolic link.
   *
   * @param string $target
   *   The symbolic link's target.
   * @param string $link
   *   The symbolic link.
   * @return this
   * @throws \MovLib\Exception\FileSystemException
   */
  public function symlink($target, $link) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "target", "link" ] as $param) {
      if (empty(${$param}) || !is_string($param)) {
        throw new \InvalidArgumentException("\${$param} cannot be empty and must be of type string.");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      if (!is_link($link)) {
        symlink($this->realpath($target), $this->realpath($link));
      }
      return $this;
    }
    catch (\ErrorException $e) {
      throw new FileSystemException("Couldn't create symbolic link '{$link}' with target '{$target}'.", null, $e);
    }
  }

  /**
   * Encode URL path preserving slashes.
   *
   * @param string $path
   *   The URL path to encode.
   * @return string
   *   The encoded URL path.
   */
  public function urlEncodePath($path) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($path) || !is_string($path)) {
      throw new \InvalidArgumentException("\$path cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return str_replace("%2F", "/", rawurlencode($path));
  }

}
