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
namespace MovLib\Presentation\History;

/**
 * @coversDefaultClass \MovLib\Presentation\History\AbstractHistory
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractHistoryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\History\AbstractHistory */
  protected $abstractHistory;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->abstractHistory = $this->getMockForAbstractClass("\\MovLib\\Presentation\\History\\AbstractHistory");
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
   * @covers ::formatChangedFile
   * @todo Implement formatChangedFile
   */
  public function testFormatChangedFile() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::formatDiff
   * @todo Implement formatDiff
   */
  public function testFormatDiff() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::formatRevision
   * @todo Implement formatRevision
   */
  public function testFormatRevision() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::diffPage
   * @todo Implement diffPage
   */
  public function testDiffPage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::revisionsPage
   * @todo Implement revisionsPage
   */
  public function testRevisionsPage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
