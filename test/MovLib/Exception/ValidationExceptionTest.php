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
namespace MovLib\Exception;

use \MovLib\Exception\ValidationException;

/**
 * @coversDefaultClass \MovLib\Exception\ValidationException
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ValidationExceptionTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $validationException = new ValidationException("message");
    $this->assertEquals("message", $validationException->getMessage());
  }

  /**
   * @covers ::__construct
   */
  public function testConstructArray() {
    $validationException = new ValidationException([ "message 1", "message 2" ]);
    $this->assertEquals("message 1<br>message 2", $validationException->getMessage());
  }

  /**
   * @covers ::appendToMessage
   */
  public function testAppendToMessage() {
    $validationException = new ValidationException("message 1");
    $validationException->appendToMessage("message 2");
    $this->assertEquals("message 1<br>message 2", $validationException->getMessage());
  }

  /**
   * @covers ::appendToMessage
   */
  public function testAppendToMessageArray() {
    $validationException = new ValidationException("message 1");
    $validationException->appendToMessage([ "message 2", "message 3" ]);
    $this->assertEquals("message 1<br>message 2<br>message 3", $validationException->getMessage());
  }

}
