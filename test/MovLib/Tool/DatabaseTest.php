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
namespace MovLib\Tool;

use \MovLib\Tool\Database;

/**
 * @coversDefaultClass \MovLib\Tool\Database
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DatabaseTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Database */
  protected $database;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->database = new Database();
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
   * @covers ::connect
   * @todo Implement connect
   */
  public function testConnect() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::escapeString
   * @todo Implement escapeString
   */
  public function testEscapeString() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::queries
   * @todo Implement queries
   */
  public function testQueries() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::query
   * @todo Implement query
   */
  public function testQuery() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::setDatabase
   * @todo Implement setDatabase
   */
  public function testSetDatabase() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::transactionActive
   * @todo Implement transactionActive
   */
  public function testTransactionActive() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::transactionCommit
   * @todo Implement transactionCommit
   */
  public function testTransactionCommit() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::transactionRollback
   * @todo Implement transactionRollback
   */
  public function testTransactionRollback() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::transactionStart
   * @todo Implement transactionStart
   */
  public function testTransactionStart() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
