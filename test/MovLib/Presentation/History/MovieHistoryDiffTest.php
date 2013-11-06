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
namespace MovLib\Presentation\History;

use \MovLib\Data\History\Movie;
use \MovLib\Presentation\History\MovieHistoryDiff;

/**
 * @coversDefaultClass \MovLib\Presentation\History\MovieHistoryDiff
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieHistoryDiffTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  
  /** @var \MovLib\Data\History\Movie */
  protected $movie;
  
  /** @var \MovLib\Presentation\History\MovieHistoryDiff */
  protected $movieHistoryDiff;
  
  /** @var string */
  protected $commitHash;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    global $kernel, $db;
    $path = "{$kernel->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }

    $this->movie = new Movie(2, "phpunitrepos");
    $this->commitHash = $this->movie->createRepository();  
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
    
    $this->movie->startEditing();
    $this->commitHash = $this->movie->saveHistory([ "original_title" => "The foobar is a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
        
    $_SERVER["MOVIE_ID"] = 2;
    $_SERVER["REVISION_HASH"] = $this->commitHash;
    
    $this->movieHistoryDiff = new MovieHistoryDiff("phpunitrepos");
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


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderExample() {
    return [];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals("History of The Shawshank Redemption (1994)", $this->getProperty($this->movieHistoryDiff, "title"));
  }

  /**
   * @covers ::getPageContent
   */
  public function testGetPageContent() {
    $this->assertContains(
      "<a href='/movie/2/history' accesskey='h' class='separator active'",
      $this->invoke($this->movieHistoryDiff, "getContent")
    );
  }

  /**
   * @covers ::getAwards
   * @todo Implement getAwards
   */
  public function testGetAwards() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getCast
   * @todo Implement getCast
   */
  public function testGetCast() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getCountries
   * @todo Implement getCountries
   */
  public function testGetCountries() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getCrew
   * @todo Implement getCrew
   */
  public function testGetCrew() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getDirectors
   * @todo Implement getDirectors
   */
  public function testGetDirectors() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getGenres
   * @todo Implement getGenres
   */
  public function testGetGenres() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getLanguages
   * @todo Implement getLanguages
   */
  public function testGetLanguages() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getLinks
   * @todo Implement getLinks
   */
  public function testGetLinks() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getRelationships
   * @todo Implement getRelationships
   */
  public function testGetRelationships() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getStyles
   * @todo Implement getStyles
   */
  public function testGetStyles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getTaglines
   * @todo Implement getTaglines
   */
  public function testGetTaglines() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getTitles
   * @todo Implement getTitles
   */
  public function testGetTitles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getTrailers
   * @todo Implement getTrailers
   */
  public function testGetTrailers() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }
  
}
