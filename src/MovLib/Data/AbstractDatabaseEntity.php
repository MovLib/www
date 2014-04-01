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
abstract class AbstractDatabaseEntity extends \MovLib\Core\AbstractDatabase implements \MovLib\Data\EntityInterface {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The entity's changed date and time.
   *
   * @var \DateTime
   */
  public $changed;

  /**
   * The entity's creation date and time.
   *
   * @var \DateTime
   */
  public $created;

  /**
   * The entity's deletion status.
   *
   * @var boolean
   */
  public $deleted = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function isGone() {
    return $this->deleted;
  }

  /**
   * {@inheritdoc}
   */
  public function getCount($from, $what = "*") {
    return $this->getMySQLi()->query("SELECT COUNT({$what}) FROM `{$from}` WHERE `{$this->singular}_id` = {$this->id} LIMIT 1")->fetch_row()[0];
  }

  /**
   * {@inheritdoc}
   */
  public function getIndexRoute() {
    static $route;
    if (!$route) {
      $route = $this->intl->rp("/{$this->getPluralKey()}");
    }
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function getRoute() {
    static $route = [];
    if (empty($route[$this->id])) {
      $route[$this->id] = $this->intl->r("/{$this->getSingularKey()}/{0}", $this->id);
    }
    return $route[$this->id];
  }

  /**
   * Initialize the entity.
   *
   * Further initialization work after an entity was initialized via PHP's built-in {@see \mysqli_result::fetch_object}
   * method or continued initialization after the own constructor was called.
   *
   * @return this
   */
  protected function init() {
    $this->changed = new \DateTime($this->changed);
    $this->created = new \DateTime($this->created);
    $this->deleted = (boolean) $this->deleted;
    return $this;
  }

  /**
   * Transform the properties to date objecs.
   *
   * <b>NOTE</b><br>
   * The properties must be passed as reference.
   *
   * @param array $properties
   *   The properties to transform.
   * @return this
   */
  protected function toDates(array $properties) {
    foreach ($properties as &$property) {
      if (isset($property)) {
        $property = new \MovLib\Data\Date($property);
      }
    }
    return $this;
  }

  /**
   * Unserialize the given properties.
   *
   * <b>NOTE</b><br>
   * The properties must be passed as reference.
   *
   * @param array $properties
   *   The properties to unserialize.
   * @return this
   */
  protected function unserialize(array $properties) {
    foreach ($properties as &$property) {
      if (isset($property)) {
        $property = unserialize($property);
      }
    }
    return $this;
  }

}
