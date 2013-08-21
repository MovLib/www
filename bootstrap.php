<?php

/* !
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
 * Bootstrap environment for CLI and PHPUnit.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

// The following variables are always available in our environment and set via nginx. We have to create them here on
// our own because PHPUnit will not invoke nginx.
$_SERVER["HOME"] = __DIR__;

// Include composer autoloader, this enables us to load our own stuff but also everything that we need via composer.
$composerAutoloader = require "{$_SERVER["HOME"]}/vendor/autoload.php";
$composerAutoloader->add("MovLib", "{$_SERVER["HOME"]}/src");
$composerAutoloader->add("MovLib\Test", "{$_SERVER["HOME"]}/tests");

// Create global configuration.
$GLOBALS["conf"] = parse_ini_file("{$_SERVER["HOME"]}/conf/movlib.ini", true);

// Needed by various objects (e.g. DelayedLogger).
$i18n = new \MovLib\Model\I18nModel(ini_get("intl.default_locale"));
$_SERVER["LANGUAGE_CODE"] = $i18n->getDefaultLanguageCode();
