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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\Select;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class SelectTest extends \MovLib\TestCase {

  public $options = [ "phpunit1" => "PHPUnit 1" ];

  public function tearDown() {
    unset($_POST);
  }

  /**
   * @covers Select::__construct
   */
  public function testOptionsAndValueExport() {
    $select = new Select("phpunit", "PHPUnit", $this->options);
    $this->assertAttributeEquals($this->options, "options", $select);
    $this->assertAttributeEquals(null, "value", $select);
  }

  /**
   * @covers Select::__construct
   */
  public function testPassedValueExport() {
    $this->assertAttributeEquals("phpunit1", "value", new Select("phpunit", "PHPUnit", $this->options, "phpunit1"));
  }

  /**
   * @covers Select::__construct
   */
  public function testPostExport() {
    $_POST["phpunit"] = "phpunit1";
    $this->assertAttributeEquals("phpunit1", "value", new Select("phpunit", "PHPUnit", $this->options));
  }

  /**
   * @covers Select::__construct
   */
  public function testValidPostOverridesPassedValue() {
    $_POST["phpunit"] = "phpunit2";
    $options          = array_merge($this->options, [ "phpunit2" => "PHPUnit 2" ]);
    $this->assertAttributeEquals("phpunit2", "value", new Select("phpunit", "PHPUnit", $options, "phpunit1"));
  }

  /**
   * @covers Select::__toString
   */
  public function testToString() {
    $options = timezone_identifiers_list();
    $select  = (new Select("phpunit", "PHPUnit", $options))->__toString();
    $c       = count($options);
    for ($i = 0; $i < $c; ++$i) {
      $this->assertContains("<option value='{$i}'>{$options[$i]}</option>", $select);
    }
  }

  /**
   * @covers Select::validate
   */
  public function testValid() {
    $_POST["phpunit"] = "phpunit1";
    $select           = new Select("phpunit", "PHPUnit", $this->options);
    $select->validate();
  }

  /**
   * @covers Select::validate
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testInvalid() {
    $_POST["phpunit"] = "phpunit3";
    $select           = new Select("phpunit", "PHPUnit", $this->options);
    $select->validate();
  }

  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::validate
   * @todo Implement validate
   */
  public function testValidate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
