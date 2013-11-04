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
namespace MovLib;

use \MovLib\Kernel;

/**
 * @coversDefaultClass \MovLib\Kernel
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class KernelTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Kernel */
  protected $kernel;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->kernel = new Kernel();
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
   * @covers ::autoload
   * @todo Implement autoload
   */
  public function testAutoload() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::delayMethodCall
   * @todo Implement delayMethodCall
   */
  public function testDelayMethodCall() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::errorHandler
   * @todo Implement errorHandler
   */
  public function testErrorHandler() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::fatalErrorHandler
   * @todo Implement fatalErrorHandler
   */
  public function testFatalErrorHandler() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @coversNothing
   * @link http://php.net/function.password-hash.php
   */
  public function testPasswordCost() {
    $timeTarget = 0.5;
    $cost       = 9;
    do {
      $cost++;
      $start  = microtime(true);
      password_hash("test", PASSWORD_DEFAULT, $this->kernel->passwordOptions);
      $end    = microtime(true);
      $actual = $end - $start;
    }
    while ($actual < $timeTarget);
    $this->assertGreaterThanOrEqual(
      $this->kernel->passwordOptions["cost"], $cost, "Please set password cost in the Kernel at least to {$cost} (hashing will take ~{$actual} seconds)."
    );
  }

  /**
   * @covers ::sendEmail
   * @todo Implement sendEmail
   */
  public function testSendEmail() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
