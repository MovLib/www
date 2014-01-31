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

use \MovLib\Presentation\Partial\FormElement\InputCheckbox;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputCheckbox
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputCheckboxTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderDefaultAttributes() {
    return [
      [ "id", "phpunit" ],
      [ "name", "phpunit" ],
      [ "type", "checkbox" ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @dataProvider dataProviderDefaultAttributes
   */
  public function testDefaultAttributes($key, $value) {
    $checkbox = new InputCheckbox("phpunit", "PHPUnit");
    $this->assertArrayHasKey($key, $checkbox->attributes);
    $this->assertEquals($value, $checkbox->attributes[$key]);
  }

  /**
   * @covers ::__construct
   */
  public function testCheckedAttribute() {
    $checkbox = new InputCheckbox("phpunit", "PHPUnit", [ "checked" => true ]);
    $this->assertTrue($checkbox->checked);
  }

  /**
   * @covers ::__construct
   */
  public function testCheckedAttributePostOverride() {
    $_POST["phpunit"] = "true";
    $checkbox = new InputCheckbox("phpunit", "PHPUnit", [ "checked" => false ]);
    $this->assertTrue($checkbox->checked);
  }

  /**
   * @covers ::render
   * @internal PHPUnit doesn't support checking of empty attributes!
   */
  public function testRender() {
    $checkbox = new InputCheckbox("phpunit", "PHPUnit");
    $this->assertTag([
      "tag"        => "input",
      "id"         => "phpunit",
      "attributes" => [ "name" => "phpunit", "type" => "checkbox", "value" => "phpunit" ],
      "ancestor"   => [ "tag" => "p" ],
    ], (string) $checkbox);
  }

  /**
   * @covers ::validate
   */
  public function testValidate() {
    $_POST["phpunit"] = "true";
    $checkbox = new InputCheckbox("phpunit", "PHPUnit", [ "required" => true ]);
    $this->assertChaining($checkbox, $checkbox->validate());
    $this->assertTrue($checkbox->checked);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateException() {
    $checkbox = new InputCheckbox("phpunit", "PHPUnit", [ "required" => true ]);
    $checkbox->validate();
  }

}
