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

use \MovLib\Data\TemporaryStorage;

/**
 * @coversDefaultClass \MovLib\Data\Temporary
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TemporaryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\TemporaryStorage */
  protected $temporary;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->temporary = new TemporaryStorage();
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
   * @covers ::delete
   * @todo Implement delete
   */
  public function testDelete() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::get
   * @todo Implement get
   */
  public function testGet() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::set
   * @todo Implement set
   */
  public function testSet() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::update
   * @todo Implement update
   */
  public function testUpdate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
