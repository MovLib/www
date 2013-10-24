<?php

/* !
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

use \MovLib\Data\User\Full as UserFull;
use \MovLib\Data\User\Session;

/**
 * @coversDefaultClass \MovLib\Data\User
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FullTest extends \MovLib\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public static function dataProviderTestCheckNameExists() {
    return [
      [ "Fleshgrinder" ],
      [ "fleshgrinder" ],
      [ "FlEsHgRiNdEr" ],
      [ "FLESHGRINDER" ],
    ];
  }

  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public static function tearDownAfterClass() {
    exec("movdev db -a"); // Static context, do not use $this
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::checkEmail
   */
  public function testCheckEmailExists() {
    $this->assertTrue((new UserFull(UserFull::FROM_ID, 1))->checkEmail());
  }

  /**
   * @covers ::checkEmail
   */
  public function testCheckEmailNotExists() {
    $user        = new UserFull();
    $user->email = "phpunit@movlib.org";
    $this->assertFalse($user->checkEmail());
  }

  /**
   * @covers ::checkName
   * @dataProvider dataProviderTestCheckNameExists
   */
  public function testCheckNameExists($name) {
    // We have to check that the query itself is agnostic to case changes (same as we did in the constructor test).
    $user       = new UserFull();
    $user->name = $name;
    $this->assertTrue($user->checkName());
  }

  /**
   * @covers ::checkName
   */
  public function testCheckNameNotExists() {
    $user       = new UserFull();
    $user->name = "PHPUnit";
    $this->assertFalse($user->checkName());
  }

  /**
   * @covers ::commit
   */
  public function testCommit() {
    $user                     = new UserFull(UserFull::FROM_ID, 1);
    $user->birthday           = "2000-01-01";
    $user->countryId          = 1;
    $user->profile            = "PHPUnit";
    $user->private            = true;
    $user->realName           = "PHPUnit PHPUnit";
    $user->sex                = 10;
    $user->systemLanguageCode = "xx";
    $user->timeZoneId         = "PHPUnit/PHPUnit";
    $user->website            = "http://phpunit.net/";
    $user->commit();

    $user = new UserFull(UserFull::FROM_ID, 1);
    $this->assertEquals("2000-01-01", $user->birthday);
    $this->assertEquals(1, $user->countryId);
    $this->assertEquals("PHPUnit", $user->profile);
    $this->assertTrue($user->private);
    $this->assertEquals("PHPUnit PHPUnit", $user->realName);
    $this->assertEquals(10, $user->sex);
    $this->assertEquals("xx", $user->systemLanguageCode);
    $this->assertEquals("PHPUnit/PHPUnit", $user->timeZoneId);
    $this->assertEquals("http://phpunit.net/", $user->website);

    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::deactivate
   * @covers ::deleteImageOriginalAndStyles
   */
  public function testDeactivate() {
    $user = new UserFull(UserFull::FROM_ID, 1);
    $user->deactivate();

    $user = new UserFull(UserFull::FROM_ID, 1);
    $this->assertFileNotExists($this->invoke($user, "getImagePath", [ UserFull::IMAGE_STYLE_SPAN_02 ]));
    $this->assertNull($this->getProperty($user, "imageChanged"));
    $this->assertNull($this->getProperty($user, "imageExtension"));
    $this->assertNull($user->birthday);
    $this->assertNull($user->countryId);
    $this->assertTrue($user->deactivated);
    $this->assertEmpty($user->profile);
    //$this->assertNull($user->facebook);
    //$this->assertNull($user->googlePlus);
    $this->assertFalse($user->private);
    $this->assertNull($user->realName);
    $this->assertEquals(0, $user->sex);
    $this->assertEquals(ini_get("date.timezone"), $user->timeZoneId);
    //$this->assertNull($user->twitter);
    $this->assertNull($user->website);

    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::getRandomPassword
   */
  public function testGetRandomPassword() {
    $this->assertRegExp("/.{20}/", UserFull::getRandomPassword());
  }

  /**
   * @covers ::getRegistrationData
   * @global \MovDev\Database $db
   */
  public function testGetRegistrationData() {
    global $db;
    $user        = new UserFull();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    $user->prepareRegistration("Test1234");
    $data        = $user->getRegistrationData();
    foreach ([ "name", "email", "password", "attempts" ] as $key) {
      $this->assertArrayHasKey($key, $data);
    }
    $this->assertEquals("PHPUnit", $data["name"]);
    $this->assertEquals("phpunit@movlib.org", $data["email"]);
    $this->assertEquals(1, $data["attempts"]);
    $this->assertTrue(password_verify("Test1234", $data["password"]));

    $db->query("TRUNCATE TABLE `tmp`");
    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::getRegistrationData
   * @expectedException \MovLib\Exception\UserException
   * @expectedExceptionMessage No data found
   * @global \MovDev\Database $db
   */
  public function testGetRegistrationDataExpired() {
    global $db;
    try {
      $user        = new UserFull();
      $user->name  = "PHPUnit";
      $user->email = "phpunit@movlib.org";
      $user->prepareRegistration("Test1234");
      $db->query("UPDATE `tmp` SET `created` = FROM_UNIXTIME(?) WHERE `key` = ?", "ss", [ strtotime("-25 hours"), "registration-phpunit@movlib.org" ]);
      $user->getRegistrationData();
    }
    finally {
      $db->query("TRUNCATE TABLE `tmp`");
    }
  }

  /**
   * @covers ::getRegistrationData
   * @expectedException \MovLib\Exception\UserException
   * @expectedExceptionMessage No data found
   */
  public function testGetRegistrationDataNoRecord() {
    (new UserFull())->getRegistrationData();
  }

  /**
   * @covers ::passwordHash
   */
  public function testPasswordHash() {
    $this->assertTrue(password_verify("Test1234", $this->invoke(new UserFull(), "passwordHash", [ "Test1234" ])));
  }

  /**
   * @covers ::prepareRegistration
   * @global \MovDev\Database $db
   */
  public function testPrepareRegistration() {
    global $db;
    $user        = new UserFull();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    $this->assertEquals($user, $user->prepareRegistration("Test1234"));
    $db->query("TRUNCATE TABLE `tmp`");
  }

  /**
   * @covers ::prepareRegistration
   * @global \MovDev\Database $db
   */
  public function testPrepareRegistrationExpiredAttempts() {
    global $db;
    $user        = new UserFull();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    $c           = UserFull::MAXIMUM_ATTEMPTS * 2;
    $time        = strtotime("-25 hours");
    $key         = "registration-{$user->email}";
    for ($i = 0; $i < $c; ++$i) {
      $user->prepareRegistration("Test1234");
      $db->query("UPDATE `tmp` SET `created` = FROM_UNIXTIME({$time}) WHERE `key` = '{$key}'");
    }
    $db->query("TRUNCATE TABLE `tmp`");
  }

  /**
   * @covers ::prepareRegistration
   * @expectedException \MovLib\Exception\UserException
   * @expectedExceptionMessage Too many registration attempts
   * @global \MovDev\Database $db
   */
  public function testPrepareRegistrationTooManyAttempts() {
    global $db;
    $user        = new UserFull();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    try {
      while (true) {
        $user->prepareRegistration("Test1234");
      }
    }
    finally {
      $db->query("TRUNCATE TABLE `tmp`");
    }
  }

  /**
   * @covers ::reactivate
   * @global \MovDev\Database $db
   */
  public function testReactivate() {
    global $db;
    $user = new UserFull(UserFull::FROM_ID, 1);
    $user->deactivate()->reactivate();
    $this->assertFalse($user->deactivated);
    $stmt = $db->query("SELECT `deactivated` FROM `users` WHERE `user_id` = ?", "d", [ 1 ]);
    $stmt->bind_result($deactivated);
    $stmt->fetch();
    $this->assertFalse((boolean) $deactivated);
    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::register
   * @global \MovDev\Database $db
   */
  public function testRegister() {
    global $db, $i18n;
    $user        = new UserFull();
    $user->name  = "PHPUnit";
    $user->email = "phpunit@movlib.org";
    $user->prepareRegistration("Test1234");
    $data        = $user->getRegistrationData();
    $user->register($data["password"]);
    $this->assertEquals("PHPUnit", $user->name);
    $this->assertEquals("phpunit@movlib.org", $user->email);
    $this->assertEquals(4, $user->id);

    $result = $db->query("SELECT * FROM `users` WHERE `user_id` = ? LIMIT 1", "d", [ $user->id ])->get_result()->fetch_assoc();
    $this->assertEmpty($result["dyn_profile"]);
    $this->assertEquals("PHPUnit", $result["name"]);
    $this->assertEquals("phpunit", $result["avatar_name"]);
    $this->assertEquals("phpunit@movlib.org", $result["email"]);
    $this->assertEquals($i18n->languageCode, $result["system_language_code"]);
    $this->assertTrue(password_verify("Test1234", $result["password"]));

    $db->query("TRUNCATE TABLE `tmp`");
    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::updateEmail
   * @global \MovDev\Database $db
   */
  public function testUpdateEmail() {
    global $db;
    $user = new UserFull(UserFull::FROM_ID, 1);
    $this->assertEquals("richard@fussenegger.info", $user->email);
    $user->updateEmail("phpunit@movlib.org");
    $this->assertEquals("phpunit@movlib.org", $user->email);
    $stmt = $db->query("SELECT `email` FROM `users` WHERE `user_id` = ?", "d", [ 1 ]);
    $stmt->bind_result($email);
    $stmt->fetch();
    $this->assertEquals("phpunit@movlib.org", $email);
    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::updatePassword
   * @global \MovDev\Database $db
   */
  public function testUpdatePassword() {
    global $db;
    $session = new Session();
    $session->authenticate("richard@fussenegger.info", "Test1234");
    $stmt    = $db->query("SELECT `password` FROM `users` WHERE `user_id` = ? LIMIT 1", "d", [ 1 ]);
    $stmt->bind_result($oldHash);
    $stmt->fetch();
    $stmt->close();

    $user = new UserFull(UserFull::FROM_ID, 1);
    $user->updatePassword("phpunitPassword");
    $session->authenticate("richard@fussenegger.info", "phpunitPassword");
    $stmt = $db->query("SELECT `password` FROM `users` WHERE `user_id` = ? LIMIT 1", "d", [ 1 ]);
    $stmt->bind_result($newHash);
    $stmt->fetch();
    $stmt->close();

    $this->assertNotEquals($oldHash, $newHash);
    $this->exec("movdev db -s users");
  }

  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::getImageStyleAttributes
   * @todo Implement getImageStyleAttributes
   */
  public function testGetImageStyleAttributes() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
