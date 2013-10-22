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
namespace MovLib\Test\Data;

use \MovDev\Database;
use \MovLib\Data\Genres;

/**
 * @coversDefaultClass \MovLib\Data\Genres
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GenresTest extends \MovLib\Test\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Properties

  /** @var \MovLib\Data\Genges */
  private $genres;

  /** @var \MovDev\Database */
  private $db;

  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    $this->genres = new Genres();
    $this->db     = new Database();
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::orderById
   */
  public function testOrderById() {
    $expectetGenres = [
      [ "id" => 1, "name" => "Action" ],
      [ "id" => 4, "name" => "Biography" ],
      [ "id" => 7, "name" => "Documentary" ],
      [ "id" => 9, "name" => "Family" ]
    ];
    $index          = 0;
    foreach ($this->genres->orderById([ 1, 7, 4, 9 ]) as $key => $value) {
      $this->assertEquals($expectetGenres[$index]["id"], $key);
      $this->assertEquals($expectetGenres[$index]["name"], $value->name);
      ++$index;
    }
  }

  /**
   * @covers ::orderByName
   */
  public function testOrderByName() {
    global $i18n;
    $this->genres->orderByName();
    /* @var $result \mysqli_result */
    $result = array_column($this->db->query("SELECT `name` FROM `genres`")->get_result()->fetch_all(), 0);
    $i18n->getCollator()->asort($result);
    foreach ($result as $name) {
      $this->assertEquals($name, $this->genres[$name]->name);
    }
  }

}