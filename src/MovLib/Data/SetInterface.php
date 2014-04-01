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
namespace MovLib\Data;

/**
 * Defines the set interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface SetInterface {

  /**
   * Get the total count of available (not deleted) entities.
   *
   * @return integer
   *   The total count of available (not deleted) entities.
   */
  public function getCount();

  /**
   * Get all entities for the given unique identifiers.
   *
   * @param array $ids
   *   The unique identifiers to get the entities for.
   * @param string $orderBy [optional]
   *   Optional content for the <code>ORDER BY</code> SQL part, e.g. <code>"`created` DESC"</code>.
   * @return null|array
   *   All entities for the given unique identifiers, <code>NULL</code> if no entities were found.
   */
  public function getIdentifiers(array $ids, $orderBy = null);

  /**
   * Get the entity's index route.
   *
   * @return string
   *   The entity's index route.
   */
  public function getIndexRoute();

  /**
   * Get the plural all lowercased key in the default locale.
   *
   * @return string
   *   The plural all lowercased key in the default locale.
   */
  public function getPluralKey();

  /**
   * Get the plural name of the entity in the current locale.
   *
   * @return string
   *   The plural name of the entity in the current locale.
   */
  public function getPluralName();

  /**
   * Get all entities ordered and partitioned by the given parameters.
   *
   * @param string $by
   *   The SQL queries <code>ORDER BY</code> content, e.g. <code>"`created` DESC"</code>.
   * @param integer $offset
   *   The offset, usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @param integer $limit
   *   The limit (row count), usually provided by the {@see \MovLib\Presentation\PaginationTrait}.
   * @return null|array
   *   All entities ordered and partitioned by the given parameters, <code>NULL</code> if no entities were found.
   */
  public function getOrdered($by, $offset, $limit);

  /**
   * Get a random, unique, existing entity's identifier from the set.
   *
   * @return mixed
   *   A random, unique, existing entity's identifier from the set.
   */
  public function getRandom();

  /**
   * Get the singular all lowercased key in the default locale.
   *
   * @return string
   *   The singular all lowercased key in the default locale.
   */
  public function getSingularKey();

  /**
   * Get the singular name of the entity in the current locale.
   *
   * @return string
   *   The singular of the entity in the current locale.
   */
  public function getSingularName();

}
