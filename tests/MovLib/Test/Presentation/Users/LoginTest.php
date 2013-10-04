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
namespace MovLib\Test\Presentation\Users;

use \MovLib\Data\User;
use \MovLib\Presentation\Users\Login;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class LoginTest extends \PHPUnit_Framework_TestCase {

  public static function setUpBeforeClass() {
    $_SERVER["PATH_INFO"] = "/users/login";
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::__construct
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /my with status 302.
   */
  public function testAuthenticatedRedirect() {
    try {
      new Login();
    }
    catch (\MovLib\Exception\RedirectException $e) {
      $this->assertEquals(302, $e->status);
      $this->assertEquals("{$_SERVER["SERVER"]}/my", $e->route);
      throw $e;
    }
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::__construct
   * @covers \MovLib\Presentation\Users\Registration::getContent
   */
  public function testConstruct() {
    global $session;
    $session->isAuthenticated = false;
    $login = new Login();

    $inputEmail = get_reflection_property($login, "email")->getValue($login);
    $this->assertTrue($inputEmail instanceof InputEmail);
    $this->assertTrue($inputEmail->required);

    $inputPassword = get_reflection_property($login, "password")->getValue($login);
    $this->assertTrue($inputPassword instanceof InputPassword);

    $form = get_reflection_property($login, "form")->getValue($login);
    $this->assertTrue($form instanceof Form);
    $this->assertEquals("/users/login", $form->attributes["action"]);
    $this->assertEquals([ $inputEmail, $inputPassword ], get_reflection_property($form, "elements")->getValue($form));

    $this->assertEquals("/users/login", $_SERVER["PATH_INFO"]);
    $this->assertContains($form->__toString(), $login->getPresentation());
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /my with status 302.
   */
  public function testValidate() {
    global $session;
    $session->isAuthenticated = false;
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "test1234";
    $_POST["form_id"]  = "users-login";
    $this->testAuthenticatedRedirect();
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile with status 302.
   */
  public function testRedirectTo() {
    global $session;
    $session->isAuthenticated = false;
    $_GET["redirect_to"] = "/profile";
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "test1234";
    $_POST["form_id"]  = "users-login";
    try {
      new Login();
    }
    catch (\MovLib\Exception\RedirectException $e) {
      $this->assertEquals(302, $e->status);
      $this->assertEquals("{$_SERVER["SERVER"]}/profile", $e->route);
      throw $e;
    }
  }

  public function _testWrong() {
    global $session;
    $session->isAuthenticated = false;
    $_POST["form_id"]  = "users-login";
    $login = new Login();
    $this->assertContains("We either don’t know the email address, or the password was wrong.", $login->alerts);
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::validate
   */
  public function testWrongEmail() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["password"] = "test1234";
    $this->_testWrong();
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::validate
   */
  public function testWrongPassword() {
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "phpunit";
    $this->_testWrong();
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile/deactivated with status 302.
   */
  public function testDeactivated() {
    global $session;
    $session->isAuthenticated = false;
    $user = new User(User::FROM_ID, 1);
    $user->deactivate();
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "test1234";
    $_POST["form_id"]  = "users-login";
    try {
      new Login();
    }
    catch (\MovLib\Exception\RedirectException $e) {
      $this->assertEquals(302, $e->status);
      $this->assertEquals("{$_SERVER["SERVER"]}/profile/deactivated", $e->route);
      throw $e;
    }
    finally {
      exec("movdev db -s users");
    }
  }

  /**
   * @covers \MovLib\Presentation\Users\Login::validate
   */
  public function testSignOut() {
    $_SERVER["PATH_INFO"] = "/profile/sign-out";
    $login = new Login();
    $this->assertContains("You’ve been signed out successfully.", $login->alerts);
  }

}
