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

use \MovLib\Tool\Console\Command\Development\SeedImport;

/**
 * @coversDefaultClass \MovLib\Tool\Console\Command\Development\SeedImport
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedImportTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Console\Command\Development\SeedImport */
  protected $seedImport;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->seedImport = new SeedImport();
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
   * @covers ::configure
   * @todo Implement configure
   */
  public function testConfigure() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::databaseImport
   * @todo Implement databaseImport
   */
  public function testDatabaseImport() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::execute
   * @todo Implement execute
   */
  public function testExecute() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::importIntlICUCountriesAndLanguages
   * @todo Implement importIntlICUCountriesAndLanguages
   */
  public function testImportIntlICUCountriesAndLanguages() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::importTimeZones
   * @todo Implement importTimeZones
   */
  public function testImportTimeZones() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::intlTranslate
   * @todo Implement intlTranslate
   */
  public function testIntlTranslate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::seedImport
   * @todo Implement seedImport
   */
  public function testSeedImport() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::uploadImport
   * @todo Implement uploadImport
   */
  public function testUploadImport() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}