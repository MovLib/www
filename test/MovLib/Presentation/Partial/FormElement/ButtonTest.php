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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\Button;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\Button
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ButtonTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $button = new Button("phpunit", "<phpunit>");
    $this->assertEquals("<phpunit>", $button->content);
  }

  /**
   * @covers ::__toString
   */
  public function testToString() {
    $this->assertRegExp("/^<button[a-z0-9=' ]+><phpunit><\/button>$/", (string) new Button("phpunit", "<phpunit>"));
  }

  /**
   * @covers ::validate
   */
  public function testValidate() {
    $button = new Button("phpunit", "<phpunit>");
    $this->assertEquals($button, $button->validate());
  }

}
