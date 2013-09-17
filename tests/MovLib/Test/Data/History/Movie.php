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
use \mysqli;
use \ReflectionClass;
use \ReflectionMethod;

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

  /**
   * This methode is called once before all tests.
   */
  public static function setUpBeforeClass() {
    $db = new mysqli();
    $db->real_connect();
    $db->select_db($GLOBALS["movlib"]["default_database"]);
    $db->query("UPDATE movies SET commit = 'b006169990b07af17d198f6a37efb324ced95fb3' WHERE movie_id = 2");
    $db->close();
  }

  /**
   * This methode is called after each test.
   */
  public function tearDown() {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/history/movie";
    if(is_dir($path)) {
      exec("rm -Rf {$path}");
    }
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Could not find movie with ID ''!
   */
  public function testWithoutId() {
      new Movie(null);
  }

  public function testGetShortName() {
    $getShortName = new ReflectionMethod("\MovLib\Data\History\Movie", "getShortName");
    $getShortName->setAccessible(true);
    $this->assertEquals("movie", $getShortName->invoke(new Movie(2)));
  }

  public function testCreateRepository() {
    (new Movie(2))->createRepository();
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/.git/HEAD");
  }

  public function testHideRepository() {
    $movie = new Movie(2);
    $movie->createRepository();

    $hideRepository = new ReflectionMethod("\MovLib\Data\History\Movie", "hideRepository");
    $hideRepository->setAccessible(true);
    $hideRepository->invoke($movie);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/.2");
  }

  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Repository already hidden
   */
  public function testHideRepositoryIfHidden() {
    $movie = new Movie(2);
    $movie->createRepository();

    $hideRepository = new ReflectionMethod("\MovLib\Data\History\Movie", "hideRepository");
    $hideRepository->setAccessible(true);
    $hideRepository->invoke($movie);
    $hideRepository->invoke($movie);
  }

  public function testUnhideRepository() {
    $movie = new Movie(2);
    $movie->createRepository();

    // hide
    $hideRepository = new ReflectionMethod("\MovLib\Data\History\Movie", "hideRepository");
    $hideRepository->setAccessible(true);
    $hideRepository->invoke($movie);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/.2");
    //unhide
    $unhideRepository = new ReflectionMethod("\MovLib\Data\History\Movie", "unhideRepository");
    $unhideRepository->setAccessible(true);
    $unhideRepository->invoke($movie);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2");
  }

  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage Repository not hidden
   */
  public function testUnhideRepositoryIfNotHidden() {
    $movie = new Movie(2);
    $movie->createRepository();

    $unhideRepository = new ReflectionMethod("\MovLib\Data\History\Movie", "unhideRepository");
    $unhideRepository->setAccessible(true);
    $unhideRepository->invoke($movie);
  }

  public function testStartEditing() {
    $movie = new Movie(2);
    $movie->startEditing();

    $reflectionClass = new ReflectionClass('\MovLib\Data\History\Movie');
    $reflectionProperty = $reflectionClass->getProperty('commitHash');
    $reflectionProperty->setAccessible(true);
    $this->assertEquals(
      "b006169990b07af17d198f6a37efb324ced95fb3",
      $reflectionProperty->getValue($movie)
    );
  }


  public function testWriteFiles() {
    $movie = new Movie(2);
    $movie->createRepository();

    // wrong offset name
    $movie->writeFiles(["foo" => "bar"]);
    $this->assertFileNotExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/foo");

    // offset which should be written to file directly
    $movie->writeFiles(["original_title" => "The Shawshank Redemption"]);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title");
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title",
      "The Shawshank Redemption"
    );

    // offset with language prefix which should be written to file directly
    $movie->writeFiles(["en_synopsis" => "A very short synopsis."]);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/en_synopsis");
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/en_synopsis",
      "A very short synopsis."
    );

    // no file should be written if the offset is not set
    $this->assertFileNotExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/de_synopsis");

    // offset which should be written to file serialized
    $movie->writeFiles(["titles" => [["id" => 1, "title" => "foo"], ["id" => 2, "title" => "bar"]]]);
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/titles");
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/titles",
      'a:2:{i:0;a:2:{s:2:"id";i:1;s:5:"title";s:3:"foo";}i:1;a:2:{s:2:"id";i:2;s:5:"title";s:3:"bar";}}'
    );
  }

  public function testGitHelperMethodes() {
    $movie = new Movie(2);
    $movie->createRepository();

    // reflected properties
    $reflectionClass = new ReflectionClass('\MovLib\Data\History\Movie');
    $reflectionProperty = $reflectionClass->getProperty('path');
    $reflectionProperty->setAccessible(true);
    $path = $reflectionProperty->getValue($movie);

    // reflected methodes
    $stageAllFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "stageAllFiles");
    $stageAllFiles->setAccessible(true);
    $unstageFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "unstageFiles");
    $unstageFiles->setAccessible(true);
    $resetFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "resetFiles");
    $resetFiles->setAccessible(true);
    $commitFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "commitFiles");
    $commitFiles->setAccessible(true);

    // write files
    $movie->writeFiles(["original_title" => "The foobar is a lie", "year" => 2000, "runtime" => 42]);

    // stage all files
    $stageAllFiles->invoke($movie);
    exec("cd {$path} && git status", $output);
    $this->assertEquals($output[4], "# Changes to be committed:");
    $this->assertEquals($output[7], "#	new file:   original_title");
    $this->assertEquals($output[8], "#	new file:   runtime");
    $this->assertEquals($output[9], "#	new file:   year");

    // commit all staged files
    $commitFiles->invoke($movie, "movie created");
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertEquals($output[1], "nothing to commit (working directory clean)");

    // update files
    $movie->writeFiles(["original_title" => "The foobar is not a lie", "year" => 2001, "runtime" => 42]);

    // stage all files
    $stageAllFiles->invoke($movie);
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertEquals($output[1], "# Changes to be committed:");
    $this->assertEquals($output[4], "#	modified:   original_title");
    $this->assertEquals($output[5], "#	modified:   year");

    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/year", 2001);

    // unstage year
    $unstageFiles->invoke($movie, ["year"]);
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertEquals($output[6], "# Changes not staged for commit:");
    $this->assertEquals($output[10], "#	modified:   year");

    // reset year
    $resetFiles->invoke($movie, ["year"]);
    unset($output);
    exec("cd {$path} && git status", $output);
    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/year", 2000);
  }

  /**
   * @expectedException        \MovLib\Exception\HistoryException
   * @expectedExceptionMessage No changed files to commit
   */
  public function testCommitWithoutChangedFiles() {
    $movie = new Movie(2);
    $movie->createRepository();

    $commitFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "commitFiles");
    $commitFiles->setAccessible(true);
    $commitFiles->invoke($movie, "movie created without files");
  }

  public function testGetChangedFiles() {
    $movie = new Movie(2);
    $movie->createRepository();

    $stageAllFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "stageAllFiles");
    $stageAllFiles->setAccessible(true);
    $getChangedFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "getChangedFiles");
    $getChangedFiles->setAccessible(true);
    $commitFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "commitFiles");
    $commitFiles->setAccessible(true);

    $movie->writeFiles(["original_title" => "The foobar is not a lie", "year" => 2001, "runtime" => 42]);
    $stageAllFiles->invoke($movie);
    $commitFiles->invoke($movie, "initial commit");

    // with unstaged files
    $movie->writeFiles(["original_title" => "The foobar is a lie", "year" => 2002, "runtime" => 42]);
    $this->assertEquals("original_title year", implode(" ", $getChangedFiles->invoke($movie)));

    // with 2 commits
    $stageAllFiles->invoke($movie);
    $commitFiles->invoke($movie, "second commit");
    $this->assertEquals("original_title year", implode(" ", $getChangedFiles->invoke($movie, "HEAD", "HEAD^1")));
  }

  public function testGetLastCommits() {
    // @todo: implement
  }

  public function testGetLastCommitHash() {
    // @todo: implement
  }

  public function testGetDiffAsHTML() {
    $movie = new Movie(2);
    $movie->createRepository();

    $stageAllFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "stageAllFiles");
    $stageAllFiles->setAccessible(true);
    $commitFiles = new ReflectionMethod("\MovLib\Data\History\Movie", "commitFiles");
    $commitFiles->setAccessible(true);

    $movie->writeFiles(["original_title" => "The foobar is a lie"]);
    $stageAllFiles->invoke($movie);
    $commitFiles->invoke($movie, "initial commit");

    $movie->writeFiles(["original_title" => "The bar is not a lie"]);
    $stageAllFiles->invoke($movie);
    $commitFiles->invoke($movie, "second commit");

    $this->assertEquals(
      "The<span class='red'>foobar</span><span class='green'>bar</span> is<span class='green'>not</span> a lie",
      $movie->getDiffasHTML("HEAD", "HEAD^1", "original_title")
    );
  }

}
