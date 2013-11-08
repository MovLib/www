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
namespace MovLib\Tool\Console\Command;

/**
 * @coversDefaultClass \MovLib\Tool\Console\Command\AbstractCommand
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractCommandTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Console\Command\AbstractCommand */
  protected $abstractCommand;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->abstractCommand = $this->getMockForAbstractClass("\\MovLib\\Tool\\Console\\Command\\AbstractCommand");
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
   * @covers ::progressStart
   * @todo Implement progressStart
   */
  public function testProgressStart() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::progressAdvance
   * @todo Implement progressAdvance
   */
  public function testProgressAdvance() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::progressFinish
   * @todo Implement progressFinish
   */
  public function testProgressFinish() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::addInputOption
   * @todo Implement addInputOption
   */
  public function testAddInputOption() {
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
   * @covers ::execute
   * @todo Implement execute
   */
  public function testExecute() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getShortcut
   * @todo Implement getShortcut
   */
  public function testGetShortcut() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::write
   * @todo Implement write
   */
  public function testWrite() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
