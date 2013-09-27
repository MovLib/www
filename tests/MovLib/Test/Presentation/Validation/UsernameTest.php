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
namespace MovLib\Test\Presentation\Validation;

use \MovLib\Data\User;
use \MovLib\Presentation\Validation\Username;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UsernameTest extends \PHPUnit_Framework_TestCase {

  /** @var \MovLib\Presentation\Validation\Username */
  public $username;

  public function setUp() {
    $this->username = new Username(new User());
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::__construct
   */
  public function testConstruct() {
    $this->assertTrue($this->username->user instanceof User);
    $this->assertNull($this->username->username);
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::__toString
   */
  public function testToString() {
    $this->assertEmpty($this->username->__toString());
    $this->username = new Username(new User(), "PHPUnit");
    $this->assertEquals("PHPUnit", $this->username->__toString());
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::set
   */
  public function testSet() {
    $this->username->set("PHPUnit");
    $this->assertEquals("PHPUnit", $this->username->username);
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::validate
   * @expectedException ValidationException
   * @expectedExceptionMessage The username cannot begin with a space.
   */
  public function testValidateSpaceAtBeginning() {
    $this->username->set(" PHPUnit")->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::validate
   * @expectedException ValidationException
   * @expectedExceptionMessage The username cannot end with a space.
   */
  public function testValidateSpaceAtEnding() {
    $this->username->set("PHPUnit ")->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::validate
   * @expectedException ValidationException
   * @expectedExceptionMessage The username cannot contain multiple spaces in a row.
   */
  public function testValidateMultipleSpacesInRow() {
    $this->username->set("PHP  Unit")->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::validate
   * @expectedException ValidationException
   * @expectedExceptionMessage The username is too long: it must be 40 characters or less.
   */
  public function testValidateLength() {
    $this->username->set(str_repeat("PHPUnit", 10))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::validate
   * @expectedException ValidationException
   * @expectedExceptionMessage The username Fleshgrinder is already taken, please choose another one.
   */
  public function testValidateCheckName() {
    $this->username->set("Fleshgrinder")->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\Username::validate
   */
  public function testValidate() {
    $this->username->set("PHPUnit")->validate();
  }

}
