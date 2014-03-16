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

use \MovLib\Data\FileSystem;
use \MovLib\Data\Shell;
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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Absolute path to the nginx configuration.
   *
   * Don't add the document root to this path, this is added automatically.
   *
   * @var string
   */
  protected $etcPath = "/etc/nginx";

  /**
   * Used to collect plural routes that need translation.
   *
   * @var array
   */
  protected $emptyPluralTranslations = [];

  /**
   * Used to collect singular routes that need translation.
   *
   * @var array
   */
  protected $emptySingularTranslations = [];

  /**
   * Regular expression to match identifier within a route that ensures that the route doesn't start or only contains
   * zeros.
   *
   * @var string
   */
  public $idRegExp = "([1-9][0-9]*)";

  /**
   * Regular expression to match ISO alpha-2 codes.
   *
   * @var string
   */
  public $isoAlpha2RegExp = "([a-z][a-z])";

  /**
   * The namespace of the presenters of the route file we're currently compiling.
   *
   * @var null|string
   */
  protected $routesNamespace;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get nginx persistent disk cache instruction for location block.
   *
   * @param string $presenter [optional]
   *   The name of the presenter if the route's URL is empty (only applies to home pages).
   * @return string
   *   The nginx instruction to check for cached presentation before delivery.
   */
  public function cache($presenter = null) {
    if (isset($presenter) && $presenter !== false) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (empty($presenter) || !is_string($presenter)) {
        throw new \InvalidArgumentException("\$presenter cannot be empty and must be of type string");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $presenter = "/{$presenter}";
    }
    if ($presenter === false) {
      return "include sites/conf/fastcgi_params.conf;\n";
    }
    return "try_files \$movlib_cache{$presenter} @php;\n";
  }

  /**
   * Compiles and translates nginx routes for all servers.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @global \MovLib\Data\Database
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  public function compileAndTranslateRoutes() {
    // Don't remove the $db variable just because it's unused, it's used in the included routes.php file!
    global $kernel, $db, $i18n;

    // Let the user know what's going to happen.
    $this->write("Starting to translate and compile nginx routes ...");

    // Store currently set language code to reset after all routes have been built.
    $currentLanguageCode = $i18n->languageCode;

    // Make sure that the routes directory exists.
    $routesDirectory = FileSystem::createDirectory("{$kernel->documentRoot}{$this->etcPath}/sites/conf/routes");

    // Generate routes file for each system language.
    foreach ($kernel->systemLanguages as $languageCode => $locale) {
      $translatedRoutes   = null;
      $i18n->locale       = $locale;
      $i18n->languageCode = $languageCode;

      // We need output buffering to catch the output of the following require call.
      $obStart = ob_start(function ($buffer) use (&$translatedRoutes) {
        $translatedRoutes = $buffer;
      });

      // Make sure output buffering was started successfully.
      if ($obStart === false) {
        throw new \RuntimeException("Couldn't start output buffering!");
      }

      // Execute the routes source file and translate all routes with the closure.
      foreach (FileSystem::glob($routesDirectory, "php") as $routesFile) {
        $this->routesNamespace = basename($routesFile, ".php");
        try {
          include $routesFile;
        }
        catch (\Exception $e) {
          ob_end_clean();
          throw $e;
        }
        $this->routesNamespace = null;
      }

      // End output buffering for this system language ...
      if (ob_end_clean() === false) {
        throw new \RuntimeException("Couldn't get buffered output!");
      }

      // ... and write it to the target directory.
      FileSystem::putContent("{$routesDirectory}/{$i18n->languageCode}.conf", $translatedRoutes, LOCK_EX);
      $this->write("Written routing file for '{$i18n->languageCode}' ...");

      // Print the keys that still need translation.
      foreach ([ "singular", "plural" ] as $form) {
        if (!empty($this->{"empty{$form}Translations"}[$i18n->languageCode])) {
          $this->write("The following {$form} form(s) still need translation:", self::MESSAGE_TYPE_ERROR);
          $this->write($this->{"empty{$form}Translations"}[$i18n->languageCode], self::MESSAGE_TYPE_COMMENT);
        }
      }
    }

    // Reload nginx and load the newly translated routes.
    Shell::execute("service nginx reload");

    // Make sure that the previously set language code is set again globally in case other commands are executed with
    // the same instance.
    $i18n->languageCode = $currentLanguageCode;

    // Let the user know that everything went fine.
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
    parent::execute($input, $output);
    $this->checkPrivileges();
    $this->compileAndTranslateRoutes();
    return 0;
  }

  /**
   * Get translated and formatted route.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Tool\Kernel $kernel
   * @staticvar array $routes
   *   Use to cache the routes.
   * @param string $route
   *   The untranslated route to translate.
   * @param boolean $plural
   *   Whether this route key is singular or plural.
   * @param array $args [optional]
   *   Replacement arguments for the route key.
   * @return string
   *   The translated and formatted route.
   */
  protected function getTranslatedRoute($route, $plural, array &$args = null) {
    global $i18n, $kernel;
    static $routes = [];

    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($route) || !is_string($route)) {
      throw new \InvalidArgumentException("\$route cannot be empty and must be of type string");
    }
    if (!is_bool($plural)) {
      throw new \InvalidArgumentException("\$plural cannot be empty and must be of type boolean");
    }
    if (isset($args) && (empty($args) || !is_array($args))) {
      throw new \InvalidArgumentException("\$args cannot be empty and must be of type array");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $form = ($plural === true) ? "plural" : "singular";

    // We only need to translate the route if it isn't in the default locale.
    if ($i18n->locale != $i18n->defaultLocale) {
      // Check if we already have the route translations for this locale cached.
      if (!isset($routes[$form][$i18n->locale])) {
        $routes[$form][$i18n->locale] = require "{$kernel->pathTranslations}/routes/{$i18n->locale}.{$form}.php";
      }

      // Check if we have a translation for this route and use it if we have one.
      if (!empty($routes[$form][$i18n->locale][$route])) {
        $route = $routes[$form][$i18n->locale][$route];
      }
      else {
        $this->{"empty{$form}Translations"}[$i18n->languageCode][$route] = $route;
      }
    }
    if (empty($args) && ($num = preg_match_all("/{[a-z0-9_]+}/", $route)) > 0) {
      $args = array_fill(0, $num, $this->idRegExp);
    }
    if (!empty($args)) {
      return \MessageFormatter::formatMessage($i18n->locale, $route, $args);
    }
    return $route;
  }

  /**
   * Get nginx inctruction to rewrite request to the not found page.
   *
   * @return string
   *   The nginx inctruction to rewrite request to the not found page.
   */
  public function notFound() {
    return "rewrite .* /error/NotFound last;\n";
  }

  /**
   * Translate singular route.
   *
   * @param string $route
   *   The route to translate.
   * @param null|array $args [optional]
   *   Arguments that should be inserted into the pattern.
   * @return string
   *   The translated route.
   */
  public function r($route, array &$args = null) {
    return $this->getTranslatedRoute($route, false, $args);
  }

  /**
   * Get nginx instructions to redirect a singular route which is missing it's identifying arguments to its parent
   * plural route.
   *
   * @param string $singularKey
   *   The untranslated singular route key.
   * @param string $pluralKey
   *   The untranslated plural route key.
   * @param array $args [optional]
   *   Replacement tokens for the route, defaults to <code>NULL</code>.
   * @return string
   *   The nginx instructions to redirect singular to plural route.
   */
  public function redirectSingularToPlural($singularKey, $pluralKey, array $args = null) {
    $singular = $this->r($singularKey, $args);
    $plural   = $this->rp($pluralKey, $args);

    // Make sure that singular and plural aren't the same.
    if ($singular == $plural) {
      return;
    }

    // We can create a direct match if we have no arguments.
    if (empty($args)) {
      $match = "=";
    }
    // Otherwise we're forced to create a regular expression.
    else {
      $c = count($args);
      for ($i = 0, $j = 1; $i < $c; ++$i, ++$j) {
        $args[$i] = "\${$j}";
      }

      $match    = "~*";
      $singular = "'^{$singular}$'";
      $plural   = "'{$this->rp($pluralKey, $args)}'";
    }

    return "location {$match} {$singular} { return 301 {$plural}; }\n";
  }

  /**
   * Reset the currently set default routes namespace.
   *
   * @return this
   */
  public function resetRoutesNamespace() {
    $this->routesNamespace = null;
    return $this;
  }

  /**
   * Translate plural route.
   *
   * @param string $route
   *   The route to translate.
   * @param null|array $args [optional]
   *   Arguments that should be inserted into the pattern.
   * @return string
   *   The translated route.
   */
  public function rp($route, array &$args = null) {
    return $this->getTranslatedRoute($route, true, $args);
  }

  /**
   * Set nginx variable.
   *
   * @param string $value
   *   The value to set the variable to.
   * @param string $key [optional]
   *   The variable's name without leading <code>"movlib_"</code>.
   * @return string
   *   The nginx instruction to set the variable in proper nginx syntax.
   */
  public function set($value, $key = "presenter") {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "value", "key" ] as $param) {
      if (empty(${$param})) {
        throw new \InvalidArgumentException("\${$param} cannot be empty and must be of type string");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if ($key == "presenter" && !empty($this->routesNamespace)) {
      $value = "{$this->routesNamespace}\\\\{$value}";
    }
    return "set \$movlib_{$key} '{$value}';\n";
  }

  /**
   * Set the default namespace of the current routes.
   *
   * @var string $namespace
   *   The default namespace of the current routes.
   * @return this
   */
  public function setRoutesNamespace($namespace) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($namespace) || !is_string($namespace)) {
      throw new \InvalidArgumentException("\$namespace cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->routesNamespace = str_replace("\\", "\\\\", $namespace);
    return $this;
  }

}
