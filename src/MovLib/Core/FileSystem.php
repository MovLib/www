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

use \MovLib\Core\Shell;
use \MovLib\Exception\FileSystemException;

/**
 * Defines the file system object.
 *
 * The core file system is also the factory for all custom stream wrappers.
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
   * The active config instance.
   *
   * <b>NOTE</b><br>
   * Must be public to allow stream wrappers to access the configuration.
   *
   * @var \MovLib\Core\Config
   */
  public $config;

  /**
   * The real document root path.
   *
   * <b>NOTE</b><br>
   * Must be public to allow stream wrappers to access the configuration.
   *
   * @var string
   */
  public $documentRoot;

  /**
   * The process group.
   *
   * @var string
   */
  public $group;

  /**
   * The active log instance.
   *
   * <b>NOTE</b><br>
   * Must be public to allow stream wrappers to access the configuration.
   *
   * @var \MovLib\Core\Log
   */
  public $log;

  /**
   * Whether this process has elevated privileges or not.
   *
   * @see FileSystem::setProcessOwner()
   * @var boolean
   */
  public $privileged = false;

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

  /**
   * The process user.
   *
   * @var string
   */
  public $user;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new file system object.
   *
   * @param \MovLib\Core\Config $config
   * @param \MovLib\Core\Log $log
   * @param type $documentRoot
   */
  public function __construct(\MovLib\Core\Config $config, \MovLib\Core\Log $log, $documentRoot) {
    $this->config       = $config;
    $this->documentRoot = $documentRoot;
    $this->log          = $log;

    /* @var $class \MovLib\Core\StreamWrapper\AbstractLocalStream */
    foreach (self::$streamWrappers as $scheme => $class) {
      $class::$fs = $this;
      stream_wrapper_register($scheme, $class);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Compress file.
   *
   * @param string $uri
   *   URI of the file to compress.
   * @return string
   *   URI of the compressed file.
   * @throws \MovLib\Exception\FileSystemException
   */
  public function compress($uri) {
    try {
      $realpath = $this->realpath($uri);
      (new Shell())->execute("zopfli --ext 'gz' --gzip --verbose '{$realpath}'");
      $urigz = "{$uri}.gz";
      touch($urigz, filemtime($uri));
      if ($this->privileged && $this->user && $this->group) {
        chown($urigz, $this->user);
        chgrp($urigz, $this->group);
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
        $this->log->error($e);
      }
    }
    return $this;
  }

  /**
   * Get the external URL for the given URI.
   *
   * @staticvar array $streamWrappers
   *   Used to cache the stream wrapper instances.
   * @staticvar array $urls
   *   Used to cache generated URLs.
   * @param string $uri
   *   The URI to get the external URL for.
   * @return string
   *   The external URL for the given URI.
   * @throws \LogicException
   *   If the given URI doesn't support external URLs.
   */
  public function getExternalURL($uri) {
    static $streamWrappers = [], $urls = [];
    if (isset($urls[$uri])) {
      return $urls[$uri];
    }
    $scheme = explode("://", $uri, 2)[0];
    if (empty($streamWrappers[$scheme])) {
      $streamWrappers[$scheme] = new self::$streamWrappers[$scheme]();
    }
    return ($urls[$uri] = $streamWrappers[$scheme]->getExternalPath($uri));
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
   * @param integer $mode
   *   The {@see \RecursiveIteratorIterator} mode, see original documentation for more info. Defaults to
   *   {@see \RecursiveIteratorIterator::CHILD_FIRST}.
   * @return \RecursiveIteratorIterator
   */
  public function getRecursiveIterator($uri, $mode = \RecursiveIteratorIterator::CHILD_FIRST) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($uri) || !is_string($uri)) {
      throw new \InvalidArgumentException("\$uri cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uri, \RecursiveDirectoryIterator::SKIP_DOTS), $mode);
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
    assert(strpos($uri, "://") !== false, "A valid URI has the format <scheme>://<path>, your URI is {$uri}!");
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      $streamWrapper = new self::$streamWrappers[explode("://", $uri, 2)[0]]();
      $streamWrapper->uri = $uri;
      return $streamWrapper;
    }
    catch (\ErrorException $e) {
      throw new FileSystemException("No stream wrapper available to handle '{$uri}'.", null, $e);
    }
  }

  /**
   * Check if given directory is empty.
   *
   * @param string $uri
   *   URI or canonical path of the directory.
   * @return boolean
   *   <code>TRUE</code> if the directory is empty, <code>FALSE</code> otherwise.
   */
  public function isDirectoryEmpty($uri) {
    return (count(glob("{$this->realpath($uri)}/*")) === 0);
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
    assert(!empty($uri) && is_string($uri), "\$uri cannot be empty and must be of type string.");
    assert(is_bool($force), "\$force must be of type boolean.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    self::$registeredFiles[$uri] = $force;
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
    assert(!empty($filename) && is_string($filename), "\$filename cannot be empty and must be of type string.");
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
   * Set process owner (only used in CLI).
   *
   * @param string $user
   *   The process user, pass <code>NULL</code> to reset the user.
   * @param string $group
   *   The process group, pass <code>NULL</code> to reset the group.
   * @return this
   */
  public function setProcessOwner($user, $group) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(PHP_SAPI == "cli", "Process owner is set by php-fpm in HTTP context and you aren't allowed to change it!");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Determine if current process is privileged.
    $this->privileged = (posix_getuid() === 0);

    // Set or reset user based on passed parameter, if we're going to set the user make sure the user actually exists.
    if (isset($user)) {
      assert(posix_getpwnam($user));
      $this->user = $user;
    }
    else {
      $this->user = posix_getpwuid(posix_getuid())["name"];
    }

    // Set or reset group, same as above.
    if (isset($group)) {
      assert(posix_getgrnam($group));
      $this->group = $group;
    }
    else {
      $this->group = posix_getgrgid(posix_getgid())["name"];
    }

    return $this;
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
    if (empty($path) || $path == "/") {
      return $path;
    }
    return str_replace("%2F", "/", rawurlencode($path));
  }

}
