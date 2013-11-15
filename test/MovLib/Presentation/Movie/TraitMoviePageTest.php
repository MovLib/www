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

use \MovLib\Data\History\Movie;

/**
 * @coversDefaultClass \MovLib\Presentation\Movie\TraitMoviePage
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TraitMoviePageTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\Movie\TraitMoviePage */
  protected $traitMoviePage;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    global $kernel, $db, $i18n;
    $path = "{$kernel->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }

    $movie = new Movie(2, "phpunitrepos");
    $movie->createRepository();  
        
    $_SERVER["MOVIE_ID"] = 2;
    
    $this->traitMoviePage = new \MovLib\Presentation\History\Movie\MovieRevisions("phpunitrepos"); 
  }

  /**
   * Called after each test.
   */
  protected function tearDown() {
    global $kernel;
    $path = "{$kernel->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::initMovie
   */
  public function testInitMovie() {
    $this->assertNotNull($this->getProperty($this->traitMoviePage, "model"));
    $this->assertAttributeInstanceOf("\\MovLib\\Data\\Movie", "model", $this->traitMoviePage);
    $this->assertEquals("History of The Shawshank Redemption (1994)", $this->getProperty($this->traitMoviePage, "title"));
  }
  
  /**
   * @covers ::getBreadcrumbs
   */
  public function testGetBreadcrumbs() {
    $this->assertEquals(
      [ [ "/movies", "Movies", [ "title" => "Have a look at the latest movie entries at MovLib." ] ] ],
      $this->invoke($this->traitMoviePage, "getBreadcrumbs")
    );
  }

  /**
   * @covers ::getSecondaryNavigationMenuItems
   */
  public function testGetSecondaryNavigationMenuItems() {
    $this->assertNotNull($this->invoke($this->traitMoviePage, "getSecondaryNavigationMenuItems"));
  }

}
