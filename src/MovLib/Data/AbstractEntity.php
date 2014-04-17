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

use \MovLib\Data\DateTime;

/**
 * Defines the base class for database entity objects.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEntity extends \MovLib\Data\AbstractConfig {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Constant used as value for properties that are mandatory during construction of an entity.
   *
   * @var integer
   */
  const MANDATORY = -1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity's unique identifier.
   *
   * @var integer
   */
  public $id = self::MANDATORY;

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

  /**
   * The entity's unique identifier this entity is in relation to.
   *
   * An entity might be instantiated for another entity, e.g. a set of genres for a single movie, this property will
   * contain the unique identifier of the movie this particular genre was instantiated for.
   *
   * @see \MovLib\Data\AbstractSet::loadEntitySets()
   * @var mixed
   */
  public $entityId;

  /**
   * The entity's index route in the current locale.
   *
   * @var string
   */
  public $routeIndex;

  /**
   * The entity's Wikipedia link in the current locale.
   *
   * @var null|string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the entity.
   *
   * Set the properties of the entity for cases when \mysqli_result::fetch_object is not applicable.
   *
   * @param \stdClass $properties
   *   The properties to set, prefixed by {@see AbstractEntity::singularKey}.
   * @return this
   */
  public function construct(\stdClass $properties) {
    foreach ($this as $k => $v) {
      $kp = $this->singularKey . ucfirst($k);
      if ($this->$k === self::MANDATORY || isset($properties->$kp)) {
        $this->$k = $properties->$kp;
      }
    }
    return $this->init();
  }

  /**
   * Get the count of a relationship.
   *
   * <b>EXAMPLE</b><br>
   * <code><?php
   *
   * public function getCount($from, $where = null, $what = "*") {
   *   return $this->getMySQLi()->query("SELECT COUNT({$what}) FROM `{$from}` WHERE {$where}")->fetch_row()[0];
   * }
   *
   * ?></code>
   *
   * @param string $from
   *   The table defining the relationship that is to be counted.
   * @param string $where [Optional]
   *   The WHERE clause of the SQL query (without WHERE).
   * @param string $what
   *   The content of the <code>COUNT()</code> function in the SQL query.
   * @return integer
   *   The count of the relationship.
   */
  public function getCount($from, $where = null, $what = "*") {
    if ($where) {
      $where = " WHERE {$where}";
    }
    $result = $this->getMySQLi()->query("SELECT COUNT({$what}) FROM `{$from}`{$where} LIMIT 1");
    $count  = $result->fetch_row()[0];
    $result->free();
    return $count;
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
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->singularKey), "You must set the \$singularKey property in your class " . static::class);
    assert(!empty($this->pluralKey), "You must set the \$pluralKey property in your class " . static::class);
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->routeKey   || ($this->routeKey   = "/{$this->singularKey}/{0}");
    $this->routeArgs  || ($this->routeArgs  = $this->id);
    $this->route      || ($this->route      = $this->intl->r($this->routeKey, $this->routeArgs));
    $this->routeIndex || ($this->routeIndex = $this->intl->rp("/{$this->pluralKey}"));
    $this->tableName  || ($this->tableName  = $this->pluralKey);
    $this->changed    = new DateTime($this->changed);
    $this->created    = new DateTime($this->created);
    $this->deleted    = (boolean) $this->deleted;
    return $this;
  }

  /**
   * Whether this entity is gone or not.
   *
   * @return boolean
   *   <code>TRUE</code> if the entity is gone, <code>FALSE</code> otherwise.
   */
  public function isGone() {
    return $this->deleted;
  }

  /**
   * Translate and format singular entity sub-route.
   *
   * @param string $route
   *   The route key of the subpage.
   * @param array $args [optional]
   *   Additional route arguments, defaults to <code>NULL</code>.
   * @return string
   *   The translated and formatted singular route.
   * @throws \IntlException
   */
  public function r($route, $args = null) {
    return "{$this->routeKey}{$this->intl->r($route, $args)}";
  }

}
