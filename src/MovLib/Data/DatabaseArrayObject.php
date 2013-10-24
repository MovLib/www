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
 * Extends the default database class with properties and methods for the PHP interfaces <code>ArrayAccess</code>,
 * <code>Countable</code> and <code>Iterator</code>. This allows working with this class like it would be an array.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DatabaseArrayObject extends \MovLib\Data\Database implements \ArrayAccess, \Countable, \Iterator {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The basic query for most methods.
   *
   * @var string
   */
  protected $query;

  /**
   * The internal array containing all objects.
   *
   * @var array
   */
  protected $objectsArray;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function count() {
    return count($this->objectsArray);
  }

  /**
   * @inheritdoc
   */
  public function current() {
    return current($this->objectsArray);
  }

  /**
   * @inheritdoc
   */
  public function key() {
    return key($this->objectsArray);
  }

  /**
   * @inheritdoc
   */
  public function next() {
    return next($this->objectsArray);
  }

  /**
   * @inheritdoc
   */
  public function rewind() {
    return reset($this->objectsArray);
  }

  /**
   * @inheritdoc
   */
  public function valid() {
    return isset($this->objectsArray[key($this->objectsArray)]);
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return isset($this->objectsArray[$offset]);
  }

  /**
   * @inheritdoc
   */
  public function &offsetGet($offset) {
    return $this->objectsArray[$offset];
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $this->objectsArray[$offset] = $value;
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    unset($this->objectsArray[$offset]);
  }

}
