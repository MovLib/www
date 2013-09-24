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
namespace MovLib\Test\Presentation\Partial\FormElement;

use \MovLib\Presentation\Validation\URL;
use \MovLib\Presentation\Partial\FormElement\InputURL;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputURLTest extends \MovLib\Test\Presentation\Validation\URLTest {

  /**
   * @covers InputURL::__construct
   */
  public function testDefaults() {
    $input = new InputURL("phpunit", "PHPUnit");
    $this->assertEquals("http(s)://", $input->attributes["placeholder"]);
    $this->assertEquals("phpunit", $input->id);
    $this->assertEquals("url", $input->attributes["type"]);
    $this->assertEquals(URL::PATTERN, $input->attributes["pattern"]);
    $this->assertFalse($input->attributes["data-allow-external"]);
    $this->assertFalse($input->attributes["data-check-reachability"]);
  }

  /**
   * @covers InputURL::__toString
   */
  public function testOutput() {
    $input = (new InputURL("phpunit", "PHPUnit"))->__toString();
    // The label is very important, any form element has to have a label!
    $this->assertContains("<label for='phpunit'>PHPUnit</label>", $input);
    $this->assertContains("data-allow-external='false'", $input);
    $this->assertContains("data-check-reachability='false'", $input);
    $this->assertContains("id='phpunit'", $input);
    $this->assertContains("name='phpunit'", $input);
    $this->assertContains("pattern='" . URL::PATTERN . "'", $input);
    $this->assertContains("placeholder='http(s)://'", $input);
    $this->assertContains("type='url'", $input);
  }

}
