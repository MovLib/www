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
 * Defines the routing interface for data classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface RoutingInterface {

  /**
   * Get the object's route.
   *
   * @return \MovLib\Core\Routing\Route
   *   The object's route.
   */
  public function getRoute();

  /**
   * Append a rout part to the data class's route and translate/format it.
   *
   * This method can be used in abstracted classes to append certain parts that are the same for all data classes to the
   * route of the concrete class and get the translated and formatted route back. Note that you don't have to know the
   * arguments count of the concrete class's route. Simply start from 0 (zero) as you're used to, the passed route part
   * is processed by this method and the returned route is correctly formatted.
   *
   * <b>EXAMPLE</b><br>
   * <pre>$genre->r("/history/{0}", $revision->id); // /genre/1/history/20140527145549</pre>
   *
   * @param string $routePart
   *   The route to append to the concrete class's route.
   * @param array $args [optional]
   *   Additional arguments to format the route, defaults to an empty array.
   * @param string $languageCode [optional]
   *   The system language's ISO 639-1 alpha-2 code to translate the route to, defaults to <code>NULL</code> and the
   *   current language is used.
   * @return string
   *   The translated and formatted route.
   * @throws \IntlException
   */
  public function r($routePart, array $args = [], $languageCode = null);

}
