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
namespace MovLib\Test\Data;

use \MovDev\Database;
use \MovLib\Data\Session;
use \MovLib\Data\User;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class SessionTest extends \PHPUnit_Framework_TestCase {

  /** @var \MovLib\Data\User */
  public $user;

  public function tearDown() {
    global $session;
    if ($this->user instanceof User) {
      $this->user->reactivate();
    }
    $session->authentication = time();
  }

  /**
   * @covers \MovLib\Data\Session::__construct
   */
  public function testConstruct() {
    $session = new Session();
    $this->assertEquals(ini_get("session.name"), get_reflection_property($session, "name")->getValue($session));
  }

  /**
   * @covers \MovLib\Data\Session::authenticate
   */
  public function testAuthenticate() {
    (new Session())->authenticate("richard@fussenegger.info", "test1234");
  }

  /**
   * @covers \MovLib\Data\Session::authenticate
   * @expectedException \MovLib\Exception\UserException
   */
  public function testAuthenticateDeactivatedUser() {
    try {
      $this->user = new User(User::FROM_ID, 1);
      $this->user->deactivate();
      (new Session())->authenticate("richard@fussenegger.info", "test1234");
    }
    // Rebuild the users, the deactivation purged all data!
    finally {
      exec("movcli db -s users");
    }
  }

  /**
   * @covers \MovLib\Data\Session::authenticate
   * @expectedException \MovLib\Exception\SessionException
   * @expectedExceptionMessage Could not find user with email
   */
  public function testAuthenticateInvalidEmail() {
    (new Session())->authenticate("webmaster@movlib.org", "test1234");
  }

  /**
   * @covers \MovLib\Data\Session::authenticate
   * @expectedException \MovLib\Exception\SessionException
   * @expectedExceptionMessage Invalid password
   */
  public function testAuthenticateInvalidPassword() {
    (new Session())->authenticate("richard@fussenegger.info", "wrong-password");
  }

  /**
   * @covers \MovLib\Data\Session::checkAuthorization
   */
  public function testCheckAuthorization() {
    global $session;
    $session->checkAuthorization("PHPUnit");
  }

  /**
   * @covers \MovLib\Data\Session::checkAuthorization
   * @expectedException \MovLib\Exception\UnauthorizedException
   * @expectedExceptionMessage PHPUnit
   */
  public function testCheckAuthorizationException() {
    (new Session())->checkAuthorization("PHPUnit");
  }

  /**
   * @covers \MovLib\Data\Session::checkAuthorizationTimestamp
   */
  public function testCheckAuthorizationTimestamp() {
    global $session;
    $session->checkAuthorizationTimestamp("PHPUnit");
  }

  /**
   * @covers \MovLib\Data\Session::checkAuthorizationTimestamp
   * @expectedException \MovLib\Exception\UnauthorizedException
   * @expectedExceptionMessage PHPUnit
   */
  public function testCheckAuthorizationTimestampException() {
    global $session;
    $session->authentication = strtotime("-2 hours");
    $session->checkAuthorizationTimestamp("PHPUnit");
  }

  /**
   * @covers \MovLib\Data\Session::destroy
   */
  //public function testDestroy() {
  // @todo Test with cURL
  //}

  /**
   * @covers \MovLib\Data\Session::init
   */
  public function testInit() {
    $user    = new User(User::FROM_ID, 1);
    $session = new Session();
    $time    = time();
    get_reflection_method($session, "init")->invokeArgs($session, [ 1, $time ]);
    $this->assertEquals($user->id, $session->userId);
    $this->assertEquals($user->name, $session->userName);
    $this->assertEquals($user->timeZoneId, $session->userTimeZoneID);
    $this->assertEquals($time, $session->authentication);
    $this->assertNotEmpty($session->csrfToken);
  }

  /**
   * @covers \MovLib\Data\Session::delete
   * @covers \MovLib\Data\Session::getActiveSessions
   * @covers \MovLib\Data\Session::insert
   * @covers \MovLib\Data\Session::update
   */
  public function testInsertGetActiveSessionsUpdateAndDelete() {
    global $session;
    $oldSessionId = $session->id = md5(openssl_random_pseudo_bytes(1024));
    $session->insert();
    $this->_testInsertGetActiveSessionsUpdateAndDelete();
    $session->id = md5(openssl_random_pseudo_bytes(1024));
    get_reflection_property($session, "ipAddress")->setValue($session, "192.168.1.1");
    get_reflection_property($session, "userAgent")->setValue($session, "PHPUnit");
    $session->update($oldSessionId);
    $this->_testInsertGetActiveSessionsUpdateAndDelete();
    $session->delete();
    $this->_testInsertGetActiveSessionsUpdateAndDelete(false);
  }

  private function _testInsertGetActiveSessionsUpdateAndDelete($findIt = true) {
    global $session;
    $activeSessions = $session->getActiveSessions();
    // There might be other active sessions from developers working on the site.
    $found = false;
    foreach ($activeSessions as $delta => $activeSession) {
      if ($activeSession["session_id"] == $session->id) {
        $found = true;
        if ($findIt === true) {
          $this->assertArrayHasKey("authentication", $activeSession);
          $this->assertEquals($session->id, $activeSession["session_id"]);
          $this->assertEquals(get_reflection_property($session, "userAgent")->getValue($session), $activeSession["user_agent"]);
          $this->assertEquals(inet_pton(get_reflection_property($session, "ipAddress")->getValue($session)), $activeSession["ip_address"]);
        }
      }
    }
    if ($findIt === true) {
      $this->assertTrue($found, "Couldn't find session!");
    }
    else {
      $this->assertFalse($found, "Found session, but it shouldn't be there!");
    }
  }

  /**
   * @covers \MovLib\Data\Session::passwordNeedsRehash
   */
  public function testPasswordNeedsRehash() {
    global $session;
    $db = new Database();
    $hashBefore = $this->_testPasswordNeedsRehash($db);
    $needsRehash = password_hash("test1234", PASSWORD_DEFAULT, [ "cost" => $GLOBALS["movlib"]["password_cost"] - 1 ]);
    $session->passwordNeedsRehash($needsRehash, "test1234");
    $this->assertNotEquals($hashBefore, $this->_testPasswordNeedsRehash($db));
    $db->query("UPDATE `users` SET `password` = ? WHERE `user_id` = 1", "s", [ $hashBefore ]);
  }

  private function _testPasswordNeedsRehash($db) {
    return $db->select("SELECT `password` FROM `users` WHERE `user_id` = 1 LIMIT 1")[0]["password"];
  }

  /**
   * @covers \MovLib\Data\Session::regenerate
   */
  //public function testRegenerate() {
  // @todo Test with cURL
  //}

  /**
   * @covers \MovLib\Data\Session::shutdown
   */
  //public function testShutdown() {
  // @todo Test with cURL
  //}

  /**
   * @covers \MovLib\Data\Session::start
   */
  //public function testStart() {
  // @todo Test with cURL
  //}

  /**
   * @covers \MovLib\Data\Session::validateCsrfToken
   */
  //public function testValidateCsrfToken() {
  // @todo Test with cURL
  //}

}
