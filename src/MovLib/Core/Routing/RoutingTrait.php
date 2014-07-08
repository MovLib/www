<?php

/* !
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
namespace MovLib\Core\Routing;

/**
 * Defines the routing trait.
 *
 * The routing trait provides a default implementation for the {@see \MovLib\Core\Data\RoutingInterface}.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait RoutingTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Array containing route parts that were already processed.
   *
   * @var array
   */
  protected $processedPaths = [];

  /**
   * The concrete object's route.
   *
   * @var \MovLib\Core\Routing\Route
   */
  public $route;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @see \MovLib\Core\Data\RoutingInterface::getRoute();
   */
  final public function getRoute($path = null, array $args = null, array $options = null) {
    // No need to do anything if no path was given, note that we always return a clone and make sure that nobody is
    // changing our route's instance.
    if ($path === null) {
      return clone $this->route;
    }

    // We already know this path, nothing to do.
    if (empty($this->processedPaths[$path])) {
      // We only need to process the passed path if our concrete object has any route arguments and the path contains
      // placeholders.
      if (isset($this->route->args) && strpos($path, "{") !== false) {
        // We use a closure at this point, we'd have to expose the callback method if we'd implement it in class scope
        // because the callback has to be public. We don't want to expose it to anyone.
        $this->processedPaths[$path] = preg_replace_callback("/{([^}]+)}/", function () {
          static $c = null;
          $c === null && ($c = substr_count($this->route->path, "{") - 1); // Minus one, formatting starts at zero.
          ++$c;
          return "{{$c}}";
        }, $path);
      }
      // Insert into cache for faster look-ups later.
      else {
        $this->processedPaths[$path] = $path;
      }
    }
    $options["path"] = "{$this->route->path}{$this->processedPaths[$path]}";

    // We always have to merge our arguments with the passed arguments.
    isset($args) && ($options["args"] = array_merge($this->route->args, $args));

    // Now we can simply clone our own route and export the new options.
    $route = clone $this->route;
    $route->setOptions($options);

    return $route;
  }

  /**
   * @see \MovLib\Core\Data\RoutingInterface::r()
   */
  final public function r($routePart, array $args = [], $languageCode = null) {
    // This array is used to cache previously generated routes. Note that we have to build a deep structure because this
    // trait is shared among many entities. We use the class name to create that structure. This ensures that routes are
    // correctly cached for each entity.
    static $routes = [];

    // Use the cached route if we already built this once.
    if (isset($routes[static::name][$routePart])) {
      // We have to rebuild the arguments each time, because we can't be certain that this is the same entity that
      // was previously requested.
      if ($this->route->args) {
        $args = empty($args) ? $this->route->args : array_merge($this->route->args, $args);
      }
      return $this->intl->r($routes[static::name][$routePart], $args, $languageCode);
    }

    // The route will change if it contains placeholders, but later look-ups will be unprocessed, therefore we have to
    // keep a copy and use the original route as cache key.
    $cacheKey = $routePart;

    // We only need to process the passed route part if our concrete class has any route arguments.
    if ($this->route->args) {
      // We have to renumber the placeholders from the passed route part to allow insertion of our own arguments.
      if (strpos($routePart, "{") !== false) {
        // We can safely assume that we have arguments, even if not, the signature contains an array default value and
        // the merge is safe.
        $args  = array_merge($this->route->args, $args);

        // We use a closure at this point, we'd have to expose the callback method if we'd implement in class scope
        // because the callback has to be public. We don't want to expose it.
        $routePart = preg_replace_callback("/{([^}]+)}/", function ($matches) {
          static $c = null;

          // Count our placeholders if we haven't done so yet.
          if (!$c) {
            $c = substr_count($this->route->path, "{") - 1; // Minus one because formatting starts at index zero.
          }

          // Increment, insert, return...
          ++$c;
          return "{{$c}}";
        }, $routePart);
      }
      // The passed part doesn't contain any placeholders, but we do.
      else {
        $args = $this->route->args;
      }
    }

    // Add this route to our cache and we're done.
    $routes[static::name][$cacheKey] = "{$this->route->path}{$routePart}";
    return $this->intl->r($routes[static::name][$cacheKey], $args, $languageCode);
  }

  /**
   * Set the concrete object's route.
   *
   * @param \MovLib\Core\Intl $intl
   *   The internationalization instance for route translation.
   * @param string $path
   *   The concrete object's untranslated route path.
   * @param array $args [optional]
   *   The route's formatting arguments.
   * @return this
   */
  protected function setRoute(\MovLib\Core\Intl $intl, $path, array $args = null) {
    $this->route = new Route($intl, $path, isset($args) ? [ "args" => $args ] : null);
    return $this;
  }

}
