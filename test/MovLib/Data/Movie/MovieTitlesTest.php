<?php

/*!
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
namespace MovLib\Data\Movie;

use \MovLib\Data\Movie\MovieTitles;

/**
 * @coversDefaultClass \MovLib\Data\Movie\MovieTitles
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTitlesTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Movie\MovieTitles */
  protected $movieTitles;


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->movieTitles = new MovieTitles(2);
    $this->assertNotNull($this->movieTitles->movieId);
  }

  /**
   * @covers ::orderById
   */
  public function testOrderById() {
    $this->movieTitles = new MovieTitles(2);
    $movieTitles = $this->movieTitles->orderById();
    $this->assertEquals(1, $movieTitles[0]->id);
    $this->assertEquals("Die Verurteilten", $movieTitles[0]->title);
    $this->assertEquals(
      [ 0 => [ "de" => "deutsches Kommentar" ], 1 => [ "en" => "english comment" ] ],
      $movieTitles[0]->dynComments
    );
  }

}
