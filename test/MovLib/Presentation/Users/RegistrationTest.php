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

use \MovLib\Data\User\Session;
use \MovLib\Data\User\User;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Users\Registration;
use \MovLib\Tool\Console\Command\Development\SeedImport;

/**
 * @coversDefaultClass \MovLib\Presentation\Users\Registration
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RegistrationTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\Users\Registration */
  protected $registration;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   * @global \MovLib\TestKernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  protected function setUp() {
    global $kernel, $session;
    $kernel->requestMethod = "GET";
    $kernel->requestURI    = "/users/registration";
    $session               = new Session();
    $this->registration    = new Registration();
  }

  /**
   * Called after all tests have been executed.
   */
  public static function tearDownAfterClass() {
    (new SeedImport())->databaseImport([ "users" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderUsernamesIllegalCharacters() {
    return [
      [ "PHP/Unit" ],
      [ "PHP_Unit" ],
      [ "PHP@Unit" ],
      [ "PHP#Unit" ],
      [ "PHP<Unit" ],
      [ "PHP>Unit" ],
      [ "PHP|Unit" ],
      [ "PHP(Unit" ],
      [ "PHP)Unit" ],
      [ "PHP[Unit" ],
      [ "PHP]Unit" ],
      [ "PHP{Unit" ],
      [ "PHP}Unit" ],
      [ "PHP?Unit" ],
      [ "PHP\\Unit" ],
      [ "PHP=Unit" ],
      [ "PHP:Unit" ],
      [ "PHP;Unit" ],
      [ "PHP,Unit" ],
      [ "PHP'Unit" ],
      [ 'PHP"Unit' ],
      [ "PHP&Unit" ],
      [ 'PHP$Unit' ],
      [ "PHP*Unit" ],
      [ "PHP~Unit" ],
      [ "PHP&amp;Unit" ],
      [ "PHP&gt;Unit" ],
      [ "PHP&lt;Unit" ],
      [ "PHP&quot;Unit" ],
      // @todo Extend with more test data!
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @expectedException \MovLib\Exception\Client\RedirectSeeOtherException
   * @expectedExceptionMessage Redirecting user to /my with status 303.
   * @global \MovLib\Data\User\Session $session
   */
  public function testConstruct() {
    global $session;
    $session->isAuthenticated = true;
    new Registration();
  }

  /**
   * @covers ::__construct
   */
  public function testConstructToken() {
    $_GET["token"] = "phpunit";
    new Registration();
  }

  /**
   * @covers ::getContent
   * @global \MovLib\TestKernel $kernel
   */
  public function testGetContent() {
    global $kernel;
    $actual = $this->invoke(new Registration(), "getContent");
    $this->assertTag([ "tag" => "form", "attributes" => [ "autocomplete" => "off" ]], $actual);
    $this->assertTag([ "tag" => "input", "id" => "username" ], $actual);
    $this->assertTag([ "tag" => "input", "id" => "email" ], $actual);
    $this->assertTag([ "tag" => "input", "id" => "password" ], $actual);
    $this->assertTag([ "tag" => "input", "id" => "terms" ], $actual);
    $this->assertNotTag([ "tag" => "small", "content" => "Mistyped something" ], $actual);
    $this->assertNotTag([ "tag" => "a", "attributes" => [ "href" => $kernel->requestURI ] ], $actual);
  }

  /**
   * @covers ::getContent
   * @global \MovLib\TestKernel $kernel
   */
  public function testGetContentAccepted() {
    global $kernel;
    $this->setProperty($this->registration, "accepted", true);
    $actual = $this->invoke($this->registration, "getContent");
    $this->assertTag([ "tag" => "small", "content" => "Mistyped something" ], $actual);
    $this->assertTag([ "tag" => "a", "attributes" => [ "href" => $kernel->requestURI ] ], $actual);
    $this->assertNotTag([ "tag" => "form" ], $actual);
  }

  /**
   * @covers ::validate
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\TestKernel $kernel
   */
  public function testValidate() {
    global $db, $kernel;

    // Setup
    $kernel->requestMethod = "POST";
    $testData = [
      "username" => "PHPUnit",
      "email"    => "phpunit@movlib.org",
      "password" => "PHPUnitPassword1234",
      "terms"    => true,
    ];
    foreach ($testData as $name => $value) {
      $_POST[$name] = $value;
      $this->getProperty($this->registration, $name)->value = $value;
    }
    $this->registration->validate();

    $result = $db->query("SELECT `data` FROM `tmp` WHERE `key` = 'registration-phpunit@movlib.org' LIMIT 1")->get_result()->fetch_assoc();
    $this->assertArrayHasKey("data", $result);
    $data = unserialize($result["data"]);
    $this->assertEquals(1, $data["attempts"]);
    $this->assertEquals("phpunit@movlib.org", $data["email"]);
    $this->assertEquals("PHPUnit", $data["name"]);
    $this->assertTrue(password_verify("PHPUnitPassword1234", $data["password"]));

    $this->assertArrayHasKey(0, $kernel->delayedEmails);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Email\\Users\\Registration", $kernel->delayedEmails[0]);

    $this->assertEquals(202, http_response_code());

    $this->assertPresentationContainsAlert($this->registration, "Registration Successful", Alert::SEVERITY_SUCCESS);

    // Teardown
    $db->query("DELETE FROM `tmp` WHERE `key` = 'registration-phpunit@movlib.org'");
  }

  /**
   * @covers ::validate
   */
  public function testUsernameSpaceBeginning() {
    $_POST["username"]  = " PHPUnit";
    $this->registration->validate();
    $this->assertPresentationContainsAlert($this->registration, "The username cannot begin with a space.");
  }

  /**
   * @covers ::validate
   */
  public function testUsernameSpaceEnding() {
    $_POST["username"] = "PHPUnit ";
    $this->registration->validate();
    $this->assertPresentationContainsAlert($this->registration, "The username cannot end with a space.");
  }

  /**
   * @covers ::validate
   */
  public function testUsernameSpacesInRow() {
    $_POST["username"] = "PHP  Unit";
    $this->registration->validate();
    $this->assertPresentationContainsAlert($this->registration, "The username cannot contain multiple spaces in a row.");
  }

  /**
   * @covers ::validate
   * @dataProvider dataProviderUsernamesIllegalCharacters
   */
  public function testUsernameIllegalCharacters($username) {
    $_POST["username"] = $username;
    $inputUsername = $this->getProperty($this->registration, "username");
    $inputUsername->value = $username;
    $inputUsername->validate();
    $this->registration->validate();
    $this->assertPresentationContainsAlert($this->registration, "The username cannot contain any of the following characters:");
  }

  /**
   * @covers ::validate
   */
  public function testUsernameTooLong() {
    $this->getProperty($this->registration, "username")->value = $_POST["username"] = str_repeat("PHPUnit", User::NAME_MAXIMUM_LENGTH * 2);
    $this->registration->validate();
    $this->assertPresentationContainsAlert($this->registration, "The username is too long: it must be");
  }

  /**
   * @covers ::validate
   */
  public function testUsernameExists() {
    $this->getProperty($this->registration, "username")->value = $_POST["username"] = "Fleshgrinder";
    $this->registration->validate();
    $this->assertPresentationContainsAlert($this->registration, "The username is already taken, please choose another one.");
  }

  /**
   * @covers ::validate
   */
  public function testTerms() {
    $this->getProperty($this->registration, "username")->value = $_POST["username"] = "PHPUnit";
    $errors["terms"] = "terms not accepted";
    $this->registration->validate($errors);
    $this->assertPresentationContainsAlert($this->registration, "You have to accept the");
  }

  /**
   * @covers ::validate
   * @global \MovLib\TestKernel $kernel
   */
  public function testEmailExists() {
    global $kernel;
    $this->getProperty($this->registration, "username")->value = $_POST["username"] = "PHPUnit";
    $this->getProperty($this->registration, "email")->value = "richard@fussenegger.info";
    $this->registration->validate();
    $this->assertArrayHasKey(0, $kernel->delayedEmails);
    $this->assertInstanceOf("\\MovLib\\Presentation\\Email\\Users\\RegistrationEmailExists", $kernel->delayedEmails[0]);
  }

  /**
   * @covers ::validate
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\TestKernel $kernel
   */
  public function testTooManyRegistrationAttempts() {
    global $db, $kernel;

    // Setup
    $kernel->requestMethod = "POST";
    $testData = [
      "username" => "PHPUnit",
      "email"    => "phpunit@movlib.org",
      "password" => "PHPUnitPassword1234",
      "terms"    => true,
    ];
    foreach ($testData as $name => $value) {
      $_POST[$name] = $value;
      $this->getProperty($this->registration, $name)->value = $value;
    }

    for ($i = 0; $i <= User::MAXIMUM_ATTEMPTS + 1; ++$i) {
      $this->registration->validate();
    }
    $this->assertPresentationContainsAlert($this->registration, "Too many registration attempts with this email address. Please wait 24 hours before trying again.");

    // Teardown
    $db->query("TRUNCATE TABLE `tmp`");
  }

  /**
   * @covers ::__construct
   * @covers ::validateToken
   * @todo Implement validateToken
   */
  public function testValidateToken() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
