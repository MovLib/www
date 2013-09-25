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

use \MovLib\Data\MovieImage;
use \MovLib\Data\MovieImages;
use \MovLib\Data\MySQLi as PHPMysqli;
use \MovLib\View\ImageStyle\ResizeImageStyle;

/**
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieImagesTest extends \PHPUnit_Framework_TestCase {

  public static $images = [];

  /** @var \MovLib\Data\MovieImages */
  public $movieImages;

  public function setUp() {
    $this->movieImages = new MovieImages(1, MovieImage::IMAGETYPE_PHOTO, new ResizeImageStyle(MovieImage::IMAGESTYLE_DETAILS_STREAM), "/movie/1/photo", "PHPUnit");
  }

  public static function setUpBeforeClass() {
    self::tearDownAfterClass();
    $query = "INSERT INTO `movies_images` (`movie_id`, `image_id`, `type`, `user_id`, `license_id`, `filename`, `width`, `height`, `size`, `ext`, `changed`, `created`, `upvotes`, `dyn_descriptions`, `hash`, `source`) VALUES\n";
    $types = null;
    $params = [];
    $c = $GLOBALS["movlib"]["pagination_size"] * 4;
    for ($i = 1; $i <= $c; ++$i) {
      self::$images[] = [
        "movie_id"         => 1,
        "image_id"         => $i,
        "type"             => MovieImage::IMAGETYPE_PHOTO,
        "user_id"          => 1,
        "license_id"       => 1,
        "filename"         => "image{$i}",
        "width"            => 10,
        "height"           => 10,
        "size"             => 10,
        "ext"              => "jpg",
        "changed"          => time(),
        "created"          => strtotime("-" . ($c - $i) . " days"),
        "upvotes"           => $i,
        "dyn_descriptions" => "PHPUnit",
        "hash"             => "0123456789012345",
        "source"           => "",
      ];
      $query .= "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?), ?, COLUMN_CREATE('en', ?), ?, ?)";
      $types .= "ddidisiiisiiisss";
      $params = array_merge($params, array_values(self::$images[$i - 1]));
      if ($i !== $c) {
        $query .= ",\n";
      }
    }
    $mysqli = new PHPMysqli();
    $stmt = $mysqli->prepare($query);
    array_unshift($params, $types);
    $c = count($params);
    for ($i = 0; $i < $c; ++$i) {
      $params[$i] = &$params[$i];
    }
    call_user_func_array([ $stmt, "bind_param" ], $params);
    $stmt->execute();
  }

  public static function tearDownAfterClass() {
    $mysqli = new PHPMysqli();
    $mysqli->query("DELETE FROM `movies_images` WHERE `movie_id` = 1 AND `type` = " . MovieImage::IMAGETYPE_PHOTO);
  }

  /**
   * @covers \MovLib\Data\MovieImages::__construct
   */
  public function testConstruct() {
    $this->assertEquals("PHPUnit", get_reflection_property($this->movieImages, "movieTitle")->getValue($this->movieImages));
    $this->assertEquals(1, $this->movieImages->movieId);
    $this->assertEquals(MovieImage::IMAGETYPE_PHOTO, $this->movieImages->type);
    $this->assertNotEmpty(get_reflection_property($this->movieImages, "query")->getValue($this->movieImages));
  }

  /**
   * @covers \MovLib\Data\MovieImages::initImageProperties
   */
  public function testInitImageProperties() {
    $mysqli = new PHPMysqli();
    $queryResult = $mysqli->query(
      "SELECT
        `image_id`,
        `country_id`,
        COLUMN_GET(`dyn_descriptions`, 'en' AS BINARY) AS `description`
      FROM `movies_images`
      WHERE `movie_id` = 1
        AND `image_id` = 1
        AND `type` = " . MovieImage::IMAGETYPE_PHOTO . "
        LIMIT 1"
    );
    $result = null;
    while ($row = $queryResult->fetch_assoc()){
      $result[] = $row;
    }
    $this->assertNotEmpty($result[0]);
    $images = get_reflection_method($this->movieImages, "initImageProperties")->invokeArgs($this->movieImages, [ $result ]);
    $this->assertContains("PHPUnit", $images[0]["alt"]);
    $this->assertContains("photo", $images[0]["alt"]);
    $this->assertEquals("/movie/1/photo/1", $images[0]["uri"]);
  }

  /**
   * @covers \MovLib\Data\MovieImages::getOrderedByCreatedAsc
   */
  public function testGetOrderedByCreatedAsc() {
    $paginationSize = $GLOBALS["movlib"]["pagination_size"];
    $imageId = $paginationSize * 2;

    // Default pagination without including the image id in result.
    $images = $this->movieImages->getOrderedByCreatedAsc($imageId);
    $c = count($images);
    $this->assertEquals($paginationSize, $c);
    $this->assertEquals(self::$images[$imageId]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId + $c - 1]["image_id"], $images[$c - 1]["image_id"]);

    // Default pagination including the image id in result.
    $images = $this->movieImages->getOrderedByCreatedAsc($imageId, true);
    $c = count($images);
    $this->assertEquals($paginationSize, $c);
    $this->assertEquals(self::$images[$imageId - 1]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId + $c - 2]["image_id"], $images[$c - 1]["image_id"]);

    // Custom pagination without including the image id in result.
    $imageCount = 8;
    $images = $this->movieImages->getOrderedByCreatedAsc($imageId, false, $imageCount);
    $c = count($images);
    $this->assertEquals($imageCount, $c);
    $this->assertEquals(self::$images[$imageId]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId + $c - 1]["image_id"], $images[$c - 1]["image_id"]);

    // Custom pagination including the image id in result.
    $imageCount = 9;
    $images = $this->movieImages->getOrderedByCreatedAsc($imageId, true, $imageCount);
    $c = count($images);
    $this->assertEquals($imageCount, $c);
    $this->assertEquals(self::$images[$imageId - 1]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId + $c - 2]["image_id"], $images[$c - 1]["image_id"]);
  }

  /**
   * @covers \MovLib\Data\MovieImages::getOrderedByCreatedDesc
   */
  public function testGetOrderedByCreatedDesc() {
    $paginationSize = $GLOBALS["movlib"]["pagination_size"];
    $imageId = $paginationSize * 2;

    // Default pagination without including the image id in result.
    $images = $this->movieImages->getOrderedByCreatedDesc($imageId);
    $c = count($images);
    $this->assertEquals($paginationSize, $c);
    $this->assertEquals(self::$images[$imageId - 2]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId - $c - 1]["image_id"], $images[$c - 1]["image_id"]);

    // Default pagination including the image id in result.
    $images = $this->movieImages->getOrderedByCreatedDesc($imageId, true);
    $c = count($images);
    $this->assertEquals($paginationSize, $c);
    $this->assertEquals(self::$images[$imageId - 1]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId - $c]["image_id"], $images[$c - 1]["image_id"]);

    // Custom pagination without including the image id in result.
    $imageCount = 8;
    $images = $this->movieImages->getOrderedByCreatedDesc($imageId, false, $imageCount);
    $c = count($images);
    $this->assertEquals($imageCount, $c);
    $this->assertEquals(self::$images[$imageId - 2]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId - $c - 1]["image_id"], $images[$c - 1]["image_id"]);

    // Custom pagination including the image id in result.
    $imageCount = 9;
    $images = $this->movieImages->getOrderedByCreatedDesc($imageId, true, $imageCount);
    $c = count($images);
    $this->assertEquals($imageCount, $c);
    $this->assertEquals(self::$images[$imageId - 1]["image_id"], $images[0]["image_id"]);
    $this->assertEquals(self::$images[$imageId - $c]["image_id"], $images[$c - 1]["image_id"]);
  }

  /**
   * @covers \MovLib\Data\MovieImages::getOrderedByUpvotes
   */
  public function testGetOrderedByUpvotesASC() {
    $images = $this->movieImages->getOrderedByUpvotes(0, "ASC");
    foreach (array_slice(self::$images, 0, $GLOBALS["movlib"]["pagination_size"]) as $delta => $image) {
      $this->assertEquals($image["upvotes"], $images[$delta]["upvotes"]);
    }
  }

  /**
   * @covers \MovLib\Data\MovieImages::getOrderedByUpvotes
   */
  public function testGetOrderedByUpvotesDESC() {
    $images = $this->movieImages->getOrderedByUpvotes(0, "DESC");
    foreach (array_slice(array_reverse(self::$images), 0, $GLOBALS["movlib"]["pagination_size"]) as $delta => $image) {
      $this->assertEquals($image["upvotes"], $images[$delta]["upvotes"]);
    }
  }

}
