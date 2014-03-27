#!/usr/bin/env php
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

/**
 * Initialize kernel for CLI usage and start application.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

// Create symbolic links if called directly.
if (basename($_SERVER["SCRIPT_FILENAME"]) == basename(__FILE__) && realpath($_SERVER["SCRIPT_FILENAME"]) == __FILE__) {
  if (count($argv) === 0) {
    if (posix_getuid() !== 0) {
      trigger_error("Creation of symbolic links only works as root (or sudo).", E_USER_ERROR);
    }

    /**
     * Write message to console output.
     *
     * @global array $argv
     * @param string $message
     *   The message to write.
     */
    $write = function ($message) {
      global $argv;
      foreach ([ "-v", "-vv", "-vvv", "--verbose" ] as $verbose) {
        if (in_array($verbose, $argv)) {
          echo $message , PHP_EOL;
          return;
        }
      }
    };

    // Create symbolic links for all three binaries.
    // - "admin" is the default command
    // - "dev" is only available in development environments
    // - "install" is for various installation tasks that are needed in production and in development
    foreach ([ "admin", "dev", "install" ] as $binary) {
      $binary = "/usr/local/bin/mov{$binary}";
      $write("Creating symbolic link for {$binary}");
      if (!is_link($binary)) {
        symlink(__FILE__, $binary);
      }
    }

    exit();
  }
}

require dirname(__DIR__) . "/lib/autoload.php";
(new \MovLib\Core\Kernel())->bootCLI(dirname(__DIR__), basename($_SERVER["PHP_SELF"], ".php"));
