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

// The web application catches all errors and exceptions itself and displays the errors to the user or developer. The
// default setting is therefor to surpress display errors. Re-activate for console and PHPUnit tests.
ini_set("display_errors", 1);

$_SERVER["DOCUMENT_ROOT"] = __DIR__;

$composerAutoloader       = require "{$_SERVER["DOCUMENT_ROOT"]}/vendor/autoload.php";
$composerAutoloader->add("MovLib", "{$_SERVER["DOCUMENT_ROOT"]}/src");
$composerAutoloader->add("MovLib\Test", "{$_SERVER["DOCUMENT_ROOT"]}/tests");

$i18n                     = new \MovLib\Data\I18n();
$_SERVER["LANGUAGE_CODE"] = $i18n->defaultLanguageCode;

$session                  = new \MovLib\Data\Session();
$session->userId          = 1;
$session->userName        = "Fleshgrinder";
$session->csrfToken       = "csrf";
$session->isAuthenticated = true;
$session->authentication  = time();

$GLOBALS["movlib"]        = parse_ini_file("{$_SERVER["DOCUMENT_ROOT"]}/conf/movlib.ini");

// The following variables are always available in our environment and set via nginx. We have to create them here on
// our own because PHPUnit will not invoke nginx.
$_SERVER["SCHEME"]        = "https";
$_SERVER["SERVER_NAME"]   = "{$_SERVER["LANGUAGE_CODE"]}.{$GLOBALS["movlib"]["default_domain"]}";
$_SERVER["SERVER"]        = "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}";

// Flag indicating if in development environment.
define("DEV", strpos($GLOBALS["movlib"]["version"], "-dev") === false ? false : true);

/**
 * Get accessible reflection method of <var>$object</var>.
 *
 * @param stdObject $object
 *   The object containing the method.
 * @param string $method_name
 *   The name of the method.
 * @return \ReflectionMethod
 *   The accessible reflection method.
 */
function get_reflection_method($object, $method_name) {
  $f = new \ReflectionMethod($object, $method_name);
  $f->setAccessible(true);
  return $f;
}

/**
 * Get accessible property of <var>$object</var>.
 *
 * @param stdObject $object
 *   The object containing the property.
 * @param string $property_name
 *   The name of the property.
 * @return \ReflectionProperty
 *   The accessible reflection property.
 */
function get_reflection_property($object, $property_name) {
  $p = new \ReflectionProperty($object, $property_name);
  $p->setAccessible(true);
  return $p;
}
