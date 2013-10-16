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
use \MovLib\Data\Countries;

/**
 * @coversDefaultClass \MovLib\Data\Countries
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class CountriesTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Countries */
  private $countries;

  /** @var \MovDev\Database */
  private $db;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->countries = new Countries();
    $this->db        = new Database();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::orderByCode
   * @group Database
   */
  public function testOrderByCode() {
    $this->countries->orderByCode();
    foreach (array_column($this->db->query("SELECT `iso_alpha-2` FROM `countries` ORDER BY `iso_alpha-2` ASC")->get_result()->fetch_all(), 0) as $code) {
      $this->assertEquals($code, $this->countries[$code]->code);
    }
  }

  /**
   * @covers ::orderByCode
   * @group Database
   */
  public function testOrderByCodeFilter() {
    $this->countries->orderByCode([ "US", "AT" ]);
    foreach (array_column($this->db->query("SELECT `iso_alpha-2` FROM `countries` WHERE `country_id` IN('US', 'AT') ORDER BY `iso_alpha-2` ASC")->get_result()->fetch_all(), 0) as $code) {
      $this->assertEquals($code, $this->countries[$code]->code);
    }
  }

  /**
   * @covers ::orderById
   * @group Database
   */
  public function testOrderById() {
    $this->countries->orderById();
    foreach (array_column($this->db->query("SELECT `country_id` FROM `countries`")->get_result()->fetch_all(), 0) as $id) {
      $this->assertEquals($id, $this->countries[$id]->id);
    }
  }

  /**
   * @covers ::orderById
   * @group Database
   */
  public function testOrderByIdFilter() {
    $this->countries->orderById([ 1, 2 ]);
    foreach (array_column($this->db->query("SELECT `country_id` FROM `countries` WHERE `country_id` IN (1, 2)")->get_result()->fetch_all(), 0) as $id) {
      $this->assertEquals($id, $this->countries[$id]->id);
    }
  }

  /**
   * @covers ::orderByName
   * @group Database
   */
  public function testOrderByName() {
    global $i18n;
    $this->countries->orderByName();
    /* @var $result \mysqli_result */
    $result = array_column($this->db->query("SELECT `name` FROM `countries`")->get_result()->fetch_all(), 0);
    $i18n->getCollator()->asort($result);
    foreach ($result as $name) {
      $this->assertEquals($name, $this->countries[$name]->name);
    }
  }

}
