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
  protected $absolute = false;

  /**
   * The route's arguments.
   *
   * @var array|null
   */
  protected $arguments;

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
  protected $fragment;

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
  protected $hostname = ".movlib.org";

  /**
   * The untranslated route splitted into its parts.
   *
   * <b>NOTE</b><br>
   * The parts still contain the Intl placeholders, you may access the placeholders values by directly accessing the
   * public {@see ::$args} array.
   *
   * @var array
   */
  protected $parts = [];

  /**
   * The route's query parts.
   *
   * @var array|null
   */
  protected $query;

  /**
   * The route's untranslated path.
   *
   * @var string
   */
  protected $path;

  /**
   * The route's scheme, defaults to <code>"https"</code>
   *
   * @var string
   */
  protected $scheme = "https";


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
   * @param array $arguments [optional]
   *   The route's formatting arguments, defaults to <code>NULL</code>.
   * @param array $options [optional]
   *   {@see ::setOptions}, defaults to <code>NULL</code> and no additional options will be set.
   */
  public function __construct(\MovLib\Core\Intl $intl, $path, array $arguments = null, array $options = null) {
    $this->intl = $intl;
    $this->setPath($path, $arguments);
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
  public function compile() {
    $this->compiled = URL::encodePath($this->intl->r($this->path, $this->arguments));

    if (isset($this->query)) {
      $this->compiled .= URL::buildQuery($this->query);
    }

    if (isset($this->fragment)) {
      $this->compiled .= "#{$this->fragment}";
    }

    // Note that we'll prepend the language code only if the hostname starts with a dot (default).
    if ($this->absolute === true) {
      $hostname       = $this->hostname{0} === "." ? "{$this->intl->code}{$this->hostname}" : $this->hostname;
      $this->compiled = "{$this->scheme}://{$hostname}{$this->compiled}";
    }

    return $this->compiled;
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument($index) {
    if (isset($this->arguments[$index])) {
      return $this->arguments[$index];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getArguments() {
    return $this->arguments;
  }

  /**
   * {@inheritdoc}
   */
  public function getFragment() {
    return $this->fragment;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageCode() {
    return $this->intl->getCode();
  }

  /**
   * {@inheritdoc}
   */
  public function getHostname() {
    return $this->hostname;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function getPathPart($index) {
    if (isset($this->parts[$index])) {
      return $this->parts[$index];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasArguments() {
    return $this->arguments === null;
  }

  /**
   * {@inheritdoc}
   */
  public function hasQuery() {
    return $this->query === null;
  }

  /**
   * {@inheritdoc}
   */
  public function isAbsolute() {
    return $this->absolute;
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
   * @throws \InvalidArgumentException
   *   If <var>$absolute</var> is not of type boolean.
   */
  public function setAbsolute($absolute) {
    // @devStart
    if (is_bool($absolute) === false) {
      throw new \InvalidArgumentException("Absolute must be of type boolean.");
    }
    // @devEnd
    $this->absolute = $absolute;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function setArgument($argument, $index = null) {
    if (isset($index)) {
      $this->arguments[$index] = $argument;
    }
    else {
      $this->arguments[] = $argument;
    }
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function setArguments(array $arguments) {
    $this->unsetArguments();
    foreach ($arguments as $index => $argument) {
      $this->setArgument($argument, $index);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   * @throws \InvalidArgumentException
   *   If <var>$fragment</var> is invalid.
   */
  public function setFragment($fragment) {
    // @devStart
    if (empty($fragment)) {
      throw new \InvalidArgumentException("Fragment cannot be empty.");
    }
    if (strpos($fragment, "#") !== false) {
      throw new \InvalidArgumentException("Fragment cannot contain a hash (#) character.");
    }
    // @devEnd
    $this->fragment = (string) $fragment;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   * @throws \InvalidArgumentException
   *   If <var>$code</var> is not a valid system language.
   */
  public function setLanguageCode($code) {
    $this->intl->setCode($code);
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function setHostname($hostname) {
    $this->hostname = $hostname;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    foreach ($options as $property => $value) {
      $this->{"set{$property}"}($value);
    }
    $this->parts = explode("/", substr($this->path, 1));
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   * @throws \InvalidArgumentException
   *   If <var>$path</var> is not a string or empty.
   */
  public function setPath($path, array $arguments = null) {
    // @devStart
    if (empty($path)) {
      throw new \InvalidArgumentException("Path cannot be empty.");
    }
    // @devEnd
    $this->path      = (string) $path;
    $this->parts     = explode("/", substr($this->path, 1));
    $this->arguments = $arguments;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   * @throws \InvalidArgumentException
   *   If <var>$parameters</var> is empty.
   */
  public function setQuery(array $parameters) {
    // @devStart
    if (empty($parameters)) {
      throw new \InvalidArgumentException("Parameters cannot be empty.");
    }
    // @devEnd
    $this->unsetQuery();
    foreach ($parameters as $key => $value) {
      $this->setQueryParameter($key, $value);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setQueryParameter($key, $value) {
    $this->query[$key] = $value;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   * @throws \InvalidArgumentException
   *   If <var>$scheme</var> is not <code>"http"</code> or <code>"https"</code>.
   */
  public function setScheme($scheme) {
    // @devStart
    if ($scheme !== "http" && $scheme !== "https") {
      throw new \InvalidArgumentException("Scheme must be either 'http' or 'https': {$scheme}");
    }
    // @devEnd
    $this->scheme = $scheme;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function unsetArguments() {
    $this->arguments = null;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function unsetFragment() {
    $this->fragment = null;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function unsetQuery() {
    $this->query = null;
    return $this->reset();
  }

  /**
   * {@inheritdoc}
   */
  public function unsetQueryParameter($key) {
    if (isset($this->query[$key])) {
      unset($this->query[$key]);
      return $this->reset();
    }
    return $this;
  }

}
