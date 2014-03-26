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
  if (posix_getuid() !== 0) {
    trigger_error("Creation of symbolic links only works as root (or sudo).", E_USER_ERROR);
  }
  foreach ([ "admin", "dev", "install" ] as $binary) {
    $binary = "/usr/local/bin/mov{$binary}";
    if (!is_link($binary)) {
      symlink(__FILE__, $binary);
    }
  }
  exit("Successfully created symbolic links.");
}

// Assume that we were invoked via one of the symbolic links.
$autoloader = require dirname(__DIR__) . "/lib/autoload.php";
$kernel     = new \MovLib\Core\Kernel();
$kernel->boot(dirname(__DIR__));
(new \MovLib\Console\Application(basename($_SERVER["PHP_SELF"], ".php")))->run();
