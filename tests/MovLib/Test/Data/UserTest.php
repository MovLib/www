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
namespace MovLib\Test\Data;

use \MovLib\Data\User;
use \ReflectionMethod;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserTest extends \PHPUnit_Framework_TestCase {

  public static function tearDownAfterClass() {
    exec("movcli db -s users");
  }

  /**
   * @covers User::checkEmail
   */
  public function testCheckEmail() {
    $user = new User();
    $this->assertTrue($user->checkEmail("richard@fussenegger.info"));
    $this->assertFalse($user->checkEmail("phpunit@movlib.org"));
  }

  /**
   * @covers User::checkName
   */
  public function testCheckName() {
    $user = new User();
    $this->assertTrue($user->checkName("Fleshgrinder"));
    $this->assertFalse($user->checkName("PHPUnit"));
  }

  /**
   * @covers User::setAuthenticationToken
   */
  public function testSetAuthenticationToken() {
    $user = new User(User::FROM_ID, 1);
    $this->assertEmpty($user->authenticationToken);
    $user->setAuthenticationToken();
    $this->assertNotEmpty($user->authenticationToken);
    return $user;
  }

  /**
   * @covers User::prepareTemporaryData
   * @depends testSetAuthenticationToken
   */
  public function testPrepareTemporaryData(User $user) {
    return $user->prepareTemporaryData("s", [ "phpunit" ], [ "phpunit" ]);
  }

  /**
   * @covers User::getTemporaryData
   * @depends testPrepareTemporaryData
   */
  public function testGetTemporaryData(User $user) {
    $rm = new ReflectionMethod($user, "getTemporaryData");
    $rm->setAccessible(true);
    $data = $rm->invokeArgs($user, [ $user->authenticationToken, [ "phpunit" => "BINARY" ]]);
    $this->assertTrue(isset($data["phpunit"]));
    $this->assertEquals("phpunit", $data["phpunit"]);
    $this->assertEmpty($user->select("SELECT * FROM `tmp` WHERE `key` = ?", "s", [ $user->authenticationToken ]));
  }

}
