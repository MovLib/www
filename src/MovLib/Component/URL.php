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
namespace MovLib\Component;

/**
 * Defines the URL object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class URL {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "URL";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The URL's scheme (e.g. <code>"http"</code>).
   *
   * @var string
   */
  public $scheme;

  /**
   * The URL's host (e.g. <code>"movlib.org"</code>).
   *
   * @var string
   */
  public $host;

  /**
   * The URL's port (e.g. <code>80</code>).
   *
   * @var integer
   */
  public $port;

  /**
   * The URL's user.
   *
   * @var string
   */
  public $user;

  /**
   * The URL's password.
   *
   * @var string
   */
  public $pass;

  /**
   * The URL's path.
   *
   * @var string
   */
  public $path;

  /**
   * The URL's query.
   *
   * @var array
   */
  public $query;

  /**
   * The URL's fragment (without leading hash character).
   *
   * @var string
   */
  public $fragment;


  // ------------------------------------------------------------------------------------------------------------------- Protected Properties


  /**
   * The compiled URL.
   *
   * @var string
   */
  protected $compiled;

  /**
   * The URL's path parts.
   *
   * @var array
   */
  protected $parts;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new URL.
   *
   * @param mixed $url
   *   The URL to parse and export into class scope.
   * @throws \InvalidArgumentException
   *   If <var>$url</var> is invalid.
   */
  public function __construct($url) {
    // Use PHP's built-in function to parse the URL.
    $parsed = parse_url($url);

    // @devStart
    if ($parsed === false) {
      throw new \InvalidArgumentException("The URL seems to be invalid: {$url}");
    }
    // @devEnd

    // Export the extracted parts to class scope.
    foreach ($parsed as $part => $value) {
      $this->$part = $value;
    }

    // Determine the individual parts of the path.
    if (isset($this->path)) {
      $this->parts = explode("/", substr($this->path, 1));
    }

    // Determine the individual parts of the query.
    if (isset($this->query)) {
      parse_str($this->query, $this->query);
    }
  }

  /**
   * Get the URL's string representation.
   *
   * @return string
   *   The URL's string representation.
   */
  public function __toString() {
    return $this->compiled ?: $this->compile();
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Build URL encoded query string.
   *
   * @param array $query
   *   Associative query array.
   * @return string
   *   URL encoded query string.
   */
  public static function buildQuery(array $query) {
    return "?" . http_build_query($query, null, null, PHP_QUERY_RFC3986);
  }

  /**
   * Encode URL path preserving slashes.
   *
   * @param string $path
   *   The URL path to encode.
   * @return string
   *   The encoded URL path.
   */
  public static function encodePath($path) {
    if (empty($path) || $path === "/") {
      return $path;
    }
    return str_replace("%2F", "/", rawurlencode($path));
  }

  /**
   * Extracts URLs from a string.
   *
   * @param string $text
   *   The string containing URLs.
   * @return array
   *   Array containing all URLs that where found in <var>$text</var>.
   */
  public static function extract($text) {
    preg_match_all("/(https?|ftps?)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/", $text, $matches);
    $urls = [];
    foreach ($matches[0] as $url) {
      $urls[] = new URL($url);
    }
    return $urls;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Compile the URL.
   *
   * @return string
   *   The compiled URL.
   */
  public function compile() {
    $pass           = $this->pass     ? ":{$this->pass}"                 : null;
    $credentials    = $this->user     ? "{$this->user}{$pass}@"          : null;
    $port           = $this->port     ? ":{$this->port}"                 : null;
    $query          = $this->query    ? static::buildQuery($this->query) : null;
    $fragment       = $this->fragment ? "#{$this->fragment}"             : null;
    $this->compiled = "{$this->scheme}://{$credentials}{$this->host}{$port}/{$this->path}{$query}{$fragment}";
    return $this->compiled;
  }

  /**
   * Get a part from the URL's path.
   *
   * @param integer $index
   *   The path part's index.
   * @return mixed
   *   The path's part at <var>$index</var> or <code>NULL</code> if nothing exists at <var>$index</var>.
   */
  public function part($index) {
    if (isset($this->parts[$index])) {
      return $this->parts[$index];
    }
  }

}
