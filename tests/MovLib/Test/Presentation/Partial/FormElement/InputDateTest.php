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

use \MovLib\Presentation\Partial\FormElement\InputDate;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputDate::__construct
   */
  public function testConstruct() {
    $max        = time();
    $min        = strtotime("-1 year");
    $inputDate  = new InputDate("phpunit", "PHPUnit", [ "max" => $max, "min" => $min ]);
    $attributes = get_reflection_property($inputDate, "attributes")->getValue($inputDate);
    $this->assertArrayHasKey("max", $attributes);
    $this->assertArrayHasKey("min", $attributes);
    $this->assertArrayHasKey("type", $attributes);
    $this->assertEquals("date", $attributes["type"]);
    $this->assertEquals($max, get_reflection_property($inputDate, "max")->getValue($inputDate));
    $this->assertEquals($min, get_reflection_property($inputDate, "min")->getValue($inputDate));
    $this->assertEquals(date("Y-m-d", $max), $attributes["max"]);
    $this->assertEquals(date("Y-m-d", $min), $attributes["min"]);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputDate::__toString
   */
  public function testToString() {
    $max       = time();
    $min       = strtotime("-1 year");
    $inputDate = (new InputDate("phpunit", "PHPUnit", [ "max" => $max, "min" => $min ]))->__toString();
    $this->assertContains("max='" . date("Y-m-d", $max) . "'", $inputDate);
    $this->assertContains("min='" . date("Y-m-d", $min) . "'", $inputDate);
    $this->assertContains("type='date'", $inputDate);
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputDate::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode 1
   */
  public function testInvalidFormat() {
    (new InputDate("phpunit", "PHPUnit", null, date("d-M-y", time())))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputDate::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode 2
   */
  public function testInvalidDate() {
    (new InputDate("phpunit", "PHPUnit", null, "2013-02-30"))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputDate::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode 3
   */
  public function testInvalidMax() {
    (new InputDate("phpunit", "PHPUnit", [ "max" => time() ], date(InputDate::RFC3339, strtotime("+1 year"))))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Partial\FormElement\InputDate::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode 4
   */
  public function testInvalidMin() {
    (new InputDate("phpunit", "PHPUnit", [ "min" => time() ], date(InputDate::RFC3339, strtotime("-1 year"))))->validate();
  }

}
