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

/**
 * Mock of delayed_register() from main.php
 */
function delayed_register($class, $weight = null, $method = null) {}

/**
 * Wrap the actual bootstrap in a function for better control over global variables.
 *
 * @global array $backup
 * @global \MovLib\Tool\Configuration $config
 * @global \MovLib\Data\I18n $i18n
 * @global \MovLib\Data\User\Session $session
 */
function bootstrap() {
  global $backup, $config, $i18n, $session;
  $documentRoot       = __DIR__;
  $composerAutoloader = require "{$documentRoot}/vendor/autoload.php";
  $composerAutoloader->add("MovLib", "{$documentRoot}/src/");

  // @todo get rid of this
  $GLOBALS["movlib"] = parse_ini_file("{$documentRoot}/conf/movlib.ini");
  new \MovLib\Exception\ConsoleHandlers();
  $config            = new \MovLib\Tool\Configuration();
  $i18n              = new \MovLib\Data\I18n();

  foreach ([
    "LANGUAGE_CODE"   => $i18n->defaultLanguageCode,
    "REQUEST_URI"     => "/",
    "SCHEME"          => "https",
    "SERVER_NAME"     => "{$i18n->defaultLanguageCode}.{$config->domainDefault}",
    "SERVER"          => "https://{$i18n->defaultLanguageCode}.{$config->domainDefault}",
    "SERVER_PROTOCOL" => "HTTP/1.1",
    "SERVER_VERSIOn"  => "",
    "REMOTE_ADDR"     => "127.0.0.1",
    "HTTP_USER_AGENT" => ini_get("user_agent"),
  ] as $k => $v) {
    if (empty($_SERVER[$k])) {
      $_SERVER[$k] = $v;
    }
  }

  $session           = new \MovLib\Data\User\Session();
  $init              = new \ReflectionMethod($session, "init");
  $init->setAccessible(true);
  $init->invokeArgs($session, [ 1 ]);

  $backup = [
    "config"  => clone $config,
    "i18n"    => clone $i18n,
    "session" => clone $session,
  ];

  // @todo get rid of this
  if ($config->production === false) {
    $composerAutoloader->add("MovDev", "{$documentRoot}/src/");
  }
}

// Call the bootstrap function.
bootstrap();
