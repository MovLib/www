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
namespace MovLib\Test\Presentation\Partial;

use \MovLib\Presentation\Partial\Alert;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Alert
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AlertTest extends \MovLib\Test\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $alert = new Alert("Hello World!");
    $this->assertEquals("Hello World!", $alert->message);
    $this->assertNull($alert->severity);
    $this->assertNull($alert->title);
  }

  /**
   * @covers ::__toString
   */
  public function testToString() {
    $alert        = new Alert("Hello World!");
    $this->assertEquals("<div class='alert' role='alert'><div class='container'>Hello World!</div></div>", $alert->__toString());
    $alert->title = "PHPUnit";
    $this->assertEquals("<div class='alert' role='alert'><div class='container'><h4 class='alert__title'>PHPUnit</h4>Hello World!</div></div>", $alert->__toString());
    foreach ([ Alert::SEVERITY_ERROR, Alert::SEVERITY_INFO, Alert::SEVERITY_SUCCESS, Alert::SEVERITY_WARNING ] as $severity) {
      $alert->severity = $severity;
      $this->assertEquals("<div class='alert{$severity}' role='alert'><div class='container'><h4 class='alert__title'>PHPUnit</h4>Hello World!</div></div>", $alert->__toString());
    }
  }

}
