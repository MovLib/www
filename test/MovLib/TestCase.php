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
namespace MovLib;

use \MovLib\Presentation\AbstractPresenter;
use \MovLib\Presentation\Partial\Alert;

/**
 * Extended PHPUnit Framework TestCase
 *
 * Extension of default PHPUnit Framework TestCase for MovLib providing several useful methods for easy testing of our
 * application.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


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


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Assert that the presentation contains an alert message with the given alert severity and contains the given text.
   *
   * @param \MovLib\Presentation\AbstractPresenter $page
   *   The object that should contain the alert message.
   * @param string $contains
   *   The text the alert message should contain.
   * @param string $severity [optional]
   *   The severity levelt he alert message should have, defaults to <code>Alert::SEVERITY_ERROR</code>.
   * @param string $message [optional]
   *   The error message if the alert message isn't present, has the wrong severity or doesn't contain the text.
   */
  protected function assertPresentationContainsAlert(AbstractPresenter $page, $contains, $severity = Alert::SEVERITY_ERROR, $message = "The presentation doesn't contain the desired alert message.") {
    $this->assertTag([
      "tag"        => "div",
      "content"    => $contains,
      "attributes" => [ "class" => trim($severity) ]
    ], $page->alerts, $message, true);
  }

  /**
   * Assert that the method returns itself for chaining.
   *
   * @param mixed $object
   *   The object the method should return.
   * @param mixed $actual
   *   The actual return value of the method.
   * @param string $message [optional]
   *   The error message.
   */
  protected function assertChaining($object, $actual, $message = "Method should return its own instance for chaining.") {
    $this->assertEquals($object, $actual, $message);
  }

  /**
   * Restore global objects to initial state.
   *
   * @global array $backup
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\TestKernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function assertPostConditions() {
    global $backup, $db, $kernel, $i18n, $session;
    foreach ($backup as $name => $object) {
      ${$name} = clone $object;
    }
    $kernel->delayedEmails  = null;
    $kernel->delayedMethods = null;
  }

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
   * Get the value of an inaccessible property of the given object or class.
   *
   * @param string|object $objectOrClassName
   *   A valid class instance or the full class name.
   * @param string $propertyName
   *   Case-sensitive full name of the inaccessible property to get the value from.
   * @return mixed
   *   The value of the inaccessible property.
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  public final function getProperty($objectOrClassName, $propertyName) {
    return $this->property($objectOrClassName, $propertyName)->getValue($objectOrClassName);
  }

  /**
   * Get the value of an inaccessible static property of given object or class.
   *
   * @param string|object $objectOrClassName
   *   A valid class instance or the full class name.
   * @param string $staticPropertyName
   *   Case-sensitive full name of the inaccessible static property to get the value from.
   * @return mixed
   *   The value of the inaccessible static property.
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  public final function getStaticProperty($objectOrClassName, $staticPropertyName) {
    return $this->property($objectOrClassName, $staticPropertyName)->getValue();
  }

  /**
   * Set the value of an inaccessible property of the given object or class.
   *
   * @param string|object $objectOrClassName
   *   A valid class instance or the full class name.
   * @param string $propertyName
   *   Case-sensitive full name of the inaccessible property to get the value from.
   * @param mixed $value
   *   The value to set.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  public final function setProperty($objectOrClassName, $propertyName, $value) {
    $this->property($objectOrClassName, $propertyName)->setValue($objectOrClassName, $value);
    return $this;
  }

  /**
   * Set the value of an inaccessible static property of given object or class.
   *
   * @param string|object $objectOrClassName
   *   A valid class instance or the full class name.
   * @param string $staticPropertyName
   *   Case-sensitive full name of the inaccessible static property to get the value from.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  public final function setStaticProperty($objectOrClassName, $staticPropertyName, $value) {
    $this->property($objectOrClassName, $staticPropertyName)->setValue($value);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods


  /**
   * Helper method to cache properties and set them accessible.
   *
   * @param string|object $objectOrClassName
   *   A valid class instance or the full class name.
   * @param string $propertyName
   *   Case-sensitive full name of the inaccessible static property to get the value from.
   * @return \ReflectionProperty
   *   The property <var>$name</var> from <var>$object</var>.
   * @throws \InvalidArgumentException
   * @throws \ReflectionException
   */
  private function property($objectOrClassName, $propertyName) {
    if ((!is_object($objectOrClassName) && !is_string($objectOrClassName)) || !is_string($propertyName)) {
      throw new \InvalidArgumentException;
    }
    $key = is_object($objectOrClassName) ? get_class($objectOrClassName) . $propertyName : "{$objectOrClassName}{$propertyName}";
    if (!isset($this->reflectionProperties[$key])) {
      $this->reflectionProperties[$key] = new \ReflectionProperty($objectOrClassName, $propertyName);
      $this->reflectionProperties[$key]->setAccessible(true);
    }
    return $this->reflectionProperties[$key];
  }

}
