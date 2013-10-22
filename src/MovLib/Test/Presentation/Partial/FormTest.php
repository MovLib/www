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
class FormTest extends \MovLib\Test\TestCase {

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
    $this->setStaticProperty("\\MovLib\\Presentation\\AbstractBase", "tabindex", 1);
    $this->inputEmail             = new InputEmail();
    $this->inputSubmit            = new InputSubmit();
    $this->form                   = new Form($this, [ $this->inputEmail ]);
    $this->form->actionElements[] = $this->inputSubmit;
  }

  public function tearDown() {
    unset($_SERVER["MULTIPART"]);
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    global $session;
    $_SERVER["MULTIPART"]           = 0;
    $this->form                     = new Form($this, [ $this->inputEmail ], "phpunit-id");
    $this->inputEmail->attributes[] = "autofocus";
    $this->assertEquals([ $this->inputEmail ], $this->getProperty($this->form, "elements"));
    $this->assertEquals("phpunit-id", $this->form->id);
    $this->assertEquals("<input name='form_id' type='hidden' value='phpunit-id'><input name='csrf' type='hidden' value='{$session->csrfToken}'>", $this->form->hiddenElements);
    $this->assertEquals([ "action" => $_SERVER["PATH_INFO"], "method" => "post", "enctype" => "multipart/form-data" ], $this->form->attributes);
  }

  /**
   * @covers ::__construct
   */
  public function testUploadError() {
    $_SERVER["MULTIPART"] = UPLOAD_ERR_INI_SIZE;
    $stub                 = $this->getMock("\\MovLib\\Presentation\\FormPage", [ "validate" ], [ "PHPUnit" ]);
    list($number, $unit) = $this->invoke($stub, "formatBytes", [ ini_get("upload_max_filesize") ]);
    $stub->expects($this->once())->method("validate")->with($this->equalTo([
        "multipart" => "The image is too large: it must be {$number} {$unit} or less."
    ]));
    new Form($stub, [ ]);
  }

  /**
   * @covers ::__construct
   */
  public function testInvalidCSRF() {
    $_POST["form_id"] = "phpunit";
    $stub             = $this->getMock("\\MovLib\\Presentation\\FormPage", [ "validate" ], [ "PHPUnit" ]);
    $stub->expects($this->once())->method("validate")->with($this->equalTo([
        "csrf" => "The form has become outdated. Copy any unsaved work in the form below and then <a href='/'>reload this page</a>."
    ]));
    new Form($stub, [ ], "phpunit");
  }

  /**
   * @covers ::__construct
   */
  public function testAutoValidation() {
    global $session;
    $_POST["form_id"] = "phpunit";
    $_POST["csrf"]    = $session->csrfToken;
    $_POST["email"]   = "phpunit@movlib.org";
    $stub             = $this->getMock("\\MovLib\\Presentation\\FormPage", [ "validate" ], [ "PHPUnit" ]);
    $stub->expects($this->once())->method("validate")->with($this->equalTo(null));
    new Form($stub, [ new InputEmail() ], "phpunit");
  }

  /**
   * @covers ::__construct
   */
  public function testAutoValidationInvalid() {
    global $session;
    $_POST["form_id"] = "phpunit";
    $_POST["csrf"]    = $session->csrfToken;
    $_POST["email"]   = "root@localhost";
    $stub             = $this->getMock("\\MovLib\\Presentation\\FormPage", [ "validate" ], [ "PHPUnit" ]);
    $stub->expects($this->once())->method("validate")->with($this->equalTo([
        "email" => "The email address is invalid."
    ]));
    new Form($stub, [ new InputEmail() ], "phpunit");
  }

  /**
   * @covers ::open
   */
  public function testOpen() {
    global $session;
    $open = $this->form->open();
    $this->assertEquals("<form action='/' method='post'><input name='form_id' type='hidden' value='phpunit'><input name='csrf' type='hidden' value='{$session->csrfToken}'>", $open);
    return $open;
  }

  /**
   * @covers ::close
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
   */
  public function testToString() {
    $args                           = func_get_args();
    $this->inputEmail->attributes[] = "autofocus";
    $this->assertEquals("{$args[0]}{$this->inputEmail}{$args[1]}", (string) $this->form);
  }

}
