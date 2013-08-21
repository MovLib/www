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
namespace MovLib\Test\Model;

use \MovLib\Model\BaseModel;
use \mysqli;
use \PHPUnit_Framework_TestCase;
use \ReflectionMethod;

/**
 * Test the utility functions of the abstract base class of all models.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractModelTest extends PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The base model of all MovLib model classes.
   *
   * @var \MovLib\Model\BaseModel
   */
  private $db;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Fixture called before any of the tests will be executed.
   */
  public function setUp() {
    $this->db = new BaseModel();
  }

  /**
   * Fixture before this class instance is created.
   */
  public static function setUpBeforeClass() {
    $db = new mysqli();
    $db->real_connect();
    $db->select_db(BaseModel::DEFAULT_DB);
    $db->query("CREATE TABLE IF NOT EXISTS `phpunit` (`number` INT, `string` TEXT)");
    $db->close();
  }

  /**
   * Fixture after all tests have been executed and class instance is destroyed.
   */
  public static function tearDownAfterClass() {
    $db = new mysqli();
    $db->real_connect();
    $db->select_db(BaseModel::DEFAULT_DB);
    $db->query("DROP TABLE IF EXISTS `phpunit`");
    $db->close();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * Test the connect method.
   */
  public function testConnect() {
    $connect = new ReflectionMethod($this->db, "connect");
    $connect->setAccessible(true);
    $this->assertSame($this->db, $connect->invoke($this->db));
  }

  /**
   * Test generic insert method.
   */
  public function testInsert() {
    $this->assertSame($this->db, $this->db->insert("phpunit", "is", [ "number" => 42, "string" => "foobar" ]));
  }

  /**
   * Test generic query method with parameters.
   */
  public function testSelect() {
    $this->assertEquals([[ "number" => 42, "string" => "foobar" ]], $this->db->select("SELECT * FROM `phpunit` WHERE `number` = ? LIMIT 1", "i", [ 42 ]));
  }

  /**
   * Test invalid SQL.
   *
   * @expectedException \MovLib\Exception\DatabaseException
   */
  public function testInvalidSql() {
    $this->db->query("SELECT ******** FROM `phpunit` WHERE `number` = ? LIMIT 1", "i", [ 42 ]);
  }

  /**
   * Test generic update method.
   */
  public function testUpdate() {
    $this->assertEquals($this->db, $this->db->update("phpunit", "isis", [ "number" => 24, "string" => "barfoo" ], [ "number" => 42, "string" => "foobar" ]));
  }

  /**
   * Test generic query all method without parameters.
   */
  public function testSelectAll() {
    $this->assertEquals([[ "number" => 24, "string" => "barfoo" ]], $this->db->selectAll("SELECT * FROM `phpunit`"));
  }

  /**
   * Test generic delete method.
   */
  public function testDelete() {
    $this->assertEquals($this->db, $this->db->delete("phpunit", "is", [ "number" => 24, "string" => "barfoo" ]));
  }

  /**
   * Test disconnect method.
   */
  public function testDisconnect() {
    $disconnect = new ReflectionMethod($this->db, "disconnect");
    $disconnect->setAccessible(true);
    $this->assertSame($this->db, $disconnect->invoke($this->db));
  }

}
