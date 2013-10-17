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

use \MovLib\Exception\ErrorException;

/**
 * @coversDefaultClass \MovLib\Exception\ErrorException
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ErrorExceptionTest extends \PHPUnit_Framework_TestCase {

  static function dataProviderTestConstruct() {
    return [
      [ E_ERROR ],
      [ E_WARNING ],
      [ E_NOTICE ],
    ];
  }

  /**
   * @covers ::__construct
   * @dataProvider dataProviderTestConstruct
    */
  public function testConstruct($errorCode) {
    $line = __LINE__;
    $errorException = new ErrorException($errorCode, "phpunit", __FILE__, $line);
    $this->assertInstanceOf("\\ErrorException", $errorException);
    $this->assertEquals($errorCode, $errorException->getCode());
    $this->assertEquals("phpunit", $errorException->getMessage());
    $this->assertEquals(__FILE__, $errorException->getFile());
    $this->assertEquals($line, $errorException->getLine());
    $found = false;
    foreach (get_reflection_property("\\MovLib\\Data\\Delayed\\Logger", "entries")->getValue() as $priority => $entries) {
      foreach ($entries as $entry) {
        if ($entry["entry"] === $errorException) {
          $found = true;
          break;
        }
      }
    }
    $this->assertTrue($found);
  }

}
