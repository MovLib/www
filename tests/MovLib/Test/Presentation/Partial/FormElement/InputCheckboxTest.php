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

use \MovLib\Presentation\Partial\FormElement\InputCheckbox;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputCheckbox
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputCheckboxTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $inputCheckbox = new InputCheckbox("phpunit", "PHPUnit");
    $this->assertArrayHasKey("type", $inputCheckbox->attributes);
    $this->assertEquals("checkbox", $inputCheckbox->attributes["type"]);
    $this->assertFalse($inputCheckbox->value);
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructCheckedViaPOST() {
    $_POST["phpunit"] = "on";
    $this->assertTrue((new InputCheckbox("phpunit", "PHPUnit"))->value);
  }

  /**
   * @covers ::__toString
   * @group Presentation
   */
  public function testToString() {
    $this->assertContains("checked", (string) new InputCheckbox("phpunit", "PHPUnit", null, true));
  }

  /**
   * @covers ::validate
   * @group Validation
   */
  public function testValidate() {
    $_POST["phpunit"] = "on";
    (new InputCheckbox("phpunit", "PHPUnit"))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Partial\FormElement\InputCheckbox::E_MANDATORY
   * @expectedExceptionMessage mandatory
   * @group Validation
   */
  public function testValidateRequired() {
    (new InputCheckbox("phpunit", "PHPUnit", [ "required" ]))->validate();
  }

}
