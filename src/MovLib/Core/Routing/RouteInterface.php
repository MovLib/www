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
   * Get and/or set an argument from the route's arguments.
   *
   * @param integer $index
   *   The index to get.
   * @return mixed
   *   The route argument's argument at the index or <ocde>NULL</code> if no argument exists for <var>$index</var>.
   */
  public function arg($index);

  /**
   * Compile the route.
   *
   * @param string $languageCode [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for recompilation of the route, this will affect the target
   *   language of the translation and (if absolute) the subdomain. Defaults to <code>NULL</code> and the current
   *   language from the intl instance that was passed to the constructor will be used. Note that
   * @return string
   *   The recompiled route.
   * @throws \IntlException
   *   If formatting of the route fails.
   */
  public function compile($languageCode = null);

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
  public function part($index);

  /**
   * Empty the compilation cache of the route.
   *
   * @return this
   */
  public function reset();

  /**
   * Set the route's options.
   *
   * @param array $options
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
   *     <li><code>"path"</code> A string that represents the route's path part of the URL.</li>
   *     <li><code>"query"</code> An array of query key-value-pairs (without any URL encoding) to append.</li>
   *     <li><code>"scheme"</code> The scheme of the URL if <code>"absolute"</code> is set to <code>TRUE</code>. The
   *     default value is <code>"https"</code>.</li>
   *   </ul>
   */
  public function setOptions(array $options);

}
