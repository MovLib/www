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
namespace MovLib\Core\Entity;

/**
 * Defines the entity interface.
 *
 * Entities are the classes that represent a user creatable, editable and deletable part of the website. Examples
 * include Movies, Series and Help Articles. Not included are the users themselves or system pages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface EntityInterface {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the entity's name (short class name).
   *
   * @return string
   *   The entity's name (short class name).
   */
  public function __toString();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the entity bundle's title (e.g. Movie, Series, Person).
   *
   * <b>NOTE</b><br>
   * The default implementation {@see \MovLib\Core\Entity\AbstractEntity} provides a public bundle title property as
   * well, you may use this property directly for performance reasons if you need the default locale. You can also
   * access the public bundle property directly which contains the bundle's title in the system's default locale.
   *
   * @param string $locale
   *   The entity bundle's title locale to get.
   * @return string
   *   The entity bundle's title.
   */
  public function bundleTitle($locale);

  /**
   * Initialize the entity.
   *
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @return this
   */
  public function init(array $values = null);

  /**
   * Get the entity's lemma.
   *
   * The lemma is the title or name of the entity. The actual property name almost always differs and might even have
   * multiple permutations (e.g. movies with their display titles, original titles, ...). This method provides a public
   * access point that will always return the correct value.
   *
   * <b>NOTE</b><br>
   * The default implementation {@see \MovLib\Core\Entity\AbstractEntity} provides a public lemma property as well, you
   * may use this property directly for performance reasons if you need the default locale. Especially since a call to
   * this method might result in another database query.
   *
   * @param string $locale
   *   The entity lemma's locale to get.
   * @return string
   *   The entity's lemma.
   */
  public function lemma($locale);

  /**
   * Get the entity's parent entities and sets.
   *
   * @return array
   *   The entity's parent entities and sets.
   */
  public function parents();

  /**
   * Get the entity's show route.
   *
   * <b>NOTE</b><br>
   * The default implementation {@see \MovLib\Core\Entity\AbstractEntity} provides a public show route property as well,
   * you may use this property directly for performance reasons if you need the default locale.
   *
   * @param string $locale
   *   The entity show route's locale to get.
   * @return \MovLib\Core\Routing\Route
   *   The entity's show route.
   */
  public function route($locale);

}
