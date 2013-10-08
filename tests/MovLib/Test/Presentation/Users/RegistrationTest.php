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
use \MovLib\Data\User;
use \MovLib\Presentation\Email\Users\Registration as RegistrationEmail;
use \MovLib\Presentation\Email\Users\RegistrationEmailExists;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Users\Registration;

/**
 * @coversDefaultClass \MovLib\Presentation\Users\Registration
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RegistrationTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  private static $db;

  private static $sessionBackup;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function setUpBeforeClass() {
    global $session;
    self::$db             = new \MovDev\Database();
    self::$sessionBackup  = clone $session;
    $session              = new Session();
    $_SERVER["PATH_INFO"] = "/users/registration";
  }

  public static function tearDownAfterClass() {
    global $session;
    $session = self::$sessionBackup;
    unset($_SERVER["PATH_INFO"]);
    unset($_POST);
    unset($_GET);
    exec("movdev db -a");
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  private function _testUsername($username, $contains) {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["form_id"]  = "users-registration";
    $_POST["terms"]    = "true";
    $_POST["username"] = $username;
    $this->assertContains($contains, (new Registration())->getPresentation());
  }

  private function _testTokenValidation() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["form_id"]  = "users-registration";
    $_POST["terms"]    = "true";
    $_POST["username"] = "PHPUnit";
    new Registration();
    foreach (get_reflection_property("\\MovLib\\Data\\Delayed\\Mailer", "emails")->getValue() as $email) {
      if ($email instanceof RegistrationEmail) {
        $email->init();
        $_GET["token"] = get_reflection_property($email, "token")->getValue($email);
        $this->assertNotEmpty($_GET["token"]);
        break;
      }
    }
    unset($_POST);
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /my with status 302.
   * @group Presentation
   */
  public function testRedirectIfAuthenticated() {
    global $session;
    $session = self::$sessionBackup;
    try {
      new Registration();
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
    $registration = new Registration();

    $this->assertEquals("Registration", get_reflection_property($registration, "title")->getValue($registration));

    $inputEmail = get_reflection_property($registration, "email")->getValue($registration);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputEmail", $inputEmail);
    $this->assertEquals("email", get_reflection_property($inputEmail, "id")->getValue($inputEmail));
    $this->assertEquals("Email Address", get_reflection_property($inputEmail, "label")->getValue($inputEmail));
    $this->assertTrue(in_array("autofocus", $inputEmail->attributes));
    $this->assertArrayHasKey("placeholder", $inputEmail->attributes);
    $this->assertEquals("Enter your email address", $inputEmail->attributes["placeholder"]);

    $help = get_reflection_property($inputEmail, "help")->getValue($inputEmail);
    $this->assertEquals("<a href='/users/login'>Already have an account?</a>", get_reflection_property($help, "content")->getValue($help));
    $this->assertFalse(get_reflection_property($help, "popup")->getValue($help));

    $inputUsername = get_reflection_property($registration, "username")->getValue($registration);
    $this->assertInstanceOf("\\MovLib\Presentation\\Partial\\FormElement\\InputText", $inputUsername);
    $this->assertEquals("username", get_reflection_property($inputUsername, "id")->getValue($inputUsername));
    $this->assertEquals("Username", get_reflection_property($inputUsername, "label")->getValue($inputUsername));
    $this->assertArrayHasKey("aria-required", $inputUsername->attributes);
    $this->assertArrayHasKey("maxlength", $inputUsername->attributes);
    $this->assertArrayHasKey("placeholder", $inputUsername->attributes);
    $this->assertEquals("true", $inputUsername->attributes["aria-required"]);
    $this->assertEquals(User::MAX_LENGTH_NAME, $inputUsername->attributes["maxlength"]);
    $this->assertEquals("Enter your desired username", $inputUsername->attributes["placeholder"]);
    $this->assertTrue(in_array("required", $inputUsername->attributes));

    $inputTerms = get_reflection_property($registration, "terms")->getValue($registration);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputCheckbox", $inputTerms);
    $inputTermsLabel = get_reflection_property($inputTerms, "label")->getValue($inputTerms);
    $this->assertContains(">Terms of Use<", $inputTermsLabel);
    $this->assertContains(">Privacy Policy<", $inputTermsLabel);
    $this->assertArrayHasKey("aria-required", $inputTerms->attributes);
    $this->assertEquals("true", $inputTerms->attributes["aria-required"]);
    $this->assertTrue(in_array("required", $inputTerms->attributes));

    $form = get_reflection_property($registration, "form")->getValue($registration);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\Form", $form);
    $this->assertEquals($_SERVER["PATH_INFO"], $form->attributes["action"]);
    $this->assertEquals("span span--6 offset--3", $form->attributes["class"]);
    $this->assertEquals([ $inputEmail, $inputUsername, $inputTerms ], get_reflection_property($form, "elements")->getValue($form));

    $this->assertArrayHasKey(0, $form->actionElements);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Partial\\FormElement\\InputSubmit", $form->actionElements[0]);
    $this->assertEquals("Click here to sign up after you filled out all fields", $form->actionElements[0]->attributes["title"]);
    $this->assertEquals("Sign Up", $form->actionElements[0]->attributes["value"]);
    $this->assertEquals(1, count($form->actionElements));
  }

  /**
   * @covers ::getContent
   * @group Presentation
   */
  public function testContentAccepted() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["form_id"]  = "users-registration";
    $_POST["terms"]    = "true";
    $_POST["username"] = "PHPUnit";
    $registration      = new Registration();
    $content           = get_reflection_method($registration, "getContent")->invoke($registration);
    $this->assertNotContains((string) get_reflection_property($registration, "form")->getValue($registration), $content);
    $this->assertEquals("<div class='container'><small>Mistyped something? No problem, simply <a href='/users/registration'>go back</a> and fill out the form again.</small></div>", $content);
  }

  /**
   * @covers ::getContent
   * @group Presentation
   */
  public function testGetContentNotAccepted() {
    $registration = new Registration();
    $form         = get_reflection_property($registration, "form")->getValue($registration);
    $this->assertEquals("<div class='container'><div class='row'>{$form}</div></div>", get_reflection_method($registration, "getContent")->invoke($registration));
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testUsernameSpaceAtBeginning() {
    $this->_testUsername(" PHPUnit", "The username cannot begin with a space.");
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testUsernameSpaceAtEnd() {
    $this->_testUsername("PHPUnit ", "The username cannot end with a space.");
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testUsernameMultipleSpaces() {
    $this->_testUsername("PHP  Unit", "The username cannot contain multiple spaces in a row.");
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testUsernameSlash() {
    $this->_testUsername("PHP/Unit", "The username cannot contain slashes.");
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testUsernameLength() {
    $this->_testUsername(str_repeat("PHPUnit ", 10), "The username is too long: it must be " . User::MAX_LENGTH_NAME . " characters or less.");
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testUsernameExists() {
    $this->_testUsername("Fleshgrinder", "The username <em class='placeholder'>Fleshgrinder</em> is already taken, please choose another one.");
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testEmailExists() {
    $_POST["email"]    = "richard@fussenegger.info";
    $_POST["form_id"]  = "users-registration";
    $_POST["terms"]    = "true";
    $_POST["username"] = "PHPUnit";
    (new Registration())->validate();
    $found = false;
    foreach (get_reflection_property("\\MovLib\\Data\\Delayed\\Mailer", "emails")->getValue() as $email) {
      if (($found = $email instanceof RegistrationEmailExists) === true) {
        break;
      }
    }
    $this->assertTrue($found);
  }

  /**
   * @covers ::validate
   * @group Presentation
   * @group Validation
   */
  public function testValidRegistration() {
    $_POST["email"]    = "phpunit@movlib.org";
    $_POST["form_id"]  = "users-registration";
    $_POST["terms"]    = "true";
    $_POST["username"] = "PHPUnit";
    $registration = new Registration();
    $found = false;
    foreach (get_reflection_property("\\MovLib\\Data\\Delayed\\Mailer", "emails")->getValue() as $email) {
      if (($found = $email instanceof RegistrationEmail) === true) {
        break;
      }
    }
    $this->assertTrue($found);
    $this->assertTrue(get_reflection_property($registration, "accepted")->getValue($registration));
    $this->assertEquals(202, http_response_code());
    $this->assertContains(Alert::SEVERITY_INFO, $registration->alerts);
    $this->assertContains("Registration Successful", $registration->alerts);
    $this->assertContains("An email with further instructions has been sent to <em class='placeholder'>phpunit@movlib.org</em>.", $registration->alerts);
    self::$db->query("DELETE FROM `users` WHERE `email` = ?", "s", [ $_POST["email"] ]);
  }

  /**
   * @covers ::__construct
   * @group Presentation
   * @group Validation
   */
  public function testTokenValidationCall() {
    $_GET["token"] = "phpunit";
    $stub = $this->getMock("\\MovLib\\Presentation\\Users\\Registration", [ "validateToken" ]);
    $stub->expects($this->once())->method("validateToken");
    $stub->__construct();
  }

  /**
   * @covers ::validateToken
   * @group Presentation
   * @group Validation
   */
  public function testUsernameAndEmailAlreadyRegistered() {
    $this->_testTokenValidation();
    (new User())->register("PHPUnit", "phpunit@movlib.org", "phpunit1234");
    $registration  = new Registration();
    $inputEmail    = get_reflection_property($registration, "email")->getValue($registration);
    $inputUsername = get_reflection_property($registration, "username")->getValue($registration);
    foreach ([ $inputEmail, $inputUsername ] as $input) {
      $this->assertArrayHasKey("class", $input->attributes);
      $this->assertContains("invalid", $input->attributes["class"]);
      $this->assertArrayHasKey("aria-invalid", $input->attributes);
      $this->assertEquals("true", $input->attributes["aria-invalid"]);
    }
    $this->assertContains("Seems like you already signed up with this email address, did you <a href='/users/reset-password'>forget your password</a>?", $registration->getPresentation());
    self::$db->query("DELETE FROM `users` WHERE `email` = ?", "s", [ "phpunit@movlib.org" ]);
  }

  /**
   * @covers ::validateToken
   * @group Presentation
   * @group Validation
   */
  public function testUsernameTakenInMeantime() {
    $this->_testTokenValidation();
    (new User())->register("PHPUnit", "movlib@phpunit.de", "phpunit1234");
    $registration  = new Registration();
    $inputUsername = get_reflection_property($registration, "username")->getValue($registration);
    $this->assertArrayHasKey("class", $inputUsername->attributes);
    $this->assertContains("invalid", $inputUsername->attributes["class"]);
    $this->assertArrayHasKey("aria-invalid", $inputUsername->attributes);
    $this->assertEquals("true", $inputUsername->attributes["aria-invalid"]);
    $this->assertContains("Unfortunately in the meantime someone took your desired username <em class='placeholder'>PHPUnit</em>, please choose another one.", $registration->getPresentation());
    self::$db->query("DELETE FROM `users` WHERE `email` = ?", "s", [ "movlib@phpunit.de" ]);
  }

  /**
   * @covers ::validateToken
   * @expectedException \MovLib\Exception\RedirectException
   * @expectedExceptionMessage Redirecting user to /profile/password-settings with status 302.
   * @group Presentation
   * @group Validation
   */
  public function testValidToken() {
    global $session;
    $sessionStub = $this->getMock("\\MovLib\\Data\\Session");
    $sessionStub->expects($this->once())->method("authenticate");
    $session = $sessionStub;
    $this->_testTokenValidation();
    try {
      new Registration();
    }
    finally {
      $this->assertArrayHasKey("password", $_SESSION);
      $this->assertNotEmpty($_SESSION["password"]);
      $userData = (new \MovDev\Database())->selectAssoc("SELECT * FROM `users` WHERE `email` = ?", "s", [ "phpunit@movlib.org" ]);
      $this->assertNotEmpty($userData);
      $this->assertEquals("PHPUnit", $userData["name"]);
      self::$db->query("DELETE FROM `users` WHERE `email` = ?", "s", [ "phpunit@movlib.org" ]);
    }
  }

}
