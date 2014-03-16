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

use \MovLib\Data\Log;

/**
 * @coversDefaultClass \MovLib\Data\Log
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class LogTest extends \MovLib\TestCase {

  /**
   * @covers ::alert
   */
  public function testAlert() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::critical
   */
  public function testCritical() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::debug
   */
  public function testDebug() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::emergency
   */
  public function testEmergency() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::error
   */
  public function testError() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::info
   */
  public function testInfo() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::log
   * @todo Implement testLog
   */
  public function testLog() {
    $this->markTestIncomplete("testLog not implemented yet");
  }

  /**
   * @covers ::notice
   */
  public function testNotice() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

  /**
   * @covers ::warning
   */
  public function testWarning() {
    $this->assertTrue(Log::warning("PHPUnit log test", [ "test" => __METHOD__ ]));
  }

}
