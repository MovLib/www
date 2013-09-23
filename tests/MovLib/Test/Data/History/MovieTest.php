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
namespace MovLib\Test\Data\History;

use \MovLib\Data\History\Movie;

/**
 * Test the Movie.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTest extends \PHPUnit_Framework_TestCase {

  /** @var \mysqli */
  static $db;

  /** @var \MovLib\Data\History\Movie */
  public $movie;

  public static function setUpBeforeClass() {
    static::$db = new \mysqli();
    static::$db->real_connect();
    static::$db->select_db($GLOBALS["movlib"]["default_database"]);
  }

  public function setUp() {
    $this->movie = new Movie(2);
    $commitHash = $this->movie->createRepository();
    static::$db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");
  }

  public static function tearDownAfterClass() {
    static::$db->close();
  }

  public function tearDown() {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/history/movie";
    if(is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::getShortName
   */
  public function testGetShortName() {
    $this->assertEquals("movie", get_reflection_method($this->movie, "getShortName")->invoke($this->movie));
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::createRepository
   */
  public function testCreateRepository() {
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/.git/HEAD");
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::hideRepository
   */
  public function testHideRepository() {
    get_reflection_method($this->movie, "hideRepository")->invoke($this->movie);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/.2");
  }

  /**
   * @expectedException \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Repository already hidden
   * @covers \Movlib\Data\History\AbstractHistory::hideRepository
   * @depends testHideRepository
   */
  public function testHideRepositoryIfHidden() {
    $f = get_reflection_method($this->movie, "hideRepository");
    $f->invoke($this->movie);
    $f->invoke($this->movie);
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::unhideRepository
   */
  public function testUnhideRepository() {
    get_reflection_method($this->movie, "hideRepository")->invoke($this->movie);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/.2");

    get_reflection_method($this->movie, "unhideRepository")->invoke($this->movie);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2");
  }

  /**
   * @expectedException \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Repository not hidden
   * @covers \Movlib\Data\History\AbstractHistory::unhideRepository
   */
  public function testUnhideRepositoryIfNotHidden() {
    get_reflection_method($this->movie, "unhideRepository")->invoke($this->movie);
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::startEditing
   * @covers \Movlib\Data\History\AbstractHistory::getCommitHash
   */
  public function testStartEditing() {
    static::$db->query("UPDATE `movies` SET `commit` = 'b006169990b07af17d198f6a37efb324ced95fb3' WHERE `movie_id` = 2");
    $this->movie->startEditing();
    $p = get_reflection_property($this->movie, "commitHash");
    $this->assertNotNull($p->getValue($this->movie));
    $this->assertEquals("b006169990b07af17d198f6a37efb324ced95fb3", $p->getValue($this->movie));
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::writeFiles
   */
  public function testWriteFiles() {
    // wrong offset name
    $this->movie->writeFiles(["foo" => "bar"]);
    $this->assertFileNotExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/foo");

    // offset which should be written to file directly
    $this->movie->writeFiles(["original_title" => "The Shawshank Redemption"]);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title");
    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title", "The Shawshank Redemption");

    // offset with language prefix which should be written to file directly
    $this->movie->writeFiles(["en_synopsis" => "A very short synopsis."]);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/en_synopsis");
    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/en_synopsis", "A very short synopsis.");

    // no file should be written if the offset is not set
    $this->assertFileNotExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/de_synopsis");

    // offset which should be written to file serialized
    $this->movie->writeFiles([ "titles" => [[ "id" => 1, "title" => "foo" ], [ "id" => 2, "title" => "bar" ]] ]);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/titles");
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/titles",
      'a:2:{i:0;a:2:{s:2:"id";i:1;s:5:"title";s:3:"foo";}i:1;a:2:{s:2:"id";i:2;s:5:"title";s:3:"bar";}}'
    );
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::stageAllFiles
   * @covers \Movlib\Data\History\AbstractHistory::unstageFiles
   * @covers \Movlib\Data\History\AbstractHistory::resetFiles
   * @covers \Movlib\Data\History\AbstractHistory::commitFiles
   */
  public function testGitHelperMethodes() {
    // reflected properties
    $path = get_reflection_property($this->movie, "path")->getValue($this->movie);

    // reflected methodes
    $stageAllFiles = get_reflection_method($this->movie, "stageAllFiles");

    // write files
    $this->movie->writeFiles([ "original_title" => "The foobar is a lie", "year" => 2000, "runtime" => 42 ]);

    // stage all files
    $stageAllFiles->invoke($this->movie);
    exec("cd {$path} && git status", $output);
    $this->assertEquals("# Changes to be committed:", $output[1]);
    $this->assertEquals("#	new file:   original_title", $output[4]);
    $this->assertEquals("#	new file:   runtime", $output[5]);
    $this->assertEquals("#	new file:   year", $output[6]);

    // commit all staged files
    get_reflection_method($this->movie, "commitFiles")->invoke($this->movie, "movie created");
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertEquals("nothing to commit (working directory clean)", $output[1]);

    // update files
    $this->movie->writeFiles([ "original_title" => "The foobar is not a lie", "year" => 2001, "runtime" => 42 ]);

    // stage all files
    $stageAllFiles->invoke($this->movie);
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertEquals("# Changes to be committed:", $output[1]);
    $this->assertEquals("#	modified:   original_title", $output[4]);
    $this->assertEquals("#	modified:   year", $output[5]);

    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/year", 2001);

    // unstage year
    get_reflection_method($this->movie, "unstageFiles")->invoke($this->movie, [ "year" ]);
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertEquals("# Changes not staged for commit:", $output[6]);
    $this->assertEquals("#	modified:   year", $output[10]);

    // reset year
    get_reflection_method($this->movie, "resetFiles")->invoke($this->movie, [ "year" ]);
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/year", 2000);
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::getChangedFiles
   * @covers \Movlib\Data\History\AbstractHistory::getDirtyFiles
   */
  public function testGetChangedFiles() {
    $stageAllFiles = get_reflection_method($this->movie, "stageAllFiles");
    $commitFiles = get_reflection_method($this->movie, "commitFiles");

    $this->movie->writeFiles([ "original_title" => "The foobar is not a lie", "year" => 2001, "runtime" => 42 ]);
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "initial commit");

    // with unstaged files
    $this->movie->writeFiles([ "original_title" => "The foobar is a lie", "year" => 2002, "runtime" => 42 ]);
    $dirtyFiles = get_reflection_method($this->movie, "getDirtyFiles")->invoke($this->movie);
    $this->assertEquals("original_title year", implode(" ", $dirtyFiles));

    // with 2 commits
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "second commit");
    $changedFiles = get_reflection_method($this->movie, "getChangedFiles")->invoke($this->movie, "HEAD", "HEAD^1");
    $this->assertEquals("original_title year", implode(" ", $changedFiles));
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::getLastCommits
   */
  public function testGetLastCommits() {
    $stageAllFiles  = get_reflection_method($this->movie, "stageAllFiles");
    $commitFiles    = get_reflection_method($this->movie, "commitFiles");
    $getLastCommits = get_reflection_method($this->movie, "getLastCommits");

    $this->movie->writeFiles([ "original_title" => "The foobar is a lie" ]);
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "initial commit");

    $this->movie->writeFiles([ "year" => 2001 ]);
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "second commit");

    $this->movie->writeFiles([ "runtime" => 300 ]);
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "third commit");

    $commits = $getLastCommits->invoke($this->movie);
    $this->assertEquals("third commit", $commits[0]["subject"]);
    $this->assertEquals("second commit", $commits[1]["subject"]);
    $this->assertEquals("initial commit", $commits[2]["subject"]);

    $oneCommit = $getLastCommits->invoke($this->movie, 1);
    $this->assertEquals("third commit", $oneCommit[0]["subject"]);
    $this->assertCount(1, $oneCommit);
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::getLastCommitHash
   * @depends testGetLastCommits
   */
  public function testGetLastCommitHash() {
    $this->movie->writeFiles(["original_title" => "The foobar is a lie"]);
    get_reflection_method($this->movie, "stageAllFiles")->invoke($this->movie);
    get_reflection_method($this->movie, "commitFiles")->invoke($this->movie, "initial commit");
    $this->assertEquals(
      get_reflection_method($this->movie, "getLastCommitHash")->invoke($this->movie),
      get_reflection_method($this->movie, "getLastCommits")->invoke($this->movie)[0]["hash"]
    );
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::getDiffasHTML
   */
  public function testGetDiffAsHTML() {
    $stageAllFiles  = get_reflection_method($this->movie, "stageAllFiles");
    $commitFiles    = get_reflection_method($this->movie, "commitFiles");

    $this->movie->writeFiles(["original_title" => "The foobar is a lie"]);
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "initial commit");

    $this->movie->writeFiles(["original_title" => "The bar is not a lie"]);
    $stageAllFiles->invoke($this->movie);
    $commitFiles->invoke($this->movie, "second commit");

    $diff = $this->movie->getDiffasHTML("HEAD", "HEAD^1", "original_title");
    $this->assertEquals(" The", $diff[5]);
    $this->assertEquals("-foobar", $diff[6]);
    $this->assertEquals("+bar", $diff[7]);
    $this->assertEquals("  is", $diff[8]);
    $this->assertEquals("+not", $diff[9]);
    $this->assertEquals("  a lie", $diff[10]);

//    $this->assertEquals(
//      "The<span class='red'>foobar</span><span class='green'>bar</span> is<span class='green'>not</span> a lie",
//      $this->movie->getDiffasHTML("HEAD", "HEAD^1", "original_title")
//    );
  }

  /**
   * @expectedException \MovLib\Exception\HistoryException
   * @expectedExceptionMessage startEditing() has to be called before saveHistory()!
   * @covers \Movlib\Data\History\AbstractHistory::saveHistory
   */
  public function testSaveHistoryWithoutStartEditing() {
    $this->movie->saveHistory([], "initial commit");
  }

  /**
   * @covers \Movlib\Data\History\AbstractHistory::saveHistory
   */
  public function testSaveHistory() {
    $this->movie->startEditing();
    $this->movie->saveHistory([ "original_title" => "The foobar is a lie"], "initial commit" );
    $this->assertFileExists(("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title"));
  }

  /**
   * @expectedException \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Someone else edited the same information about the movie!
   * @covers \Movlib\Data\History\AbstractHistory::saveHistory
   */
  public function testSaveHistoryIfSomeoneElseAlreadyChangedTheSameInformation() {
    $this->movie->startEditing();
    $this->movieUserOne = $this->movie;
    $this->movieUserTwo = clone $this->movie;

    $commitHash = $this->movieUserOne->saveHistory([ "original_title" => "The foobar is a lie" ], "initial commit");
    static::$db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");

    $this->movieUserTwo->saveHistory([ "original_title" => "The bar is not a lie" ], "initial commit");

    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title", "The foobar is a lie");
  }

}
