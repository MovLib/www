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
namespace MovLib\Data\User;

use \MovLib\Data\User\Collection;

/**
 * @coversDefaultClass \MovLib\Data\User\Collection
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CollectionTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\User\Collection */
  protected $collection;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->collection = new Collection();
  }

  /**
   * Called after each test.
   */
  protected function tearDown() {

  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderExample() {
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
   * @covers ::getMediaConditions
   * @todo Implement getMediaConditions
   */
  public function testGetMediaConditions() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getSleeveConditions
   * @todo Implement getSleeveConditions
   */
  public function testGetSleeveConditions() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
