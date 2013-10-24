<?php

/* !
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
namespace MovLib\Presentation\Partial\FormElement;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\AbstractInput
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractInputTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Test Fixtures


  public function tearDown() {
    unset($_POST);
  }


  // ------------------------------------------------------------------------------------------------------------------- Test Methods


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $stub = $this->getMockForAbstractClass("\\MovLib\\Presentation\\Partial\\FormElement\\AbstractInput", [ "phpunit", "PHPUnit", [ "value" => "phpunit" ] ]);
    $this->assertEquals("phpunit", $stub->value);
    return $stub;
  }

  /**
   * @covers ::__construct
   */
  public function testConstructValueViaPOST() {
    $_POST["phpunit"] = "phpunit";
    $stub             = $this->getMockForAbstractClass("\\MovLib\\Presentation\\Partial\\FormElement\\AbstractInput", [ "phpunit", "PHPUnit" ]);
    $this->assertEquals("phpunit", $stub->value);
    $this->assertEquals("phpunit", $stub->attributes["value"]);
    return $stub;
  }

  /**
   * @covers ::__construct
   */
  public function testConstructEmptyValueViaPOST() {
    $_POST["phpunit"] = "";
    $stub             = $this->getMockForAbstractClass("\\MovLib\\Presentation\\Partial\\FormElement\\AbstractInput", [ "phpunit", "PHPUnit" ]);
    $this->assertNull($stub->value);
    $this->assertNull($stub->attributes["value"]);
    return $stub;
  }

  /**
   * @covers ::__toString
   * @depends testConstruct
   */
  public function testToString($stub) {
    $this->assertContains("value='phpunit'", $stub->__toString());
  }

  /**
   * @covers ::__toString
   * @depends testConstructEmptyValueViaPOST
   */
  public function testToStringEmptyValue($stub) {
    $this->assertNotContains("value='phpunit'", $stub->__toString());
  }

}
