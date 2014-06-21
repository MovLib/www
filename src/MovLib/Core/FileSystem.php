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


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "FileSystem";
  // @codingStandardsIgnoreEnd

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
   * <b>NOTE</b><br>
   * Must be public to allow stream wrappers to access the configuration.
   *
   * @var string
   */
  public $documentRoot;

  /**
   * The hostname for static content.
   *
   * @var string
   */
  public $hostnameStatic;

  /**
   * The process group.
   *
   * @var string
   */
  public $group;

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
   * <b>NOTE</b><br>
   * This might be a total code smell at this point, but it's also very fast because we don't have to actually load the
   * stream wrappers to get their scheme and canonical absolute class name.
   *
   * @var array
   */
  protected static $streamWrappers = [
    "dr"     => "\\MovLib\\Core\\StreamWrapper\\DocumentRootStreamWrapper",
    "asset"  => "\\MovLib\\Core\\StreamWrapper\\AssetStreamWrapper",
    "upload" => "\\MovLib\\Core\\StreamWrapper\\UploadStreamWrapper",
  ];

  /**
   * Whether the stream wrappers where already registered or not.
   *
   * @var boolean
   */
  protected static $streamWrappersRegistered = false;

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
   * @param string $hostnameStatic [optional]
   *   The hostname for static content, will be used to build external URLs of static files.
   */
  public function __construct($hostnameStatic = "movlib.org") {
    // We directly prepend two slashes to the static hostname and build us a "protocol relative" domain.
    $this->hostnameStatic = "//{$hostnameStatic}";

    // Find the real document root by going up the current directory three times: /src/MovLib/Core
    $this->documentRoot   = dirname(dirname(dirname(__DIR__)));

    // Only register the stream wrappers if we haven't done so yet.
    if (self::$streamWrappersRegistered === false) {
      foreach (self::$streamWrappers as $scheme => $class) {
        // PHP's stream wrapper implementation doesn't allow dependency injection, we're therefore forced to static
        // property injection to ensure that every instance of a stream wrapper will have access to the file system.
        $class::$fileSystem = $this;

        // We don't care at this point if a stream wrapper was already registered or not.
        stream_wrapper_register($scheme, $class);
      }
      self::$streamWrappersRegistered = true;
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
   * Delete a directory and all of its content.
   *
   * @param string $uri
   *   Canonical absolute URI to the directory.
   * @return this
   * @throws \MovLib\Core\StreamWrapper\StreamException
   *   If deletion fails.
   */
  public function deleteRecursive($uri) {
    if (is_dir($uri)) {
      /* @var $fileinfo \SplFileInfo */
      foreach ($this->getRecursiveIterator($uri) as $fileinfo) {
        if ($fileinfo->isDir()) {
          rmdir($fileinfo->getPathname());
        }
        else {
          unlink($fileinfo->getPathname());
        }
      }
      rmdir($uri);
    }
    else {
      unlink($uri);
    }
    return $this;
  }

  /**
   * Delete all files that were registered for deletion.
   *
   * @param \MovLib\Core\Log $log
   *   The active log instance.
   * @return this
   */
  public function deleteRegisteredFiles(\MovLib\Core\Log $log) {
    foreach (self::$registeredFiles as $uri => $recursive) {
      try {
        if ($recursive === true) {
          $this->deleteRecursive($uri);
        }
        else {
          is_dir($uri) ? rmdir($uri) : unlink($uri);
        }
      }
      catch (\Exception $e) {
        $log->error($e);
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
   * @param string $cacheBuster [optional]
   *   A cache buster that should be appended to the URL, defaults to <code>NULL</code>.
   * @return string
   *   The external URL for the given URI.
   * @throws \InvalidArgumentException
   *   If no stream wrapper is registered for the scheme of <var>$uri</var>.
   * @throws \LogicException
   *   If the stream wrapper for <var>$uri</var> doesn't support external URLs.
   */
  public function getExternalURL($uri, $cacheBuster = null) {
    static $streamWrappers = [], $urls = [];

    // Directly return the cached URL for this URI if we have it.
    if (isset($urls[$uri])) {
      return $urls[$uri];
    }

    // Get the URI's scheme for stream wrapper look up.
    $scheme = explode("://", $uri, 2)[0];

    // Create new stream wrapper for this scheme if we have no cached instance.
    if (empty($streamWrappers[$scheme])) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (empty(self::$streamWrappers[$scheme])) {
        throw new \InvalidArgumentException("Unknown scheme '{$scheme}'.");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd

      // Instantiate and cache the stream wrapper instance.
      $streamWrappers[$scheme] = new self::$streamWrappers[$scheme]();

      // @devStart
      // @codeCoverageIgnoreStart
      if (!($streamWrappers[$scheme] instanceof StreamWrapper\ExternalStreamWrapperInterface)) {
        throw new \LogicException("Stream wrapper for scheme '{$scheme}' doesn't support external URLs.");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
    }

    // Prepend the static hostname to the URL, all external URLs built via stream wrappers are file downloads, and cache
    // the generated URL.
    return ($urls[$uri] = "{$this->hostnameStatic}{$streamWrappers[$scheme]->getExternalURL($uri, $cacheBuster)}");
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

}
