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

use \MovLib\Component\DateTime;
use \MovLib\Core\Cache\CacheItem;

/**
 * @coversDefaultClass \MovLib\Core\Cache\CacheItem
 * @group Cache
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CacheItemTest extends \MovLib\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $cacheItem = new CacheItem("key", "data", 42);
    $this->assertEquals("data", $cacheItem->data);
    $this->assertEquals((new DateTime())->modify("+42 seconds"), $this->getProperty($cacheItem, "expire"));
    $this->assertEquals("key", $cacheItem->key);
  }

  /**
   * @covers ::expired
   * @depends testConstruct
   */
  public function testExpired() {
    // Negative values are perfectly valid, in fact any integer value is valid.
    $cacheItem = new CacheItem("key", "data", -1);
    $this->assertTrue($cacheItem->expired());
  }

  /**
   * @covers ::expired
   * @depends testConstruct
   */
  public function testNotExpired() {
    $this->assertFalse((new CacheItem("key", "data", 86400))->expired());
  }

}
