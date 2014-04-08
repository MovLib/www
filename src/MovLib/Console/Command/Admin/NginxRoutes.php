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
namespace MovLib\Console\Command\Admin;

use \MovLib\Console\MySQLi;
use \MovLib\Console\Command\Install\Nginx;
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
class NginxRoutes extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * URI to the route files.
   *
   * @var string
   */
  const ROUTES_URI = "dr://etc/nginx/sites/conf/routes";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The active admin database instance.
   *
   * @var \MovLib\Console\MySQLi
   */
  protected $db;

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
   *   <code>false</code> to disable caching or the name of the presenter if the
   *   route's URL is empty (only applies to home pages).
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
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("nginx-routes");
    $this->setDescription("Translate and compile nginx routes for all servers.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->db = new MySQLi("movlib");

    // Don't remove the $db variable just because it's unused, it's used in the included routes.php file!
    $this->writeVerbose("Starting to translate and compile nginx routes ...", self::MESSAGE_TYPE_COMMENT);

    $this->writeDebug("Creating <comment>" . self::ROUTES_URI . "</comment>");
    mkdir(self::ROUTES_URI);

    $locales = implode("</comment>, <comment>", $this->config->locales);
    $this->writeDebug("Generating routes for all system locales: <comment>{$locales}</comment>");

    foreach ($this->config->locales as $locale) {
      $translatedRoutes = null;
      $this->intl->setLocale($locale);
      $this->writeDebug("Generating routes for system locale <comment>{$this->intl->locale}</comment>");

      $this->writeDebug("Started output buffering, here be dragons\n\n...\n");
      $obStart = ob_start(function ($buffer) use (&$translatedRoutes) {
        $translatedRoutes = $buffer;
      });

      if ($obStart === false) {
        throw new \RuntimeException("Couldn't start output buffering!");
      }

      foreach (new \RegexIterator(new \DirectoryIterator(self::ROUTES_URI), "/\.php$/") as $fileinfo) {
        $this->routesNamespace = $fileinfo->getBasename(".php");
        try {
          require $fileinfo->getPathname();
        }
        catch (\Exception $e) {
          ob_end_clean();
          throw $e;
        }
        $this->routesNamespace = null;
      }

      if (ob_end_clean() === false) {
        throw new \RuntimeException("Couldn't get buffered output!");
      }
      $this->writeDebug("\nEnded output buffering =)");

      $this->writeDebug("Writing translated routing file for <comment>{$this->intl->locale}</comment>");
      file_put_contents("dr://etc/nginx/sites/conf/routes/{$this->intl->languageCode}.conf", $translatedRoutes);

      foreach ([ "singular", "plural" ] as $form) {
        if (!empty($this->{"empty{$form}Translations"}[$this->intl->locale])) {
          $this->write("The following {$this->intl->locale} {$form} form(s) still need translation:", self::MESSAGE_TYPE_ERROR);
          $this->write($this->{"empty{$form}Translations"}[$this->intl->locale], self::MESSAGE_TYPE_COMMENT);
        }
      }
    }

    // Reload nginx and load the newly translated routes.
    if ($this->privileged) {
      (new Nginx($this->diContainer))->importKeysAndCertificates($output);
      $this->exec("nginx -t");
      $this->exec("service nginx reload");
    }
    else {
      $this->write("Cannot reload nginx, only possible as privileged user (root or sudo).", self::MESSAGE_TYPE_ERROR);
    }

    // Make sure that the previously set language code is set again globally in case other commands are executed with
    // the same instance.
    $this->intl->setLocale($this->intl->defaultLocale);

    // Let the user know that everything went fine.
    $this->writeVerbose("Successfully translated and compiled routes, plus reloaded nginx!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

  /**
   * Get translated and formatted route.
   *
   * @staticvar array $routes
   *   Use to cache the routes.
   * @param string $pattern
   *   The untranslated route to translate.
   * @param string $context
   *   Either <code>"singular"</code> or <code>"plural"</code>.
   *   Whether this route key is singular or plural.
   * @param array $args [optional]
   *   Replacement arguments for the route key.
   * @return string
   *   The translated and formatted route.
   */
  protected function getTranslatedRoute($pattern, $context, array &$args = null) {
    static $routes = [];

    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($pattern) || !is_string($pattern)) {
      throw new \InvalidArgumentException("\$route cannot be empty and must be of type string");
    }
    if ($context != "singular" && $context != "plural") {
      throw new \InvalidArgumentException("\$context has to be either 'singular' or 'plural'");
    }
    if (isset($args) && (empty($args) || !is_array($args))) {
      throw new \InvalidArgumentException("\$args cannot be empty and must be of type array");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We only need to translate the route if it isn't in the default locale.
    if ($this->intl->locale != $this->config->defaultLocale) {
      // Check if we already have the route translations for this locale cached.
      if (empty($routes[$this->intl->locale][$context])) {
        $routes[$this->intl->locale][$context] = require "dr://var/intl/{$this->intl->locale}/routes/{$context}.php";
      }

      // Check if we have a translation for this route and use it if we have one.
      if (empty($routes[$this->intl->locale][$context][$pattern])) {
        $this->{"empty{$context}Translations"}[$this->intl->locale][$pattern] = $pattern;
      }
      else {
        $pattern = $routes[$this->intl->locale][$context][$pattern];
      }
    }

    // Check if the route contains any placeholder tokens if no arguments were passed. Default placeholder tokens in
    // routes are replaced with the identifier regular expression.
    if (empty($args) && ($num = preg_match_all("/{[a-z0-9_]+}/", $pattern)) > 0) {
      $args = array_fill(0, $num, $this->idRegExp);
    }

    // Let the message formatter replace any placeholder tokens if we have arguments at this point.
    if (!empty($args)) {
      return \MessageFormatter::formatMessage($this->intl->locale, $pattern, $args);
    }

    return $pattern;
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
    return $this->getTranslatedRoute($route, "singular", $args);
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
    return $this->getTranslatedRoute($route, "plural", $args);
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
