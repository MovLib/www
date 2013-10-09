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
use \MovLib\Data\Collator;
use \MovLib\Data\I18n;
use \MovLib\Data\Movie;
use \MovLib\Data\Image\Movie as MovieImage;

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
   * @covers ::__construct
   * @expectedException \MovLib\Exception\MovieException
   */
  public function testConstructInvalidId() {
    new Movie(-1);
  }

  /**
   * @covers ::getAwards
   */
  public function testGetAwardsEmpty() {
    $awards = (new Movie(1))->getAwards();
    $this->assertEmpty($awards);
    $this->assertTrue(is_array($awards), "Awards not returned as array!");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getAwards
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetAwardsWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n = new I18n("ja_JP");
    // Retrieve all award names for our test movie and sort them by name.
    $dbAwards = (new Database())->select(
      "SELECT
        `a`.`name` AS `name`,
        COLUMN_GET(`a`.`dyn_names`, 'ja' AS BINARY) AS `name_localized`,
        `ma`.`award_count`
        FROM `movies_awards` `ma`
          INNER JOIN `awards` `a`
          ON `ma`.`award_id` = `a`.`award_id`
        WHERE `ma`.`movie_id` = ?",
      "d",
      [ $this->movie->id ]
    );
    $c = count($dbAwards);
    $tmpDbAwards = [];
    for ($i = 0; $i < $c; ++$i) {
      $dbAwards[$i]["name"] = empty($dbAwards[$i]["name_localized"]) ? $dbAwards[$i]["name"] : $dbAwards[$i]["name_localized"];
      $tmpDbAwards["{$dbAwards[$i]["name"]}{$dbAwards[$i]["award_count"]}"] = $dbAwards[$i]["name"];
    }
    (new Collator("ja_JP"))->ksort($tmpDbAwards);
    $awardNames = array_values($tmpDbAwards);

    $awards = $this->movie->getAwards();
    $this->assertCount($c, $awards);

    $c = count($awards);
    for ($i = 0; $i < $c; ++$i) {
      // Check if the name is correct (Also tests name fallbacks).
      $this->assertEquals($awardNames[$i], $awards[$i]["name"]);
      $this->assertArrayHasKey("id", $awards[$i]);
      $this->assertArrayHasKey("award_count", $awards[$i]);
      $this->assertArrayHasKey("year", $awards[$i]);
      $this->assertArrayHasKey("won", $awards[$i]);
    }
    $i18n = $i18nBackup;
  }

  /**
   * @covers ::getCountries
   */
  public function testGetCountriesEmpty() {
    $countries = (new Movie(null, [ "id" => -1 ]))->getCountries();
    $this->assertEmpty($countries);
    $this->assertTrue(is_array($countries), "Countries not returned as array!");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getCountries
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetCountriesWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n = new I18n("de_AT");

    $dbCountries = (new Database())->select(
      "SELECT
        `c`.`country_id` AS `id`,
        `c`.`iso_alpha-2` AS `code`,
        COLUMN_GET(`c`.`dyn_translations`, ? AS CHAR(255)) AS `name`
      FROM `movies_countries` `mc`
        INNER JOIN `countries` `c`
        ON `mc`.`country_id` = `c`.`country_id`
      WHERE `mc`.`movie_id` = ?",
      "sd",
      [ $i18n->languageCode, $this->movie->id ]
    );
    $c = count($dbCountries);
    $tmpCountries = [];
    for ($i = 0; $i < $c; ++$i) {
      $tmpCountries[$dbCountries[$i]["name"]] = $dbCountries[$i];
    }
    (new Collator("de_AT"))->ksort($tmpCountries);
    $dbCountries = array_values($tmpCountries);

    $countries = $this->movie->getCountries();
    $this->assertCount($c, $countries);
    for ($i = 0; $i < $c; ++$i) {
      $this->assertEquals($dbCountries[$i], $countries[$i]);
    }

    $i18n = $i18nBackup;
  }

  /**
   * @covers ::getDisplayPoster
   */
  public function testGetDisplayPosterEmpty() {
    $poster = (new Movie(null, [ "id" => -1]))->getDisplayPoster();
    $this->assertEquals((new MovieImage(-1, MovieImage::IMAGETYPE_POSTER)), $poster);
  }

  /**
   * @todo Test when Movie image is fixed.
   * @covers ::getDisplayPoster
   */
  public function testGetDisplayPosterWithDataAndMovieTitle() {
//    $dbPosterId = (new Database())->select(
//      "SELECT
//        `image_id` AS `id`
//      FROM `movies_images`
//      WHERE `movie_id` = ? AND `type` = ?
//      ORDER BY `upvotes` DESC
//      LIMIT 1",
//      "di",
//      [ $this->movie->id, MovieImage::IMAGETYPE_POSTER ]
//    );
//    $dbPoster = new MovieImage($this->movie->id, MovieImage::IMAGETYPE_POSTER, $dbPosterId[0]["id"], $this->movie->originalTitle);
//
//    $poster = $this->movie->getDisplayPoster($this->movie->originalTitle);
//    $this->assertEquals($dbPoster, $poster);
  }

  /**
   * @covers ::getDisplayTitle
   */
  public function testGetDisplayTitleFallback() {
    $originalTitle = "PHPUnit";
    $this->assertEquals($originalTitle, (new Movie(null, [ "id" => -1, "originalTitle" => $originalTitle ]))->getDisplayTitle());
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getDisplayTitle
   */
  public function testGetDisplayTitleWithData() {
    global $i18n;
    $dbTitle = (new Database())->select(
      "SELECT
        `title`
      FROM `movies_titles`
      WHERE `movie_id` = ?
        AND `is_display_title` = true
        AND `language_id` = ?
      LIMIT 1",
      "di",
      [ $this->movie->id, $i18n->getLanguages(I18n::KEY_CODE)[$i18n->languageCode][I18n::KEY_ID] ]
    )[0]["title"];

    $this->movie->originalTitle = "PHPUnit";
    $this->assertEquals($dbTitle, $this->movie->getDisplayTitle());
  }

  /**
   * @covers ::getGenres
   */
  public function testGetGenresEmpty() {
    $genres = (new Movie(null, [ "id" => -1 ]))->getGenres();
    $this->assertEmpty($genres);
    $this->assertTrue(is_array($genres), "Genres not returned as array!");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getGenres
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetGenresWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n = new I18n("de_AT");

    $dbGenres = (new Database())->select(
      "SELECT
        `g`.`genre_id`,
        `g`.`name`,
        COLUMN_GET(`g`.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`
      FROM `movies_genres` `mg`
        INNER JOIN `genres` `g`
          ON `mg`.`genre_id` = `g`.`genre_id`
      WHERE `mg`.`movie_id` = ?",
      "d",
      [ $this->movie->id ]
    );
    $c = count($dbGenres);
    $tmpGenres = [];
    for ($i = 0; $i < $c; ++$i) {
      $dbGenres[$i]["name"] = $dbGenres[$i]["name_localized"] ?: $dbGenres[$i]["name"];
      $tmpGenres[$dbGenres[$i]["name"]] = $dbGenres[$i];
    }
    (new Collator("de_AT"))->ksort($tmpGenres);
    $dbGenres = array_values($tmpGenres);

    $genres = $this->movie->getGenres();
    $this->assertCount($c, $genres);
    for ($i = 0; $i < $c; ++$i) {
      $this->assertEquals($dbGenres[$i], $genres[$i]);
    }

    $i18n = $i18nBackup;
  }

}
