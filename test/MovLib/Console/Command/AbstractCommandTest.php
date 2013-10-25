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
namespace MovLib\Console\Command;

/**
 * @coversDefaultClass \MovLib\Console\Command\AbstractCommand
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractCommandTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Console\Command\AbstractCommand */
  protected $abstractCommand;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->abstractCommand = $this->getMockForAbstractClass("\\MovLib\\Console\\Command\\AbstractCommand");
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
   * @covers ::__get
   * @todo Implement __get
   */
  public function testGet() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::progress
   * @todo Implement progress
   */
  public function testProgress() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getDialog
   * @todo Implement getDialog
   */
  public function testGetDialog() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::ask
   * @todo Implement ask
   */
  public function testAsk() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::askConfirmation
   * @todo Implement askConfirmation
   */
  public function testAskConfirmation() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::askWithChoices
   * @todo Implement askWithChoices
   */
  public function testAskWithChoices() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::checkPrivileges
   * @todo Implement checkPrivileges
   */
  public function testCheckPrivileges() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::exec
   * @todo Implement exec
   */
  public function testExec() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::system
   * @todo Implement system
   */
  public function testSystem() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::exitOnError
   * @todo Implement exitOnError
   */
  public function testExitOnError() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::write
   * @todo Implement write
   */
  public function testWrite() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::setInput
   * @todo Implement setInput
   */
  public function testSetInput() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::setIO
   * @todo Implement setIO
   */
  public function testSetIO() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::setOutput
   * @todo Implement setOutput
   */
  public function testSetOutput() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
