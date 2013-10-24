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
namespace MovLib\Data;

use \MovLib\Data\Companies;

/**
 * @coversDefaultClass \MovLib\Data\Companies
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class CompaniesTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Companies */
  private $companies;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->companies = new Companies();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::orderById
   */
  public function testOrderById() {
    $expectetGenres = [
      [ "id" => 1, "name" => "Anchor Bay" ],
      [ "id" => 4, "name" => "DreamWorks Animation" ],
      [ "id" => 7, "name" => "Blue Sky Studios" ],
      [ "id" => 9, "name" => "Metro-Goldwyn-Mayer" ]
    ];
    $index = 0;
    foreach ($this->companies->orderById([ 1, 7, 4, 9 ]) as $key => $value) {
      $this->assertEquals($expectetGenres[$index]["id"], $key);
      $this->assertEquals($expectetGenres[$index]["name"], $value->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByName
   */
  public function testOrderByName() {
    global $db, $i18n;
    $this->companies->orderByName();
    /* @var $result \mysqli_result */
    $result = array_column($db->query("SELECT `name` FROM `companies`")->get_result()->fetch_all(), 0);
    $i18n->getCollator()->asort($result);
    foreach ($result as $name) {
      $this->assertEquals($name, $this->companies[$name]->name);
    }
  }

  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
