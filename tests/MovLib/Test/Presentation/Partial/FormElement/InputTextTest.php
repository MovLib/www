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

use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputTextTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\AbstractFormElement::__construct
   * @covers \MovLib\Presentation\Partial\FormElement\InputText::__construct
   */
  public function testConstruct() {
    $inputText = new InputText("phpunit", "PHPUnit");

    // AbstractFormElement tests
    $this->assertEquals("PHPUnit", $inputText->label);
    $this->assertEquals("phpunit", $inputText->attributes["id"]);
    $this->assertEquals("phpunit", $inputText->attributes["name"]);
    $this->assertEquals("phpunit", $inputText->id);
    $this->assertEquals("phpunit", $inputText->labelAttributes["for"]);
    $this->assertRegExp("/[0-9]+/", (string) $inputText->attributes["tabindex"]);

    // InputText tests
    $this->assertEquals("text", $inputText->attributes["type"]);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputText::__toString
   */
  public function testToString() {
    $inputText = (new InputText("phpunit", "PHPUnit"))->__toString();
    $this->assertContains("<label for='phpunit'>PHPUnit</label>", $inputText);
    $this->assertContains("id='phpunit'", $inputText);
    $this->assertContains("name='phpunit'", $inputText);
    $this->assertContains("type='text'", $inputText);
    $this->assertRegExpr("/tabindex='[0-9]+'/", $inputText);
  }

}
