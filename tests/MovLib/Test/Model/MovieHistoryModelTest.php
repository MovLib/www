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
namespace MovLib\Test\Model;

use \MovLib\Exception\HistoryException;
use \MovLib\Model\MovieHistoryModel;
use \PHPUnit_Framework_TestCase;

/**
 * Test the MovieHistoryModel.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieHistoryModelTest extends PHPUnit_Framework_TestCase {
  /**
   * Fixture after all tests have been executed and class instance is destroyed.
   */
  public static function tearDownAfterClass() {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/history/movie";
    if(is_dir($path)) {
      //exec("rm -Rf {$path}");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests

  public function testWithoutId() {
    try {
      new MovieHistoryModel(null);
    } catch (HistoryException $expected) {
      return;
    }
    $this->fail('An expected exception has not been raised (id is null).');
  }

  public function testGetRelationName() {
    $this->assertEquals("movie", (new MovieHistoryModel(2))->getShortName());
  }

  public function testCreateRepositoryFolder() {
    (new MovieHistoryModel(2))->createRepositoryFolder();
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2");
  }

  public function testInitRepository() {
    (new MovieHistoryModel(2))->initRepository();
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/.git/HEAD");
  }

  public function testWriteFiles() {
    $test = new MovieHistoryModel(2);
    $test->writeFiles();

    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/runtime");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/year");

    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/de_synopsis");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/en_synopsis");

    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_titles");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_taglines");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_links");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_trailers");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_relationships");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_cast");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_crew");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_awards");

    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_styles");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_genres");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_languages");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_countries");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movies_directors");
  }

  public function testWriteToFile() {
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/original_title",
      "The Shawshank Redemption"
    );
  }

  public function writeRelatedRowsToFile() {
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movie_directors",
      '[{"person_id":5}]'
    );
    $this->assertStringEqualsFile(
      "{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/movie_titles",
      '[{"title":"Die Verurteilten","is_display_title":1,"language_id":52,
       "dyn_comments":{"de":"deutsches Kommentar","en":"english comment"}}]'
    );
  }

  public function testCommit() {
    (new MovieHistoryModel(2))->commit(20, "initial commit");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/movie/2/.git/refs/heads/master");
  }

  public function testGetDiffAsHTML() {
    $test = new MovieHistoryModel(2);
    $test->movie[0]["original_title"] = "The Foobar is a lie";
    $test->writeFiles();
    $test->commit(20, "second commit");
    $this->assertEquals("The<span class='red'>Shawshank Redemption</span><span class='green'>Foobar is a lie</span>\n",
      $test->getDiffasHTML("HEAD", "HEAD^1", "original_title"));
  }

  public function testGetChangedFiles() {
    $test = new MovieHistoryModel(2);
    $changed_files = $test->getChangedFiles("HEAD", "HEAD^1");
    $this->assertEquals("original_title", $changed_files[0]);
  }

  public function testGetLastCommits() {
    $result = (new MovieHistoryModel(2))->getLastCommits();
    $this->assertEquals(20, $result[0]["author_id"]);
    $this->assertEquals("second commit", $result[0]["subject"]);
  }

}
