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

  /** @var \MovLib\Data\History\Movie */
  private $movie;

  /** @var \MovLib\Presentation\History\MovieHistoryDiff */
  private $historyDiffPage;

  public function setUp() {
    global $config, $db;

    $path = "{$config->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }

    $_SERVER["MOVIE_ID"] = 2;

    $this->historyDiffPage    = new MovieHistoryDiff("phpunitrepos");
    $this->movie              = new Movie($_SERVER["MOVIE_ID"], "phpunitrepos");
    $_SERVER["REVISION_HASH"] = $this->movie->createRepository();
    $db->query("UPDATE `movies` SET `commit` = '{$_SERVER["REVISION_HASH"]}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");

    $this->movie->startEditing();
    $_SERVER["REVISION_HASH"] = $this->movie->saveHistory([ "original_title" => "The foobar is a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$_SERVER["REVISION_HASH"]}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");
    }

  public function tearDown() {
    global $config;
    $path = "{$config->documentRoot}/private/phpunitrepos";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }

  public function testGetPageContent() {
    $this->assertContains(
      "<a href='/movie/2/history' accesskey='h' class='separator active'",
      $this->invoke($this->historyDiffPage, "getContent")
    );
  }

  public function testContentDiffPage() {
    $this->assertContains(
      "Original Title", $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
    $this->assertContains(
      "<span class='green'>The foobar is a lie</span>", $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
  }

  public function testTextDiff() {
    global $db;
    $this->movie->startEditing();
    $_SERVER["REVISION_HASH"] = $this->movie->saveHistory([ "original_title" => "The bar is not a lie" ], "added original title");
    $db->query("UPDATE `movies` SET `commit` = '{$_SERVER["REVISION_HASH"]}' WHERE `movie_id` = {$_SERVER["MOVIE_ID"]}");

    $this->assertContains(
      "Original Title", $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
    $this->assertContains(
      "The <span class='red'>foo</span>bar is <span class='green'>not </span>a lie", $this->invoke($this->historyDiffPage, "contentDiffPage")
    );

    $from = "The bar is not a lie";
    $to   = "The foobar is a lie";
    $this->assertContains(
      "The <span class='red'>bar</span> <span class='green'>foobar</span> is <span class='red'>not</span> a lie",
      $this->invoke($this->historyDiffPage, "textDiffOfStrings", [$from, $to])
    );
  }

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
      ], $this->invoke($this->historyDiffPage, "formatFileNames", [ $fileNames ])
    );
  }

  public function testDiffIdsWithUsers() {
    $diff = ["added" => [1, 3 ], "removed" => [2 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/users/1' class='green' title='More about Fleshgrinder'>Fleshgrinder</a></li><li><a "
      . "href='/users/3' class='green' title='More about Ravenlord'>Ravenlord</a></li><li><a href='/users/2' class='red' "
      . "title='More about ftorghele'>ftorghele</a></li></ul>", $this->invoke($this->historyDiffPage, "diffIds", [ $diff, "\MovLib\Data\User\Users" ])->__toString()
    );
  }

  public function testGetCountries() {
    global $i18n;
    $diff = ["added" => [1, 3 ], "removed" => [2 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/countries/1' class='green' title='More about Andorra'>Andorra</a></li><li><a href='/countries/3' "
      . "class='green' title='More about Afghanistan'>Afghanistan</a></li><li><a href='/countries/2' class='red' "
      . "title='More about United Arab Emirates'>United Arab Emirates</a></li></ul>", $this->invoke($this->historyDiffPage, "getCountries", [ $diff ])->__toString()
    );

    $i18n = new \MovLib\Data\I18n("de-at");
    $this->assertContains(
      "Vereinigte Arabische Emirate", $this->invoke($this->historyDiffPage, "getCountries", [ $diff ])->__toString()
    );
    $i18n = new \MovLib\Data\I18n();
  }

  public function testGetDirectors() {
    $diff = ["added" => [1, 3 ], "removed" => [2, 4 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/persons/1' class='green' title='More about Luc Besson'>Luc Besson</a></li><li><a href='/persons/3' "
      . "class='green' title='More about Natalie Portman'>Natalie Portman</a></li><li><a href='/persons/2' class='red' "
      . "title='More about Jean Reno'>Jean Reno</a></li><li><a href='/persons/4' class='red' title='More about Gary "
      . "Oldman'>Gary Oldman</a></li></ul>", $this->invoke($this->historyDiffPage, "getDirectors", [ $diff ])->__toString()
    );
  }

  public function testGetGenres() {
    global $i18n;
    $diff = ["added" => [1, 3 ], "removed" => [2, 4 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/genres/1' class='green' title='More about Action'>Action</a></li><li><a href='/genres/3' class="
      . "'green' title='More about Animation'>Animation</a></li><li><a href='/genres/2' class='red' title='More about "
      . "Adventure'>Adventure</a></li><li><a href='/genres/4' class='red' title='More about Biography'>Biography</a></li></ul>", $this->invoke($this->historyDiffPage, "getGenres", [ $diff ])->__toString()
    );

    $i18n = new \MovLib\Data\I18n("de-at");
    $this->assertContains(
      "Abenteuer", $this->invoke($this->historyDiffPage, "getGenres", [ $diff ])->__toString()
    );
    $i18n = new \MovLib\Data\I18n();
  }

  public function testGetLanguages() {
    global $i18n;
    $diff = ["added" => [1, 3 ], "removed" => [2, 4 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/languages/1' class='green' title='More about Abkhazian'>Abkhazian</a></li><li><a href='/languages/3' "
      . "class='green' title='More about Afrikaans'>Afrikaans</a></li><li><a href='/languages/2' class='red' title='More "
      . "about Afar'>Afar</a></li><li><a href='/languages/4' class='red' title='More about Akan'>Akan</a></li></ul>", $this->invoke($this->historyDiffPage, "getLanguages", [ $diff ])->__toString()
    );

    $i18n = new \MovLib\Data\I18n("de-at");
    $this->assertContains(
      "Abchasisch", $this->invoke($this->historyDiffPage, "getLanguages", [ $diff ])->__toString()
    );
    $i18n = new \MovLib\Data\I18n();
  }

  public function testGetStyles() {
    global $i18n;
    $diff = ["added" => [1, 3 ], "removed" => [2, 4 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/styles/1' class='green' title='More about Film noir'>Film noir</a></li><li><a href='/styles/3' "
      . "class='green' title='More about Neo-noir'>Neo-noir</a></li><li><a href='/styles/2' class='red' title='More about "
      . "Color film noir'>Color film noir</a></li><li><a href='/styles/4' class='red' title='More about Cinema verite'>"
      . "Cinema verite</a></li></ul>", $this->invoke($this->historyDiffPage, "getStyles", [ $diff ])->__toString()
    );

    $i18n = new \MovLib\Data\I18n("de-at");
    // no translation in db use name (en)
    $this->assertContains(
      "Color film noir", $this->invoke($this->historyDiffPage, "getStyles", [ $diff ])->__toString()
    );
    $i18n = new \MovLib\Data\I18n();
  }

  public function testGetCast() {
    $diff = ["added" => [
      ["id" => 4, "roles" => "franz"]
    ], "removed" => [
      ["id" => 1, "roles" => "markus"]
    ], "edited" => [
      ["id" => 3, "roles" => "Mike", "old" => ["id" => 3, "roles" => "Michael"] ]
    ]];
    $this->assertEquals(
      "<ul><li><a href='/persons/1' class='red' title='Information about Luc Besson'>Luc Besson</a></li><li><a href="
      . "'/persons/4' class='green' title='Information about Gary Oldman'>Gary Oldman</a></li><li><a href='/persons/3' "
      . "class='' title='Information about Natalie Portman'>Natalie Portman</a><ul class=''><li><span class='property-name'>"
      . "roles:</span> Mi<span class='red'>k</span><span class='green'>cha</span>e<span class='green'>l</span></li></ul></li></ul>",
      $this->invoke($this->historyDiffPage, "getCast", [ $diff ])->__toString()
    );
  }

  public function testDiffArrayWithCrew() {
//    $diff = ["added" => [
//
//    ], "removed" => [
//
//    ], "edited" => [
//
//    ]];
//    $this->assertEquals(
//      "",
//      $this->invoke($this->historyDiffPage, "getCrew", [ $diff ])->__toString()
//    );
    $this->markTestIncomplete();
  }

  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getAwards
   * @todo Implement getAwards
   */
  public function testGetAwards() {
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

  /**
   * @covers ::getBreadcrumbs
   * @todo Implement getBreadcrumbs
   */
  public function testGetBreadcrumbs() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::contentRevisionsPage
   * @todo Implement contentRevisionsPage
   */
  public function testContentRevisionsPage() {
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
   * @covers ::getDiff
   * @todo Implement getDiff
   */
  public function testGetDiff() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::textDiffOfRevisions
   * @todo Implement textDiffOfRevisions
   */
  public function testTextDiffOfRevisions() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::textDiffOfStrings
   * @todo Implement textDiffOfStrings
   */
  public function testTextDiffOfStrings() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
