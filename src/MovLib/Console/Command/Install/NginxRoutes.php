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
namespace MovLib\Console\Command\Install;

use \DocBlockReader\Reader as DocReader;
use \MovLib\Console\Command\Install\Nginx;
use \MovLib\Core\Intl;
use \MovLib\Data\Collator;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines the nginx routes command object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class NginxRoutes extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Used to collect all dynamic FastCGI parameteres.
   *
   * @var array
   */
  protected $fastCgiParams = [];

  /**
   * URI to the dynamic FastCGI parameteres file.
   *
   * @var string
   */
  protected $fastCgiParamsURI = "dr://etc/nginx/sites/conf/fastcgi_dynamic_params.conf";

  /**
   * URI to the location of the nginx configuration that contains the location blocks.
   *
   * @var string
   */
  protected $locationConfigurationURI = "dr://etc/nginx/sites/conf/routes/{{ language_code }}.conf";

  /**
   * Namespaces for which no regular expression is inserted.
   *
   * @var array
   */
  protected $noRegularExpressionNamespaces = [ "Help", "Profile", "SystemPage" ];

  /**
   * Presenter short name's which have no regular expression auto-inserted.
   *
   * @var array
   */
  protected $noRegularExpressionPresenters = [ "Create", "Charts", "Random" ];

  /**
   * Contains all available regular expression tokens.
   *
   * @var array
   */
  protected $regularExpressionTokens = [
    "cc" => [ "regex" => "([A-Z]{2})",    "var" => "country_code"        ],
    "id" => [ "regex" => "([1-9][0-9]*)", "var" => "{{ presenter }}_id"  ],
    "lc" => [ "regex" => "([a-z]{2})",    "var" => "language_code"       ],
    "un" => [ "regex" => "([^/].+)",      "var" => "user_name"           ],
  ];

  /**
   * Namespace to regular expression mapping.
   *
   * @var array
   */
  protected $regularExpressionTokensMap = [ "User" => "un", "Country" => "cc", "Language" => "lc" ];

  /**
   * Namespace parts that should be removed from the canonical absolute class name of each presenter before trying to
   * auto-generate the path.
   *
   * @var array
   */
  protected $removeNamespaceParts = [ "\\MovLib\\Presentation", "\\SystemPage" ];

  /**
   * Array used to collect route keys that might be untranslated.
   *
   * @var array
   */
  protected $translationPossiblyMissing = [];


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
    $this->writeVerbose("Starting to translate and compile nginx routes...", self::MESSAGE_TYPE_COMMENT);

    // Translate and compile routes for all system locales.
    foreach ($this->intl->systemLocales as $code => $locale) {
      $this->writeDebug("Translating and compiling routes for <comment>{$locale}</comment>");
      $this->translateAndCompileRoutes(new Intl($this->config, $code));
    }

    // Dynamic FastCGI parameters are the same for all locales, therefore we can write them once.
    if (!empty($this->fastCgiParams)) {
      $this->writeVeryVerbose("Writing dynamic FastCGI parameters to <comment>{$this->fastCgiParamsURI}</comment>");
      file_put_contents($this->fastCgiParamsURI, implode($this->fastCgiParams));
    }

    // Only attempt to realod nginx if we have the needed privileges.
    if ($this->privileged) {
      (new Nginx($this->diContainer))->importKeysAndCertificates($output);
      $this->exec("nginx -t");
      $this->exec("service nginx reload");
    }
    else {
      $this->write("Cannot reload nginx, only possible  as privileged user (root or sudo).", self::MESSAGE_TYPE_ERROR);
    }

    $this->writeVerbose("Successfully translated and compiled nginx routes!", self::MESSAGE_TYPE_INFO);

    // Possibly untranslated route parts come last because we want the caller to see this.
    if (!empty($this->translationPossiblyMissing)) {
      foreach ($this->translationPossiblyMissing as $locale => $possiblyMissing) {
        $this->write("The following {$locale} routes might be untranslated, please check!", self::MESSAGE_TYPE_COMMENT);
        $this->write($possiblyMissing);
      }
    }

    return 0;
  }

  /**
   * Generate an nginx direct matching location block, including possible redirects.
   *
   * @param \MovLib\Core\Intl $intl
   *   The Intl instance.
   * @param \MovLib\Data\Collator $collator
   *   A collator for correct sorting of sub routes.
   * @param string $locations
   *   The variable used to collect all locations.
   * @param array $redirects
   *   The array used to collect all redirects.
   * @param array $routes
   *   The array containing all sub routes for which we can generate direct matching location blocks.
   * @return this
   */
  protected function generateDirectMatchLocation(Intl $intl, Collator $collator, &$locations, array &$redirects, array $routes) {
    $locations .= "#\n# Direct matching (fastest)\n#\n";
    $collator->ksort($routes);
    foreach ($routes as $route => $args) {
      list($cache, $presenter) = $args;
      try {
        $set = $this->getDataSet($presenter);
        $tpk = $this->translateRoute($intl, "/{$set->pluralKey}");
        $tsk = str_replace("/{0}", "", $this->translateRoute($intl, "/{$set->singularKey}/{0}"));
        if (empty($redirects["{$tpk}{$tsk}"]) && $tpk != $tsk) {
          $locations .= "\nlocation = '{$tsk}' {\n  return 301 '{$tpk}';\n}\n";
          $redirects["{$tpk}{$tsk}"] = true;
        }
      }
      catch (\LogicException $e) {
        // Ignore!
      }
      $locations .= "\nlocation = '{$route}' {\n  set \$movlib_presenter '" . str_replace("\\", "\\\\", $presenter) . "';\n  {$this->getCache($cache)};\n}\n";
    }
    return $this;
  }

  /**
   * Generate nginx locations that are nested within a protecting matching block.
   *
   * @param \MovLib\Data\Collator $collator
   *   A collator for correct sorting of sub routes.
   * @param string $locations
   *   The variable used to collect all locations.
   * @param array $redirects
   *   The array used to collect all redirects.
   * @param array $routes
   *   The array containing all sub routes for which we can generate location blocks.
   * @param string $protection
   *   The route of the matching protection block.
   * @param string $indent [internal]
   *   Used in recursion for correct indentation of output.
   * @return this
   */
  protected function generateProtectedLocation(Collator $collator, &$locations, array &$redirects, array $routes, $protection, $indent = "") {
    $locations .= "\n{$indent}#\n{$indent}# Protection block for '{$protection}' routes\n{$indent}#\n\n{$indent}location ^~ '/{$protection}/' {\n";
    $collator->ksort($routes);
    foreach ($routes as $route => $args) {
      if (strpos($route, "/") === false) {
        $this->generateProtectedLocation($collator, $locations, $redirects, $args, "{$protection}/{$route}", "{$indent}  ");
        continue;
      }

      list($cache, $presenter) = $args;
      $presenterParts = array_map("mb_strtolower", explode("\\", $presenter));
      $nginxVariables = null;

      $route = preg_replace_callback("#/(.+)/{(.+)}#U", function ($matches) use ($indent, &$nginxVariables, $presenterParts) {
        static $c = 1;
        $name = str_replace("{{ presenter }}", $presenterParts[$c - 1], $this->regularExpressionTokens[$matches[2]]["var"]);
        $nginxVariables .= "\n{$indent}    set \$movlib_{$name} \${$c};";
        if (empty($this->fastCgiParams[$name])) {
          $offset = strtoupper($name) . str_repeat(" ", (20 - strlen($name)));
          $var    = '$movlib_' . $name;
          $var    = $var . str_repeat(" ", (30 - strlen($var)));
          $this->fastCgiParams[$name] = "fastcgi_param    {$offset}{$var}if_not_empty;\n";
        }
        ++$c;
        return "/{$matches[1]}/{$this->regularExpressionTokens[$matches[2]]["regex"]}";
      }, $route);

      $presenter  = str_replace("\\", "\\\\", $presenter);
      $locations .= <<<NGX

{$indent}  location ~* '^{$route}$' {
{$indent}    set \$movlib_presenter '{$presenter}';{$nginxVariables}
{$indent}    {$this->getCache($cache)};
{$indent}  }

NGX;
    }
    $locations .= "\n{$indent}  # Display 404 page if route wasn't found in protection block!\n{$indent}  rewrite .* '/error/NotFound' last;\n{$indent}}\n\n";
    return $this;
  }

  /**
   * Get nginx cache location configuration.
   *
   * @param mixed $cache
   *   The cache instructions extracted from the class.
   * @return string
   *   The nginx cache location configuration.
   */
  protected function getCache($cache) {
    if ($cache === false) {
      return "include 'sites/conf/fastcgi_params.conf'";
    }
    if ($cache === true) {
      $cache = null;
    }
    return "try_files \$movlib_cache{$cache} @php";
  }

  /**
   * Get the data set associated with a presenter.
   *
   * @param string $className
   *   The presenter's class name.
   * @return \MovLib\Data\AbstractSet
   *   The data set associated with this presenter.
   * @throws \LogicException
   *   If no set exists for the given class.
   */
  protected function getDataSet($className) {
    $setName               = basename(dirname(strtr($className, "\\", "/")));
    $setClassName          = "\\MovLib\\Data\\{$setName}\\{$setName}Set";
    $setExists             = class_exists($setClassName);

    $setAlternateName      = explode("\\", trim(str_replace("\\MovLib\\Presentation", "", $className), "\\"))[0];
    $setAlternateClassName = "\\MovLib\\Data\\{$setAlternateName}\\{$setName}Set";
    $setAlternateExists    = class_exists($setAlternateClassName);

    if ($setExists === false && $setAlternateExists === false) {
      throw new \LogicException("Couldn't find set '{$setClassName}' nor '{$setAlternateClassName}' for presenter '{$className}'!");
    }
    if ($setAlternateExists === true) {
      $setClassName = $setAlternateClassName;
    }
    // Image related classes always require an entity's unique identifier they belong to for loading, we fake this by
    // passing an invalid number, remember that we won't call any loading methods and that it doesn't matter for us at
    // this point, we only want access to the singular and plural key.
    return new $setClassName($this->diContainer, -1);
  }

  /**
   *
   * Get route from class name.
   *
   * @param string $className
   *   The class name to get the route for.
   * @param \DocBlockReader\Reader $docReader
   *   Reader for annotations.
   * @return string
   *   The route from class name.
   */
  protected function getRouteFromClassName($className, DocReader $docReader) {
    $route = "";

    // Some namespace's don't need a regular expression in their route.
    $noRegularExpression = false;
    foreach ($this->noRegularExpressionNamespaces as $namespace) {
      if (strpos($className, "\\{$namespace}\\") !== false) {
        $noRegularExpression = true;
        break;
      }
    }

    // Split the class's name at the PHP namespace separator into it's parts.
    $className = str_replace($this->removeNamespaceParts, "", $className);
    $parts     = explode("\\", trim($className, "\\"));
    $c         = count($parts) - 1;

    // We use the identifier regular expression by default, but some namespace's have special known regular expressions.
    $regularExpressionToken = "id";
    foreach ($this->regularExpressionTokensMap as $namespace => $token) {
      if ($parts[0] == $namespace) {
        $regularExpressionToken = $token;
        break;
      }
    }

    // We have to find out the plural name if this is an index route.
    if ($parts[$c] == "Index") {
      // Remove the index from the parts and correct the counter.
      array_pop($parts) && --$c;

      // Now we have to replace the new last key with its plural form.
      if (!($parts[$c] = $docReader->getParameter("routePluralKey"))) {
        $parts[$c] = $this->getDataSet($className)->pluralKey;
      }
    }

    do {
      // Replace show with token for identifier regular expression.
      if ($parts[$c] == "Show") {
        if ($noRegularExpression) {
          continue;
        }
        $part = "{{$regularExpressionToken}}";
      }
      // Transform CamelCase to camel-case; class name's never contain any characters that aren't allowed in a URL, so
      // we don't have to perform any special cleaning jobs at this point.
      else {
        $part = trim(preg_replace_callback("/[A-Z][^A-Z]/", function ($match) {
          return "-" . $match{0};
        }, $parts[$c]), "-");

        // Prepend regular expression token if this isn't a root route and if we're allowed to.
        if (!$noRegularExpression && !in_array($parts[$c], $this->noRegularExpressionPresenters) && $c > 0) {
          $part = "{{$regularExpressionToken}}/{$part}";
        }
      }

      // We're going backwards, therefore we have to append the already translated parts.
      $route = "/{$part}{$route}";
    }
    while (--$c > -1);

    return mb_strtolower($route);
  }

  /**
   * Replace the regular expression tokens with simple numeric placeholders.
   *
   * <b>EXAMPLE</b><br>
   * The route <code>"/entity/{id}"</code> will become <code>"/entity/{0}"</code>
   *
   * @param string $pattern
   *   The message formatter pattern to replace tokens in.
   * @return string
   *   The message formatter pattern with replaced tokens.
   */
  protected function replaceRegularExpressionTokens($pattern) {
    return preg_replace_callback("/{[^}].+}/", function () {
      static $c = 0;
      return "{" . $c++ . "}";
    }, $pattern);
  }

  /**
   * Build the complete routes array.
   *
   * @staticvar string $slash
   *   Simply contains <code>"/"</code>.
   * @param string $translatedRoute
   *   The translated route to set the location for.
   * @param array $routes
   *   The array used to build the hierarchy.
   * @param string $className
   *   The canonical absolute class name of the presenter.
   * @param \DocBlockReader\Reader $docReader
   *   Annotation DocReader instance.
   * @return this
   */
  protected function setRouteLocation($translatedRoute, array &$routes, $className, DocReader $docReader) {
    static $slash = "/";

    // Split the route into its parts.
    $parts = explode($slash, trim($translatedRoute, $slash));

    // Determine if we can utilize the persistent disk cache for this route.
    $cache = $docReader->getParameter("routeCache");

    // Some special routes need the presenter's name appended to the cache key because they have no URL part. This
    // applies to any root routes, presentations which are accessed directly, e.g.: https://movlib.org/
    if (!is_null($cache) && !is_bool($cache) && $cache{0} != $slash) {
      $cache = "{$slash}{$cache}";
    }

    // Build the route's location arguments.
    $args = [ $cache, str_replace("\\MovLib\\Presentation\\", "", $className) ];

    // We can create a direct matchin location block if the route doesn't contain a single token.
    if (strpos($translatedRoute, "{") === false) {
      empty($routes[$slash]) && ($routes[$slash] = []);
      $routes[$slash][$translatedRoute] = $args;
    }
    // Otherwise we want it to be enclosed in a protecting matching block.
    else {
      // Now we have to go down the road and create keys for each protecting matching block.
      $level =& $routes;
      foreach ($parts as $part) {
        if ($part{0} == "{") {
          break;
        }
        empty($level[$part]) && ($level[$part] = []);
        $level =& $level[$part];
      }

      // We add the route to the last level that we just created.
      $level[$translatedRoute] = $args;
    }

    return $this;
  }

  /**
   * Translate and compile routes for the given Intl instance.
   *
   * @param \MovLib\Core\Intl $intl
   *   The Intl instance to translate and compile the routes for.
   * @return this
   */
  protected function translateAndCompileRoutes(Intl $intl) {
    /* @var $routes array Used to collect all routes for this locale with suitable nginx configuration hierarchy. */
    $routes = [];

    /* @var $translatedRoutes array Used to collect the final translated routes for the Intl look-up file. */
    $translatedRoutes = [];

    /* @var $collator \MovLib\Data\Collator Used to sort routes. */
    $collator = new Collator($intl->locale);

    /* @var $fileinfo \SplFileInfo */
    foreach (new \RegexIterator($this->fs->getRecursiveIterator("dr://src/MovLib/Presentation"), "/\.php$/") as $fileinfo) {
      $path = $fileinfo->getPathname();
      $this->writeDebug("Translating and compiling routes for <comment>{$path}</comment>");

      // The error presentations are for internal nginx usage only and don't have public accessible routes.
      // The tool presentations are unused at the moment.
      foreach ([ "Error", "Tool" ] as $pathPart) {
        if (strpos($path, "/{$pathPart}/") !== false) {
          $this->writeDebug("Routes aren't auto-generated for presenters in {$pathPart} namespace, <comment>skipping!</comment>");
          continue 2;
        }
      }

      // Build the canonical absolute class name for this file.
      $className = strtr(str_replace([ "dr://src", ".php" ], "", $path), "/", "\\");

      // Make sure this is an actual concrete presenter.
      $reflector = new \ReflectionClass($className);
      if (!$reflector->isInstantiable()) {
        $this->writeDebug("Route aren't auto-generated for unconcrete presenters, <comment>skipping!</comment>");
        continue;
      }

      // Check if we have a special route annotation.
      $docReader = new DocReader($className);
      $route     = $docReader->getParameter("route");

      // Allow concrete classes to opt-out from the automated route creation.
      if ($route === false) {
        continue;
      }

      // We'll try to build the route based on the current namespace, filename and hierarchy if no annotation was found.
      if (!$route) {
        $this->writeDebug("<comment>No @route annotation found</comment>, trying to build route based on class's name.");
        $route = $this->getRouteFromClassName($className, $docReader);
      }

      // Now we can be certain that we have the full route in the default locale, time to translate.
      $translatedRoute = $this->translateRoute($intl, $route, $docReader, $translatedRoutes);

      // Keep track of this translated route.
      $translatedRoutes[$this->replaceRegularExpressionTokens($route)] = $this->replaceRegularExpressionTokens($translatedRoute);

      // We need protecting location blocks for routes with regular expressions, build them.
      $this->setRouteLocation($translatedRoute, $routes, $className, $docReader);
    }

    // Sort the routes in natural order, a sorted routes file makes it easier for humans to find entries.
    $collator->ksort($routes);

    // Build the final nginx location block configuration.
    $locations = "";
    $redirects = [];
    foreach ($routes as $protection => $subRoutes) {
      if ($protection == "/") {
        $this->generateDirectMatchLocation($intl, $collator, $locations, $redirects, $subRoutes);
      }
      else {
        $this->generateProtectedLocation($collator, $locations, $redirects, $subRoutes, $protection);
      }
    }

    // Write the locations to the appropriate position on disk.
    $locationConfigurationURI = str_replace("{{ language_code }}", $intl->languageCode, $this->locationConfigurationURI);
    $this->writeVeryVerbose("Writing routes file for <comment>{$intl->locale}</comment> to <comment>{$locationConfigurationURI}</comment>");
    file_put_contents($locationConfigurationURI, $locations);

    // Write the look-up file for the Intl class if this isn't the default locale and if we actually have anything to
    // be looked up.
    if ($intl->locale != $intl->defaultLocale && !empty($translatedRoutes)) {
      // Build the look-up file's content.
      $this->writeDebug("Expanding route parts...");
      $collator->ksort($translatedRoutes);
      $expandedRouteParts = "<?php return [";
      foreach ($translatedRoutes as $k => $v) {
        $expandedRouteParts .= "\"{$k}\"=>\"{$v}\",";
      }
      // Be sure to remove the last comma from the output.
      $expandedRouteParts = substr($expandedRouteParts, 0, -1) . "];";

      // Write the content to the file.
      $target = "dr://var/intl/{$intl->locale}/routes.php";
      $this->writeVeryVerbose("Writing route parts look-up file to <comment>{$target}</comment>");
      file_put_contents($target, $expandedRouteParts);
    }

    return $this;
  }

  /**
   * Translate given route.
   *
   * @staticvar string $slash
   *   Simply contains <code>"/"</code>.
   * @staticvar array $messages
   *   Used to cache lowercased versions of all available message translations.
   * @param \MovLib\Core\Intl $intl
   *   The current Intl instance.
   * @param string $route
   *   The route to translate.
   * @param \DocBlockReader\Reader $docReader [optional]
   *   Annotation DocReader instance, if given class is checked for possible forced singular form.
   * @return string
   *   The translated route.
   */
  protected function translateRoute(Intl $intl, $route, DocReader $docReader = null) {
    static $slash = "/";
    static $messages = [];

    assert(!empty($route), "A route cannot be empty!");
    assert($route{0} == $slash, "A route must start with a slash: {$route}");

    // Nothing to translate if this is the root route.
    if ($route == $slash) {
      return $slash;
    }

    // We have to lowercase all available translations because all routes are lowercased.
    if (empty($messages[$intl->locale])) {
      foreach ($intl->getTranslations("messages") as $k => $v) {
        $messages[$intl->locale][mb_strtolower($k)] = mb_strtolower($v);
      }
    }

    $translated = "";
    $parts      = explode($slash, $route);
    $c          = count($parts);
    $token      = null;

    // Directly jump over the first index, it's always empty because routes start with a slash.
    while (--$c) {
      // If this part of the route starts with a curly brace it's a message fromatter token and part of the next route
      // part.
      if ($parts[$c]{0} == "{") {
        $token = "{$slash}{$parts[$c]}";
        continue;
      }

      // Remove the dashes from the route for regular translation look-up.
      $part    = "";
      $pattern = strtr($parts[$c], "-", " ");
      if (isset($messages[$intl->locale][$pattern])) {
        $part = $messages[$intl->locale][$pattern];
      }

      // There can be various reasons why the translated version and the pattern match:
      // - The pattern wasn't translated at all
      // - The pattern really is the same as the translation
      // - The pattern is an irregular plural form
      if (empty($part) || $part == $pattern) {
        $forceSingular = false;
        if ($docReader) {
          $forceSingular = $docReader->getParameter("routeForceSingular");
        }

        // This might be an irregular plural/singular form (e.g. series).
        $pluralPattern = "{0,plural,one{{$parts[$c]}}other{{$parts[$c]}}}";
        if (isset($messages[$intl->locale][$pluralPattern])) {
          $part = \MessageFormatter::formatMessage(
            $intl->locale,
            $messages[$intl->locale][$pluralPattern],
            [ $forceSingular || !empty($token) ? 1 : 42 ]
          );
        }

        // Still no luck, remember this pattern.
        if (empty($part) || $part == $pattern) {
          $this->translationPossiblyMissing[$intl->locale] = $part = $pattern;
        }
      }

      // Not that we're going backwards and have append the already translated route parts. It's also important that we
      // don't forget about the token that might have been set in the last iteration of this loop. We also have to
      // reset the token because we consumed it.
      $translated = $slash . strtr($part, " ", "-") . "{$token}{$translated}";
      $token      = null;
    }

    return $translated;
  }

}
