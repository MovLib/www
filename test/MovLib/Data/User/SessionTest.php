<?php

/* !
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright Ã‚Â© 2013-present {@link https://movlib.org/ MovLib}.
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
use \MovLib\Tool\Console\Command\Development\SeedImport;

/**
 * @coversDefaultClass \MovLib\Data\User\Session
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright Ã‚Â© 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SessionTest extends \MovLib\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  public function tearDown() {
    global $session;
    $session->authentication = $_SERVER["REQUEST_TIME"];
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals(session_name(), $this->getProperty(new Session(), "name"));
  }

  /**
   * @covers ::__construct
   * @todo Test with cURL.
   */
  public function testConstructNoUserId() {
    $this->markTestIncomplete("TODO: Test session construction without user id with cURL.");
  }

  /**
   * @covers ::__construct
   * @todo Test with cURL.
   */
  public function testConstructValidSession() {
    $this->markTestIncomplete("TODO: Test valid session construction with cURL.");
  }

  /**
   * @covers ::__construct
   * @todo Test with cURL.
   */
  public function testConstructValidSessionResumeFromPersistentStorage() {
    $this->markTestIncomplete("TODO: Test valid session resuming with cURL.");
  }

  /**
   * @covers ::authenticate
   */
  public function testAuthenticate() {
    (new Session())->authenticate("richard@fussenegger.info", "Test1234");
  }

  /**
   * @covers ::authenticate
   * @expectedException \MovLib\Exception\UserException
   */
  public function testAuthenticateDeactivatedUser() {
    $user = new UserFull(UserFull::FROM_ID, 1);
    $user->deactivate();
    (new Session())->authenticate("richard@fussenegger.info", "Test1234");
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /**
   * @covers ::authenticate
   * @expectedException \MovLib\Exception\SessionException
   * @expectedExceptionMessage Couldn't find user with email
   */
  public function testAuthenticateInvalidEmail() {
    (new Session())->authenticate("webmaster@movlib.org", "Test1234");
  }

  /**
   * @covers ::authenticate
   * @expectedException \MovLib\Exception\SessionException
   * @expectedExceptionMessage Invalid password
   */
  public function testAuthenticateInvalidPassword() {
    (new Session())->authenticate("richard@fussenegger.info", "wrong-password");
  }

  /**
   * @covers ::authenticate
   * @todo Test with cURL.
   */
  public function testAuthenticateRegenerateAnonymous() {
    $this->markTestIncomplete("TODO: Test session id regeneration with cURL.");
  }

  /**
   * @covers ::authenticate
   * @todo Test with cURL.
   */
  public function testAuthenticateStart() {
    $this->markTestIncomplete("TODO: Test start of new session with cURL.");
  }

  /**
   * @covers ::checkAuthorization
   */
  public function testCheckAuthorization() {
    global $session;
    $session->checkAuthorization("PHPUnit");
  }

  /**
   * @covers ::checkAuthorization
   * @expectedException \MovLib\Exception\Client\UnauthorizedException
   */
  public function testCheckAuthorizationException() {
    (new Session())->checkAuthorization("PHPUnit");
  }

  /**
   * @covers ::checkAuthorizationTimestamp
   */
  public function testCheckAuthorizationTimestamp() {
    global $session;
    $session->checkAuthorizationTimestamp("PHPUnit");
  }

  /**
   * @covers ::checkAuthorizationTimestamp
   * @expectedException \MovLib\Exception\Client\UnauthorizedException
   */
  public function testCheckAuthorizationTimestampExceptionNotAuthenticated() {
    global $session;
    $session->isAuthenticated = false;
    $session->checkAuthorizationTimestamp("PHPUnit");
  }

  /**
   * @covers ::checkAuthorizationTimestamp
   * @expectedException \MovLib\Exception\Client\UnauthorizedException
   */
  public function testCheckAuthorizationTimestampExceptionTimeout() {
    global $session;
    $session->authentication = strtotime("-2 hours");
    $session->checkAuthorizationTimestamp("PHPUnit");
  }

  /**
   * @covers ::destroy
   * @todo Test with cURL.
   */
  public function testDestroy() {
    $this->markTestIncomplete("TODO: Test Session::destroy() with cURL.");
  }

  /**
   * @covers ::init
   */
  public function testInit() {
    $user    = new UserFull(UserFull::FROM_ID, 1);
    $session = new Session();
    $this->invoke($session, "init", [ 1, $_SERVER["REQUEST_TIME"] ]);
    $this->assertEquals($user->id, $session->userId);
    $this->assertEquals($user->name, $session->userName);
    $this->assertEquals($user->timeZoneId, $session->userTimeZoneId);
    $this->assertEquals($_SERVER["REQUEST_TIME"], $session->authentication);
    $this->assertNotEmpty($session->csrfToken);
  }

  /**
   * @covers ::init
   * @expectedException \MovLib\Exception\SessionException
   * @expectedExceptionMessage Could not fetch user data for user ID
   */
  public function testInitNoUser() {
    $this->invoke(new Session(), "init", [ 99999999, $_SERVER["REQUEST_TIME"] ]);
  }

  /**
   * @covers ::init
   */
  public function testInitAnonymousUser() {
    $session = new Session();
    $this->invoke($session, "init", [ 0, $_SERVER["REQUEST_TIME"] ]);
    $this->assertEquals(0, $session->userId);
    $this->assertEquals($_SERVER["REMOTE_ADDR"], $session->userName);
    $this->assertEquals(ini_get("date.timezone"), $session->userTimeZoneId);
    $this->assertFalse($session->isAuthenticated);
  }

  /**
   * @covers ::init
   * @expectedException \MovLib\Exception\SessionException
   * @expectedExceptionMessage Empty or invalid IP address (this is more or less impossible, check web server and if behind a proxy check implementation).
   */
  public function testInitAnonymousUserInvalidIP() {
    $_SERVER["REMOTE_ADDR"] = "phpunit";
    $this->invoke(new Session(), "init", [ 0, $_SERVER["REQUEST_TIME"] ]);
  }

  /**
   * @covers ::delete
   * @covers ::getActiveSessions
   * @covers ::insert
   * @covers ::update
   */
  public function testInsertGetActiveSessionsUpdateAndDelete() {
    global $session;
    $oldSessionId = $session->id  = md5(openssl_random_pseudo_bytes(1024));
    $session->insert();
    $this->_testInsertGetActiveSessionsUpdateAndDelete();
    $session->id  = md5(openssl_random_pseudo_bytes(1024));
    $this->setProperty($session, "ipAddress", "192.168.1.1");
    $this->setProperty($session, "userAgent", "PHPUnit");
    $session->update($oldSessionId);
    $this->_testInsertGetActiveSessionsUpdateAndDelete();
    $session->delete();
    $this->_testInsertGetActiveSessionsUpdateAndDelete(false);
    $this->exec("movdev db -s users");
  }

  private function _testInsertGetActiveSessionsUpdateAndDelete($findIt = true) {
    global $session;
    $activeSessions = $session->getActiveSessions();
    // There might be other active sessions from developers working on the site.
    $found          = false;
    foreach ($activeSessions as $delta => $activeSession) {
      if ($activeSession["session_id"] == $session->id) {
        $found = true;
        if ($findIt === true) {
          $this->assertArrayHasKey("authentication", $activeSession);
          $this->assertEquals($session->id, $activeSession["session_id"]);
          $this->assertEquals($this->getProperty($session, "userAgent"), $activeSession["user_agent"]);
          $this->assertEquals(inet_pton($this->getProperty($session, "ipAddress")), $activeSession["ip_address"]);
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
   * @covers ::passwordNeedsRehash
   * @global \MovLib\Tool\Configuration $config
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\User\Session $session
   */
  public function testPasswordNeedsRehash() {
    global $config, $db, $session;
    $hashBefore  = $this->_testPasswordNeedsRehash($db);
    $needsRehash = password_hash("Test1234", PASSWORD_DEFAULT, [ "cost" => $config->passwordCost - 1 ]);
    $session->passwordNeedsRehash($needsRehash, "Test1234");
    $this->assertNotEquals($hashBefore, $this->_testPasswordNeedsRehash($db));
    $db->query("UPDATE `users` SET `password` = ? WHERE `user_id` = 1", "s", [ $hashBefore ]);
    (new SeedImport())->databaseImport([ "users" ]);
  }

  /** @global \MovDev\Database $db */
  private function _testPasswordNeedsRehash() {
    global $db;
    $stmt = $db->query("SELECT `password` FROM `users` WHERE `user_id` = 1 LIMIT 1");
    $stmt->bind_result($password);
    $stmt->fetch();
    return $password;
  }

  /**
   * @covers ::regenerate
   * @todo Test with cURL.
   */
  public function testRegenerate() {
    $this->markTestIncomplete("TODO: Test Session::regenerate() with cURL.");
  }

  /**
   * @covers ::shutdown
   * @todo Test with cURL.
   */
  public function testShutdown() {
    $this->markTestIncomplete("TODO: Test Session::shutdown() with cURL.");
  }

  /**
   * @covers ::start
   * @todo Test with cURL.
   */
  public function testStart() {
    $this->markTestIncomplete("TODO: Test Session::start() with cURL.");
  }

  /**
   * @covers ::validateCsrfToken
   */
  public function testValidateCsrfTokenNoSet() {
    global $session;
    $this->assertFalse($session->validateCsrfToken());
  }

  /**
   * @covers ::validateCsrfToken
   */
  public function testValidateCsrfTokenInvalid() {
    global $session;
    $_POST["csrf"] = "phpunit";
    $this->assertFalse($session->validateCsrfToken());
  }

  /**
   * @covers ::validateCsrfToken
   */
  public function testValidateCsrfToken() {
    global $session;
    $_POST["csrf"] = $session->csrfToken;
    $this->assertTrue($session->validateCsrfToken());
  }

}
