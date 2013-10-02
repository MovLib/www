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
namespace MovLib\Test\Data;

use \MovDev\Database;
use \MovLib\Data\I18n;
use \MovLib\Data\Movie;

/**
 * @coversDefaultClass \MovLib\Data\Movie
 * @group Database
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie data object under test.
   *
   * @var \MovLib\Data\Movie
   */
  public $movie;

  /**
   * The ID of the movie under test.
   *
   * @var int
   */
  public $movieId = 3;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  /**
   * @inheritdoc
   */
  public function setUp() {
    $this->movie = new Movie($this->movieId);
  }

  /**
   * @inheritdoc
   */
  public static function tearDownAfterClass() {
    exec("movdev db -s movies");
  }


  // ------------------------------------------------------------------------------------------------------------------- Test methods


  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::__construct
   */
  public function testConstruct() {
    global $i18n;
    // Set created timestamp and synopsis for testing.
    $db = new Database();
    $created = time();
    $synopsis = "PHPUnit synopsis";
    $db->query(
      "UPDATE `movies` SET `created` = FROM_UNIXTIME(?), `dyn_synopses` = COLUMN_ADD(`dyn_synopses`, ?, ?) WHERE `movie_id` = ?",
      "issd",
      [ $created, $i18n->languageCode, $synopsis, $this->movieId ]
    );
    $movieProperties = [
      "id"            => $this->movieId,
      "originalTitle" => "Léon",
      "rating"        => 0,
      "meanRating"    => 0,
      "votes"         => 0,
      "deleted"       => false,
      "year"          => 1994,
      "runtime"       => 110,
      "rank"          => null,
      "synopsis"      => $synopsis,
      "created"       => $created,
    ];

    // Empty construction.
    $movie = new Movie();
    foreach ($movieProperties as $k => $v) {
      $this->assertEmpty($movie->{$k});
    }

    // Construction from id, also test the weight of the movie id over the properties array.
    $movie = new Movie($movieProperties["id"], [ "id" => "wrong" ]);
    foreach ($movieProperties as $k => $v) {
      $this->assertEquals($v, $movie->{$k});
    }

    // Construction from properties array.
    $movie = new Movie(null, $movieProperties);
    foreach ($movieProperties as $k => $v) {
      $this->assertEquals($v, $movie->{$k});
    }
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getAwards
   */
  public function testGetAwards() {
    global $i18n;

    // Without awards.
    $awards = (new Movie(1))->getAwards();
    $this->assertEmpty($awards);
    $this->assertTrue(is_array($awards));

    // With awards.
    $i18n->languageCode = "ja";
    $dbAwards = [
      /** @todo Implement award asserts. */
    ];
    $awards = $this->movie->getAwards();
    $this->assertCount(10, $awards);

    // Name fallback.
    $japanAcademyPrizeId = 1;
    $japanAcademyPrize = "日本アカデミー賞";
    $czechLionId = 2;
    $czechLion = "Czech Lion";
    $c = count($awards);
    for ($i = 0; $i < $c; ++$i) {
      if ($awards[$i]["id"] === $japanAcademyPrizeId) {
        $this->assertEquals($japanAcademyPrize, $awards[$i]["name"]);
      }
      if ($awards[$i]["id"] === $czechLionId) {
        $this->assertEquals($czechLion, $awards[$i]["name"]);
      }
    }
    $i18n = new I18n();
  }

  /**
   * @covers ::getCast
   */
  public function testGetCast() {
    // Without cast.
    $cast = (new Movie(1))->getCast();
    $this->assertEmpty($cast);
    $this->assertTrue(is_array($cast));

    // With cast.
    /** @todo Implement cast asserts. */
  }

  /**
   * @covers ::getCountries
   */
  public function testGetCountries() {
    /** @todo Implement */
  }

}
