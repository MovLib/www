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

class ConcreteFormElement extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {

  public $attributes = [];

  public $help;

  public $label;

  public function __construct($id, $label, array $attributes = [], $help = null, $helpPopup = true) {
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
  }

  public function __toString() {
    return "";
  }

  public function validate() {
    return $this;
  }

}

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\AbstractFormElement
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractFormElementTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $formElement = new ConcreteFormElement("phpunit", "PHPUnit");
    $this->assertArrayHasKey("id", $formElement->attributes);
    $this->assertArrayHasKey("name", $formElement->attributes);
    $this->assertArrayHasKey("tabindex", $formElement->attributes);
    $this->assertEquals("PHPUnit", $formElement->label);
    $this->assertEquals("phpunit", $formElement->attributes["id"]);
    $this->assertEquals("phpunit", $formElement->attributes["name"]);
    $this->assertEquals("phpunit", $formElement->id);
    $this->assertTrue(is_int($formElement->attributes["tabindex"]));
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructAdditionalAttributes() {
    $formElement = new ConcreteFormElement("phpunit", "PHPUnit", [ "foo" => "bar" ]);
    $this->assertArrayHasKey("foo", $formElement->attributes);
    $this->assertEquals("bar", $formElement->attributes["foo"]);
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructHelp() {
    $formElement = new ConcreteFormElement("phpunit", "PHPUnit", [], "Hello World!");
    $this->assertArrayHasKey("aria-describedby", "phpunit-help");
    $this->assertEquals("Hello World!", get_reflection_property($formElement->help, "content")->getValue($formElement->help));
    $this->assertContains("phpunit", get_reflection_property($formElement->help, "id")->getValue($formElement->help));
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Help", $formElement->help);
    $this->assertTrue(get_reflection_property($formElement->help, "popup"));
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructHelpPopup() {
    $formElement = new ConcreteFormElement("phpunit", "PHPUnit", [], "Hello World!", false);
    $this->assertFalse(get_reflection_property($formElement->help, "popup")->getValue($formElement->help));
  }

  /**
   * @covers ::invalid
   * @group Presentation
   */
  public function testInvalid() {
    $formElement = new ConcreteFormElement("phpunit", "PHPUnit");
    $formElement->invalid();
    $this->assertArrayHasKey("aria-invalid", $formElement->attributes);
    $this->assertArrayHasKey("class", $formElement->attributes);
    $this->assertContains("invalid", $formElement->attributes["class"]);
    $this->assertEquals("true", $formElement->attributes["aria-invalid"]);
  }

  /**
   * @covers ::required
   * @group Presentation
   */
  public function testRequired() {
    $formElement = new ConcreteFormElement("phpunit", "PHPUnit");
    $formElement->required();
    $this->assertArrayHasKey("aria-required", $formElement->attributes);
    foreach ($formElement->attributes as $k => $v) {
      if (($found = $v == "required") === true) {
        break;
      }
    }
    $this->assertTrue($found);
  }

}
