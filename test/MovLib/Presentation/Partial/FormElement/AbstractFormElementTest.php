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
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\AbstractFormElement
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractFormElementTest extends \MovLib\TestCase {

  /**
   * Get stub for {@see \MovLib\Presentation\Partial\FormElement\AbstractFormElement}.
   *
   * @param array $params [optional]
   *   Parameters that should be passed to the constructor, defaults to empty array. Note that the mandatory parameters
   *   id and label are automatically added to the params.
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   Stub for {@see \MovLib\Presentation\Partial\FormElement\AbstractFormElement}.
   */
  protected function getStub(array $params = []) {
    array_unshift($params, "phpunit", "PHPUnit");
    return $this->getMockForAbstractClass("\\MovLib\\Presentation\\Partial\\FormElement\\AbstractFormElement", $params);
  }

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $stub = $this->getStub();
    $this->assertEquals("phpunit", $this->getProperty($stub, "id"));
    $this->assertEquals("PHPUnit", $this->getProperty($stub, "label"));
    $this->assertEquals("phpunit", $stub->attributes["id"]);
  }

  /**
   * @covers ::__construct
   */
  public function testAttributesExport() {
    $attr = [ "foo" => "bar", "bar" => "foo" ];
    $stub = $this->getStub([ $attr ]);
    foreach ($attr as $k => $v) {
      $this->assertArrayHasKey($k, $stub->attributes);
      $this->assertEquals($v, $stub->attributes[$k]);
    }
  }

  /**
   * @covers ::invalid
   */
  public function testInvalid() {
    $stub = $this->getStub();
    $this->assertChaining($stub->invalid(), $stub);
    $this->assertArrayHasKey("aria-invalid", $stub->attributes);
    $this->assertEquals("true", $stub->attributes["aria-invalid"]);
    $this->assertArrayHasKey("class", $stub->attributes);
    $this->assertContains("invalid", $stub->attributes["class"]);
  }

  /**
   * @covers ::setHelp
   */
  public function testSetHelp() {
    $stub = $this->getStub();
    $this->assertChaining($stub->setHelp("help message"), $stub);
    $this->assertArrayHasKey("aria-describedby", $stub->attributes);
    $this->assertEquals("phpunit-help", $stub->attributes["aria-describedby"]);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Help", $this->getProperty($stub, "help"));
  }

  /**
   * @covers ::filterInput
   */
  public function testFilterInput() {
    $_POST["foo"] = "bar";
    $stub = $this->getStub();
    $this->assertEquals("bar", $this->invoke($stub, "filterInput", [ "foo" ]));
  }

  /**
   * @covers ::filterInput
   */
  public function testFilterInputNull() {
    $stub = $this->getStub();
    $this->assertNull($this->invoke($stub, "filterInput", [ "foo" ]));
  }

}
