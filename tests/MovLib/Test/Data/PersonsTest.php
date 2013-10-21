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

use \MovDev\Database;
use \MovLib\Data\Persons;

/**
 * @coversDefaultClass \MovLib\Data\Persons
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PersonsTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Persons */
  private $persons;

  /** @var \MovDev\Database */
  private $db;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->persons = new Persons();
    $this->db      = new Database();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::orderById
    */
  public function testOrderById() {
    $expectedPersons = [
      [ "id" => 3, "name" => "Natalie Portman" ],
      [ "id" => 5, "name" => "Frank Darabont" ],
      [ "id" => 7, "name" => "Morgan Freeman" ]
    ];
    $index = 0;
    foreach ($this->persons->orderById([ 5,3,7 ]) as $key => $value) {
      $this->assertEquals($expectedPersons[$index]["id"], $key);
      $this->assertEquals($expectedPersons[$index]["name"], $value->name);
      ++$index;
    }
  }


  /**
   * @covers ::orderByCreated
   */
  public function testOrderByCreated() {
    $this->persons->orderByCreated();
    $index = 0;
    foreach (array_column($this->db->query("SELECT `name` FROM `persons` ORDER BY created ASC")->get_result()->fetch_all(), 0) as $name) {
      $this->assertEquals($name, $this->persons[$index]->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByCreated
   */
  public function testOrderByCreatedWithOffsetAndLimit() {
    $this->persons->orderByCreated(5,3);
    $index = 0;
    foreach (array_column($this->db->query("SELECT `name` FROM `persons` ORDER BY created ASC LIMIT 5, 3")->get_result()->fetch_all(), 0) as $name) {
      $this->assertEquals($name, $this->persons[$index]->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByName
   */
  public function testOrderByName() {
    global $i18n;
    $this->persons->orderByName();
    /* @var $result \mysqli_result */
    $result = array_column($this->db->query("SELECT `name` FROM `persons`")->get_result()->fetch_all(), 0);
    $i18n->getCollator()->asort($result);
    foreach ($result as $name) {
      $this->assertEquals($name, $this->persons[$name]->name);
    }
  }

}
