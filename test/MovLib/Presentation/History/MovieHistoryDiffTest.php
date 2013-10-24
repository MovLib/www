<?php

/* !
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
namespace MovLib\Presentation\History;

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
class MovieHistoryDiffTest extends \MovLib\TestCase {

  /** @var \MovLib\Data\History\Movie */
  private $movie;

  /** @var \MovLib\Presentation\History\MovieHistoryDiff */
  private $historyDiffPage;

  public function setUp() {
    global $db;

    $path = "{$_SERVER["DOCUMENT_ROOT"]}/private/phpunitrepos";
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
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/private/phpunitrepos";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::getPageContent
   */
  public function testGetPageContent() {
    $this->assertContains(
      "<a href='/movie/2/history' accesskey='h' class='separator active'",
      $this->invoke($this->historyDiffPage, "getContent")
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::contentDiffPage
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfRevisions
   */
  public function testContentDiffPage() {
    $this->assertContains(
      "Original Title", $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
    $this->assertContains(
      "<span class='green'>The foobar is a lie</span>", $this->invoke($this->historyDiffPage, "contentDiffPage")
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfRevisions
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfStrings
   */
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
      ], $this->invoke($this->historyDiffPage, "formatFileNames", [ $fileNames ])
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   * @covers \Movlib\Data\User\Users::orderById
   */
  public function testDiffIdsWithUsers() {
    $diff = ["added" => [1, 3 ], "removed" => [2 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/users/1' class='green' title='More about Fleshgrinder'>Fleshgrinder</a></li><li><a "
      . "href='/users/3' class='green' title='More about Ravenlord'>Ravenlord</a></li><li><a href='/users/2' class='red' "
      . "title='More about ftorghele'>ftorghele</a></li></ul>", $this->invoke($this->historyDiffPage, "diffIds", [ $diff, "\MovLib\Data\User\Users" ])->__toString()
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   * @covers \MovLib\Presentation\History\TraitHistory::getCountries
   * @covers \Movlib\Data\Counties::orderById
   */
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

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   * @covers \MovLib\Presentation\History\TraitHistory::getDirectors
   * @covers \Movlib\Data\User\Persons::orderById
   */
  public function testGetDirectors() {
    $diff = ["added" => [1, 3 ], "removed" => [2, 4 ], "edited" => [ ] ];
    $this->assertEquals(
      "<ul><li><a href='/persons/1' class='green' title='More about Luc Besson'>Luc Besson</a></li><li><a href='/persons/3' "
      . "class='green' title='More about Natalie Portman'>Natalie Portman</a></li><li><a href='/persons/2' class='red' "
      . "title='More about Jean Reno'>Jean Reno</a></li><li><a href='/persons/4' class='red' title='More about Gary "
      . "Oldman'>Gary Oldman</a></li></ul>", $this->invoke($this->historyDiffPage, "getDirectors", [ $diff ])->__toString()
    );
  }

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   * @covers \MovLib\Presentation\History\TraitHistory::getGenres
   * @covers \Movlib\Data\User\Genres::orderById
   */
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

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   * @covers \MovLib\Presentation\History\TraitHistory::getLanguages
   * @covers \Movlib\Data\User\Languages::orderById
   */
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

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffIds
   * @covers \MovLib\Presentation\History\TraitHistory::getStyles
   * @covers \Movlib\Data\User\Styles::orderById
   */
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

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffArray
   * @covers \MovLib\Presentation\History\TraitHistory::diffArrayItems
   * @covers \MovLib\Presentation\History\TraitHistory::getCast
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfStrings
   * @covers \Movlib\Data\User\Persons::orderById
   */
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

  /**
   * @covers \MovLib\Presentation\History\TraitHistory::diffArray
   * @covers \MovLib\Presentation\History\TraitHistory::diffArrayItems
   * @covers \MovLib\Presentation\History\TraitHistory::getCrew
   * @covers \MovLib\Presentation\History\TraitHistory::textDiffOfStrings
   * @covers \Movlib\Data\User\Persons::orderById
   */
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
  }

}
