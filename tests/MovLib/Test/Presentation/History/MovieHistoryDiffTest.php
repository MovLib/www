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
namespace MovLib\Test\Presentation\History;

use \MovLib\Data\History\Movie;
use \MovLib\Presentation\History\MovieHistoryDiff;

/**
 * Test the movie history
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieHistoryDiffTest extends \MovLib\Test\TestCase {

  /** @var \mysqli */
  static $db;

  /** @var \MovLib\Data\History\Movie */
  private $movie;

  /** @var \MovLib\Presentation\History\MovieHistoryDiff */
  private $historyDiffPage;

  public static function setUpBeforeClass() {
    static::$db = new \mysqli();
    static::$db->real_connect();
    static::$db->select_db($GLOBALS["movlib"]["default_database"]);

    $path = "{$_SERVER["DOCUMENT_ROOT"]}/phpunitrepos";
    if(is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }

  public static function tearDownAfterClass() {
    static::$db->close();
  }

  public function setUp() {
    $_SERVER["MOVIE_ID"] = 2;

    $this->historyDiffPage = new MovieHistoryDiff("phpunitrepos");

    $this->movie = new Movie($_SERVER["MOVIE_ID"], "phpunitrepos");
    $_SERVER["REVISION_HASH"] = $this->movie->createRepository();
    static::$db->query("UPDATE `movies` SET `commit` = '{$_SERVER["REVISION_HASH"]}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");

    $this->movie->startEditing();
    $_SERVER["REVISION_HASH"] = $this->movie->saveHistory([ "original_title" => "The foobar is a lie" ], "added original title");
    static::$db->query("UPDATE `movies` SET `commit` = '{$_SERVER["REVISION_HASH"]}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");
  }

  public function tearDown() {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/phpunitrepos";
    if(is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::contentDiffPage
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfRevisions
   */
  public function testContentDiffPage() {
    $this->assertContains(
      "Original Title",
      $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
    $this->assertContains(
      "<span class='green'>The foobar is a lie</span>",
      $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfRevisions
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfStrings
   */
  public function testTextDiff() {
    $this->movie->startEditing();
    $_SERVER["REVISION_HASH"] = $this->movie->saveHistory([ "original_title" => "The bar is not a lie" ], "added original title");
    static::$db->query("UPDATE `movies` SET `commit` = '{$_SERVER["REVISION_HASH"]}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");

    $this->assertContains(
      "Original Title",
      $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
    $this->assertContains(
      "The <span class='red'>foo</span>bar is <span class='green'>not </span>a lie",
      $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::formatFileNames
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
      ],
      $this->invoke($this->historyDiffPage, "formatFileNames", [ $fileNames ])
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   */
  public function testDiffIdsWithUsers() {
    $diff = ["added" => [1,3], "removed" => [2], "edited" => []];

    $listItems = $this->invoke($this->historyDiffPage, "diffIds", [ $diff, "\MovLib\Data\Users" ]);

    var_dump($listItems);

    $this->assertEquals(
      "...",
      $listItems
    );
  }

}
