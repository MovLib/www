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
namespace MovLib\Console\Command;

use \MovLib\Console\Command\AbstractCommand;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Compile nginx routes and reload the server.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class NginxRoutes extends AbstractCommand {

  /**
   * {@inheritDoc}
   */
  public function __construct() {
    parent::__construct("routes");
  }

  /**
   * {@inheritDoc}
   */
  protected function configure() {
    $this->setDescription("Compile all routes and reload nginx.");
  }

  /**
   * {@inheritDoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $i18n;
    $this->setIO($input, $output);

    // The nginx and service commands are only available as privileged user.
    if (posix_getuid() !== 0) {
      $this->exitOnError("This script must be executed as privileged user (root or sudo).");
    }

    $locale = $i18n->getDefaultLanguageCode();

    /**
     * This closure will be used within our routes script to translate the strings.
     *
     * @param string $route
     *   The route to translate.
     * @return string
     *   The translated route.
     */
    $r = function ($route) use ($i18n, $locale) {
      return @$i18n->formatMessage("route", $route, null, [ "language_code" => $locale ]);
    };

    // Go through all supported languages and generate the routes.
    error_reporting(E_ALL ^ E_NOTICE);
    foreach ($GLOBALS["conf"]["i18n"]["supported_languages"] as $delta => $locale) {
      ob_start();
      require "{$_SERVER["HOME"]}/conf/nginx/sites/conf/routes.php";
      $routes[$locale] = ob_get_clean();
      file_put_contents("{$_SERVER["HOME"]}/conf/nginx/sites/conf/routes/{$locale}.conf", $routes[$locale]);
    }
    error_reporting(-1);

    // Test and reload the newly created routes.
    $this
      ->exec("nginx -t", "Compilation of routes failed for some reason, please review the following error message reported by nginx -t and fix the problem:")
      ->exec("service nginx reload", "Reloading nginx failed for some reason, please review the following error message reported by nginx's init script:")
      ->write("Compilation of routes succeeded and nginx was reloaded!")
    ;
  }

}
