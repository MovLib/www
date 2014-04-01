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
namespace MovLib\Data;

/**
 * Defines the entity interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface EntityInterface {

  /**
   * Whether this entity is gone or not.
   *
   * @return boolean
   *   <code>TRUE</code> if the entity is gone, <code>FALSE</code> otherwise.
   */
  public function isGone();

  /**
   * Get the count of a relationship.
   *
   * <b>EXAMPLE</b><br>
   * <code><?php
   *
   * public function getCount($from, $what = "*") {
   *   return $this->getMySQLi()->query("SELECT COUNT({$what}) FROM `{$from}` WHERE `id` = 1 LIMIT 1")->fetch_row()[0];
   * }
   *
   * ?></code>
   *
   * @param string $from
   *   The table defining the relationship that is to be counted.
   * @param string $what
   *   The content of the <code>COUNT()</code> function in the SQL query.
   * @return integer
   *   The count of the relationship.
   */
  public function getCount($from, $what = "*");

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
   * Get the entity's route.
   *
   * @return string
   *   The entity's route.
   */
  public function getRoute();

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
