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
 * Defines the base class for data entities.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntity extends \MovLib\Core\AbstractDatabase {


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Further initialization work after an entity was initialized via PHP's built-in {@see \mysqli_result::fetch_object()}
   * method or continued initialization after the own init method was called.
   *
   * @return this
   */
  abstract public function initFetchObject();

  /**
   * Get the entity's plural name.
   *
   * <b>NOTE</b><br>
   * This has to be the all lowercased name of the entity, as used in routes and in the database.
   *
   * <b>EXAMPLE</b><br>
   * <code><?php
   *
   * public function getPluralName() {
   *   return "entities";
   * }
   *
   * ?></code>
   *
   * @return string
   *   The entitiy's plural name.
   */
  abstract public function getPluralName();

  /**
   * Get the entity's singular name.
   *
   * <b>NOTE</b><br>
   * This has to be the all lowercased name of the entity, as used in routes and in the database.
   *
   * <b>EXAMPLE</b><br>
   * <code><?php
   *
   * public function getSingularName() {
   *   return "entity";
   * }
   *
   * ?></code>
   *
   * @return string
   *   The entitiy's plural name.
   */
  abstract public function getSingularName();


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
   *   The table containing the data that is to be counted.
   * @param string $what
   *   The content of the <code>"COUNT"</code> function in the SQL query.
   * @return integer
   *   The count of the relationship.
   */
  public function getCount($from, $what = "*") {
    return $this->getMySQLi()->query("SELECT COUNT({$what}) FROM `{$from}` WHERE `{$this->getSingularName()}_id` = {$this->id} LIMIT 1")->fetch_row()[0];
  }

}
