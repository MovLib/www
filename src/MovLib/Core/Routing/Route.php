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
class Route implements RouteInterface {


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
   * @var string|null
   */
  public $fragment;

  /**
   * The internationalization instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The route's hostname including the leading subdomain separator.
   *
   * @var string
   */
  public $hostname = ".movlib.org";

  /**
   * The untranslated route splitted into its parts.
   *
   * <b>NOTE</b><br>
   * The parts still contain the Intl placeholders, you may access the placeholders values by directly accessing the
   * public {@see ::$args} array.
   *
   * @var array
   */
  public $parts = [];

  /**
   * The route's query parts.
   *
   * @var array|null
   */
  public $query;

  /**
   * The route's untranslated path.
   *
   * @var string
   */
  public $path;

  /**
   * The route's scheme, defaults to <code>"https"</code>
   *
   * @var string
   */
  public $scheme = "https";


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
   *   {@see ::setOptions}
   */
  public function __construct(\MovLib\Core\Intl $intl, $path, array $options = null) {
    $this->intl = $intl;
    $this->path = $path;
    isset($options) && $this->setOptions($options);
  }

  /**
   * {@inheritdoc}
   */
  public function __clone() {
    $this->intl = clone $this->intl;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return $this->compiled ?: $this->compile();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function arg($index) {
    if (isset($this->args[$index])) {
      return $this->args[$index];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function compile($languageCode = null) {
    // Use the current language code if none was passed.
    $languageCode || ($languageCode = $this->intl->code);

    // @devStart
    if (empty(\MovLib\Core\Intl::$systemLanguages[$languageCode])) {
      throw new \InvalidArgumentException("The passed language code must be a valid system language code: {$languageCode}");
    }
    // @devEnd

    // Translate, format and encode the route.
    $this->compiled = URL::encodePath($this->intl->r($this->path, $this->args, $languageCode));

    // Honor optional options.
    $this->query    && ($this->compiled .= "?" . http_build_query($this->query, null, null, PHP_QUERY_RFC3986));
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
   * {@inheritdoc}
   */
  public function part($index) {
    if (isset($this->parts[$index])) {
      return $this->parts[$index];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->compiled = null;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    foreach ($options as $property => $value) {
      $this->$property = $value;
    }
    $this->parts = explode("/", substr($this->path, 1));
    return $this->reset();
  }

}
