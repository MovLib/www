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

use \MovLib\Core\Cache\PHPCache;
use \MovLib\Core\Storage\MemoryStorage;

/**
 * @coversDefaultClass \MovLib\Core\Cache\PHPCache
 * @group Cache
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PHPCacheTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  public function dataProviderSetInvalidKey() {
    return [
      [ "/phpunit" ],
      [ "<phpunit>" ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    new PHPCache(new MemoryStorage());
  }

  /**
   * @covers ::__construct
   * @covers ::getKey
   */
  public function testConstructSetKey() {
    $key = "phpunit";
    $phpCache = new PHPCache(new MemoryStorage(), $key);
    $this->assertEquals($key, $phpCache->getKey());
  }

  /**
   * @covers ::delete
   */
  public function testDelete() {
    $storage = new MemoryStorage();
    $storage->save("phpunit", "data");
    $phpCache = new PHPCache($storage);
    $this->assertChaining($phpCache, $phpCache->delete("phpunit"));
    if ($storage->exists("phpunit")) {
      $this->fail("Key phpunit still exists in storage.");
    }
  }

  /**
   * @covers ::delete
   */
  public function testDeleteNoItem() {
    (new PHPCache(new MemoryStorage()))->delete("phpunit");
  }

  /**
   * @covers ::deleteMultiple
   */
  public function testDeleteMultiple() {
    $storage = new MemoryStorage();
    $keys = [];
    for ($i = 0; $i < 10; ++$i) {
      $key = "phpunit{$i}";
      $storage->save($key, "data{$i}");
      $keys[] = $key;
    }
    $phpCache = new PHPCache($storage);
    $this->assertChaining($phpCache, $phpCache->deleteMultiple($keys));
    foreach ($keys as $key) {
      if ($storage->exists($key)) {
        $this->fail("Key {$key} still exists in storage.");
      }
    }
  }

  /**
   * @covers ::deleteMultiple
   */
  public function testDeleteMultipleNoItems() {
    $keys = [];
    for ($i = 0; $i < 10; ++$i) {
      $keys[] = "phpunit{$i}";
    }
    $phpCache = new PHPCache(new MemoryStorage());
    $this->assertChaining($phpCache, $phpCache->deleteMultiple($keys));
  }

  /**
   * @covers ::garbageCollection
   */
  public function testGarbageCollection() {
    $storage = new MemoryStorage();
    $keys = [];
    for ($i = 0; $i < 10; ++$i) {
      $key = "phpunit{$i}";
      $storage->save($key, serialize(new CacheItem($key, "data{$i}", -3600)));
      $keys[] = $key;
    }
    $phpCache = new PHPCache($storage);
    $this->assertChaining($phpCache, $phpCache->garbageCollection());
    foreach ($keys as $key) {
      if ($storage->exists($key)) {
        $this->fail("Key {$key} still exists in storage.");
      }
    }
  }

  /**
   * @covers ::get
   */
  public function testGet() {
    $storage = new MemoryStorage();
    $storage->save("phpunit", serialize(new CacheItem("phpunit", $this)));
    $this->assertEquals($this, (new PHPCache($storage))->get("phpunit"));
  }

  /**
   * @covers ::setKey
   * @covers ::getKey
   */
  public function testSetGetKey() {
    $this->assertEquals("phpunit", (new PHPCache(new MemoryStorage()))->setKey("phpunit")->getKey());
  }

  /**
   * @covers ::setKey
   * @expectedException \InvalidArgumentException
   */
  public function testSetKeyEmpty() {
    (new PHPCache(new MemoryStorage()))->setKey("");
  }

  /**
   * @covers ::setKey
   * @dataProvider dataProviderSetInvalidKey
   * @expectedException \InvalidArgumentException
   * @param string $invalidKey
   */
  public function testSetInvalidKey($invalidKey) {
    (new PHPCache(new MemoryStorage()))->setKey($invalidKey);
  }

  /**
   * @covers ::set
   */
  public function testSet() {
    $storage = new MemoryStorage();
    (new PHPCache($storage))->set($this, "phpunit");
    $this->assertEquals($this, unserialize($storage->load("phpunit"))->data);
  }

}
