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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\InputDateSeparate;

/**
 * @todo Description of InputDateSeparateTest
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateSeparateTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   */
  public function testRequired() {
    $obj = new InputDateSeparate("phpunit", "PHPUnit", [ "required" ]);
    $this->assertTrue($this->getProperty($obj, "required"));
    $this->assertFalse(in_array("required", $obj->attributes));
  }

  /**
   * @covers ::__construct
   */
  public function testMaxAndMin() {
    $obj = new InputDateSeparate("phpunit", "PHPUnit", null, 2000, 1900);
    $this->assertEquals(2000, $this->getProperty($obj, "yearMax"));
    $this->assertEquals(1900, $this->getProperty($obj, "yearMin"));
  }

  /**
   * @covers ::__construct
   */
  public function testValueExport() {
    $obj = new InputDateSeparate("phpunit", "PHPUnit", [ "value" => "2000-12-01" ]);
    $this->assertEquals(2000, $this->getProperty($obj, "year"));
    $this->assertEquals(12, $this->getProperty($obj, "month"));
    $this->assertEquals(1, $this->getProperty($obj, "day"));
  }

}
