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

/**
 * Defines the route interface.
 *
 * <b>NOTE</b><br>
 * The constructor isn't forced by this interface because extending classes might want to add additional parameters that
 * are needed to generate a route.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface RouteInterface {

  /**
   * Implements <code>clone</code> callback.
   */
  public function __clone();

  /**
   * Get the route's string representation in the language that was passed to the constructor or last passed to the
   * compile method.
   *
   * @return string
   *   The route's string representation.
   */
  public function __toString();

  /**
   * Compile the route.
   *
   * @return string
   *   The compiled route.
   * @throws \IntlException
   *   If formatting of the route fails.
   */
  public function compile();

  /**
   * Get formatting argument.
   *
   * @param integer $index
   *   The index to get.
   * @return mixed
   *   The formatting argument or <code>NULL</code> if no argument exists at <var>$index</var>.
   */
  public function getArgument($index);

  /**
   * Get the formatting arguments.
   *
   * @return array|null
   *   The formatting arguments or <code>NULL</code> if there are none.
   */
  public function getArguments();

  /**
   * Get the fragment.
   *
   * @return string|null
   *   The fragment without the leading <code>"#"</code> or <code>NULL</code> if no fragment is set.
   */
  public function getFragment();

  /**
   * Get the hostname.
   *
   * @return string
   *   The hostname.
   */
  public function getHostname();

  /**
   * Get the ISO 639-1 alpha-2 language code.
   *
   * @return string
   *   The ISO 639-1 alpha-2 language code.
   */
  public function getLanguageCode();

  /**
   * Get the path.
   *
   * @return string
   *   The path.
   */
  public function getPath();

  /**
   * Get path part.
   *
   * @param integer $index
   *   The index to get, the route's path is splitted by the URL separator character, the slash (<code>"/"</code>). Part
   *   <code>0</code> would be the first string after the first separator. Assume the route <code>"/foo/bar"</code>,
   *   part <code>0</code> would be <code>"foo"</code> and so on.
   * @return string|null
   *   The route path's part at the index or <code>NULL</code> if no part exists for <var>$index</var>.
   */
  public function getPathPart($index);

  /**
   * Check whether this route has arguments or not.
   *
   * @return boolean
   *   <code>TRUE</code> if it has arguments, <code>FALSE</code> otherwise.
   */
  public function hasArguments();

  /**
   * Check whether this route has a query or not.
   *
   * @return boolean
   *   <code>TRUE</code> if it has a query, <code>FALSE</code> otherwise.
   */
  public function hasQuery();

  /**
   * Check whether this route is absolute or not.
   *
   * @return boolean
   *   <code>TRUE</code> if it is absolute, <code>FALSE</code> otherwise.
   */
  public function isAbsolute();

  /**
   * Empty the compilation cache of the route.
   *
   * @return this
   */
  public function reset();

  /**
   * Set absolute.
   *
   * @param boolean $absolute
   *   The new absolute state.
   * @return this
   */
  public function setAbsolute($absolute);

  /**
   * Set argument.
   *
   * @param string $argument
   *   The argument to set.
   * @param mixed $index [optional]
   *   The argument's index, defaults to <code>NULL</code> and the argument is simply appended to the current arguments.
   * @return this
   */
  public function setArgument($argument, $index = null);

  /**
   * Set arguments.
   *
   * @param array $arguments
   *   The arguments to set.
   * @return this
   */
  public function setArguments(array $arguments);

  /**
   * Set fragment.
   *
   * @param string $fragment
   *   The fragment to set.
   * @return this
   */
  public function setFragment($fragment);

  /**
   * Set the ISO 639-1 alpha-2 language code.
   *
   * @param string $code
   *   The ISO 639-1 alpha-2 language code to set.
   * @return this
   */
  public function setLanguageCode($code);

  /**
   * Set the hostname.
   *
   * @param string $hostname
   *   The hostname to set.
   * @return this
   */
  public function setHostname($hostname);

  /**
   * Set options, this method allows you to set multiple options at once.
   *
   * @param array $options
   *   Possible options are:
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
   *     <li><code>"path"</code> A string that represents the route's path part of the URL.</li>
   *     <li><code>"query"</code> An array of query key-value-pairs (without any URL encoding) to append.</li>
   *     <li><code>"scheme"</code> The scheme of the URL if <code>"absolute"</code> is set to <code>TRUE</code>. The
   *     default value is <code>"https"</code>.</li>
   *   </ul>
   */
  public function setOptions(array $options);

  /**
   * Set the route's path.
   *
   * @param string $path
   *   The route's path to set.
   * @param array $args [optional]
   *   The route's formatting arguments, defaults to <code>NULL</code>.
   * @return this
   */
  public function setPath($path, array $args = null);

  /**
   * Set query.
   *
   * @param array $parameters
   *   The route's query parameters to set.
   * @return this
   */
  public function setQuery(array $parameters);

  /**
   * Set query parameter.
   *
   * @param string $key
   *   The query parameter's key.
   * @param mixed $value
   *   The query parameter's value.
   * @return this
   */
  public function setQueryParameter($key, $value);

  /**
   * Set the scheme.
   *
   * @param string $scheme
   *   The scheme to set.
   * @return this
   */
  public function setScheme($scheme);

  /**
   * Unset arguments.
   *
   * @return this
   */
  public function unsetArguments();

  /**
   * Unset fragment.
   *
   * @return this
   */
  public function unsetFragment();

  /**
   * Unset query.
   *
   * @return this
   */
  public function unsetQuery();

  /**
   * Unset query parameter.
   *
   * @return this
   */
  public function unsetQueryParameter($key);

}
