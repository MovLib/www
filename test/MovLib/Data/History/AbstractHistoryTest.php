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
namespace MovLib\Data\History;

/**
 * @coversDefaultClass \MovLib\Data\History\AbstractHistory
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractHistoryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\History\AbstractHistory */
  protected $abstractHistory;
  
  /** @var String */
  protected $commitHash;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures

  
  /**
   * Called before each test.
   */
  protected function setUp() {
    global $config, $db;

    $path = "{$config->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      $this->exec("rm -rf {$path}");
    }

    $this->abstractHistory = $this->getMockForAbstractClass("\\MovLib\\Data\\History\\AbstractHistory", [2, "phpunitrepos"], "Movie");
    $this->commitHash  = $this->abstractHistory->createRepository();
    $db->query("UPDATE `movies` SET `commit` = '{$this->commitHash}' WHERE `movie_id` = 2");
    
    $this->abstractHistory->files = [
      "original_title",
      "runtime",
      "year",
      "de_synopsis",
      "en_synopsis"
    ];
    
    $this->abstractHistory->serializedFiles = [
      "titles",
      "taglines",
      "links",
      "trailers",
      "cast",
      "awards",
      "relationships",
      "directors",
      "countries",
      "genres",
      "styles",
      "languages",
      "crew"
    ];
  }

  /**
   * Called after each test.
   */
  protected function tearDown() {
    global $config;
    $path = "{$config->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      $this->exec("rm -rf {$path}");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->assertEquals("phpunitrepos", $this->getProperty($this->abstractHistory, "context"));
    $this->assertEquals("movie", $this->abstractHistory->type);
    $this->assertEquals("/var/www/private/phpunitrepos/movie/2", $this->getProperty($this->abstractHistory, "path"));
  }

  /**
   * @covers ::commitFiles
   * @todo Implement commitFiles
   */
  public function testCommitFiles() {
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is a lie" ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "movie created" ]);
    $this->exec("cd {$this->getProperty($this->abstractHistory, "path")} && git status", $output);
    $this->assertEquals("nothing to commit (working directory clean)", $output[1]);
  }

  /**
   * @covers ::createRepository
   * @todo Implement createRepository
   */
  public function testCreateRepository() {
    global $config;
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/2");
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/2/.git/HEAD");
  }

  /**
   * @covers ::getChangedFiles
   * @todo Implement getChangedFiles
   */
  public function testGetChangedFiles() {
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is not a lie", "year" => 2001, "runtime" => 42 ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "initial commit" ]);

    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is a lie", "year" => 2002, "runtime" => 42 ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "second commit" ]);
    
    $this->assertEquals("original_title year", implode(" ", $this->invoke($this->abstractHistory, "getChangedFiles", [ "HEAD", "HEAD^1" ])));
  }

  /**
   * @covers ::getCommitHashFromDb
   */
  public function testGetCommitHashFromDb() {
    $this->assertEquals($this->commitHash, $this->invoke($this->abstractHistory, "getCommitHashFromDb"));
  }

  /**
   * @covers ::getArrayDiff
   */
  public function testGetArrayDiff() {
    // cast with id 1 and 4 in "added"
    $this->invoke($this->abstractHistory, "writeFiles", [ ["cast" => [
          ["id" => 1, "roles" => "franz" ],
          ["id" => 4, "roles" => "sebastian" ]
      ] ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "cast with id 1 and 4" ]);

    $this->assertEquals(1, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["added"][0]["id"]);
    $this->assertEquals(4, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["added"][1]["id"]);

    // cast with id 2 in "added"
    $this->invoke($this->abstractHistory, "writeFiles", [ ["cast" => [
          ["id" => 1, "roles" => "franz" ],
          ["id" => 2, "roles" => "richard" ],
          ["id" => 4, "roles" => "sebastian" ]
      ] ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "added cast with id 2" ]);

    $this->assertEquals(2, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["added"][0]["id"]);

    // cast with id 1 in "removed"
    $this->invoke($this->abstractHistory, "writeFiles", [ ["cast" => [
          ["id" => 2, "roles" => "richard" ],
          ["id" => 4, "roles" => "sebastian" ]
      ] ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "removed cast with id 1" ]);

    $this->assertEquals(1, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["removed"][0]["id"]);

    // cast with id 2 in "edited"
    $this->invoke($this->abstractHistory, "writeFiles", [ ["cast" => [
          ["id" => 2, "roles" => "markus" ],
          ["id" => 4, "roles" => "sebastian" ]
      ] ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "edited cast with id 2" ]);

    $this->assertEquals(2, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["edited"][0]["id"]);
    $this->assertEquals("richard", $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["edited"][0]["old"]["roles"]);
    $this->assertEquals("markus", $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["edited"][0]["roles"]);
    $this->assertEquals(false, isset($this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["added"][0]));
    $this->assertEquals(false, isset($this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["removed"][0]));

    // cast with id 2 in "edited", 4 is "removed" and 5 is "added"
    $this->invoke($this->abstractHistory, "writeFiles", [ ["cast" => [
          ["id" => 2, "roles" => "franz" ],
          ["id" => 5, "roles" => "sebastian" ]
      ] ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "edited cast with id 2" ]);

    $this->assertEquals(2, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["edited"][0]["id"]);
    $this->assertEquals("markus", $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["edited"][0]["old"]["roles"]);
    $this->assertEquals("franz", $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["edited"][0]["roles"]);
    $this->assertEquals(4, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["removed"][0]["id"]);
    $this->assertEquals(5, $this->abstractHistory->getArrayDiff("HEAD", "HEAD~1", "cast")["added"][0]["id"]);
  }

  /**
   * @covers ::getArrayDiffIdCompare
   */
  public function testGetArrayDiffIdCompare() {
    $a = [ "id" => 4, "foo" => "bar" ];
    $b = [ "id" => 5, "foo" => "bar" ];
    $this->assertEquals(-1, $this->invoke($this->abstractHistory, "getArrayDiffIdCompare", [ $a, $b ]));
    
    $a = [ "id" => 5, "foo" => "bar" ];
    $b = [ "id" => 5, "foo" => "bar" ];
    $this->assertEquals(0, $this->invoke($this->abstractHistory, "getArrayDiffIdCompare", [ $a, $b ]));
    
    $a = [ "id" => 5, "foo" => "bar" ];
    $b = [ "id" => 4, "foo" => "bar" ];
    $this->assertEquals(1, $this->invoke($this->abstractHistory, "getArrayDiffIdCompare", [ $a, $b ]));
    
    $this->assertEquals(-1, $this->invoke($this->abstractHistory, "getArrayDiffIdCompare", [ 1, $b ]));
    $this->assertEquals(-1, $this->invoke($this->abstractHistory, "getArrayDiffIdCompare", [ $a, 1 ]));
  }

  /**
   * @covers ::getArrayDiffDeepCompare
   * @todo Implement getArrayDiffDeepCompare
   */
  public function testGetArrayDiffDeepCompare() {
    $a = [ "foo" => "aaaa" ];
    $b = [ "foo" => "bbbb" ];
    $this->assertEquals(-1, $this->invoke($this->abstractHistory, "getArrayDiffDeepCompare", [ $a, $b ]));
    
    $a = [ "foo" => "aaaa" ];
    $b = [ "foo" => "aaaa" ];
    $this->assertEquals(0, $this->invoke($this->abstractHistory, "getArrayDiffDeepCompare", [ $a, $b ]));
    
    $a = [ "foo" => "bbbb" ];
    $b = [ "foo" => "aaaa" ];
    $this->assertEquals(1, $this->invoke($this->abstractHistory, "getArrayDiffDeepCompare", [ $a, $b ]));
    
    $this->assertEquals(-1, $this->invoke($this->abstractHistory, "getArrayDiffDeepCompare", [ 1, $b ]));
    $this->assertEquals(-1, $this->invoke($this->abstractHistory, "getArrayDiffDeepCompare", [ $a, 1 ]));
  }

  /**
   * @covers ::getDirtyFiles
   * @todo Implement getDirtyFiles
   */
  public function testGetDirtyFiles() {
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is a lie", "year" => 2002, "runtime" => 42 ] ]);
    $this->assertEquals("original_title runtime year", implode(" ", $this->invoke($this->abstractHistory, "getDirtyFiles")));
  }

  /**
   * @covers ::getLastCommitHashFromGit
   */
  public function testGetLastCommitHashFromGit() {
    $this->assertEquals($this->commitHash, $this->invoke($this->abstractHistory, "getLastCommitHashFromGit"));
  }

  /**
   * @covers ::getFileAtRevision
   */
  public function testGetFileAtRevision() {
    $this->invoke($this->abstractHistory, "writeFiles", [ ["original_title" => "The foobar is a lie" ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "initial commit" ]);

    $this->assertEquals("", $this->abstractHistory->getFileAtRevision("original_title", "HEAD^1"));
    
    $this->invoke($this->abstractHistory, "writeFiles", [ ["original_title" => "The bar is not a lie" ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "second commit" ]);

    $this->assertEquals("The foobar is a lie", $this->abstractHistory->getFileAtRevision("original_title", "HEAD^1"));
  }

  /**
   * @covers ::getLastCommits
   */
  public function testGetLastCommits() {
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is a lie" ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "initial commit" ]);

    $this->invoke($this->abstractHistory, "writeFiles", [ [ "year" => 2001 ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "second commit" ]);

    $this->invoke($this->abstractHistory, "writeFiles", [ [ "runtime" => 300 ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "third commit" ]);

    $commits = $this->invoke($this->abstractHistory, "getLastCommits");
    $this->assertEquals("third commit", $commits[0]["subject"]);
    $this->assertEquals("second commit", $commits[1]["subject"]);
    $this->assertEquals("initial commit", $commits[2]["subject"]);

    $oneCommit = $this->invoke($this->abstractHistory, "getLastCommits", [ 1 ]);
    $this->assertEquals("third commit", $oneCommit[0]["subject"]);
    $this->assertCount(1, $oneCommit);
  }

  /**
   * @covers ::getShortName
   */
  public function testGetShortName() {
    $this->assertEquals("movie", $this->invoke($this->abstractHistory, "getShortName"));
  }

  /**
   * @covers ::hideRepository
   */
  public function testHideRepository() {
    global $config;
    $this->invoke($this->abstractHistory, "hideRepository");
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/.2");
  }
  
  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Repository already hidden
   * @covers ::hideRepository
   */
  public function testHideRepositoryIfHidden() {
    $this->invoke($this->abstractHistory, "hideRepository");
    $this->invoke($this->abstractHistory, "hideRepository");
  }

  /**
   * @covers ::writeFiles
   */
  public function testWriteFiles() {
    global $config;
     // wrong offset name
    $this->invoke($this->abstractHistory, "writeFiles", [ ["foo" => "bar" ] ]);
    $this->assertFileNotExists("{$config->documentRoot}/private/phpunitrepos/movie/2/foo");

    // offset which should be written to file directly
    $this->invoke($this->abstractHistory, "writeFiles", [ ["original_title" => "The Shawshank Redemption" ] ]);
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/2/original_title");
    $this->assertStringEqualsFile("{$config->documentRoot}/private/phpunitrepos/movie/2/original_title", "The Shawshank Redemption");

    // offset with language prefix which should be written to file directly
    $this->invoke($this->abstractHistory, "writeFiles", [ ["en_synopsis" => "A very short synopsis." ] ]);
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/2/en_synopsis");
    $this->assertStringEqualsFile("{$config->documentRoot}/private/phpunitrepos/movie/2/en_synopsis", "A very short synopsis.");

    // no file should be written if the offset is not set
    $this->assertFileNotExists("{$config->documentRoot}/private/phpunitrepos/movie/2/de_synopsis");

    // offset which should be written to file serialized
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "titles" => [[ "id" => 1, "title" => "foo" ], [ "id" => 2, "title" => "bar" ] ] ] ]);
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/2/titles");
    $this->assertStringEqualsFile(
      "{$config->documentRoot}/private/phpunitrepos/movie/2/titles", 'a:2:{i:0;a:2:{s:2:"id";i:1;s:5:"title";s:3:"foo";}i:1;a:2:{s:2:"id";i:2;s:5:"title";s:3:"bar";}}'
    );
  }

  /**
   * @covers ::resetFiles
   */
  public function testResetFiles() {
    global $config;
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is a lie" ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "commitFiles", [ "initial commit" ]);

    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The bar is not a lie" ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");

    $this->invoke($this->abstractHistory, "resetFiles", [ [ "original_title" ] ]);
    $this->assertStringEqualsFile("{$config->documentRoot}/private/phpunitrepos/movie/2/original_title", "The foobar is a lie");
  }

  /**
   * @covers ::saveHistory
   */
  public function testSaveHistory() {
    global $config;
    $this->abstractHistory->startEditing();
    $this->abstractHistory->saveHistory([ "original_title" => "The foobar is a lie" ], "initial commit");
    $this->assertFileExists(("{$config->documentRoot}/private/phpunitrepos/movie/2/original_title"));
  }
  
  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage startEditing() has to be called before saveHistory()!
   * @covers ::saveHistory
   */
  public function testSaveHistoryWithoutStartEditing() {
    $this->abstractHistory->saveHistory([ ], "initial commit");
  }

  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Someone else edited the same information about the movie!
   * @covers ::saveHistory
   */
  public function testSaveHistoryIfSomeoneElseAlreadyChangedTheSameInformation() {
    global $config, $db;
    $this->abstractHistory->startEditing();
    $this->movieUserOne = $this->abstractHistory;
    $this->movieUserTwo = clone $this->abstractHistory;

    $commitHash = $this->movieUserOne->saveHistory([ "original_title" => "The foobar is a lie" ], "initial commit");
    $db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");

    $this->movieUserTwo->saveHistory([ "original_title" => "The bar is not a lie" ], "initial commit");

    $this->assertStringEqualsFile("{$config->documentRoot}/private/phpunitrepos/movie/2/original_title", "The foobar is a lie");
  }

  /**
   * @covers ::stageAllFiles
   */
  public function testStageAllFiles() {
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "original_title" => "The foobar is a lie", "year" => 2000, "runtime" => 42 ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    
    $this->exec("cd {$this->getProperty($this->abstractHistory, "path")} && git status", $output);
    $this->assertEquals("# Changes to be committed:", $output[1]);
    $this->assertEquals("#	new file:   original_title", $output[4]);
    $this->assertEquals("#	new file:   runtime", $output[5]);
    $this->assertEquals("#	new file:   year", $output[6]);
  }

  /**
   * @covers ::startEditing
   */
  public function testStartEditing() {
    global $db;
    $db->query("UPDATE `movies` SET `commit` = 'b006169990b07af17d198f6a37efb324ced95fb3' WHERE `movie_id` = 2");
    $this->abstractHistory->startEditing();
    $p = $this->getProperty($this->abstractHistory, "commitHash");
    $this->assertNotNull($p);
    $this->assertEquals("b006169990b07af17d198f6a37efb324ced95fb3", $p);
  }

  /**
   * @covers ::unhideRepository
   */
  public function testUnhideRepository() {
    global $config;
    $this->invoke($this->abstractHistory, "hideRepository");
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/.2");

    $this->invoke($this->abstractHistory, "unhideRepository");
    $this->assertFileExists("{$config->documentRoot}/private/phpunitrepos/movie/2");
  }
  
  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Repository not hidden
   * @covers ::unhideRepository
   */
  public function testUnhideRepositoryIfNotHidden() {
    $this->invoke($this->abstractHistory, "unhideRepository");
  }

  /**
   * @covers ::unstageFiles
   */
  public function testUnstageFiles() {
    $this->invoke($this->abstractHistory, "writeFiles", [ [ "year" => 2000 ] ]);
    $this->invoke($this->abstractHistory, "stageAllFiles");
    $this->invoke($this->abstractHistory, "unstageFiles", [ [ "year" ] ]);
    
    $this->exec("cd {$this->getProperty($this->abstractHistory, "path")} && git status", $output);
    $this->assertEquals("# Untracked files:", $output[1]);
    $this->assertEquals("#	year", $output[4]);
  }

  /**
   * @covers ::writeToFile
   */
  public function testWriteToFile() {
    $this->invoke($this->abstractHistory, "writeToFile", [ "foo", "bar" ]);
    $this->assertFileExists("{$this->getProperty($this->abstractHistory, "path")}/foo");
    $this->assertStringEqualsFile("{$this->getProperty($this->abstractHistory, "path")}/foo", "bar");
  }

}
