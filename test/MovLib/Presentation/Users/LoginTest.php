<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Users;

use \MovLib\Data\UnixShell as sh;
use \MovLib\Data\User\Session;
use \MovLib\Data\User\Full as User;
use \MovLib\Presentation\Users\Login;

/**
 * @coversDefaultClass \MovLib\Presentation\Users\Login
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class LoginTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  private static $sessionBackup;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function setUpBeforeClass() {
    global $session;
    self::$sessionBackup  = clone $session;
    $_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"] = "/users/login";
  }

  public function setUp() {
    global $session;
    $session = new Session();
  }

  public static function tearDownAfterClass() {
    unset($_SERVER["PATH_INFO"]);
  }

  public function tearDown() {
    global $session;
    $session = self::$sessionBackup;
    unset($_GET);
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
   * @expectedException \MovLib\Exception\Client\RedirectSeeOtherException
   * @expectedExceptionMessage Redirecting user to /my with status 303.
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
    */
  public function testFormConfiguration() {
    $login = new Login();

    $inputEmail = $this->getProperty($login, "email");
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputEmail", $inputEmail);
    $this->assertEquals("email", $this->getProperty($inputEmail, "id"));
    $this->assertEquals("Email Address", $this->getProperty($inputEmail, "label"));
    $this->assertTrue(in_array("autofocus", $inputEmail->attributes));
    $this->assertArrayHasKey("placeholder", $inputEmail->attributes);
    $this->assertEquals("Enter your email address", $inputEmail->attributes["placeholder"]);

    $help = $this->getProperty($inputEmail, "help");
    $this->assertEquals("<a href='/users/reset-password'>Forgot your password?</a>", $this->getProperty($help, "content"));
    $this->assertFalse($this->getProperty($help, "popup"));

    $inputPassword = $this->getProperty($login, "password");
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputPassword", $inputPassword);
    $this->assertEquals("password", $this->getProperty($inputPassword, "id"));
    $this->assertEquals("Password", $this->getProperty($inputPassword, "label"));
    $this->assertArrayHasKey("placeholder", $inputPassword->attributes);
    $this->assertEquals("Enter your password", $inputPassword->attributes["placeholder"]);

    $form = $this->getProperty($login, "form");
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Form", $form);
    $this->assertEquals($_SERVER["PATH_INFO"], $form->attributes["action"]);
    $this->assertEquals([ $inputEmail, $inputPassword ], $this->getProperty($form, "elements"));

    $this->assertArrayHasKey(0, $form->actionElements);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputSubmit", $form->actionElements[0]);
    $this->assertEquals("Click here to sign in after you filled out all fields", $form->actionElements[0]->attributes["title"]);
    $this->assertEquals("Sign In", $form->actionElements[0]->attributes["value"]);
    $this->assertEquals(1, count($form->actionElements));
  }

  /**
   * @covers ::getContent
    */
  public function testGetContent() {
    $login   = new Login();
    $content = $this->invoke($login, "getContent");
    $form    = $this->getProperty($login, "form");
    $this->assertEquals("<div class='container'><div class='row'>{$form}</div></div>", $content);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\Client\RedirectSeeOtherException
   * @expectedExceptionMessage Redirecting user to /my with status 303.
     */
  public function testValidCredentials() {
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["form_id"]  = "users-login";
    $_POST["password"] = "Test1234";
    (new Login());
  }

  /**
   * @covers ::__construct
   * @covers ::validate
   * @expectedException \MovLib\Exception\Client\RedirectSeeOtherException
   * @expectedExceptionMessage Redirecting user to /profile with status 303.
     */
  public function testRedirectToViaGetParameter() {
    $_GET["redirect_to"] = "/profile";
    $this->testValidCredentials();
  }

  /**
   * @covers ::__construct
   * @covers ::validate
   * @expectedException \MovLib\Exception\Client\RedirectSeeOtherException
   * @expectedExceptionMessage Redirecting user to /profile?foo=bar with status 303.
     */
  public function testRedirectToOnDifferentRoute() {
    $pathInfo = $_SERVER["PATH_INFO"];
    $_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"] = "/profile?foo=bar";
    $this->testValidCredentials();
    $_SERVER["PATH_INFO"] = $pathInfo;
  }

  /**
   * @covers ::validate
     */
  public function testInvalidEmail() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["password"] = "Test1234";
    $this->_testInvalidCredentials();
  }

  /**
   * @covers ::validate
     */
  public function testInvalidPassword() {
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "WrongPassword123";
    $this->_testInvalidCredentials();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\Client\RedirectSeeOtherException
   * @expectedExceptionMessage Redirecting user to /profile/deactivated with status 303.
    */
  public function testDeactivated() {
    (new User(User::FROM_ID, 1))->deactivate();
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["password"] = "Test1234";
    $_POST["form_id"]  = "users-login";
    try {
      new Login();
    }
    catch (\MovLib\Exception\RedirectException $e) {
      throw $e;
    }
    finally {
      sh::execute("movlib si -d users");
    }
  }

  /**
   * @covers ::__construct
    */
  public function testSignOut() {
    global $session;
    $session              = $this->getMock("\\MovLib\\Data\\User\\Session");
    $this->invoke($session, "init", [ 1 ]);
    $session->expects($this->once())->method("destroy");
    $_SERVER["PATH_INFO"] = $_SERVER["REQUEST_URI"] = "/profile/sign-out";
    $login                = new Login();
    $this->assertContains("We hope to see you again soon.", $login->alerts);
    $this->assertContains("You’ve been signed out successfully.", $login->alerts);
    $session              = new Session();
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

  /**
   * @covers ::getBreadcrumbs
   * @todo Implement getBreadcrumbs
   */
  public function testGetBreadcrumbs() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
