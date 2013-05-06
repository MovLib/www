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
namespace MovLib\Test\Model;

use \PHPUnit_Framework_TestCase;
use \ReflectionClass;

/**
 * Test class for testing the utility functions of the abstract base class of all models.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractModelTest extends PHPUnit_Framework_TestCase {

  /**
   * Make protected or private method accessible.
   *
   * @param string $methodName
   *   Name of the method.
   * @return \ReflectionFunction
   */
  protected static function getMethod($methodName) {
    $method = (new ReflectionClass('MovLib\Model\AbstractModel'))->getMethod($methodName);
    $method->setAccessible(true);
    return $method;
  }

  /**
   * Test if exceptions are thrown correctly for all kind of problems that might occur while retrieving a connection to
   * one of our databases.
   *
   * @test
   */
  public function testGetConnection() {
    /* @var $getConnection \ReflectionFunction */
    $getConnection = self::getMethod('getConnection');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass('MovLib\Model\AbstractModel', [ 'table' ]);
    $this->setExpectedException('MovLib\Exception\DatabaseException');
    $getConnection->invokeArgs($mock, [ 'database', 'table' ]);
  }

}
