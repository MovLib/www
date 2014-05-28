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
namespace MovLib\Core\Routing;

use \MovLib\Component\URL;

/**
 * Defines the route object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Route {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Route";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this route is absolute or not.
   *
   * @var boolean
   */
  public $absolute = false;

  /**
   * The route's arguments.
   *
   * @var array|null
   */
  public $args;

  /**
   * Used to cache the route after it was built once.
   *
   * @var string
   */
  protected $compiled;

  /**
   * The route's fragment.
   *
   * @var null|string
   */
  public $fragment;

  /**
   * Active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The untranslated route splitted into its parts.
   *
   * @var array
   */
  public $parts;

  /**
   * The route's query parts.
   *
   * @var array|null
   */
  public $query;

  /**
   * The untranslated route.
   *
   * @var string
   */
  public $route;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new route.
   *
   * @param \MovLib\Core\Intl $intl
   *   Active Intl instance.
   * @param string $route
   *   The untranslated route.
   * @param array $options [optional]
   *   Additional options for this route.
   */
  public function __construct(\MovLib\Core\Intl $intl, $route, array $options = []) {
    // Merge in defaults.
    $options += [
      "absolute" => false,
      "args"     => null,
      "fragment" => null,
      "query"    => null,
    ];

    // Export to class scope.
    $this->absolute = $options["absolute"];
    $this->args     = $options["args"];
    $this->fragment = $options["fragment"];
    $this->intl     = $intl;
    $this->parts    = explode("/", $route);
    $this->query    = $options["query"];
    $this->route    = $route;

    // The first element is always empty because a route always starts with a slash.
    array_shift($this->parts);
  }

  /**
   * Get the route's string representation.
   *
   * @return string
   *   The route's string representation.
   */
  public function __toString() {
    return $this->compiled ?: $this->recompile();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Recompile the route.
   *
   * @param string $locale [optional]
   *   Use different locale for recompilation of this route, defaults to <code>NULL</code> and the locale from the
   *   Intl instance that was passed to the constructor is used. Note that this will affect the subdomain as well if
   *   this is a absolute route.
   * @return string
   *   The recompiled route.
   */
  public function recompile($locale = null) {
    // Translate, format and encode the route.
    $this->compiled = URL::encodePath($this->intl->r($this->route, $this->args, $locale));

    // Append optional parts.
    $this->query    && ($this->compiled .= http_build_query($this->query, null, null, PHP_QUERY_RFC3986));
    $this->fragment && ($this->compiled .= $this->fragment);

    // Compile absolute if necessary.
    if ($this->absolute) {
      // Format subdomain.
      $subDomain = $locale ? "{$locale{0}}{$locale{1}}" : $this->intl->languageCode;

      // @todo The scheme and hostname are hardcoded, we have to get this from somewhere else, but introducing more
      //       dependencies is a bad choice. We need to resolve this in some way.
      // @todo Do we need routes that point to a non-subdomain target? I dont' think so.
      $this->compiled = "https://{$subDomain}.movlib.org{$this->compiled}";
    }

    return $this->compiled;
  }

  /**
   * Empty the compilation cache of the route.
   *
   * Useful if the locale was changed and you want to reset it back to its initial locale.
   *
   * @return this
   */
  public function reset() {
    $this->compiled = null;
    return $this;
  }

}
