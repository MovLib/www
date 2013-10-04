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

use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Validation\EmailAddress;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputEmail
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmailTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @covers \MovLib\Presentation\Partial\FormElement\AbstractInput::__construct
   * @covers \MovLib\Presentation\Partial\FormElement\AbstractFormElement::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $input = new InputEmail("phpunit", "PHPUnit");
    $attributes = get_reflection_property($input, "attributes")->getValue($input);
    foreach ([
      "aria-required" => "true",
      "id"            => "phpunit",
      "maxlength"     => 254,
      "name"          => "phpunit",
      "pattern"       => InputEmail::PATTERN,
      "tabindex"      => null,
      "type"          => "email",
      0               => "required",
    ] as $k => $v) {
      $this->assertArrayHasKey($k, $attributes);
      if ($v) {
        $this->assertEquals($v, $attributes[$k]);
      }
    }
    $this->assertTrue(is_int($attributes["tabindex"]));
  }

  /**
   * @covers ::__toString
   * @covers \MovLib\Presentation\Partial\FormElement\AbstractInput::__toString
   * @group Presentation
   */
  public function testToString() {
    $input = (new InputEmail("phpunit", "PHPUnit"))->__toString();
    $this->assertContains(" aria-required='true'", $input);
    $this->assertContains(" id='phpunit'", $input);
    $this->assertContains(" maxlength='254'", $input);
    $this->assertContains(" name='phpunit'", $input);
    $this->assertContains(" pattern='" . htmlspecialchars(EmailAddress::PATTERN, ENT_QUOTES|ENT_HTML5) . "'", $input);
    $this->assertContains(" required", $input);
    $this->assertContains(" tabindex='", $input);
    $this->assertContains(" type='email'", $input);
    $this->assertContains("<label for='phpunit'>PHPUnit</label>", $input);
  }

}
