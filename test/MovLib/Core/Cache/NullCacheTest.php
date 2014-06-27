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
namespace MovLib\Core\Cache;

use \MovLib\Core\Cache\NullCache;

/**
 * @coversDefaultClass \MovLib\Core\Cache\NullCache
 * @group Cache
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class NullCacheTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Core\Cache\NullCache */
  protected $nullCache;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->nullCache = new NullCache();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::decrement
   */
  public function testDecrement() {
    $this->assertEquals(0, $this->nullCache->decrement("phpunit", 42, 42, 42));
  }

  /**
   * @covers ::increment
   */
  public function testIncrement() {
    $this->assertEquals(0, $this->nullCache->increment("phpunit", 42, 42, 42));
  }

  /**
   * @covers ::delete
   */
  public function testDelete() {
    $this->assertChaining($this->nullCache, $this->nullCache->delete("phpunit"));
  }

  /**
   * @covers ::deleteMultiple
   */
  public function testDeleteMultiple() {
    $this->assertChaining($this->nullCache, $this->nullCache->deleteMultiple([ "phpunit" ]));
  }

  /**
   * @covers ::garbageCollection
   */
  public function testGarbageCollection() {
    $this->assertChaining($this->nullCache, $this->nullCache->garbageCollection());
  }

  /**
   * @covers ::set
   */
  public function testSet() {
    $this->assertChaining($this->nullCache, $this->nullCache->set("phpunit", "phpunit", 42));
  }

  /**
   * @covers ::get
   * @depends testSet
   */
  public function testGet() {
    $this->assertNull($this->nullCache->get("phpunit"));
  }

  /**
   * @covers ::setMultiple
   */
  public function testSetMultiple() {
    $this->assertChaining($this->nullCache, $this->nullCache->setMultiple([
      "phpunit1" => "phpunit1",
      "phpunit2" => "phpunit2",
      "phpunit3" => "phpunit3",
      "phpunit4" => "phpunit4",
    ], 42));
  }

  /**
   * @covers ::testGetMultiple
   * @depends testSetMultiple
   */
  public function testGetMultiple() {
    $this->assertEquals([], $this->nullCache->getMultiple([ "phpunit1", "phpunit2", "phpunit3", "phpunit4" ]));
  }

  /**
   * @covers ::purge
   */
  public function testPurge() {
    $this->assertChaining($this->nullCache, $this->nullCache->purge());
  }

  /**
   * @covers ::setKey
   */
  public function testSetKey() {
    $this->assertChaining($this->nullCache, $this->nullCache->setKey("phpunit"));
  }

  /**
   * @covers ::getKey
   * @depends testSetKey
   */
  public function testGetKey() {
    $this->assertNull($this->nullCache->getKey());
  }

}
