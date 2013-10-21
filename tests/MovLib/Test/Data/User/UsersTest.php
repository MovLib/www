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
namespace MovLib\Test\Data\User;

use \MovLib\Data\User\Users;
/**
 * @coversDefaultClass \MovLib\Data\User\Users
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UsersTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\User\Users */
  private $users;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->users = new Users();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::orderById
    */
  public function testOrderById() {
    $expectedUsers = [
      [ "id" => 1, "name" => "Fleshgrinder" ],
      [ "id" => 2, "name" => "ftorghele" ],
      [ "id" => 3, "name" => "Ravenlord" ]
    ];
    $index = 0;
    foreach ($this->users->orderById([ 3,1,2 ]) as $key => $value) {
      $this->assertEquals($expectedUsers[$index]["id"], $key);
      $this->assertEquals($expectedUsers[$index]["name"], $value->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByNewest
   */
  public function testOrderByNewest() {
    $expectedUsers = [
      "Ravenlord",
      "ftorghele",
      "Fleshgrinder"
    ];
    $index = 0;
    foreach ($this->users->orderByNewest() as $value) {
      $this->assertEquals($expectedUsers[$index], $value->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByNewest
   */
  public function testOrderByNewestWithOffsetAndLimit() {
    $users = $this->users->orderByNewest(1, 2);
    $this->assertEquals("ftorghele", $users[0]->name);
    $this->assertEquals("Fleshgrinder", $users[1]->name);
  }

  /**
   * @covers ::orderByName
   */
  public function testOrderByName() {
    $expectedUsers = [
      "Fleshgrinder",
      "ftorghele",
      "Ravenlord"
    ];
    $index = 0;
    foreach ($this->users->orderByName() as $key => $value) {
      $this->assertEquals($expectedUsers[$index], $key);
      $this->assertEquals($expectedUsers[$index], $value->name);
      ++$index;
    }
  }

}
