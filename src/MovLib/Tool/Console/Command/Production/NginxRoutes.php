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

use \MovLib\Tool\Console\Command\Production\FixPermissions;
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
  use \MovLib\Data\TraitFileSystem;

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
     * Regular expression to match identifier within a route that ensures that the route doesn't start or only contains
     * zeros.
     *
     * @var string
     */
    $idRegExp = "([1-9][0-9]*)";

    /**
     * Regular expression to match ISO alpha-2 codes.
     *
     * @var string
     */
    $isoAlpha2RegExp = "([a-z][a-z])";

    /**
     * Associative array containing the currently untranlated singular forms for each locale.
     *
     * @var array
     */
    $emptyTranslationsSingular = [];

    /**
     * Associative array containing the currently untranlated plural forms for each locale.
     *
     * @var array
     */
    $emptyTranslationsPlural = [];

    /**
     * This closure will be used within our routes script to translate singular strings.
     *
     * @global \MovLib\Data\I18n $i18n
     * @global \MovLib\Tool\Kernel $kernel
     * @param string $route
     *   The route to translate.
     * @param null|array $args [optional]
     *   Arguments that should be inserted into the pattern.
     * @return string
     *   The translated route.
     */
    $r = function ($route, array $args = null) use (&$emptyTranslationsSingular) {
      global $i18n, $kernel;
      static $routes = [];

      // We only need to translate the route if it isn't in the default locale.
      if ($i18n->locale != $i18n->defaultLocale) {
        // Check if we already have the route translations for this locale cached.
        if (!isset($routes[$i18n->locale])) {
          $routes[$i18n->locale] = require "{$kernel->pathTranslations}/routes/{$i18n->locale}.php";
        }

        // Check if we have a translation for this route and use it if we have one.
        if (!empty($routes[$i18n->locale][$route])) {
          $route = $routes[$i18n->locale][$route];
        }
        else {
          $emptyTranslationsSingular[$i18n->languageCode][$route] = $route;
        }
      }

      if ($args) {
        return \MessageFormatter::formatMessage($i18n->locale, $route, $args);
      }
      return $route;
    };

    /**
     * This closure will be used within our routes script to translate plural strings.
     *
     * @global \MovLib\Data\I18n $i18n
     * @global \MovLib\Tool\Kernel $kernel
     * @param string $route
     *   The route to translate.
     * @param null|array $args [optional]
     *   Arguments that should be inserted into the pattern.
     * @return string
     *   The translated route.
     */
    $rp = function ($route, array $args = null) use (&$emptyTranslationsPlural) {
      global $i18n, $kernel;
      static $routes = [];

      // We only need to translate the route if it isn't in the default locale.
      if ($i18n->locale != $i18n->defaultLocale) {
        // Check if we already have the route translations for this locale cached.
        if (!isset($routes[$i18n->locale])) {
          $routes[$i18n->locale] = require "{$kernel->pathTranslations}/routes/{$i18n->locale}.plural.php";
        }

        // Check if we have a translation for this route and use it if we have one.
        if (!empty($routes[$i18n->locale][$route])) {
          $route = $routes[$i18n->locale][$route];
        }
        else {
          $emptyTranslationsPlural[$i18n->languageCode][$route] = $route;
        }
      }

      if ($args) {
        return \MessageFormatter::formatMessage($i18n->locale, $route, $args);
      }
      return $route;
    };

    foreach ($kernel->systemLanguages as $languageCode => $locale) {
      $i18n->locale       = $locale;
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

      // Print the keys that still need translation.
      if (!empty($emptyTranslationsSingular[$i18n->languageCode])) {
        $this->write("The following singular forms for '{$i18n->languageCode}' still need translations:", self::MESSAGE_TYPE_ERROR);
        $this->write(array_values($emptyTranslationsSingular[$i18n->languageCode]), self::MESSAGE_TYPE_COMMENT);
      }
      if (!empty($emptyTranslationsPlural[$i18n->languageCode])) {
        $this->write("The following plural forms for '{$i18n->languageCode}' still need translations:", self::MESSAGE_TYPE_ERROR);
        $this->write(array_values($emptyTranslationsPlural[$i18n->languageCode]), self::MESSAGE_TYPE_COMMENT);
      }
    }

    $this->shellExecuteDisplayOutput("service nginx reload");

    // Make sure all files have the correct permissions.
    (new FixPermissions())->fixPermissions("{$kernel->documentRoot}/conf/nginx");
    $i18n->languageCode = $currentLanguageCode;
    return $this->write("Successfully translated and compiled routes, plus reloaded nginx!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("nginx-routes");
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
