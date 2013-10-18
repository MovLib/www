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
use \MovLib\Presentation\History\MovieHistory;

/**
 * Test the movie history
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieHistoryTest extends \MovLib\Test\TestCase {

  public static function setUpBeforeClass() {
    global $db;
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/phpunitrepos";
    if(is_dir($path)) {
      exec("rm -rf {$path}");
    }

    $_SERVER["MOVIE_ID"] = 2;

    $movie = new Movie(2, "phpunitrepos");
    $commitHash = $movie->createRepository();
    $db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");
  }

  public static function tearDownAfterClass() {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/phpunitrepos";
    if(is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::contentRevisionsPage
   */
  public function testContentRevisionsPage() {
    global $db;
    $movie        = new Movie(2, "phpunitrepos");
    $historyPage  = new MovieHistory("phpunitrepos");

    $this->assertContains(
      "No revisions found",
      $this->invoke($historyPage, "contentRevisionsPage")
    );

    $movie->startEditing();
    $commitHash = $movie->saveHistory([ "original_title" => "The foobar is a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");

    $this->assertContains(
      "added original title",
      $this->invoke($historyPage, "contentRevisionsPage")
    );
    $this->assertContains(
      "<li>Original Title</li>",
      $this->invoke($historyPage, "contentRevisionsPage")
    );

    $movie->startEditing();
    $commitHash = $movie->saveHistory([ "original_title" => "The bar is not a lie", "cast" => [1,2,3] ], "edited original title, added cast");
    $db->query("UPDATE `movies` SET `commit` = '{$commitHash}' WHERE `movie_id` = 2");

    $this->assertContains(
      "edited original title, added cast",
      $this->invoke($historyPage, "contentRevisionsPage")
    );
    $this->assertContains(
      "<li>Cast</li><li>Original Title</li>",
      $this->invoke($historyPage, "contentRevisionsPage")
    );
  }

}
