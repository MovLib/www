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
namespace MovLib\Data;

use \MovLib\Data\MovieImages;

/**
 * @coversDefaultClass \MovLib\Data\MovieImages
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieImagesTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\MovieImages */
  protected $movieImages;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->movieImages = new MovieImages();
  }

  /**
   * Called after each test.
   */
  protected function tearDown() {

  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public static function dataProviderExample() {
    return [];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


    /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getOrderedByCreatedAsc
   * @todo Implement getOrderedByCreatedAsc
   */
  public function testGetOrderedByCreatedAsc() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getOrderedByCreatedDesc
   * @todo Implement getOrderedByCreatedDesc
   */
  public function testGetOrderedByCreatedDesc() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getOrderedByUpvotes
   * @todo Implement getOrderedByUpvotes
   */
  public function testGetOrderedByUpvotes() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getStreamImages
   * @todo Implement getStreamImages
   */
  public function testGetStreamImages() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::initImageProperties
   * @todo Implement initImageProperties
   */
  public function testInitImageProperties() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
