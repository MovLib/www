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
 * @coversDefaultClass \MovLib\Presentation\History\TraitHistory
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TraitHistoryTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\History\TraitHistory */
  protected $traitHistory;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->traitHistory = $this->getMockForTrait("\\MovLib\\Presentation\\History\\TraitHistory", [ "phpunitrepos" ], "MovieHistory");
  }

  /**
   * Called after each test.
   */
  protected function tearDown() {

  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::contentDiffPage
   * @todo Implement contentDiffPage
   */
  public function testContentDiffPage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::contentRevisionsPage
   * @todo Implement contentRevisionsPage
   */
  public function testContentRevisionsPage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::diffArray
   * @todo Implement diffArray
   */
  public function testDiffArray() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::diffArrayItems
   * @todo Implement diffArrayItems
   */
  public function testDiffArrayItems() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::diffIds
   * @todo Implement diffIds
   */
  public function testDiffIds() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::formatFileNames
   * @todo Implement formatFileNames
   */
  public function testFormatFileNames() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getBreadcrumbs
   * @todo Implement getBreadcrumbs
   */
  public function testGetBreadcrumbs() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getDiff
   * @todo Implement getDiff
   */
  public function testGetDiff() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::textDiffOfRevisions
   * @todo Implement textDiffOfRevisions
   */
  public function testTextDiffOfRevisions() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::textDiffOfStrings
   * @todo Implement textDiffOfStrings
   */
  public function testTextDiffOfStrings() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
