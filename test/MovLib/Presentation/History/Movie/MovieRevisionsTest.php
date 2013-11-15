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
namespace MovLib\Presentation\History\Movie;

use \MovLib\Data\History\Movie;
use \MovLib\Presentation\History\Movie\MovieRevisions;

/**
 * @coversDefaultClass \MovLib\Presentation\History\MovieHistory
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieRevisionsTest extends \MovLib\TestCase {
  
  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\History\MovieHistoryDiff */
  protected $movieHistory;


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

    $_SERVER["MOVIE_ID"] = 2;

    $movie      = new Movie(2, "phpunitrepos");
    $commitHash = $movie->createRepository();
    $db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");
    
    $this->movieHistory = new MovieRevisions("phpunitrepos");
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
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals("History of The Shawshank Redemption (1994)", $this->getProperty($this->movieHistory, "title"));
  }

  /**
   * @covers ::getPageContent
   */
  public function testGetPageContent() {
    $this->assertContains(
      "<a href='/movie/2/history' accesskey='h' class='separator active'",
      $this->invoke($this->movieHistory, "getContent")
    );
  }

}
