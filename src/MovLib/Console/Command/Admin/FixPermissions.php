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
namespace MovLib\Console\Command\Admin;

use \MovLib\Core\FileSystem;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix permissions on directories and files.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FixPermissions extends \MovLib\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("fix-permissions");
    $this->setDescription("Fix permissions on a directory and its contents.");
    $this->addArgument("uri", InputArgument::OPTIONAL, "The URI to fix, either a directory or file, all stream wrappers are available.", "dr://");
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Core\FileSystem $fs
   * @global \MovLib\Core\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $fs, $kernel;
    // Fixing permissions of all files only works reliable if executed as privileged user.
    if ($kernel->privileged === false) {
      $this->write(
        "You're executing this command as non privileged user which should work, if you get permission denied errors " .
        "execute as root or via sudo, but you should definitely check which files caused the problem.",
        self::MESSAGE_TYPE_ERROR
      );
    }

    $uri = str_replace("\\", "/", $input->getArgument("uri"));
    $this->writeDebug("URI: <comment>{$uri}</comment>...");

    if (strpos($uri, "://") === false) {
      $this->write("Not a URI, assuming path relative to document root...");
      $uri = "dr://{$uri}";
      $this->writeVerbose("New URI is <comment>{$uri}</comment>");
    }

    if ($fs->realpath($uri) === false) {
      throw new \InvalidArgumentException("The passed URI '{$uri}' doesn't exist");
    }

    if (is_file($uri)) {
      $fileinfo = new \SplFileInfo($uri);
      if ($this->fixBinary($fileinfo) === false) {
        $this->fixDirectoryOrFile($uri);
      }
    }
    else {
      $this->writeVerbose("Fixing file and directory permission...");
      /* @var $fileinfo \SplFileInfo */
      foreach ($fs->getRecursiveIterator($uri) as $fileinfo) {
        $this->fixDirectoryOrFile($fileinfo);
      }
      $this->writeVeryVerbose("Fixed file and directory permissions...");

      // Only fix binaries if we're privileged and matching against the document root.
      if ($kernel->privileged && $uri == "dr://") {
        $this->writeVerbose("Fixing binary permissions...");
        foreach ([ "bin", "vendor/bin", "etc/init.d" ] as $binaryDirectory) {
          $binaryDirectory = "dr://{$binaryDirectory}";
          if (is_dir($binaryDirectory)) {
            foreach (new \DirectoryIterator($binaryDirectory) as $fileinfo) {
              $this->fixBinary($fileinfo);
            }
          }
        }
        $this->writeVeryVerbose("Fixed binary permissions...");
      }
      else {
        $this->writeVerbose("Binaries are only fixed if executed as privileged user and against document root.", self::MESSAGE_TYPE_ERROR);
      }

      $this->writeVerbose("Fixed permissions in <comment>{$uri}</comment>", self::MESSAGE_TYPE_INFO);
    }

    return 0;
  }

  /**
   * Write debug message.
   *
   * @param string $path
   *   Absolute path of the file that is going to be fixed.
   * @param integer $mode
   *   The mode you are applying (octal).
   * @return boolean
   *   <code>TRUE</code> if permissions were fixed, otherwise <code>FALSE</code>.
   */
  protected function fix($path, $mode) {
    $this->writeDebug(sprintf("Fixing <comment>{$path}</comment> [<comment>%04o</comment>]", $mode));
    // Owner and group are directly fixed in the stream wrapper!
    return chmod($path, $mode);
  }

  /**
   * Fix binary permissions.
   *
   * @staticvar array $validExtensions
   *   List of valid extensions for binary files.
   * @staticvar array $validShebangs
   *   List of valid shebangs for binary files.
   * @param \SplFileInfo $fileinfo
   *   The fileinfo instance of the current loop.
   * @return boolean
   *   <code>TRUE</code> if permissions were fixed, otherwise <code>FALSE</code>.
   */
  protected function fixBinary($fileinfo) {
    static $validExtensions = [ "", "php", "sh" ]; // Empty is for PHPUnit!
    static $validShebangs = [ "#!/usr/bin/env php", "#!/bin/sh", "#!/bin/bash" ];

    if ($fileinfo->isFile() && in_array($fileinfo->getExtension(), $validExtensions)) {
      $path    = $fileinfo->getPathname();
      $shebang = trim((new \SplFileObject($path, "rb"))->fgets());
      $valid   = in_array($shebang, $validShebangs);
      $this->writeVeryVerbose(
        "Extracted shebang <comment>{$shebang}</comment> which is " .
        ($valid ? "<fg=green>valid</fg=green>" : "<fg=red>not valid</fg=red>")
      );
      if ($valid) {
        return $this->fix($path, FileSystem::MODE_BIN);
      }
    }

    return false;
  }

  /**
   * Fix directory or file permissions.
   *
   * @param \SplFileInfo $fileinfo
   *   The fileinfo instance of the current loop.
   * @return boolean
   *   <code>TRUE</code> if permissions were fixed, otherwise <code>FALSE</code>.
   */
  protected function fixDirectoryOrFile($fileinfo) {
    $path = $fileinfo->getPathname();

    if ($fileinfo->isDir()) {
      return $this->fix($path, FileSystem::MODE_DIR);
    }
    elseif ($fileinfo->isFile()) {
      return $this->fix($path, FileSystem::MODE_FILE);
    }

    return false;
  }

}
