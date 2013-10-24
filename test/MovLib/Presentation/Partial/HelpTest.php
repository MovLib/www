<?php

/* !
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
namespace MovLib\Presentation\Partial;

use \MovLib\Presentation\Partial\Help;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Help
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HelpTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   */
  public function testDefaults() {
    $help = new Help("Hello World!");
    foreach ([ "content", "id", "popup" ] as $property) {
      ${$property} = $this->getProperty($help, $property);
    }
    $this->assertEquals("Hello World!", $content);
    $this->assertNull($id);
    $this->assertTrue($popup);
  }

  /**
   * @covers ::__construct
   */
  public function testIdAndPopup() {
    $help = new Help("Hello World!", "phpunit", false);
    foreach ([ "id", "popup" ] as $property) {
      ${$property} = $this->getProperty($help, $property);
    }
    $this->assertEquals(" id='phpunit-help'", $id);
    $this->assertFalse($popup);
  }

  /**
   * @covers ::__toString
   */
  public function testToStringNoIdPopup() {
    $this->assertEquals(
      "<div class='form-help popup-container' role='note'><i class='icon icon--help-circled'></i><small class='popup'>Hello World!</small></div>", (new Help("Hello World!"))->__toString()
    );
  }

  /**
   * @covers ::__toString
   */
  public function testToStringIdPopup() {
    $this->assertEquals(
      "<div class='form-help popup-container' id='phpunit-help' role='note'><i class='icon icon--help-circled'></i><small class='popup'>Hello World!</small></div>", (new Help("Hello World!", "phpunit"))->__toString()
    );
  }

  /**
   * @covers ::__toString
   */
  public function testToStringNoIdNoPopup() {
    $this->assertEquals(
      "<small class='form-help' role='note'>Hello World!</small>", (new Help("Hello World!", null, false))->__toString()
    );
  }

  /**
   * @covers ::__toString
   */
  public function testToStringIdNoPopup() {
    $this->assertEquals(
      "<small class='form-help' id='phpunit-help' role='note'>Hello World!</small>", (new Help("Hello World!", "phpunit", false))->__toString()
    );
  }

}
