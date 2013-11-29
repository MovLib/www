<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Tool\Console\Command\Production;

use \MovLib\Data\UnixShell as sh;
use \MovLib\Tool\Console\Command\Production\FixPermissions;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to translate and compile nginx routes for all servers.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class NginxRoutes extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("nginx-routes");
  }

  /**
   * Compiles and translates nginx routes for all servers.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  public function compileAndTranslateRoutes() {
    global $kernel, $db, $i18n;

    $this->write("Starting to translate and compile nginx routes ...");
    $currentLanguageCode = $i18n->languageCode;

    // Check if routes file is present.
    $routesFile = "{$kernel->documentRoot}/conf/nginx/sites/conf/routes.php";
    if (!is_file($routesFile)) {
      throw new FileSystemException("Routes file missing from storage!");
    }

    // Check if routes folder is present.
    $routesDirectory = "/etc/nginx/sites/conf/routes";
    if (!is_dir($routesDirectory)) {
      throw new FileSystemException("The nginx routes directory is missing!");
    }

    /**
     * This closure will be used within our routes script to translate the strings.
     *
     * @global \MovLib\Tool\Database $db
     * @global \MovLib\Data\I18n $i18n
     * @param string $route
     *   The route to translate.
     * @param null|array $args [optional]
     *   Arguments that should be inserted into the pattern.
     * @return string
     *   The translated route.
     */
    $r = function ($route, array $args = null) {
      global $db, $i18n;
      static $cache = [];

      if (!isset($cache[$i18n->languageCode][$route])) {
        $result = $db->query("SELECT `route_id` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $route ])->get_result()->fetch_row();
        if (!$result) {
          $db->query("INSERT INTO `routes` (`route`, `dyn_translations`) VALUES (?, '')", "s", [ $route ])->close();
        }
      }

      return ($cache[$i18n->languageCode][$route] = $i18n->r($route, $args));
    };

    foreach ($kernel->systemLanguages as $languageCode => $locale) {
      $i18n->languageCode = $languageCode;

      // We need output buffering to catch the output of the following require call.
      if (ob_start() === false) {
        throw new \RuntimeException("Couldn't start output buffering!");
      }

      // Execute the routes source file and translate all routes with the closure.
      require $routesFile;

      // Get the translated content of this run ...
      if (($routes[$i18n->languageCode] = ob_get_clean()) === false) {
        throw new \RuntimeException("Couldn't get buffered output!");
      }

      // ... and write it to the target directory.
      if (file_put_contents("{$routesDirectory}/{$i18n->languageCode}.conf", $routes[$i18n->languageCode]) === false) {
        throw new \RuntimeException("Couldn't write translated routes file to nginx routes directory.");
      }

      $this->write("Written routing file for '{$i18n->languageCode}' ...");
    }

    if (sh::executeDisplayOutput("service nginx reload") === false) {
      throw new \RuntimeException("Couldn't reload nginx!");
    }

    // Make sure all files have the correct permissions.
    (new FixPermissions())->fixPermissions("{$kernel->documentRoot}/conf/nginx");
    $i18n->languageCode = $currentLanguageCode;
    return $this->write("Successfully translated and compiled routes, plus reloaded nginx!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Translate and compile nginx routes for all servers.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->checkPrivileges();
    $this->compileAndTranslateRoutes();
    return $options;
  }

}
