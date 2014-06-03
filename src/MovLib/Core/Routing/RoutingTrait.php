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

  /**
   * @see \MovLib\Core\Data\RoutingInterface::r()
   */
  final public function r($routePart, array $args = []) {
    // This array is used to cache previously generated routes. Note that we have to build a deep structure because this
    // trait is shared among many entities. We use the class name and the concrete entity's unique identifier to create
    // that structure. This should ensure that routes are correctly cached for each entity.
    static $routes = [];

    // Use the cached route if we already built this once.
    if (isset($routes[static::name][$this->id][$routePart])) {
      return $routes[static::name][$this->id][$routePart];
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
            $c = substr_count($this->route->route, "{") - 1; // Minus one because formatting starts at index zero.
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
    $routes[static::name][$this->id][$cacheKey] = $this->container->intl->r("{$this->route->route}{$routePart}", $args);
    return $routes[static::name][$this->id][$cacheKey];
  }

}
