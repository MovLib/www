<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Test\Exception;

use \MovLib\Exception\UnauthorizedException;
use \MovLib\Presentation\Partial\Alert;

/**
 * @coversDefaultClass \MovLib\Exception\UnauthorizedException
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UnauthorizedExceptionTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @group Exceptions
   */
  public function testDefaults() {
    $unauthorizedException = new UnauthorizedException();
    $this->assertInstanceOf("\\RuntimeException", $unauthorizedException);
    $this->assertEquals("User has to authenticate to view this content.", $unauthorizedException->getMessage());
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Alert", $unauthorizedException->alert);
    $this->assertEquals("Please use the form below to sign in or go to the <a href='/user/register'>registration page to sign up</a>.", $unauthorizedException->alert->message);
    $this->assertEquals("You must be signed in to access this content.", $unauthorizedException->alert->title);
    $this->assertEquals(Alert::SEVERITY_ERROR, $unauthorizedException->alert->severity);
  }

  /**
   * @covers ::__construct
   * @group Exceptions
   */
  public function testConstruct() {
    $unauthorizedException = new UnauthorizedException("message", "title");
    $this->assertEquals("message", $unauthorizedException->alert->message);
    $this->assertEquals("title", $unauthorizedException->alert->title);
  }

}
