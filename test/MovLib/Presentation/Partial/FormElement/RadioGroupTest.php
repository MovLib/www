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

use \MovLib\Presentation\Partial\FormElement\RadioGroup;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\RadioGroup
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RadioGroupTest extends \MovLib\TestCase {

  /** @var \MovLib\Presentation\Partial\FormElement\RadioGroup */
  public $radioGroup;

  
  // ------------------------------------------------------------------------------------------------------------------- Test Fixtures


  public function setUp() {
    $this->radioGroup = new RadioGroup("phpunit", "PHPUnit", [ "phpunit1" => "PHPUnit 1" ], "phpunit1");
  }

  public function tearDown() {
    unset($_POST);
  }


  // ------------------------------------------------------------------------------------------------------------------- Test Methods


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertArrayHasKey("aria-expanded", $this->radioGroup->attributes);
    $this->assertArrayHasKey("aria-required", $this->radioGroup->attributes);
    $this->assertArrayHasKey("id", $this->radioGroup->attributes);
    $this->assertArrayHasKey("role", $this->radioGroup->attributes);
    $this->assertEquals("PHPUnit", $this->getProperty($this->radioGroup, "label"));
    $this->assertEquals("false", $this->radioGroup->attributes["aria-required"]);
    $this->assertEquals("phpunit", $this->radioGroup->attributes["id"]);
    $this->assertEquals("phpunit", $this->radioGroup->id);
    $this->assertEquals("phpunit1", $this->radioGroup->value);
    $this->assertEquals("radiogroup", $this->radioGroup->attributes["role"]);
    $this->assertEquals("true", $this->radioGroup->attributes["aria-expanded"]);

    $choices = $this->getProperty($this->radioGroup, "choices");
    $this->assertArrayHasKey("phpunit1", $choices);
    $this->assertEquals("PHPUnit 1", $choices["phpunit1"]);
  }

  /**
   * @covers ::__construct
   */
  public function testConstructValidPostInput() {
    $_POST["phpunit"] = "phpunit1";
    $radioGroup       = new RadioGroup("phpunit", "PHPUnit", [ "phpunit1" => "PHPUnit 1", "phpunit2" => "PHPUnit 2" ], "phpunit2");
    $this->assertEquals("phpunit1", $radioGroup->value);
  }

  /**
   * @covers ::__toString
   */
  public function testToString() {
    $radioGroup = $this->radioGroup->__toString();
    $this->assertContains("<fieldset", $radioGroup);
    $this->assertContains("<legend", $radioGroup);
    $this->assertContains("<label class='radio inline'><input", $radioGroup);
    $this->assertContains("type='radio'", $radioGroup);
    $this->assertContains("</label>", $radioGroup);
    $this->assertContains("</legend>", $radioGroup);
    $this->assertContains("</fieldset>", $radioGroup);
  }

  /**
   * @covers ::invalid
   */
  public function testInvalid() {
    $this->radioGroup->invalid();
    $this->assertArrayHasKey("aria-invalid", $this->radioGroup->attributes);
    $this->assertEquals("true", $this->radioGroup->attributes["aria-invalid"]);
    $this->assertArrayHasKey("class", $this->radioGroup->attributes);
    $this->assertContains("invalid", $this->radioGroup->attributes["class"]);
  }

  /**
   * @covers ::setHelp
   */
  public function testSetHelp() {
    $this->radioGroup->setHelp("PHPUnit");
    $this->assertArrayHasKey("aria-describedby", $this->radioGroup->attributes);
    $this->assertEquals("phpunit-help", $this->radioGroup->attributes["aria-describedby"]);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Help", $this->getProperty($this->radioGroup, "help"));
  }

  /**
   * @covers ::validate
   */
  public function testValidate() {
    $_POST["phpunit"] = "phpunit1";
    $this->radioGroup->validate();
    $this->assertEquals("phpunit1", $this->radioGroup->value);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateInvalidInput() {
    $_POST["phpunit"] = "phpunit42";
    $radioGroup       = new RadioGroup("phpunit", "PHPUnit", [ "phpunit1" => "PHPUnit 1", "phpunit2" => "PHPUnit 2" ], "phpunit2");
    $radioGroup->validate();
  }

}
