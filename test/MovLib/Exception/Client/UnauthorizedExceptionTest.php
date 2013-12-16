<?php

/* !
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
namespace MovLib\Exception\Client;

use \MovLib\Exception\Client\ErrorUnauthorizedException;
use \MovLib\Presentation\Partial\Alert;

/**
 * @coversDefaultClass \MovLib\Exception\Client\UnauthorizedException
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class UnauthorizedExceptionTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   * @expectedException \MovLib\Exception\Client\ErrorUnauthorizedException
   * @expectedExceptionMessage has to authenticate
   * @global \MovLib\TestKernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  public function testConstruct() {
    global $kernel, $session;
    $kernel->requestMethod = "POST";
    $_POST                 = [ "secret" => "data" ];
    $unauthorizedException = new ErrorUnauthorizedException("phpunit-msg", "phpunit-title", Alert::SEVERITY_WARNING);
    $session               = $this->getMock("\\MovLib\\Data\\User\\Session", [ "destroy" ]);
    $session->expects($this->once())->method("destroy");
    $this->assertEquals("GET", $kernel->requestMethod);
    $this->assertTrue(empty($_POST));
    $this->assertEquals(401, http_response_code());
    $this->assertEquals('WWW-Authenticate: MovLib location="/users/login"', $unauthorizedException->authenticateHeader);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Users\\Login", $unauthorizedException->presentation);
    $presentation          = $unauthorizedException->presentation->getPresentation();
    $this->assertContains("phpunit-msg", $presentation);
    $this->assertContains("phpunit-title", $presentation);
    $this->assertContains(Alert::SEVERITY_WARNING, $presentation);
    throw $unauthorizedException;
  }

  /**
   * @covers ::getPresentation
   * @todo Implement getPresentation
   */
  public function testGetPresentation() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
