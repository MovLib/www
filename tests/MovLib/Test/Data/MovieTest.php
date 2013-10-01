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
namespace MovLib\Test\Data;

use \MovDev\Database;
use \MovLib\Data\Movie;

/**
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The language code to use for the tests.
   *
   * @var string
   */
  public $languageCode;

  /**
   * The movie data object under test.
   *
   * @var \MovLib\Data\Movie
   */
  public $movie;

  /**
   * Associative array containing the movie's properties.
   *
   * @var array
   */
  public $movieProperties;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($name = NULL, array $data = array(), $dataName = '') {
    global $i18n;
    parent::__construct($name, $data, $dataName);
    $this->languageCode = "xx";
    $i18n->languageCode = $this->languageCode;
    $this->movieProperties = [
      "id"            => 3,
      "originalTitle" => "Léon",
      "rating"        => 0,
      "meanRating"    => 0,
      "votes"         => 0,
      "deleted"       => false,
      "year"          => 1994,
      "runtime"       => 110,
      "rank"          => null,
      "synopsis"      => "PHPUnit",
    ];
    $db = new Database();
    $db->query(
      "UPDATE `movies` SET `dyn_synopses` = COLUMN_ADD(`dyn_synopses`, ?, ?) WHERE `movie_id` = ?",
      "ssd",
      [ $this->languageCode, $this->movieProperties["synopsis"], $this->movieProperties["id"] ]
    );
  }

  /**
   * @inheritdoc
   */
  public function setUp() {
    $this->movie = new Movie(3);
  }


  // ------------------------------------------------------------------------------------------------------------------- Test methods


  /**
   * Test the various construction possibilities.
   *
   * @global \MovLib\Data\I18n $i18n
   * @covers \MovLib\Data\Movie::__construct
   */
  public function testConstruct() {
    global $i18n;
    $i18n->languageCode = $this->languageCode;

    // Empty construction.
    $movie = new Movie();
    foreach ($this->movieProperties as $k => $v) {
      $this->assertEmpty($movie->{$k});
    }

    // Construction from id, also test the weight of the movie id over the properties array.
    $movie = new Movie($this->movieProperties["id"], [ "id" => "wrong" ]);
    foreach ($this->movieProperties as $k => $v) {
      $this->assertEquals($v, $movie->{$k});
    }

    // Construction from properties array.
    $movie = new Movie(null, $this->movieProperties);
    foreach ($this->movieProperties as $k => $v) {
      $this->assertEquals($v, $movie->{$k});
    }
  }

}
