#!/usr/bin/env php
<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * Executable for *nix systems.
 *
 * <strong>Note!</strong> For global access create a symbolic link to this file in your local bin path, example:
 * <pre># ln -s movcli.php /usr/local/bin/movcli && chmod 755 movcli</pre>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

// Create absolut path to root directory.
$_SERVER["HOME"] = dirname(__DIR__);

// Include composer autoloader, this enables us to load our own stuff but also everything that we need via composer.
$composerAutoloader = require "{$_SERVER["HOME"]}/vendor/autoload.php";
$composerAutoloader->add("MovLib", "{$_SERVER["HOME"]}/src");

// Create global configuration.
$GLOBALS["conf"] = parse_ini_file("{$_SERVER["HOME"]}/conf/movlib.ini", true);

// Initialize application.
(new \MovLib\Console\Application\MovCli())->run();
