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

use \MovLib\Tool\Console\Command\Production\Database;
use \Symfony\Component\Console\Input\StringInput;
use \Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \MovLib\Tool\Console\Command\Production\Database
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DatabaseTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Console\Command\Production\Database */
  protected $database;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->database = new Database();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals("database", $this->database->getName());
  }

  /**
   * @covers ::configure
   */
  public function testConfigure() {
    $this->invoke($this->database, "configure");
    $this->assertNotEmpty($this->database->getDescription());
  }

  /**
   * @covers ::execute
   * @expectedException \MovLib\Exception\ConsoleException
   * @expectedExceptionMessage Not implemented yet!
   */
  public function testExecute() {
    $this->invoke($this->database, "execute", [ new StringInput(""), new NullOutput() ]);
  }

}
