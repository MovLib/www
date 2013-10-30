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

use \MovLib\Data\Mailer;

/**
 * @coversDefaultClass \MovLib\Data\Mailer
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MailerTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Mailer */
  protected $mailer;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->mailer = new Mailer();
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
   * @covers ::getBase64EncodedHTML
   * @todo Implement getBase64EncodedHTML
   */
  public function testGetBase64EncodedHTML() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getBase64EncodedPlainText
   * @todo Implement getBase64EncodedPlainText
   */
  public function testGetBase64EncodedPlainText() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getHeaders
   * @todo Implement getHeaders
   */
  public function testGetHeaders() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getMessage
   * @todo Implement getMessage
   */
  public function testGetMessage() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getParameters
   * @todo Implement getParameters
   */
  public function testGetParameters() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getRecipient
   * @todo Implement getRecipient
   */
  public function testGetRecipient() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getSubject
   * @todo Implement getSubject
   */
  public function testGetSubject() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::send
   * @todo Implement send
   */
  public function testSend() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::wordwrap
   * @todo Implement wordwrap
   */
  public function testWordwrap() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
