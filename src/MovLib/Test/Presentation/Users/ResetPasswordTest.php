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
namespace MovLib\Test\Presentation\Users;

use \MovLib\Presentation\Email\User\ResetPassword as ResetPasswordEmail;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Users\ResetPassword;

/**
 * @coversDefaultClass \MovLib\Presentation\Users\ResetPassword
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ResetPasswordTest extends \MovLib\Test\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function setUpBeforeClass() {
    $_SERVER["PATH_INFO"] = "/users/reset-password";
  }

  public static function tearDownAfterClass() {
    unset($_SERVER["PATH_INFO"]);
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::__construct
   */
  public function testFormConfiguration() {
    $resetPassword = new ResetPassword();

    $inputEmail = $this->getProperty($resetPassword, "email");
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputEmail", $inputEmail);
    $this->assertEquals("email", $this->getProperty($inputEmail, "id"));
    $this->assertEquals("Email Address", $this->getProperty($inputEmail, "label"));
    $this->assertTrue(in_array("autofocus", $inputEmail->attributes));
    $this->assertArrayHasKey("placeholder", $inputEmail->attributes);
    $this->assertEquals("Enter your email address", $inputEmail->attributes["placeholder"]);

    $form = $this->getProperty($resetPassword, "form");
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Form", $form);
    $this->assertEquals([ $inputEmail ], $this->getProperty($form, "elements"));

    $this->assertArrayHasKey(0, $form->actionElements);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputSubmit", $form->actionElements[0]);
    $this->assertEquals("Click here to request a password reset for the entered email address", $form->actionElements[0]->attributes["title"]);
    $this->assertEquals("Request Password Reset", $form->actionElements[0]->attributes["value"]);
    $this->assertEquals(1, count($form->actionElements));
  }

  /**
   * @covers ::getContent
   */
  public function testGetContent() {
    $resetPassword = new ResetPassword();
    $form          = $this->getProperty($resetPassword, "form");
    $content       = $this->invoke($resetPassword, "getContent");
    $this->assertEquals("<div class='container'><div class='row'>{$form}</div></div>", $content);
  }

  /**
   * @covers ::validate
   */
  public function testValidate() {
    $_POST["email"]   = "richard@fussenegger.info";
    $_POST["form_id"] = "users-resetpassword";
    $resetPassword    = new ResetPassword();
    $found            = false;
    foreach ($this->getStaticProperty("\\MovLib\\Data\\Delayed\\Mailer", "emails") as $email) {
      if (($found = $email instanceof ResetPasswordEmail) === true) {
        break;
      }
    }
    $this->assertTrue($found);
    $this->assertEquals(202, http_response_code());
    $this->assertContains(Alert::SEVERITY_SUCCESS, $resetPassword->alerts);
    $this->assertContains("Successfully Requested Password Reset", $resetPassword->alerts);
    $this->assertContains("An email with further instructions has been sent to <em class='placeholder'>richard@fussenegger.info</em>.", $resetPassword->alerts);
    unset($_POST);
  }

}
