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

/**
 * @coversDefaultClass \MovLib\Presentation\History\TraitHistory
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TraitHistoryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /** @var \MovLib\Data\History\Movie */
  protected $movie;
  
  /** @var \MovLib\Presentation\History\TraitHistory */
  protected $traitHistory;
  
  /** @var string */
  protected $commitHash;


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

    $this->movie = new Movie(2, "phpunitrepos");
    $this->commitHash = $this->movie->createRepository();  
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
    
    $this->movie->startEditing();
    $this->commitHash = $this->movie->saveHistory([ "original_title" => "The foobar is a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
        
    $_SERVER["MOVIE_ID"] = 2;
    $_SERVER["REVISION_HASH"] = $this->commitHash;
    
    $this->traitHistory = new \MovLib\Presentation\History\Movie\MovieRevisions("phpunitrepos"); 
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
   * @covers ::diffArray
   * @todo Implement diffArray
   */
  public function testDiffArray() {
    $this->markTestIncomplete("This test has not been implemented yet."); 
  }

  /**
   * @covers ::diffArrayItems
   * @todo Implement diffArrayItems
   */
  public function testDiffArrayItems() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::diffIds
   * @todo Implement diffIds
   */
  public function testDiffIds() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::formatFileNames
   */
  public function testFormatFileNames() {
    $fileNames = [
      "original_title",
      "cast",
      "de_synopsis",
      "en_comment"
    ];

    $this->assertEquals(
      [
      "Original Title",
      "Cast",
      "Synopsis (German)",
      "Comment (English)"
      ], $this->invoke($this->traitHistory, "formatFileNames", [ $fileNames ])
    );
  }

  /**
   * @covers ::getDiff
   * @todo Implement getDiff
   */
  public function testGetDiff() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::textDiffOfRevisions
   */
  public function testTextDiffOfRevisions() {
    global $db;
    $this->movie->startEditing();
    $this->commitHash = $this->movie->saveHistory([ "original_title" => "The bar is not a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");

    $this->assertContains(
      "The <span class='red'>foo</span>bar is <span class='green'>not </span>a lie",
      $this->invoke($this->traitHistory, "textDiffOfRevisions", [ $this->commitHash, "{$this->commitHash}^1", "original_title", true ])
    );
  }

  /**
   * @covers ::textDiffOfStrings
   */
  public function testTextDiffOfStrings() {
    $from = "The bar is not a lie";
    $to   = "The foobar is a lie";
    $this->assertContains(
      "The <span class='red'>bar</span> <span class='green'>foobar</span> is <span class='red'>not</span> a lie",
      $this->invoke($this->traitHistory, "textDiffOfStrings", [$from, $to])
    );
  }

}
