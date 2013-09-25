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
namespace MovLib\Test\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\RadioGroup;
use \MovLib\Presentation\Partial\Help;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RadioGroupTest extends \PHPUnit_Framework_TestCase {

  /** @var \MovLib\Presentation\Partial\FormElement\RadioGroup */
  public $radioGroup;

  public function setUp() {
    $this->radioGroup = new RadioGroup("phpunit", "PHPUnit", "phpunit1", [ "phpunit1" => "PHPUnit 1" ]);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::__construct
   */
  public function testConstruct() {
    $this->assertArrayHasKey("aria-expanded", $this->radioGroup->attributes);
    $this->assertArrayHasKey("aria-required", $this->radioGroup->attributes);
    $this->assertArrayHasKey("id", $this->radioGroup->attributes);
    $this->assertArrayHasKey("role", $this->radioGroup->attributes);
    $this->assertEquals("PHPUnit", $this->radioGroup->legend);
    $this->assertEquals("false", $this->radioGroup->attributes["aria-required"]);
    $this->assertEquals("phpunit", $this->radioGroup->attributes["id"]);
    $this->assertEquals("phpunit", $this->radioGroup->id);
    $this->assertEquals("phpunit1", $this->radioGroup->value);
    $this->assertEquals("radiogroup", $this->radioGroup->attributes["role"]);
    $this->assertEquals("true", $this->radioGroup->attributes["aria-expanded"]);

    $choices = get_reflection_property($this->radioGroup, "choices")->getValue($this->radioGroup);
    $this->assertArrayHasKey("phpunit1", $choices);
    $this->assertArrayHasKey("attributes", $choices["phpunit1"]);
    $this->assertArrayHasKey("label", $choices["phpunit1"]);
    $this->assertEquals("PHPUnit 1", $choices["phpunit1"]["label"]);
    foreach ([
      0       => "required",
      1       => "checked",
      "id"    => "phpunit-phpunit1",
      "name"  => "phpunit",
      "type"  => "radio",
      "value" => "phpunit1",
    ] as $k => $v) {
      $this->assertArrayHasKey($k, $choices["phpunit1"]["attributes"]);
      $this->assertEquals($v, $choices["phpunit1"]["attributes"][$k]);
    }
    $this->assertArrayHasKey("tabindex", $choices["phpunit1"]["attributes"]);
    $this->assertTrue(is_int($choices["phpunit1"]["attributes"]["tabindex"]));
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::__construct
   */
  public function testConstructInvalidPostInput() {
    $_POST["phpunit"] = "phpunit42";
    $radioGroup = new RadioGroup("phpunit", "PHPUnit", "phpunit2", [ "phpunit1" => "PHPUnit 1", "phpunit2" => "PHPUnit 2" ]);
    $this->assertEquals("phpunit2", $radioGroup->value);
    return $radioGroup;
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::__construct
   */
  public function testConstructValidPostInput() {
    $_POST["phpunit"] = "phpunit1";
    $radioGroup = new RadioGroup("phpunit", "PHPUnit", "phpunit2", [ "phpunit1" => "PHPUnit 1", "phpunit2" => "PHPUnit 2" ]);
    $this->assertEquals("phpunit1", $radioGroup->value);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::__toString
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
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::disable
   */
  public function testDisable() {
    $this->radioGroup->disable();
    $this->assertArrayHasKey("aria-disabled", $this->radioGroup->attributes);
    $this->assertEquals("true", $this->radioGroup->attributes["aria-disabled"]);
    $this->assertTrue($this->radioGroup->disabled);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::invalid
   */
  public function testInvalid() {
    $this->radioGroup->invalid();
    $this->assertArrayHasKey("aria-invalid", $this->radioGroup->attributes);
    $this->assertEquals("true", $this->radioGroup->attributes["aria-invalid"]);
    $this->assertArrayHasKey("class", $this->radioGroup->attributes);
    $this->assertContains("invalid", $this->radioGroup->attributes["class"]);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::setHelp
   */
  public function testSetHelp() {
    $this->radioGroup->setHelp("PHPUnit");
    $this->assertArrayHasKey("aria-describedby", $this->radioGroup->attributes);
    $this->assertEquals("phpunit-help", $this->radioGroup->attributes["aria-describedby"]);
    $choices = get_reflection_property($this->radioGroup, "choices")->getValue($this->radioGroup);
    foreach ($choices as $value => $choice) {
      $this->assertArrayHasKey("aria-describedby", $choice["attributes"]);
      $this->assertEquals("phpunit-help", $choice["attributes"]["aria-describedby"]);
    }
    $this->assertTrue(get_reflection_property($this->radioGroup, "help")->getValue($this->radioGroup) instanceof Help);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::validate
   */
  public function testValidate() {
    $_POST["phpunit"] = "phpunit1";
    $this->radioGroup->validate();
    $this->assertEquals("phpunit1", $this->radioGroup->value);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\RadioGroup::validate
   * @depends testConstructInvalidPostInput
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateInvalidInput(RadioGroup $radioGroup) {
    $_POST["phpunit"] = "phpunit42";
    $radioGroup->validate();
  }

}
