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
   * The internationalization instance.
   *
   * @var \MovLib\Core\Intl
   */
  public $intl;

  /**
   * The route's hostname including the leading subdomain separator.
   *
   * @var string
   */
  public $hostname;

  /**
   * The untranslated route splitted into its parts.
   *
   * <b>NOTE</b><br>
   * The parts still contain the Intl placeholders, you may access the placeholders values by directly accessing the
   * public {@see ::$args} array.
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

  /**
   * The route's scheme, defaults to <code>"https"</code>
   *
   * @var string
   */
  public $scheme;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new route.
   *
   * <b>NOTE</b><br>
   * Only use this object for internal routes, you may use the {@see \MovLib\Component\URL} object for external ones.
   *
   * @param \MovLib\Core\Intl $intl
   *   The internationalization instance to use for route formatting and translation.
   * @param string $path
   *   The route's untranslated path.
   * @param array $options [optional]
   *   Additional options for this route, possible options are:
   *   <ul>
   *     <li><code>"absolute"</code> Either <code>TRUE</code> or <code>FALSE</code> (default):
   *       <ul>
   *         <li><code>TRUE</code> will create an absolute URL including scheme and domain.</li>
   *         <li><code>FALSE</code> (default) will create a root relative URL.</li>
   *       </ul>
   *     </li>
   *     <li><code>"args"</code> An array of arguments that should be used to format the route, this array will be
   *     passed to {@see \MovLib\Core\Intl::r()}.</li>
   *     <li><code>"fragment"</code> A fragment identifier (named anchor) to append to the URL. Do not include the
   *     leading <code>"#"</code> character.</li>
   *     <li><code>"hostname"</code> The hostname of the URL if <code>"absolute"</code> is set to <code>TRUE</code>. The
   *     default value is <code>".movlib.org"</code>; note the leading dot. Hostname's with a leading dot will have the
   *     current ISO 639-1 alpha-2 code prepended automatically.</li>
   *     <li><code>"query"</code> An array of query key-value-pairs (without any URL encoding) to append.</li>
   *     <li><code>"scheme"</code> The scheme of the URL if <code>"absolute"</code> is set to <code>TRUE</code>. The
   *     default value is <code>"https"</code>.</li>
   *   </ul>
   */
  public function __construct(\MovLib\Core\Intl $intl, $path, array $options = []) {
    // Merge in defaults.
    $options += [
      "absolute" => false,
      "args"     => null,
      "fragment" => null,
      "hostname" => ".movlib.org",
      "query"    => null,
      "scheme"   => "https",
    ];

    // Export to class scope.
    $this->absolute = $options["absolute"];
    $this->args     = $options["args"];
    $this->fragment = $options["fragment"];
    $this->hostname = $options["hostname"];
    $this->intl     = $intl;
    $this->parts    = explode("/", $path);
    $this->query    = $options["query"];
    $this->route    = $path;
    $this->scheme   = $options["scheme"];

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
    return $this->compiled ?: $this->compile();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Recompile the route.
   *
   * @param string $languageCode [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for recompilation of the route, this will affect the target
   *   language of the translation and (if absolute) the subdomain. Defaults to <code>NULL</code> and the current
   *   language from the intl instance that was passed to the constructor will be used. Note that
   * @return string
   *   The recompiled route.
   * @throws \IntlException
   */
  public function compile($languageCode = null) {
    // Update our intl instance with the new language code if passed.
    $languageCode || ($languageCode = $this->intl->code);

    // Translate, format and encode the route.
    $this->compiled = URL::encodePath($this->intl->r($this->route, $this->args, $languageCode));

    // Honor optional options.
    $this->query    && ($this->compiled .= http_build_query($this->query, null, null, PHP_QUERY_RFC3986));
    $this->fragment && ($this->compiled .= $this->fragment);

    // Compile absolute route if requested. Note that we'll prepend the language code only if the hostname starts with
    // a dot (default).
    if ($this->absolute) {
      $hostname = $this->hostname{0} === "." ? "{$languageCode}{$this->hostname}" : $this->hostname;
      $this->compiled = "{$this->scheme}://{$hostname}{$this->compiled}";
    }

    return $this->compiled;
  }

  /**
   * Get a part from the route's path.
   *
   * @param integer $index
   *   The index to get, the route's path is splitted by the URL separator character, the slash (<code>"/"</code>). Part
   *   <code>0</code> would be the first string after the first separator. Assume the route <code>"/foo/bar"</code>,
   *   part <code>0</code> would be <code>"foo"</code> and so on.
   * @return string|null
   *   The route path's part at the index or <code>NULL</code> if no part exists for <var>$index</var>.
   */
  public function part($index) {
    if (isset($this->parts[$index])) {
      return $this->parts[$index];
    }
  }

  /**
   * Empty the compilation cache of the route.
   *
   * @return this
   */
  public function reset() {
    $this->compiled = null;
    return $this;
  }

}
