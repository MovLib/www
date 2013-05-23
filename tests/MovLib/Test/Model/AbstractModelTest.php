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

use \mysqli;
use \PHPUnit_Framework_TestCase;

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


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Fully qualified name of the class we want to test.
   *
   * @var string
   */
  const FQ_CLASS_NAME = "MovLib\Model\AbstractModel";

  /**
   * Name of the database table that is used for our tests.
   *
   * @var string
   */
  const TABLE_NAME = "phpunit";

  /**
   * Name of the database field for integer value.
   *
   * @var string
   */
  const FIELD_NUMBER = "number";

  /**
   * Name of the database field for string value.
   *
   * @var string
   */
  const FIELD_STRING = "string";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Mock of the abstract class we want to (function) test.
   *
   * @var \PHPUnit_Framework_MockObject_Generator
   */
  private $mock;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Fixture called before any of the tests will be executed.
   */
  public function setUp() {
    $this->mock = $this->getMockForAbstractClass(self::FQ_CLASS_NAME);
  }

  /**
   * Fixture before this class instance is created.
   */
  public static function setUpBeforeClass() {
    /* @var $mysqli \mysqli */
    $mysqli = new mysqli("p:localhost", "root", "keines", "movlib");
    $mysqli->query("CREATE TABLE IF NOT EXISTS `" . self::TABLE_NAME . "` (`" . self::FIELD_NUMBER . "` INT, `" . self::FIELD_STRING . "` TEXT)");
    $mysqli->close();
  }

  /**
   * Fixture after all tests have been executed and class instance is destroyed.
   */
  public static function tearDownAfterClass() {
    /* @var $mysqli \mysqli */
    $mysqli = new mysqli("p:localhost", "root", "keines", "movlib");
    $mysqli->query("DROP TABLE IF EXISTS `" . self::TABLE_NAME . "`");
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * Test the connect method.
   */
  public function testConnect() {
    /* @var $connect \ReflectionFunction */
    $connect = getReflectionFunction(self::FQ_CLASS_NAME, "connect");
    $this->assertSame($this->mock, $connect->invoke($this->mock));
  }

  /**
   * Test generic insert method.
   */
  public function testInsert() {
    $this->assertSame($this->mock, getReflectionFunction(self::FQ_CLASS_NAME, "insert")->invokeArgs($this->mock, [
      self::TABLE_NAME,
      "is",
      [ self::FIELD_NUMBER => 42, self::FIELD_STRING => "foobar" ]
    ]));
  }

  /**
   * Test generic query method with parameters.
   */
  public function testQuery() {
    $this->assertEquals([[
        self::FIELD_NUMBER => 42,
        self::FIELD_STRING => "foobar"
      ]],
      getReflectionFunction(self::FQ_CLASS_NAME, "query")->invokeArgs($this->mock, [
        "SELECT * FROM `" . self::TABLE_NAME . "` WHERE `" . self::FIELD_NUMBER . "` = ? LIMIT 1",
        "i", [ 42 ]
      ])
    );
  }

  /**
   * Test invalid SQL.
   * @expectedException \MovLib\Exception\DatabaseException
   */
  public function testInvalidSql() {
    getReflectionFunction(self::FQ_CLASS_NAME, "query")->invokeArgs($this->mock, [
      "SELECT ******** FROM `" . self::TABLE_NAME . "` WHERE `" . self::FIELD_NUMBER . "` = ? LIMIT 1",
      "i", [ 42 ]
    ]);
  }

  /**
   * Test generic update method.
   */
  public function testUpdate() {
    $this->assertEquals($this->mock, getReflectionFunction(self::FQ_CLASS_NAME, "update")->invokeArgs($this->mock, [
      self::TABLE_NAME,
      "isis",
      [ self::FIELD_NUMBER => 1234, self::FIELD_STRING => "barfoo" ],
      [ self::FIELD_NUMBER => 42, self::FIELD_STRING => "foobar" ]
    ]));
  }

  /**
   * Test generic query all method without parameters.
   */
  public function testQueryAll() {
    $this->assertEquals([[
        self::FIELD_NUMBER => 1234,
        self::FIELD_STRING => "barfoo"
      ]],
      getReflectionFunction(self::FQ_CLASS_NAME, "queryAll")->invokeArgs($this->mock, [
        "SELECT * FROM `" . self::TABLE_NAME . "`"
      ])
    );
  }

  /**
   * Test generic delete method.
   */
  public function testDelete() {
    $this->assertEquals($this->mock, getReflectionFunction(self::FQ_CLASS_NAME, "delete")->invokeArgs($this->mock, [
      self::TABLE_NAME,
      "is",
      [ self::FIELD_NUMBER => 1234, self::FIELD_STRING => "barfoo" ]
    ]));
  }

}
