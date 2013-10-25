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
namespace MovLib\Data\History;

/**
 * @coversDefaultClass \MovLib\Data\History\AbstractHistory
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractHistoryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\History\AbstractHistory */
  protected $abstractHistory;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->abstractHistory = $this->getMockForAbstractClass("\\MovLib\\Data\\History\\AbstractHistory");
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
   * @covers ::commitFiles
   * @todo Implement commitFiles
   */
  public function testCommitFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::createRepository
   * @todo Implement createRepository
   */
  public function testCreateRepository() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getChangedFiles
   * @todo Implement getChangedFiles
   */
  public function testGetChangedFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getCommitHash
   * @todo Implement getCommitHash
   */
  public function testGetCommitHash() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getArrayDiff
   * @todo Implement getArrayDiff
   */
  public function testGetArrayDiff() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getArrayDiffIdCompare
   * @todo Implement getArrayDiffIdCompare
   */
  public function testGetArrayDiffIdCompare() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getArrayDiffDeepCompare
   * @todo Implement getArrayDiffDeepCompare
   */
  public function testGetArrayDiffDeepCompare() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getDirtyFiles
   * @todo Implement getDirtyFiles
   */
  public function testGetDirtyFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getLastCommitHash
   * @todo Implement getLastCommitHash
   */
  public function testGetLastCommitHash() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getFileAtRevision
   * @todo Implement getFileAtRevision
   */
  public function testGetFileAtRevision() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getLastCommits
   * @todo Implement getLastCommits
   */
  public function testGetLastCommits() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getShortName
   * @todo Implement getShortName
   */
  public function testGetShortName() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::hideRepository
   * @todo Implement hideRepository
   */
  public function testHideRepository() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::writeFiles
   * @todo Implement writeFiles
   */
  public function testWriteFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::resetFiles
   * @todo Implement resetFiles
   */
  public function testResetFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::saveHistory
   * @todo Implement saveHistory
   */
  public function testSaveHistory() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::stageAllFiles
   * @todo Implement stageAllFiles
   */
  public function testStageAllFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::startEditing
   * @todo Implement startEditing
   */
  public function testStartEditing() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::unhideRepository
   * @todo Implement unhideRepository
   */
  public function testUnhideRepository() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::unstageFiles
   * @todo Implement unstageFiles
   */
  public function testUnstageFiles() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::writeToFile
   * @todo Implement writeToFile
   */
  public function testWriteToFile() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
