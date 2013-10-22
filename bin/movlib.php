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
 * Bootstrap environment for command line interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
ini_set("display_errors", true);
$_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__);
$composerAutoloader       = require "{$_SERVER["DOCUMENT_ROOT"]}/vendor/autoload.php";
$composerAutoloader->add("MovLib", "{$_SERVER["DOCUMENT_ROOT"]}/src/");
$config                   = new \MovLib\Tool\Configuration();
$db                       = new \MovLib\Tool\Database();
$i18n                     = new \MovLib\Data\I18n();
(new \MovLib\Tool\Console\Application())->run();
