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
namespace MovLib\Test\Presentation\Partial;

use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputEmail;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\Form
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class FormTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  public $id = "phpunit";

  /**
   * @var \MovLib\Presentation\Partial\Form
   */
  private $form;

  /**
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  private $inputEmail;

  /**
   * @var \MovLib\Presentation\Partial\FormElement\InputSubmit
   */
  private $inputSubmit;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  protected function setUp() {
    get_reflection_property("\\MovLib\\Presentation\\AbstractBase", "tabindex")->setValue(1);
    $this->inputEmail             = new InputEmail();
    $this->inputSubmit            = new InputSubmit();
    $this->form                   = new Form($this, [ $this->inputEmail ]);
    $this->form->actionElements[] = $this->inputSubmit;
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testConstruct() {
    global $session;
    $_SERVER["MULTIPART"] = 0;
    $this->form = new Form($this, [ $this->inputEmail ], "phpunit-id");
    $this->inputEmail->attributes[] = "autofocus";
    $this->assertEquals([ $this->inputEmail ], get_reflection_property($this->form, "elements")->getValue($this->form));
    $this->assertEquals("phpunit-id", $this->form->id);
    $this->assertEquals("<input name='form_id' type='hidden' value='phpunit-id'><input name='csrf' type='hidden' value='{$session->csrfToken}'>", $this->form->hiddenElements);
    $this->assertEquals([ "action" => $_SERVER["PATH_INFO"], "method" => "post", "enctype" => "multipart/form-data" ], $this->form->attributes);
  }

  /**
   * @covers ::open
   * @group Presentation
   */
  public function testOpen() {
    global $session;
    $open = $this->form->open();
    $this->assertEquals("<form action='/' method='post'><input name='form_id' type='hidden' value='phpunit'><input name='csrf' type='hidden' value='{$session->csrfToken}'>", $open);
    return $open;
  }

  /**
   * @covers ::close
   * @group Presentation
   */
  public function testClose() {
    $close = $this->form->close();
    $this->assertEquals("<p class='form-actions'>{$this->inputSubmit}</p></form>", $close);
    return $close;
  }

  /**
   * @covers ::__toString
   * @depends testOpen
   * @depends testClose
   * @group Presentation
   */
  public function testToString() {
    $args = func_get_args();
    $this->inputEmail->attributes[] = "autofocus";
    $this->assertEquals("{$args[0]}{$this->inputEmail}{$args[1]}", (string) $this->form);
  }

}
