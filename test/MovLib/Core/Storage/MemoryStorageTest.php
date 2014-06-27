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
namespace MovLib\Core\Storage;

use \MovLib\Core\Storage\MemoryStorage;

/**
 * @coversDefaultClass \MovLib\Core\Storage\MemoryStorage
 * @group Storage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class MemoryStorageTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Core\Storage\MemoryStorage */
  protected $memoryStorage;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /** {@inheritdoc} */
  public function setUp() {
    $this->memoryStorage = new MemoryStorage();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::writeable
   */
  public function testWriteable() {
    $this->assertTrue($this->memoryStorage->writeable());
  }

  /**
   * @covers ::save
   * @depends testWriteable
   */
  public function testSave() {
    $this->assertTrue($this->memoryStorage->save("phpunit", "data"));
    return $this->memoryStorage;
  }

  /**
   * @covers ::exists
   * @depends testSave
   */
  public function testExists(MemoryStorage $memoryStorage) {
    $this->assertTrue($memoryStorage->exists("phpunit"));
  }

  /**
   * @covers ::load
   * @depends testSave
   */
  public function testLoad(MemoryStorage $memoryStorage) {
    $this->assertEquals("data", $memoryStorage->load("phpunit"));
  }

  /**
   * @covers ::delete
   * @depends testSave
   */
  public function testDelete(MemoryStorage $memoryStorage) {
    $this->assertTrue($memoryStorage->delete("phpunit"));
  }

  /**
   * @covers ::deleteAll
   */
  public function testDeleteAll() {
    $names = [];
    for ($i = 0; $i < 10; ++$i) {
      $name = "phpunit{$i}";
      $this->memoryStorage->save($name, "data{$i}");
      $names[] = $name;
    }
    $this->assertTrue($this->memoryStorage->deleteAll());
    foreach ($names as $name) {
      if ($this->memoryStorage->exists($name)) {
        $this->fail("Key {$name} still exists in storage.");
      }
    }
  }

  /**
   * @covers ::listAll
   */
  public function testListAll() {
    $names = [];
    for ($i = 0; $i < 10; ++$i) {
      $name = "phpunit{$i}";
      $this->memoryStorage->save($name, "data{$i}");
      $names[] = $name;
    }
    $this->assertEquals($names, $this->memoryStorage->listAll());
  }

  /**
   * @covers ::getURI
   */
  public function testGetURI() {
    $this->assertEquals("phpunit", $this->memoryStorage->getURI("phpunit"));
  }

}
