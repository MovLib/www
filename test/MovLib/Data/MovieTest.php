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
namespace MovLib\Data;

use \MovLib\Data\Collator;
use \MovLib\Data\I18n;
use \MovLib\Data\Movie;

/**
 * @coversDefaultClass \MovLib\Data\Movie
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Movie */
  protected $movie;

  protected $movieId = 4;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  protected function setUp() {
    $this->movie = new Movie($this->movieId);
  }

  /**
   * @inheritdoc
   */
  public static function tearDownAfterClass() {
    self::exec("movlib si -d movies");
  }

  // ------------------------------------------------------------------------------------------------------------------- Test methods

  /**
   * @global \MovDev\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @covers ::__construct
   */
  public function testConstruct() {
    global $db, $i18n;
    // Set created timestamp and synopsis for testing.
    $created         = time();
    $synopsisBackup  = $db->query(
        "SELECT COLUMN_GET(`dyn_synopses`, ? AS BINARY) AS `synopsis` FROM `movies` WHERE `movie_id` = ? LIMIT 1", "sd", [ $i18n->languageCode, $this->movieId ]
      )->get_result()->fetch_row()[0];
    $synopsis        = "PHPUnit synopsis";
    $db->query(
      "UPDATE `movies` SET `created` = FROM_UNIXTIME(?), `dyn_synopses` = COLUMN_ADD(`dyn_synopses`, ?, ?) WHERE `movie_id` = ?", "issd", [ $created, $i18n->languageCode, $synopsis, $this->movieId ]
    );
    $movieProperties = [
      "id"            => $this->movieId,
      "originalTitle" => "Big Buck Bunny",
      "rating"        => 0,
      "meanRating"    => 0,
      "votes"         => 0,
      "deleted"       => false,
      "year"          => 2008,
      "runtime"       => 10,
      "rank"          => null,
      "synopsis"      => $synopsis,
      "website"       => "http://www.bigbuckbunny.org/",
      "created"       => $created,
    ];

    // Empty construction.
    $movie = new Movie();
    foreach ($movieProperties as $k => $v) {
      $this->assertEmpty($movie->{$k});
    }

    // Construction from id.
    $movie = new Movie($movieProperties["id"]);
    foreach ($movieProperties as $k => $v) {
      $this->assertEquals($v, $movie->{$k});
    }

    $db->query(
      "UPDATE `movies` SET `created` = FROM_UNIXTIME(?), `dyn_synopses` = COLUMN_ADD(`dyn_synopses`, ?, ?) WHERE `movie_id` = ?", "issd", [ $created, $i18n->languageCode, $synopsisBackup, $this->movieId ]
    );
  }

  /**
   * @covers ::__construct
   * @expectedException \MovLib\Exception\MovieException
   */
  public function testConstructInvalidId() {
    new Movie(-1);
  }

  /**
   * Checks if the given getter returns an empty array.
   *
   * @param string $getterName
   *   The method under test.
   */
  private function _testEmptyResultArray($getterName) {
    $result = (new Movie(null, [ "id" => -1 ]))->{"get{$getterName}"}();
    $this->assertEmpty($result);
    $this->assertTrue(is_array($result), "{$getterName} not returned as array!");
  }

  /**
   * @covers ::getAwards
   */
  public function testGetAwardsEmpty() {
    $this->_testEmptyResultArray("Awards");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getAwards
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetAwardsWithData() {
    global $i18n;
    $i18nBackup  = $i18n;
    $i18n        = new I18n("ja_JP");
    // Retrieve all award names for our test movie and sort them by name.
    $dbAwards    = (new Database())->query(
        "SELECT
        `a`.`name` AS `name`,
        COLUMN_GET(`a`.`dyn_names`, 'ja' AS BINARY) AS `name_localized`,
        `ma`.`award_count`
        FROM `movies_awards` `ma`
          INNER JOIN `awards` `a`
          ON `ma`.`award_id` = `a`.`award_id`
        WHERE `ma`.`movie_id` = ?", "d", [ $this->movie->id ]
      )->get_result()->fetch_all(MYSQLI_ASSOC);
    $c           = count($dbAwards);
    $tmpDbAwards = [ ];
    for ($i = 0; $i < $c; ++$i) {
      $dbAwards[$i]["name"]                                                 = empty($dbAwards[$i]["name_localized"]) ? $dbAwards[$i]["name"] : $dbAwards[$i]["name_localized"];
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
    $this->_testEmptyResultArray("Countries");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getCountries
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetCountriesWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n       = new I18n("de_AT");

    $result      = (new Database())->query(
        "SELECT
        `c`.`country_id` AS `id`,
        `c`.`iso_alpha-2` AS `code`,
        COLUMN_GET(`c`.`dyn_translations`, ? AS CHAR(255)) AS `name`
      FROM `movies_countries` `mc`
        INNER JOIN `countries` `c`
        ON `mc`.`country_id` = `c`.`country_id`
      WHERE `mc`.`movie_id` = ?", "sd", [ $i18n->languageCode, $this->movie->id ]
      )->get_result();
    $dbCountries = [ ];
    while ($country     = $result->fetch_object("\\MovLib\\Data\\Country")) {
      $dbCountries[$country->name] = $country;
    }
    (new Collator("de_AT"))->ksort($dbCountries);

    $countries = $this->movie->getCountries();
    $this->assertCount(count($countries), $countries);
    $this->assertEquals($dbCountries, $countries);

    $i18n = $i18nBackup;
  }

  /**
   * @todo Implement when Images are fixed.
   * @covers ::getDisplayPoster
   */
  public function testGetDisplayPosterEmpty() {
//    $poster = (new Movie(null, [ "id" => -1]))->getDisplayPoster();
//    $this->assertEquals((new MovieImage(-1, MovieImage::IMAGETYPE_POSTER)), $poster);
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
    $originalTitle            = "PHPUnit";
    $testMovie                = new Movie();
    $testMovie->originalTitle = $originalTitle;
    $this->assertEquals($originalTitle, $testMovie->getDisplayTitle());
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getDisplayTitle
   */
  public function testGetDisplayTitleWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n       = new I18n("de_AT");

    $dbTitle = (new Database())->query(
        "SELECT
        `title`
      FROM `movies_titles`
      WHERE `movie_id` = ?
        AND `is_display_title` = true
        AND `language_id` = ?
      LIMIT 1", "di", [ $this->movie->id, (new \MovLib\Data\Language(\MovLib\Data\Language::FROM_CODE, $i18n->languageCode))->id ]
      )->get_result()->fetch_row()[0];

    $this->movie->originalTitle = "PHPUnit";
    $this->assertEquals($dbTitle, $this->movie->getDisplayTitle());

    $i18n = $i18nBackup;
  }

  /**
   * @covers ::getGenres
   */
  public function testGetGenresEmpty() {
    $this->_testEmptyResultArray("Genres");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getGenres
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetGenresWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n       = new I18n("de_AT");

    $result    = (new Database())->query(
        "SELECT
        `g`.`genre_id`,
        `g`.`name`,
        COLUMN_GET(`g`.`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`
      FROM `movies_genres` `mg`
        INNER JOIN `genres` `g`
          ON `mg`.`genre_id` = `g`.`genre_id`
      WHERE `mg`.`movie_id` = ?", "d", [ $this->movie->id ]
      )->get_result();
    $dbGenres  = $result->fetch_all(MYSQLI_ASSOC);
    $c         = count($dbGenres);
    $tmpGenres = [ ];
    for ($i = 0; $i < $c; ++$i) {
      $dbGenres[$i]["name"]             = $dbGenres[$i]["name_localized"] ? : $dbGenres[$i]["name"];
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

  /**
   * @covers ::getLanguages
   */
  public function testGetLanguagesEmpty() {
    $this->_testEmptyResultArray("Languages");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getLanguages
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetLanguagesWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n       = new I18n("de_AT");

    $result      = (new Database())->query(
        "SELECT
        `l`.`language_id` AS `id`,
        `l`.`iso_alpha-2` AS `code`,
        COLUMN_GET(`l`.`dyn_translations`, ? AS CHAR(255)) AS `name`
      FROM `movies_languages` `ml`
        INNER JOIN `languages` `l`
        ON `ml`.`language_id` = `l`.`language_id`
      WHERE `ml`.`movie_id` = ?", "sd", [ $i18n->languageCode, $this->movie->id ]
      )->get_result();
    $dbLanguages = [ ];
    while ($language    = $result->fetch_object("\\MovLib\\Data\\Language")) {
      $dbLanguages[$language->name] = $language;
    }
    (new Collator("de_AT"))->ksort($dbLanguages);

    $languages = $this->movie->getLanguages();
    $this->assertCount(($c         = count($dbLanguages)), $languages);
    $this->assertEquals($dbLanguages, $languages);

    $i18n = $i18nBackup;
  }

  /**
   * @todo Test when implemented.
   * @covers ::getRelationships
   */
  public function testGetRelationshipsEmpty() {

  }

  /**
   * @todo Test when implemented.
   * @covers ::getRelationships
   */
  public function testGetRelationshipsWithData() {

  }

  /**
   * @covers ::getStyles
   */
  public function testGetStylesEmpty() {
    $this->_testEmptyResultArray("Styles");
  }

  /**
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getStyles
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetStylesWithData() {
    global $i18n;
    $i18nBackup = $i18n;
    $i18n       = new I18n("de_AT");

    $result    = (new Database())->query(
        "SELECT
        `s`.`style_id` AS `id`,
        `s`.`name` AS `name`,
        COLUMN_GET(`s`.`dyn_names`, ? AS CHAR(255)) AS `name_localized`
      FROM `movies_styles` `ms`
        INNER JOIN `styles` `s`
        ON `ms`.`style_id` = `s`.`style_id`
      WHERE `ms`.`movie_id` = ?", "sd", [ $i18n->languageCode, $this->movie->id ]
      )->get_result();
    $dbStyles  = $result->fetch_all(MYSQLI_ASSOC);
    $c         = count($dbStyles);
    $this->assertGreaterThan(0, $c);
    $tmpStyles = [ ];
    for ($i = 0; $i < $c; ++$i) {
      $dbStyles[$i]["name"]             = $dbStyles[$i]["name_localized"] ? : $dbStyles[$i]["name"];
      $tmpStyles[$dbStyles[$i]["name"]] = $dbStyles[$i];
    }
    (new Collator("de_AT"))->ksort($tmpStyles);
    $dbStyles = array_values($tmpStyles);

    $styles = $this->movie->getStyles();
    $this->assertCount($c, $styles);
    for ($i = 0; $i < $c; ++$i) {
      $this->assertEquals($dbStyles[$i], $styles[$i]);
    }

    $i18n = $i18nBackup;
  }

  /**
   * @covers ::getTagLines
   */
  public function testGetTagLinesEmpty() {
    $this->_testEmptyResultArray("TagLines");
  }

  /**
   * @todo Implement with new Tagline entity.
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getTagLines
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetTagLinesWithData() {
//    global $i18n;
//    $i18nBackup = $i18n;
//    $i18n = new I18n("de_AT");
//    $db = new Database();
//
//    // Insert some test taglines to verify sort order.
//    $db->query(
//      "INSERT INTO `movies_taglines` (`movie_id`, `tagline`, `language_id`, `dyn_comments`) VALUES (?, 'a PHPUnit', 42, ''), (?, 'Z PHPUnit', 42, '')",
//      "dd",
//      [ $this->movie->id, $this->movie->id ]
//    );
//
//    $taglines = $db->query("SELECT `tagline`, `language_id` AS `language` FROM `movies_taglines` WHERE `movie_id` = ?", "d", [ $this->movie->id ])->get_result()->fetch_all(MYSQLI_ASSOC);
//    $c = count($taglines);
//    $this->assertGreaterThan(0, $c);
//    $tmpTagLines = [];
//    $languages = (new \MovLib\Data\Languages())->orderById();
//    for ($i = 0; $i < $c; ++$i) {
//      $taglines[$i]["language"] = $languages[$taglines[$i]["language"]];
//      $tmpTagLines["{$taglines[$i]["tagline"]}{$taglines[$i]["language"]["id"]}"] = $taglines[$i];
//    }
//    (new Collator("de_AT"))->ksort($tmpTagLines);
//    $taglines = array_values($tmpTagLines);
//
//    $movieTaglines = $this->movie->getTagLines();
//    $this->assertCount($c, $movieTaglines);
//    for ($i = 0; $i < $c; ++$i) {
//      $this->assertEquals($taglines[$i], $movieTaglines[$i]);
//    }
//
//    // Delete the test taglines again.
//    $db->query("DELETE FROM `movies_taglines` WHERE `movie_id` = ? AND `tagline` LIKE '%PHPUnit%'", "d", [ $this->movie->id ]);
//    $i18n = $i18nBackup;
  }

  /**
   * @covers ::getTitles
   */
  public function testGetTitlesEmpty() {
    $this->_testEmptyResultArray("Titles");
  }

  /**
   * @todo Implement with new Title entity.
   * @global \MovLib\Data\I18n $i18n
   * @covers ::getTitles
   * @covers \MovLib\Data\I18n::getCollator
   */
  public function testGetTitlesWithData() {
//    global $i18n;
//    $i18nBackup = $i18n;
//    $i18n = new I18n("de_AT");
//    $db = new Database();
//
//    // Insert a test titles to verify sort order.
//    $db->query(
//      "INSERT INTO `movies_titles`
//          (`movie_id`, `title`, `language_id`, `dyn_comments`)
//        VALUES
//          (?, 'a PHPUnit', 42, ''),
//          (?, 'Z PHPUnit', 42, '')",
//      "dd",
//      [ $this->movie->id, $this->movie->id ]);
//
//    $dbTitles = $db->select(
//      "SELECT
//        `title`,
//        COLUMN_GET(`dyn_comments`, ? AS BINARY) AS `comment`,
//        `is_display_title`,
//        `language_id` AS `language`
//      FROM `movies_titles`
//      WHERE `movie_id` = ?",
//      "sd",
//      [ $i18n->languageCode, $this->movie->id ]
//    );
//    $c = count($dbTitles);
//    $this->assertGreaterThan(0, $c);
//    $tmpTitles = [];
//    $i18nLanguages = $i18n->getLanguages();
//    for ($i = 0; $i < $c; ++$i) {
//      $dbTitles[$i]["language"] = $i18nLanguages[$dbTitles[$i]["language"]];
//      settype($dbTitles[$i]["is_display_title"], "boolean");
//      $tmpTitles["{$dbTitles[$i]["title"]}{$dbTitles[$i]["language"]["id"]}"] = $dbTitles[$i];
//    }
//    (new Collator("de_AT"))->ksort($tmpTitles);
//    $dbTitles = array_values($tmpTitles);
//
//    $titles = $this->movie->getTitles();
//    $this->assertCount($c, $titles);
//    for ($i = 0; $i < $c; ++$i) {
//      $this->assertEquals($dbTitles[$i], $titles[$i]);
//    }
//
//    // Delete the test titles again.
//    $db->query("DELETE FROM `movies_titles` WHERE `movie_id` = ? AND `title` LIKE '%PHPUnit%'", "d", [ $this->movie->id ]);
//    $i18n = $i18nBackup;
  }

}