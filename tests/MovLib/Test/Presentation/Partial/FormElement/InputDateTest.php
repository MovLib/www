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

use \DateTime;
use \DateTimeZone;
use \MovLib\Presentation\Partial\FormElement\InputDate;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputDate
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $timeZoneId = ini_get("date.timezone");
    $timestamp  = DateTime::createFromFormat("!Y-m-d", "2013-10-11", new DateTimeZone($timeZoneId))->getTimestamp();
    $inputDate  = new InputDate("phpunit", "PHPUnit", [ "max" => $timestamp, "min" => $timestamp, "value" => "2013-10-11" ], $timeZoneId);
    $this->assertEquals("Y-m-d", $inputDate->attributes["data-format"]);
    $this->assertEquals("date", $inputDate->attributes["type"]);
    $this->assertEquals($timeZoneId, $inputDate->timeZoneId);
    $this->assertEquals($timestamp, $inputDate->timestamp);
    $this->assertEquals($timestamp, get_reflection_property($inputDate, "max")->getValue($inputDate));
    $this->assertEquals($timestamp, get_reflection_property($inputDate, "min")->getValue($inputDate));
    $this->assertEquals("2013-10-11", $inputDate->attributes["max"]);
    $this->assertEquals("2013-10-11", $inputDate->attributes["min"]);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Partial\FormElement\InputDate::E_MANDATORY
   * @expectedExceptionMessage mandatory
   * @group Validation
   */
  public function testMandatory() {
    (new InputDate("phpunit", "PHPUnit", [ "required" ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage invalid format
   * @group Validation
   */
  public function testInvalidFormat() {
    (new InputDate("phpunit", "PHPUnit", [ "value" => date("d-M-y", $_SERVER["REQUEST_TIME"]) ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage is invalid
   * @group Validation
   */
  public function testInvalidDate() {
    (new InputDate("phpunit", "PHPUnit", [ "value" => "2013-02-30" ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage greater than
   * @group Validation
   */
  public function testInvalidMax() {
    (new InputDate("phpunit", "PHPUnit", [ "max" => time(), "value" => date("Y-m-d", strtotime("+1 year")) ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage less than
   * @group Validation
   */
  public function testInvalidMin() {
    (new InputDate("phpunit", "PHPUnit", [ "min" => time(), "value" => date("Y-m-d", strtotime("-1 year")) ]))->validate();
  }

  /**
   * @covers ::validate
   * @group Validation
   */
  public function testValid() {
    $inputDate = new InputDate("phpunit", "PHPUnit", [ "value" => date("Y-m-d", $_SERVER["REQUEST_TIME"]) ]);
    $this->assertEquals($inputDate, $inputDate->validate());
  }

}
