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
    $_SERVER["DOCUMENT_ROOT"]   = dirname(dirname(__DIR__));
    $_SERVER["SERVER_NAME"]     = "movlib.org";
    $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
    $_SERVER["REMOTE_ADDR"]     = "127.0.0.1";
    $_SERVER["REQUEST_METHOD"]  = "GET";
    $_SERVER["REQUEST_URI"]     = "/";
    $_SERVER["SCHEME"]          = "https";
    $_SERVER["HTTP_USER_AGENT"] = ini_get("user_agent");
    $_SERVER["PRESENTER"]       = "Home";
    ob_start();
    $this->kernel = new Kernel();
    ob_clean();
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
   * @covers ::sendEmail
   * @todo Implement sendEmail
   */
  public function testSendEmail() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
