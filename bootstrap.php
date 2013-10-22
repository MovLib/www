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
 * Bootstrap environment for CLI and PHPUnit.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

$_SERVER["DOCUMENT_ROOT"] = __DIR__;

$composerAutoloader = require "{$_SERVER["DOCUMENT_ROOT"]}/vendor/autoload.php";
$composerAutoloader->add("MovLib", "src/");

new \MovLib\Exception\ConsoleHandlers();
$GLOBALS["movlib"] = parse_ini_file("{$_SERVER["DOCUMENT_ROOT"]}/conf/movlib.ini");
$i18n              = new \MovLib\Data\I18n();

// The following variables are always available in our environment and set via nginx. We have to create them here on
// our own because PHPUnit will not invoke nginx.
$_SERVER["LANGUAGE_CODE"]   = $i18n->defaultLanguageCode;
$_SERVER["PATH_INFO"]       = "/";
$_SERVER["REQUEST_URI"]     = "/";
$_SERVER["SCHEME"]          = "https";
$_SERVER["SERVER_NAME"]     = "{$_SERVER["LANGUAGE_CODE"]}.{$GLOBALS["movlib"]["default_domain"]}";
$_SERVER["SERVER"]          = "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}";
$_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
$_SERVER["SERVER_VERSION"]  = "";
$_SERVER["REMOTE_ADDR"]     = "127.0.0.1";
$_SERVER["HTTP_USER_AGENT"] = ini_get("user_agent");

// Flag indicating if in development environment.
define("DEV", strpos($GLOBALS["movlib"]["version"], "-dev") === false ? false : true);

if (DEV === true) {
  $composerAutoloader->add("MovDev", "src/");
  $db = new \MovDev\Database();
}

// --------------------------------------------------------------------------------------------------------------------- PHPUnit only


if (defined("MOVLIB_PHPUNIT")) {
  //$composerAutoloader->add("MovLib\\Test\\", "{$_SERVER["DOCUMENT_ROOT"]}/tests/");

  /**
   * Mock of delayed_register() from main.php
   */
  function delayed_register($class, $weight = null, $method = null) {
    // Do nothing!
  }

  // Mock a valid session for various PHPUnit tests.
  $session = new \MovLib\Data\User\Session();
  $init    = new \ReflectionMethod($session, "init");
  $init->setAccessible(true);
  $init->invokeArgs($session, [ 1 ]);
}
