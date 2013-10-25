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
namespace MovLib\Data\User;

use \MovDev\Database;
use \MovLib\Data\User\Users;

/**
 * @coversDefaultClass \MovLib\Data\User\Users
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class UsersTest extends \MovLib\TestCase {

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
    $index         = 0;
    foreach ($this->users->orderById([ 3, 1, 2 ]) as $key => $value) {
      $this->assertEquals($expectedUsers[$index]["id"], $key);
      $this->assertEquals($expectedUsers[$index]["name"], $value->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByNewest
   */
  public function testOrderByNewest() {
    global $db;
    $this->users->orderByNewest(0, 3);
    $index = 0;
    foreach (array_column($db->query("SELECT `name` FROM `users` WHERE `deactivated` = false ORDER BY user_id DESC LIMIT 3")->get_result()->fetch_all(), 0) as $name) {
      $this->assertEquals($name, $this->users[$index]->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByNewest
   */
  public function testOrderByNewestWithOffsetAndLimit() {
    global $db;
    $this->users->orderByNewest(1, 2);
    $index = 0;
    foreach (array_column($db->query("SELECT `name` FROM `users` WHERE `deactivated` = false ORDER BY user_id DESC LIMIT 1, 3")->get_result()->fetch_all(), 0) as $name) {
      $this->assertEquals($name, $this->users[$index]->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByName
   */
  public function testOrderByName() {
    global $db, $i18n;
    $this->users->orderByName();
    /* @var $result \mysqli_result */
    $result = array_column($db->query("SELECT `name` FROM `users` WHERE `deactivated` = false")->get_result()->fetch_all(), 0);
    $i18n->getCollator()->asort($result);
    foreach ($result as $name) {
      $this->assertEquals($name, $this->users[$name]->name);
    }
  }

}
