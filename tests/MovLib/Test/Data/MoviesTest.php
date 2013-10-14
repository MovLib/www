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
use \MovLib\Data\Movie;
use \MovLib\Data\Movies;

/**
 * @coversDefaultClass \MovLib\Data\Movies
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviesTest extends \PHPUnit_Framework_TestCase {

  /**
   * The movies object under test.
   *
   * @var \MovLib\Data\Movies
   */
  public $movies;

  /**
   * @inheritdoc
   */
  public function setUp() {
    $this->movies = new Movies();
  }

  public function testGetMoviesByCreated() {
    $lowerBound = 0;
    $upperBound = 2;
    $dbMovies = (new Database())->select(
      "SELECT
        `movie_id`
      FROM `movies`
      WHERE `deleted` = false
      ORDER BY `created` DESC
      LIMIT ?, ?",
      "ii",
      [ $lowerBound, $upperBound ]
    );
    $c = count($dbMovies);
    for ($i = 0; $i < $c; ++$i) {
      $dbMovies[$i] = new Movie($dbMovies[$i]["movie_id"]);
    }

    $movies = $this->movies->getMoviesByCreated($lowerBound, $upperBound);
    $this->assertCount($upperBound, $movies);
    $movieProperties = (new \ReflectionObject($movies[0]))->getProperties(\ReflectionProperty::IS_PUBLIC);
    for ($i = 0; $i < $c; ++$i) {
      foreach ($movieProperties as $propertyObj) {
        $this->assertEquals($dbMovies[$i]->{$propertyObj->name}, $movies[$i]->{$propertyObj->name});
      }
    }
  }

}
