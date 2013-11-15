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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie\MovieTitles as MovieTitlesModel;
use \MovLib\Presentation\Movie\MovieTitles;

/**
 * @coversDefaultClass \MovLib\Presentation\Movie\MovieTitles
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTitlesTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\Movie\MovieTitles */
  protected $movieTitles;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $_SERVER["MOVIE_ID"] = 2;
    $this->movieTitles = new MovieTitles();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertNotNull($this->getProperty($this->movieTitles, "model"));
    $this->assertEquals("Titles of The Shawshank Redemption (1994)", $this->getProperty($this->movieTitles, "title"));
    
  }
  
  /**
   * @covers ::formatComments
   */
  public function testFormatComments() {
    $comment = [ "de" => "deutsches Kommentar" ];
    $this->assertEquals(
      "(de) : deutsches Kommentar",
      $this->movieTitles->formatComments($comment)      
    );
  }

  /**
   * @covers ::formatTitles
   */
  public function testFormatTitles() {
    $titles = (new MovieTitlesModel(2))->orderById();
    $this->assertEquals(
      "(de) Die Verurteilten (display Title)<div class='well well--small'><ul><li>(de) : deutsches Kommentar</li><li>(en)"
      . " : english comment</li></ul></div>", $this->movieTitles->formatTitles($this->getProperty($titles, "objectsArray")[0])   
    );
  }

  /**
   * @covers ::getPageContent
   */
  public function testGetPageContent() {
    $this->assertContains("Titles of The Shawshank Redemption (1994)", $this->invoke($this->movieTitles, "getPageContent"));
  }

}
