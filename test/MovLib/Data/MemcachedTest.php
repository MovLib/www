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
namespace MovLib\Data;

use \MovLib\Data\Memcached;
use \MovLib\Data\UnixShell as sh;

/**
 * @coversDefaultClass \MovLib\Data\Memcached
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MemcachedTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Memcached */
  protected $memcached;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->memcached = new Memcached();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertInstanceOf("\\Memcached", $this->getProperty($this->memcached, "memcached"));
  }

  /**
   * @coversNothing
   */
  public function testExpiration() {
    $this->memcached->set("phpunit-testExpiration", "phpunit", 1);
    sleep(2);
    $this->assertFalse($this->memcached->get("phpunit-testExpiration"));
  }

  /**
   * @covers ::set
   */
  public function testSet() {
    $this->assertChaining($this->memcached, $this->memcached->set("phpunit-testSet", "phpunit", 1));
  }

  /**
   * @covers ::get
   * @depends testSet
   */
  public function testGet() {
    $this->assertEquals("phpunit", $this->memcached->get("phpunit-testSet"));
  }

  /**
   * @covers ::delete
   * @depends testSet
   */
  public function testDelete() {
    $this->assertChaining($this->memcached, $this->memcached->delete("phpunit-testSet"));
  }

  /**
   * @covers ::delete
   */
  public function testDeleteNotFound() {
    $this->assertChaining($this->memcached, $this->memcached->delete("phpunit-testDeleteNotFound"));
  }

  /**
   * @covers ::increment
   */
  public function testIncrement() {
    $this->assertEquals(1, $this->memcached->increment("phpunit-testIncrement"));
    $this->memcached->delete("phpunit-testIncrement");
  }

  /**
   * @covers ::isFlooding
   */
  public function testIsFloodingNo() {
    $this->assertFalse($this->memcached->isFlooding("phpunit-testIsFloodingNo", 1));
    $this->memcached->delete("phpunit-testIsFloodingNo");
  }

  /**
   * @covers ::isFlooding
   */
  public function testIsFloodingYes() {
    $this->memcached->isFlooding("phpunit-testIsFloodingYes", 1);
    $this->assertTrue($this->memcached->isFlooding("phpunit-testIsFloodingYes", 1));
    $this->memcached->delete("phpunit-testIsFloodingYes");
  }

  /**
   * @covers ::isRemoteAddressFlooding
   * @global \MovLib\TestKernel $kernel
   */
  public function testIsRemoteAddressFlooding() {
    global $kernel;
    for ($i = 0; $i <= Memcached::FLOODING_IP_MAX; ++$i) {
      $flooding = $this->memcached->isRemoteAddressFlooding("phpunit");
    }
    $this->assertTrue($flooding);
    $this->memcached->delete("phpunit{$kernel->remoteAddress}");
  }

  /**
   * @coversNothing
   */
  public function testResetServers() {
    $this->getProperty($this->memcached, "memcached")->resetServerList();
  }

  /**
   * @covers ::delete
   * @depends testResetServers
   * @expectedException \MemcachedException
   */
  public function testDeleteException() {
    $this->memcached->delete("phpunit");
  }

  /**
   * @covers ::get
   * @depends testResetServers
   * @expectedException \MemcachedException
   */
  public function testGetException() {
    $this->memcached->get("phpunit");
  }

  /**
   * @covers ::increment
   * @depends testResetServers
   * @expectedException \MemcachedException
   */
  public function testIncrementException() {
    $this->memcached->increment("phpunit");
  }

  /**
   * @covers ::set
   * @depends testResetServers
   * @expectedException \MemcachedException
   */
  public function testSetException() {
    $this->memcached->set("phpunit", "phpunit");
  }

}
