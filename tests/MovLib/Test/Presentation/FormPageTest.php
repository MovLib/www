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
namespace MovLib\Test\Presentation;

use \MovLib\Presentation\FormPage;

/**
 * @coversDefaultClass \MovLib\Presentation\FormPage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class FormPageTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::checkErrors
    */
  public function testNoErrors() {
    $formPage = new FormPage("PHPUnit");
    $this->assertFalse(get_reflection_method($formPage, "checkErrors")->invoke($formPage));
  }

  /**
   * @covers ::checkErrors
    */
  public function testStringErrors() {
    $formPage = new FormPage("PHPUnit");
    $this->assertTrue(get_reflection_method($formPage, "checkErrors")->invokeArgs($formPage, [ "msg" ]));
    $this->assertContains("msg", $formPage->alerts);
  }

  /**
   * @covers ::checkErrors
    */
  public function testArrayErrors() {
    $formPage = new FormPage("PHPUnit");
    $this->assertTrue(get_reflection_method($formPage, "checkErrors")->invokeArgs($formPage, [[ "assoc" => "msg1", "msg2" ]]));
    $this->assertContains("msg1<br>msg2", $formPage->alerts);
  }

  /**
   * @covers ::validate
     */
  public function testValidate() {
    $stub = $this->getMock("\\MovLib\\Presentation\\FormPage", [ "checkErrors" ], [ "PHPUnit" ]);
    $stub->expects($this->once())->method("checkErrors");
    $stub->validate();
  }

}
