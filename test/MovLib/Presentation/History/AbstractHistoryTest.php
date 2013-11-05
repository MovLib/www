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
 * @coversDefaultClass \MovLib\Presentation\History\AbstractHistory
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractHistoryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\History\AbstractHistory */
  protected $abstractHistory;
  
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

    $movie = new Movie(2, "phpunitrepos");
    $this->commitHash = $movie->createRepository();  
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
    
    $movie->startEditing();
    $this->commitHash = $movie->saveHistory([ "original_title" => "The foobar is a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
    
    $this->abstractHistory = $this->getMockForAbstractClass("\\MovLib\\Presentation\\History\\AbstractHistory", [ "phpunitrepos" ], "MovieHistory");
    $this->setProperty($this->abstractHistory, "historyModel", $movie);
    
    $_SERVER["MOVIE_ID"] = 2;
    $_SERVER["REVISION_HASH"] = $this->commitHash;
  }

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
   * @covers ::formatChangedFile
   */
  public function testFormatChangedFile() {
    $this->setProperty($this->abstractHistory, "revisionItemHash", $this->commitHash);
    $this->assertContains(
      "<a href='/movie/2/diff/{$this->commitHash}#Original Title'>Original Title</a>",
      $this->invoke($this->abstractHistory, "formatChangedFile", [ "Original Title" ])
    );
  }
  
  /**
   * @covers ::formatDiff
   */
  public function testFormatDiff() {
    $this->assertContains(
      "<a id='Original Title'>Original Title</a><div class='well well--small'><span class='green'>The foobar is a lie</span></div>",
      $this->invoke($this->abstractHistory, "formatDiff", [ "original_title" ])
    );
  }

  /**
   * @covers ::formatRevision
   */
  public function testFormatRevision() {
    $revisionItem = [
      "author_id"   => 1,
      "author_name" => "Fleshgrinder",
      "hash"        => $this->commitHash,
      "timestamp"   => 1383664598,
      "subject"     => "added original title"
    ];
    
    $this->assertContains(
      "<a href='/movie/2/diff/{$this->commitHash}'>diff</a> | Nov 5, 2013, 3:16:38 PM by <a href='/user/Fleshgrinder' "
      . "title='Profile of Fleshgrinder'>Fleshgrinder</a>: added original title<ul class='well well--small no-list'><li>"
      . "<a href='/movie/2/diff/{$this->commitHash}#Original Title'>Original Title</a>",
      $this->invoke($this->abstractHistory, "formatRevision", [ $revisionItem ])
    );
  }

  /**
   * @covers ::diffPage
   */
  public function testDiffPage() {
    $this->assertContains("Difference between revisions", $this->invoke($this->abstractHistory, "diffPage"));
    $this->assertContains("Original Title", $this->invoke($this->abstractHistory, "diffPage"));
    $this->assertContains(
      "<div id='revision-diff'><a href='/movie/2/history' class='pull-right'>go back</a><h2>Difference between revisions"
      . "</h2><ul><li>", $this->invoke($this->abstractHistory, "diffPage")
    );
  }

  /**
   * @covers ::revisionsPage
   */
  public function testRevisionsPage() {
    $this->assertContains(
      "<div id='revision-history'><h2>Revision history</h2>", $this->invoke($this->abstractHistory, "revisionsPage")
    );
  }

}
