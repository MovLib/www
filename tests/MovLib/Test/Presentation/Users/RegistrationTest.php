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

use \MovLib\Data\User;
use \MovLib\Presentation\Email\Users\Registration as RegistrationEmail;
use \MovLib\Presentation\Email\Users\RegistrationEmailExists;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Users\Registration;
use \MovLib\Presentation\Validation\Username;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RegistrationTest extends \PHPUnit_Framework_TestCase {

  public function setUp() {
    global $session;
    $session->isAuthenticated = false;
    $_SERVER["PATH_INFO"] = "/users/registration";
  }

  public function tearDown() {
    unset($_POST);
    unset($_GET);
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::__construct
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /my with status 302.
   */
  public function testAuthenticatedRedirect() {
    global $session;
    $session->isAuthenticated = true;
    try {
      new Registration();
    }
    catch (RedirectException $e) {
      $this->assertEquals(302, $e->status);
      $this->assertEquals("{$_SERVER["SERVER"]}/my", $e->route);
      throw $e;
    }
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::__construct
   * @covers \MovLib\Presentation\Users\Registration::getContent
   */
  public function testConstruct() {
    $registration = new Registration();

    $inputEmail = get_reflection_property($registration, "email")->getValue($registration);
    $this->assertTrue($inputEmail instanceof InputEmail);
    $this->assertTrue($inputEmail->required);

    $inputUsername = get_reflection_property($registration, "username")->getValue($registration);
    $this->assertTrue($inputUsername instanceof InputText);
    $this->assertTrue($inputUsername->required);
    $this->assertTrue($inputUsername->validator instanceof Username);

    $form = get_reflection_property($registration, "form")->getValue($registration);
    $this->assertTrue($form instanceof Form);
    $this->assertEquals("/users/registration", $form->attributes["action"]);
    $this->assertEquals([ $inputEmail, $inputUsername ], get_reflection_property($form, "elements")->getValue($form));

    $this->assertContains($form->__toString(), $registration->getPresentation());
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::validate
   */
  public function testValidate() {
    $_POST["email"]    = "phpunit@movlib.org";
    $found = false;
    foreach ($this->_testValidate() as $email) {
      if ($email instanceof RegistrationEmail) {
        $found = true;
        $this->assertEquals("phpunit@movlib.org", $email->recipient);
        $this->assertContains("Welcome", $email->subject);
        $user = get_reflection_property($email, "user")->getValue($email);
        $this->assertTrue($user instanceof User);
        $this->assertEquals("phpunit@movlib.org", $user->email);
        $this->assertEquals("PHPUnit", $user->name);
      }
    }
    $this->assertTrue($found, "Registration email missing from mailer stack!");
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::validate
   */
  public function testValidateEmailExists() {
    $_POST["email"] = "richard@fussenegger.info";
    $found = false;
    foreach ($this->_testValidate() as $email) {
      if ($email instanceof RegistrationEmailExists) {
        $found = true;
        $this->assertEquals("richard@fussenegger.info", $email->recipient);
        $this->assertEquals("Forgot Your Password?", $email->subject);
        $email->init();
        $this->assertEquals("Fleshgrinder", get_reflection_property($email, "name")->getValue($email));
      }
    }
    $this->assertTrue($found, "Registration email exists email missing from stack!");
  }

  public function _testValidate() {
    $_POST["username"] = "PHPUnit";
    $_POST["form_id"]  = "users-registration";

    $registration = new Registration();

    $this->assertTrue(get_reflection_property($registration, "accepted")->getValue($registration));
    $this->assertContains("Registration Successful", $registration->alerts);
    $this->assertNotContains(get_reflection_property($registration, "form")->getValue($registration)->__toString(), $registration->getPresentation());

    return get_reflection_property("\MovLib\Data\Delayed\Mailer", "emails")->getValue(null);
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::validateToken
   */
  public function testValidateTokenEmpty() {
    $_GET["token"] = "";
    $registration = new Registration();
    $this->assertContains("token is invalid", $registration->getPresentation());
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::validateToken
   */
  public function testValidateTokenLength() {
    $_GET["token"] = "token";
    $registration = new Registration();
    $this->assertContains("token is invalid", $registration->getPresentation());
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::validateToken
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile/password-settings with status 302.
   */
  public function testValidateToken() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["username"] = "PHPUnit";
    $_POST["form_id"]  = "users-registration";
    $registration = new Registration();
    foreach (get_reflection_property("\MovLib\Data\Delayed\Mailer", "emails")->getValue(null) as $email) {
      if ($email instanceof RegistrationEmail) {
        $email->init();
        $_GET["token"] = get_reflection_property($email, "token")->getValue($email);
        break;
      }
    }
    $this->assertArrayHasKey("token", $_GET);
    unset($_POST);
    try {
      new Registration();
    }
    catch (RedirectException $e) {
      $this->assertEquals(302, $e->status);
      $this->assertEquals("{$_SERVER["SERVER"]}/profile/password-settings", $e->route);
      throw $e;
    }
  }

  /**
   * @covers \MovLib\Presentation\Users\Registration::validateToken
   */
  public function testValidationTokenExpired() {
    $this->testValidateToken();
    $registration = new Registration();
    $this->assertContains("token has expired", $registration);
  }

}
