<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovDev;

/**
 * Trait containing special methods to ease common tasks in the dev environment.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitUtilities {

  /**
   * Used to cache instantiation of reflection methods.
   *
   * @var array
   */
  private $reflectionMethods;

  /**
   * Used to cache instantiation of reflection properties.
   *
   * @var array
   */
  private $reflectionProperties;

  /**
   * Call a protected method of <var>$object</var>.
   *
   * @param mixed $object
   *   A valid class instance.
   * @param string $name
   *   Case-sensitive full name of the protected method to call.
   * @param array $args [optional]
   *   Arguments are passed along to the method, defaults to no arguments.
   * @return mixed
   *   The return value of the method.
   * @throws \InvalidArgumentException
   */
  public final function invoke($object, $name, array $args = null) {
    if (!is_object($object) || !is_string($name)) {
      throw new \InvalidArgumentException;
    }
    $key = get_class($object) . $name;
    if (!isset($this->reflectionMethods[$key])) {
      $this->reflectionMethods[$key] = new \ReflectionMethod($object, $name);
      $this->reflectionMethods[$key]->setAccessible(true);
    }
    if ($args) {
      return $this->reflectionMethods[$key]->invokeArgs($object, $args);
    }
    return $this->reflectionMethods[$key]->invoke($object);
  }

  /**
   * Get the value of a protected property of <var>$object</var>.
   *
   * @param mixed $object
   *   A valid class instance.
   * @param string $name
   *   Case-sensitive full name of the protected property to get or set.
   * @param mixed $value [optional]
   *   If a value is passed this method acts as setter, otherwise as getter. Please note that the default value is set
   *   to <code>PHP_INT_MAX</code> because it's very unlikely that somebody wants to pass that along (unlike
   *   <code>NULL</code>).
   * @return mixed
   *   If acting as getter the value in the property, otherwise <var>$this</var> is returned.
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  public final function getProperty($object, $name) {
    return $this->property($object, $name)->getValue($object);
  }

  /**
   * Set the value of a protected property of <var>$object</var>.
   *
   * @param string|object $object
   *   Valid class instance or full class name.
   * @param string $name
   *   The name of the property
   * @param mixed $value
   *   The value to set.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  public final function setProperty($object, $name, $value) {
    $this->property($object, $name)->setValue($object, $value);
    return $this;
  }

  /**
   * Helper method to cache properties and set them accessible.
   *
   * @param string|object $object
   *   Valid class instance or full class name.
   * @param string $name
   *   The name of the property
   * @return \ReflectionProperty
   *   The property <var>$name</var> from <var>$object</var>.
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  private function property($object, $name) {
    if ((!is_object($object) && !is_string($object)) || !is_string($name)) {
      throw new \InvalidArgumentException;
    }
    $key = is_object($object) ? get_class($object) . $name : "{$object}{$name}";
    if (!isset($this->reflectionProperties[$key])) {
      $this->reflectionProperties[$key] = new \ReflectionProperty($object, $name);
      $this->reflectionProperties[$key]->setAccessible(true);
    }
    return $this->reflectionProperties[$key];
  }

}
