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
namespace MovLib\Data\User;

use \MovLib\Tool\Console\Command\Development\SeedImport;
use \MovLib\Data\User\FullUser;

/**
 * @coversDefaultClass \MovLib\Data\User\Full
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FullUserTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\User\Full */
  protected $userFull;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->userFull = new FullUser();
  }

  /**
   * @global \MovLib\Tool\Database $db
   */
  public static function tearDownAfterClass() {
    global $db;
    (new SeedImport())->databaseImport([ "users" ]);
    $db->query("TRUNCATE TABLE `tmp`");
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderConstructValid() {
    return [
      [ FullUser::FROM_ID, 1 ],
      [ FullUser::FROM_EMAIL, "richard@fussenegger.info" ],
      [ FullUser::FROM_NAME, "Fleshgrinder" ],
      [ FullUser::FROM_NAME, "fleshgrinder" ],
      [ FullUser::FROM_NAME, "FlEsHgRiNdEr" ],
      [ FullUser::FROM_NAME, "FLESHGRINDER" ],
    ];
  }

  public function dataProviderConstructInvalid() {
    return [
      [ FullUser::FROM_ID, 99 ],
      [ FullUser::FROM_EMAIL, "phpunit@movlib.org" ],
      [ FullUser::FROM_NAME, "PHPUnit" ],
    ];
  }

  public function dataProviderUsernames() {
    return [
      [ "Fleshgrinder" ],
      [ "fleshgrinder" ],
      [ "FlEsHgRiNdEr" ],
      [ "FLESHGRINDER" ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::checkEmail
   */
  public function testCheckEmailExists() {
    $this->assertTrue($this->userFull->checkEmail("richard@fussenegger.info"));
  }

  /**
   * @covers ::checkEmail
   */
  public function testCheckEmailNotExists() {
    $this->assertFalse($this->userFull->checkEmail("phpunit@movlib.org"));
  }

  /**
   * @covers ::checkName
   * @dataProvider dataProviderUsernames
   */
  public function testCheckNameExists($username) {
    $this->assertTrue($this->userFull->checkName($username));
  }

  /**
   * @covers ::checkName
   */
  public function testCheckNameNotExists() {
    $this->assertFalse($this->userFull->checkName("PHPUnit"));
  }

  /**
   * @covers ::commit
   */
  public function testCommit() {
    // Setup
    $this->userFull                     = new FullUser(FullUser::FROM_ID, 1);
    $this->userFull->aboutMe            = "PHPUnit";
    $this->userFull->birthday           = "2000-01-01";
    $this->userFull->countryCode        = "AT";
    $this->userFull->private            = true;
    $this->userFull->realName           = "PHPUnit PHPUnit";
    $this->userFull->sex                = 10;
    $this->userFull->systemLanguageCode = "xx";
    $this->userFull->timeZoneIdentifier = "PHPUnit/PHPUnit";
    $this->userFull->website            = "http://phpunit.net/";
    $this->userFull->commit();

    // Test
    $this->userFull = new FullUser(FullUser::FROM_ID, 1);
    $this->assertEquals("PHPUnit", $this->userFull->aboutMe);
    $this->assertEquals("2000-01-01", $this->userFull->birthday);
    $this->assertEquals("AT", $this->userFull->countryCode);
    $this->assertTrue($this->userFull->private);
    $this->assertEquals("PHPUnit PHPUnit", $this->userFull->realName);
    $this->assertEquals(10, $this->userFull->sex);
    $this->assertEquals("xx", $this->userFull->systemLanguageCode);
    $this->assertEquals("PHPUnit/PHPUnit", $this->userFull->timeZoneIdentifier);
    $this->assertEquals("http://phpunit.net/", $this->userFull->website);

    // Teardown
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::__construct
   * @dataProvider dataProviderConstructValid
   */
  public function testConstruct($from, $value) {
    $this->userFull = new FullUser($from, $value);
    $this->assertEquals(strtolower($this->userFull->{$from}), strtolower($value));
  }

  /**
   * @covers ::__construct
   * @expectedException \DomainException
   * @expectedExceptionMessage Couldn't find user for
   * @dataProvider dataProviderConstructInvalid
   */
  public function testConstructInvalid($from, $value) {
    new FullUser($from, $value);
  }

  /**
   * @covers ::delete
   * @global \MovLib\Tool\Database $db
   */
  public function testDelete() {
    global $db;

    // Setup
    $user = new FullUser(FullUser::FROM_ID, 1);
    $email = $user->email;

    // Test
    $user->deleteAccount();
    $this->assertNull($db->query("SELECT * FROM `users` WHERE `email` = ? LIMIT 1", "s", [ $email ])->get_result()->fetch_row());
    $result = $db->query("SELECT * FROM `users` WHERE `id` = 1")->get_result()->fetch_assoc();
    foreach ($result as $attribute => $value) {
      if ($attribute != "id" && $attribute != "name") {
        $this->assertNull($value);
      }
    }

    // Teardown
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::hashPassword
   */
  public function testHashPassword() {
    $this->assertTrue(password_verify("Test1234", $this->userFull->hashPassword("Test1234")));
  }

  /**
   * @covers ::register
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   */
  public function testRegister() {
    global $db, $i18n;

    // Setup
    $user           = new FullUser();
    $user->name     = "PHPUnit";
    $user->email    = "phpunit@movlib.org";
    $user->password = $user->hashPassword("PHPUnitPassword1234");

    // Test
    $user->join();
    $stmt   = $db->query("SELECT * FROM `users` WHERE `id` = ? LIMIT 1", "d", [ $user->id ]);
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $this->assertEmpty($result["dyn_about_me"]);
    $this->assertEquals($user->name, $result["name"]);
    $this->assertEquals($user->email, $result["email"]);
    $this->assertEquals($i18n->languageCode, $result["system_language_code"]);
    $this->assertEquals($user->password, $result["password"]);

    // Teardown
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::updateEmail
   * @global \MovLib\Tool\Database $db
   */
  public function testUpdateEmail() {
    global $db;

    // Setup
    $user = new FullUser(FullUser::FROM_ID, 1);

    // Test
    $user->updateEmail("phpunit@movlib.org");
    $this->assertEquals("phpunit@movlib.org", $db->query("SELECT `email` FROM `users` WHERE `id` = ? LIMIT 1", "d", [ 1 ])->get_result()->fetch_row()[0]);

    // Teardown
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::updatePassword
   * @global \MovLib\Data\User\Session $session
   */
  public function testUpdatePassword() {
    global $session;

    // Setup
    $user = new FullUser(FullUser::FROM_ID, 1);

    // Test
    $user->updatePassword($user->hashPassword("PHPUnitPassword1234"));
    $session->authenticate("richard@fussenegger.info", "PHPUnitPassword1234");

    // Teardown
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::verifyPassword
   */
  public function testVerifyPassword() {
    $this->userFull = new FullUser(FullUser::FROM_ID, 1);
    $this->userFull->verifyPassword("Test1234");
  }

}