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

use \MovLib\Data\Session;
use \MovLib\Data\UserExtended;
use \MovLib\Presentation\Users\Login;

/**
 * @coversDefaultClass \MovLib\Presentation\Users\Login
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class LoginTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  private static $sessionBackup;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function setUpBeforeClass() {
    global $session;
    self::$sessionBackup  = clone $session;
    $session              = new Session();
    $_SERVER["PATH_INFO"] = "/users/login";
  }

  public static function tearDownAfterClass() {
    global $session;
    $session = self::$sessionBackup;
    unset($_SERVER["PATH_INFO"]);
    unset($_POST);
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  private function _testInvalidCredentials() {
    $_POST["form_id"]  = "users-login";
    $this->assertContains("We either don’t know the email address, or the password was wrong.", (new Login())->alerts);
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /my with status 302.
   * @group Presentation
   */
  public function testAuthenticatedRedirect() {
    global $session;
    $session = self::$sessionBackup;
    try {
      new Login();
    }
    catch (\MovLib\Exception\RedirectException $e) {
      throw $e;
    }
    finally {
      $session = new Session();
    }
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testFormConfiguration() {
    $login = new Login();

    $inputEmail = get_reflection_property($login, "email")->getValue($login);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputEmail", $inputEmail);
    $this->assertEquals("email", get_reflection_property($inputEmail, "id")->getValue($inputEmail));
    $this->assertEquals("Email Address", get_reflection_property($inputEmail, "label")->getValue($inputEmail));
    $this->assertTrue(in_array("autofocus", $inputEmail->attributes));
    $this->assertArrayHasKey("placeholder", $inputEmail->attributes);
    $this->assertEquals("Enter your email address", $inputEmail->attributes["placeholder"]);

    $help = get_reflection_property($inputEmail, "help")->getValue($inputEmail);
    $this->assertEquals("<a href='/users/reset-password'>Forgot your password?</a>", get_reflection_property($help, "content")->getValue($help));
    $this->assertFalse(get_reflection_property($help, "popup")->getValue($help));

    $inputPassword = get_reflection_property($login, "password")->getValue($login);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputPassword", $inputPassword);
    $this->assertEquals("password", get_reflection_property($inputPassword, "id")->getValue($inputPassword));
    $this->assertEquals("Password", get_reflection_property($inputPassword, "label")->getValue($inputPassword));
    $this->assertArrayHasKey("placeholder", $inputPassword->attributes);
    $this->assertEquals("Enter your password", $inputPassword->attributes["placeholder"]);

    $form = get_reflection_property($login, "form")->getValue($login);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Form", $form);
    $this->assertEquals($_SERVER["PATH_INFO"], $form->attributes["action"]);
    $this->assertEquals([ $inputEmail, $inputPassword ], get_reflection_property($form, "elements")->getValue($form));

    $this->assertArrayHasKey(0, $form->actionElements);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputSubmit", $form->actionElements[0]);
    $this->assertEquals("Click here to sign in after you filled out all fields", $form->actionElements[0]->attributes["title"]);
    $this->assertEquals("Sign In", $form->actionElements[0]->attributes["value"]);
    $this->assertEquals(1, count($form->actionElements));
  }

  /**
   * @covers ::getContent
   * @group Presentation
   */
  public function testGetContent() {
    $login   = new Login();
    $content = get_reflection_method($login, "getContent")->invoke($login);
    $form    = get_reflection_property($login, "form")->getValue($login);
    $this->assertEquals("<div class='container'><div class='row'>{$form}</div></div>", $content);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /my with status 302.
   * @group Presentation
   * @group Validation
   */
  public function testValidCredentials() {
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["form_id"]  = "users-login";
    $_POST["password"] = "test1234";
    new Login();
  }

  /**
   * @covers ::__construct
   * @covers ::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile with status 302.
   * @group Presentation
   * @group Validation
   */
  public function testRedirectToViaGetParameter() {
    $_GET["redirect_to"] = rawurlencode("/profile");
    $this->testValidCredentials();
  }

  /**
   * @covers ::__construct
   * @covers ::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile?foo=bar with status 302.
   * @group Presentation
   * @group Validation
   */
  public function testRedirectToOnDifferentRoute() {
    $_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"] = "/profile?foo=bar";
    $this->testValidCredentials();
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testInvalidEmail() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["password"] = "test1234";
    $this->_testInvalidCredentials();
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testInvalidPassword() {
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "phpunit";
    $this->_testInvalidCredentials();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile/deactivated with status 302.
   * @group Presentation
   */
  public function testDeactivated() {
    (new UserExtended(UserExtended::FROM_ID, 1))->deactivate();
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "test1234";
    $_POST["form_id"]  = "users-login";
    try {
      new Login();
    }
    catch (\MovLib\Exception\RedirectException $e) {
      throw $e;
    }
    finally {
      exec("movdev db -s users");
    }
  }

  /**
   * @covers ::__construct
   * @group Presentation
   */
  public function testSignOut() {
    global $session;
    $session              = $this->getMock("\\MovLib\\Data\\Session");
    get_reflection_method($session, "init")->invokeArgs($session, [ 1 ]);
    $session->expects($this->once())->method("destroy");
    $_SERVER["PATH_INFO"] = "/profile/sign-out";
    $login                = new Login();
    $this->assertContains("We hope to see you again soon.", $login->alerts);
    $this->assertContains("You’ve been signed out successfully.", $login->alerts);
    $session              = new Session();
  }

}
