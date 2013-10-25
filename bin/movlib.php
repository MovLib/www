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
 * Bootstrap environment for command line interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
ini_set("display_errors", true);

/**
 * Bootstrap for console execution.
 *
 * @global \Composer\Autoload\ClassLoader $autoloader
 * @global \MovLib\Tool\Configuration $config
 * @global \MovLib\Tool\Database $db
 * @global \MovLib\Data\I18n $i18n
 */
call_user_func(function () {
  global $autoloader, $config, $db, $i18n;
  $root       = dirname(__DIR__);
  $autoloader = require "{$root}/vendor/autoload.php";
  $autoloader->add("MovLib", "{$root}/src/");
  new \MovLib\Exception\ConsoleHandlers();
  $config     = new \MovLib\Tool\Configuration();
  $db         = new \MovLib\Tool\Database();
  $i18n       = new \MovLib\Data\I18n();
});

// Start the console application.
(new \MovLib\Tool\Console\Application())->run();
