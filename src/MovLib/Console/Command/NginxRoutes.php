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
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("routes");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Compile all routes and reload nginx.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $i18n;
    $this->setIO($input, $output)->checkPrivileges();

    // Check that the source file is present.
    $routesFile = "{$_SERVER["DOCUMENT_ROOT"]}/conf/nginx/sites/conf/routes.php";
    if (!is_file($routesFile)) {
      $this->exitOnError("Routes files is missing from the file system!");
    }

    // Check that the target directory is present.
    $routesDir = "{$_SERVER["DOCUMENT_ROOT"]}/conf/nginx/sites/conf/routes/";
    if (!is_dir($routesDir)) {
      $this->exitOnError("Nginx routes directory is missing from the file system!");
    }
    if (!is_writable($routesDir)) {
      $this->exitOnError("Nginx routes directory is not writeable!");
    }

    /**
     * This closure will be used within our routes script to translate the strings.
     *
     * @global \MovLib\Data\I18n $i18n
     * @param string $route
     *   The route to translate.
     * @param null|array $args [optional]
     *   Arguments that should be inserted into the pattern.
     * @return string
     *   The translated route.
     */
    $r = function ($route, array $args = null) {
      global $i18n;
      return $i18n->insertRoute($route)->r($route, $args);
    };

    // Drop all routes from this server.
    // @todo Translations have to be fetched from the localize server!
    (new \MovDev\Database())->query("TRUNCATE `routes`");

    // Go through all supported languages and generate the routes.
    foreach ($GLOBALS["movlib"]["locales"] as $languageCode => $locale) {
      $i18n->languageCode = $languageCode;

      // We need output buffering to catch the output of the following require call.
      if (ob_start() === false) {
        $this->exitOnError("Could not start output buffering!");
      }

      // Execute the routes source file and translate all routes with the closure.
      require $routesFile;

      // Get the translated content of this run ...
      if (($routes[$languageCode] = ob_get_clean()) === false) {
        $this->exitOnError("Could not get buffered output!");
      }

      // ... and write it to the target directory.
      if (file_put_contents("{$routesDir}{$languageCode}.conf", $routes[$languageCode]) === false) {
        $this->exitOnError("Could not write translated routes file to nginx routes directory.");
      }
    }

    // Test and reload the newly created routes.
    $this
      ->exec("nginx -t", "Compilation of routes failed for some reason, please review the following error message reported by nginx -t and fix the problem:")
      ->exec("service nginx reload", "Reloading nginx failed for some reason, please review the following error message reported by nginx's init script:")
      ->write("Compilation of routes succeeded and nginx was reloaded!")
    ;
  }

}
