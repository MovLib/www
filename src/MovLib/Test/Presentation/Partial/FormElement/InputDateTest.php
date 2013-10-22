<?php

/* !
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
use \DateTimeImmutable;
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
class InputDateTest extends \MovLib\Test\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $date      = date("Y-m-d", $_SERVER["REQUEST_TIME"]);
    $timestamp = DateTime::createFromFormat("!Y-m-d", $date)->getTimestamp();
    $inputDate = new InputDate("phpunit", "PHPUnit", [ "max" => $timestamp, "min" => $timestamp, "value" => $date ]);
    $this->assertEquals("Y-m-d", $inputDate->attributes["data-format"]);
    $this->assertEquals("date", $inputDate->attributes["type"]);
    $this->assertEquals($timestamp, $inputDate->timestamp);
    $this->assertEquals($timestamp, $this->getProperty($inputDate, "max"));
    $this->assertEquals($timestamp, $this->getProperty($inputDate, "min"));
    $this->assertEquals($date, $inputDate->attributes["max"]);
    $this->assertEquals($date, $inputDate->attributes["min"]);
  }

  /**
   * @covers ::validate
   */
  public function testNormalization() {
    global $session;
    $date      = date("Y-m-d", $_SERVER["REQUEST_TIME"]);
    $dateTime  = DateTimeImmutable::createFromFormat("!Y-m-d", $date);
    $timestamp = $dateTime->getTimestamp();
    $inputDate = new InputDate("phpunit", "PHPUnit", [
      "value" => $dateTime->setTimezone(new DateTimeZone($session->userTimeZoneId))->format("Y-m-d"),
    ]);
    $inputDate->normalizeDate($session->userTimeZoneId);
    $this->assertEquals($date, $inputDate->value);
    $this->assertEquals($timestamp, $inputDate->timestamp);
  }

  /**
   * @covers ::validate
   */
  public function testEmpty() {
    $inputDate = new InputDate("phpunit", "PHPUnit");
    $this->assertEquals($inputDate, $inputDate->validate());
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage mandatory
   */
  public function testMandatory() {
    (new InputDate("phpunit", "PHPUnit", [ "required" ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage invalid
   */
  public function testInvalidFormat() {
    (new InputDate("phpunit", "PHPUnit", [ "value" => date("d-M-y", $_SERVER["REQUEST_TIME"]) ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage invalid
   */
  public function testInvalidDate() {
    (new InputDate("phpunit", "PHPUnit", [ "value" => "2013-02-30" ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage greater than
   */
  public function testInvalidMax() {
    (new InputDate("phpunit", "PHPUnit", [ "max" => time(), "value" => date("Y-m-d", strtotime("+1 year")) ]))->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage less than
   */
  public function testInvalidMin() {
    (new InputDate("phpunit", "PHPUnit", [ "min" => time(), "value" => date("Y-m-d", strtotime("-1 year")) ]))->validate();
  }

  /**
   * @covers ::validate
   */
  public function testValid() {
    $inputDate = new InputDate("phpunit", "PHPUnit", [
      "min"   => strtotime("-1 year"),
      "max"   => strtotime("+1 year"),
      "value" => date("Y-m-d", $_SERVER["REQUEST_TIME"]),
    ]);
    $this->assertEquals($inputDate, $inputDate->validate());
  }

}
