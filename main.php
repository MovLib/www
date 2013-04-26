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
 * Main PHP script serving all page requests within the MovLib application.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright (c) 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 * @since 0.0.1-dev
 */

/** Install path */
define('IP', __DIR__);

/**
 * Ultra fast class autoloader.
 *
 * @param string $class
 *   Fully qualified class name (automatically passed to this magic function by PHP).
 * @return void
 */
function __autoload($class) {
  require IP . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';
}

// This is the most outer place to catch an exception.
try {
  /* @var $presenter string */
  $presenter = '\\MovLib\\Presenter\\' . $_SERVER['PRESENTER'] . 'Presenter';
  echo (new $presenter())->getOutput();
}
/* @var $e \Exception */
catch (\Exception $e) {
  echo $e->getMessage();
}
