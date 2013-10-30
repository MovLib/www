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
namespace MovLib\Tool\Console\Command\Development;

use \MovLib\Tool\Console\Command\Development\CacheInspector;

/**
 * @coversDefaultClass \MovLib\Tool\Console\Command\Development\CacheInspector
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CacheInspectorTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Console\Command\Development\CacheInspector */
  protected $cacheInspector;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->cacheInspector = new CacheInspector();
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
   * @covers ::configure
   * @todo Implement configure
   */
  public function testConfigure() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::emptyDiskCache
   * @todo Implement emptyDiskCache
   */
  public function testEmptyDiskCache() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
