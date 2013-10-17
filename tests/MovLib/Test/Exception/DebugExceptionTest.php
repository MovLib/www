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
namespace MovLib\Test\Exception;

use \MovLib\Exception\DebugException;

/**
 * @coversDefaultClass \MovLib\Exception\DebugException
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class DebugExceptionTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  public $property = "phpunit";


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  public function callbackString($string) {
    return $string;
  }

  public function callbackArray(array $array) {
    return $array[0];
  }

  public function callbackObject(DebugExceptionTest $e) {
    return $e->property;
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @covers ::__toString
    */
  public function testVarDump() {
    $object = new \stdClass();
    $debugException = new DebugException($object);
    $this->assertInstanceOf("\\RuntimeException", $debugException);
    ob_start();
    var_dump($object);
    $dump = ob_get_clean();
    $this->assertEquals(htmlspecialchars($dump, ENT_QUOTES|ENT_HTML5), $debugException->__toString());
  }

  /**
   * @covers ::__construct
   * @covers ::__toString
    */
  public function testPrintR() {
    $object = new \stdClass();
    $debugException = new DebugException($object, "print_r");
    $this->assertEquals(htmlspecialchars(print_r($object, true), ENT_QUOTES|ENT_HTML5), $debugException->__toString());
  }

  /**
   * @covers ::__construct
   * @covers ::__toString
    */
  public function testCallback() {
    $debugException = new DebugException("phpunit", [ $this, "callbackString" ]);
    $this->assertEquals("phpunit", $debugException->__toString());
  }

}
