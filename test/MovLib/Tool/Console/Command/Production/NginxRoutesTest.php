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
namespace MovLib\Tool\Console\Command\Production;

use \MovLib\Tool\Console\Command\Production\NginxRoutes;
use \Symfony\Component\Console\Input\StringInput;
use \Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \MovLib\Tool\Console\Command\Production\NginxRoutes
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class NginxRoutesTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Console\Command\Production\NginxRoutes */
  protected $nginxRoutes;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->nginxRoutes = new NginxRoutes();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals("nginx-routes", $this->nginxRoutes->getName());
  }

  /**
   * @covers ::compileAndTranslateRoutes
   * @todo Implement compileAndTranslateRoutes
   */
  public function testCompileAndTranslateRoutes() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::configure
   */
  public function testConfigure() {
    $this->invoke($this->nginxRoutes, "configure");
    $this->assertNotEmpty($this->nginxRoutes->getDefinition());
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $nginxRoutes = $this->getMock("\\MovLib\\Tool\\Console\\Command\\Production\\NginxRoutes", [ "execute", "checkPrivileges" ]);
    $nginxRoutes->expects($this->any())->method("checkPrivileges")->will($this->returnValue($nginxRoutes));
    $this->invoke($nginxRoutes, "execute", [ new StringInput(""), new NullOutput() ]);
  }

  /**
   * @covers ::execute
   * @expectedException \MovLib\Exception\ConsoleException
   * @expectedExceptionMessage root
   */
  public function testExecuteNoRoot() {
    $this->invoke($this->nginxRoutes, "execute", [ new StringInput(""), new NullOutput() ]);
  }

}
