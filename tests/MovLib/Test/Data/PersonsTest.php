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


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->persons = new Persons();
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
    $expectedPersons = [
        "Luc Besson",
        "Jean Reno",
        "Natalie Portman",
        "Gary Oldman",
        "Frank Darabont",
        "Tim Robbins",
        "Morgan Freeman",
        "Sacha Goedegebure"
    ];
    $index = 0;
    foreach ($this->persons->orderByCreated() as $value) {
      $this->assertEquals($expectedPersons[$index], $value->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByCreated
   */
  public function testOrderByCreatedWithOffsetAndLimit() {
    $persons = $this->persons->orderByCreated(5, 3);
    $this->assertEquals("Tim Robbins", $persons[0]->name);
    $this->assertEquals("Morgan Freeman", $persons[1]->name);
    $this->assertEquals("Sacha Goedegebure", $persons[2]->name);
  }

  /**
   * @covers ::orderByName
   */
  public function testOrderByName() {
    $expectedPersons = [
        "Frank Darabont",
        "Gary Oldman",
        "Jean Reno",
        "Luc Besson",
        "Morgan Freeman",
        "Natalie Portman",
        "Sacha Goedegebure",
        "Tim Robbins"
    ];
    $index = 0;
    foreach ($this->persons->orderByName() as $key => $value) {
      $this->assertEquals($expectedPersons[$index], $key);
      $this->assertEquals($expectedPersons[$index], $value->name);
      ++$index;
    }
  }

}
