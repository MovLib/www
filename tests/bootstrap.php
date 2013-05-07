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
 * Setup environment for testing our software.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $loader \Composer\Autoload\ClassLoader */
$loader = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$loader->add('MovLib\Test', __DIR__);

// This variable is always available to our software and set via nginx in normal environment.
$_SERVER['LANGUAGE_CODE'] = '';


/**
 * Get a <em>protected</em> or <em>private</em> method of a class as reflection function.
 *
 * @param string $fqClassName
 *   The fully qualified class name without leading backslash (this is very important, because PHPUnit will not be
 *   able to find the class if the name starts with it).
 * @param string $methodName
 *   Name of the method.
 * @return \ReflectionFunction
 */
function getReflectionFunction($fqClassName, $methodName) {
  $method = (new \ReflectionClass($fqClassName))->getMethod($methodName);
  $method->setAccessible(true);
  return $method;
}
