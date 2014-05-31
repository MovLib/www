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
namespace MovLib\Component;

/**
 * Defines the array object trait that can be used within classes that extend {@see \ArrayObject} to use the internal
 * array. The benefit from extending this native class is the fact that your object can be casted to an array.
 *
 * <b>NOTE</b><br>
 * Our default implementation doesn't allow unsetting of values in the array and throws a {@see \BadMethodCallException}.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait ArrayObjectTrait {

  /**
   * {@inheritdoc}
   * @internal
   *   Note that we don't need a function that returns a reference because we're only dealing with objects. An implicit
   *   change of the object always works, in contrast to implicit changes to an e.g. array.
   */
  final public function offsetGet($index) {
    return parent::offsetGet($index);
  }

  /**
   * {@inheritdoc}
   */
  final public function offsetSet($index, $newval) {
    return parent::offsetSet($index, $newval);
  }

  /**
   * {@inheritdoc}
   */
  final public function offsetExists($index) {
    return parent::offsetExists($index);
  }

  /**
   * {@inheritdoc}
   */
  final public function offsetUnset($index) {
    throw new \BadMethodCallException("You're not allowed to remove elements from an array object.");
  }

}
