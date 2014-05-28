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
namespace MovLib\Core\Entity;

/**
 * Defines the entity set interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface EntitySetInterface extends \ArrayAccess, \MovLib\Core\Routing\RoutingInterface {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the entity set's name (short class name).
   *
   * @return string
   *   The entity set's name (short class name).
   */
  public function __toString();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the entities bundle's title (e.g. Movies, Series, Persons).
   *
   * <b>NOTE</b><br>
   * The default implementation {@see \MovLib\Core\Entity\AbstractEntitySet} provides a public bundle title property as
   * well, you may use this property directly for performance reasons if you need the default locale. You can also
   * access the public bundle property directly which contains the bundle's title in the system's default locale.
   *
   * @param string $locale
   *   The entities bundle title's locale to get.
   * @return string
   *   The entities bundle's title.
   */
  public function bundleTitle($locale);

  /**
   * Get a random, unique, existing entity's identifier from all undeleted entities.
   *
   * @return mixed
   *   A random, unique, existing entity's identifier from all undeleted entities.
   */
  public function getRandom();

  /**
   * Get the total count of all undeleted entities.
   *
   * @return integer
   *   The total count of all undeleted entities.
   */
  public function getTotalCount();

  /**
   * Load entities according to the conditions.
   *
   * @param \MovLib\Core\Database\Query\Condition $conditions [optional]
   *   The conditions to filter the entities, defaults to <code>NULL</code> and the default conditions are used.
   * @param string $alias [optional]
   *   An alias for the set's primary table, defaults to <code>NULL</code> and no alias will be used. It's recommended
   *   to set an alias because it makes your code more portable and easier to understand and read because it doesn't
   *   depend on the actual table's name.
   * @return this
   * @throws \mysqli_sql_exception
   */
  public function load(\MovLib\Core\Database\Query\Condition $conditions = null, $alias = null);

  /**
   * Get the entity's parent entities and sets.
   *
   * @return array
   *   The entity's parent entities and sets.
   */
  public function parents();

  /**
   * Get the entities index route.
   *
   * <b>NOTE</b><br>
   * The default implementation {@see \MovLib\Core\Entity\AbstractEntitySet} provides a public route property as well,
   * you may use this property directly for performance reasons if you need the default locale.
   *
   * @param string $locale
   *   The entities index route's locale to get.
   * @return \MovLib\Core\Routing\Route
   *   The entities index route.
   */
  public function route($locale);

}
