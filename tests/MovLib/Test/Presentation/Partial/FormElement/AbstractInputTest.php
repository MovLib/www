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

class ConcreteInput extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  public $attributes = [];

  public function __construct($id, $label, array $attributes = array(), $help = null, $helpPopup = true) {
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
  }

  public function __toString() {
    return parent::__toString();
  }

  public function validate() {
    return $this;
  }

}

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\AbstractInput
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractInputTest extends \PHPUnit_Framework_TestCase {

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    $input = new ConcreteInput("phpunit", "PHPUnit", [ "value" => "phpunit" ]);
    $this->assertArrayHasKey("value", $input->attributes);
    $this->assertEquals("phpunit", $input->attributes["value"]);
    $this->assertEquals("phpunit", $input->value);
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructValueViaPost() {
    $_POST["phpunit"] = "phpunit";
    $this->assertEquals("phpunit", (new ConcreteInput("phpunit", "PHPUnit"))->value);
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructEmptyValueViaPost() {
    $_POST["phpunit"] = "";
    $this->assertNull((new ConcreteInput("phpunit", "PHPUnit"))->value);
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstructPostValueOverPassedValue() {
    $_POST["phpunit"] = null;
    $this->assertNull((new ConcreteInput("phpunit", "PHPUnit", [ "value" => "phpunit" ]))->value);
  }

}
