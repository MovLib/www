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

use \MovLib\Data\User\Session;

/**
 * @coversDefaultClass \MovLib\Data\User\Session
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SessionTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\User\Session */
  protected $session;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->session = new Session();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::authenticate
   */
  public function testAuthenticate() {
    $this->assertChaining($this->session, $this->session->authenticate("richard@fussenegger.info", "Test1234"));
    $this->assertEquals($_SERVER["REQUEST_TIME"], $this->session->authentication);
    $this->assertEquals(1, $this->session->userId);
    $this->assertEquals("Fleshgrinder", $this->session->userName);
    $this->assertEquals("Europe/Vienna", $this->session->userTimezone);
  }

  /**
   * @covers ::authenticate
   * @expectedException \DomainException
   * @expectedExceptionMessage Couldn't find user with email 'phpunit@movlib.org'.
   */
  public function testAuthenticateInvalidEmailAddress() {
    $this->session->authenticate("phpunit@movlib.org", "PHPUnitPassword1234");
  }

  /**
   * @covers ::authenticate
   * @expectedException \DomainException
   * @expectedExceptionMessage Invalid password for user with email 'richard@fussenegger.info'.
   */
  public function testAuthenticateInvalidPassword() {
    $this->session->authenticate("richard@fussenegger.info", "PHPUnitPassword1234");
  }

  /**
   * @covers ::checkAuthorization
   */
  public function testCheckAuthorization() {
    $this->session->isAuthenticated = true;
    $this->assertChaining($this->session, $this->session->checkAuthorization("phpunit"));
  }

  /**
   * @covers ::checkAuthorization
   * @expectedException \MovLib\Exception\Client\ErrorUnauthorizedException
   */
  public function testCheckAuthorizationNotAuthenticated() {
    $this->session->checkAuthorization("phpunit");
  }

  /**
   * @covers ::checkAuthorizationTimestamp
   */
  public function testCheckAuthorizationTimestamp() {
    $this->session->authentication  = $_SERVER["REQUEST_TIME"];
    $this->session->isAuthenticated = true;
    $this->assertChaining($this->session, $this->session->checkAuthorizationTimestamp("phpunit"));
  }

  /**
   * @covers ::checkAuthorizationTimestamp
   * @expectedException \MovLib\Exception\Client\ErrorUnauthorizedException
   */
  public function testCheckAuthorizationTimestampNotAuthenticated() {
    $this->session->authentication  = $_SERVER["REQUEST_TIME"] - 86400;
    $this->session->isAuthenticated = false;
    $this->session->checkAuthorizationTimestamp("phpunit");
  }

  /**
   * @covers ::checkAuthorizationTimestamp
   * @expectedException \MovLib\Exception\Client\ErrorUnauthorizedException
   */
  public function testCheckAuthorizationTimestampExpired() {
    $this->session->authentication  = $_SERVER["REQUEST_TIME"] - 86400;
    $this->session->isAuthenticated = true;
    $this->session->checkAuthorizationTimestamp("phpunit");
  }

  /**
   * @covers ::delete
   * @todo Implement delete
   */
  public function testDelete() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::destroy
   * @todo Implement destroy
   */
  public function testDestroy() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::getActiveSessions
   * @todo Implement getActiveSessions
   */
  public function testGetActiveSessions() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::insert
   * @todo Implement insert
   */
  public function testInsert() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::passwordNeedsRehash
   * @todo Implement passwordNeedsRehash
   */
  public function testPasswordNeedsRehash() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::regenerate
   * @todo Implement regenerate
   */
  public function testRegenerate() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::shutdown
   * @todo Implement shutdown
   */
  public function testShutdown() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::start
   * @todo Implement start
   */
  public function testStart() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::update
   * @todo Implement update
   */
  public function testUpdate() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::updateUserAccess
   * @todo Implement updateUserAccess
   */
  public function testUpdateUserAccess() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::offsetExists
   * @todo Implement offsetExists
   */
  public function testOffsetExists() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::offsetSet
   * @todo Implement offsetSet
   */
  public function testOffsetSet() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

  /**
   * @covers ::offsetUnset
   * @todo Implement offsetUnset
   */
  public function testOffsetUnset() {
    $this->markTestIncomplete("This test must be implemented with cURL.");
  }

}
