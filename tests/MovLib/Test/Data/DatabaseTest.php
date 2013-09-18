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

use \MovLib\Data\Database;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase {

  public static function setUpBeforeClass() {
    $mysqli = new \mysqli();
    $mysqli->real_connect();
    $mysqli->select_db($GLOBALS["movlib"]["default_database"]);
    $mysqli->query("CREATE TABLE IF NOT EXISTS `phpunit` (`number` INT, `string` TEXT)");
    $mysqli->close();
  }

  public static function tearDownAfterClass() {
    $mysqli = new \mysqli();
    $mysqli->real_connect();
    $mysqli->select_db($GLOBALS["movlib"]["default_database"]);
    $mysqli->query("DROP TABLE IF EXISTS `phpunit`");
    $mysqli->close();
  }

  /**
   * @covers \MovLib\Data\Database::close
   * @covers \MovLib\Data\Database::connect
   * @covers \MovLib\Data\Database::prepareAndExecute
   * @covers \MovLib\Data\Database::query
   */
  public function testQuery() {
    return (new Database())->query("INSERT INTO `phpunit` (`number`, `string`) VALUES (?, ?)", "is", [ 42, "phpunit" ]);
  }

  /**
   * @covers \MovLib\Data\Database::__destruct
   * @covers \MovLib\Data\Database::close
   * @covers \MovLib\Data\Database::disconnect
   * @covers \MovLib\Data\Database::prepareAndExecute
   * @covers \MovLib\Data\Database::select
   * @depends testQuery
   */
  public function testSelect(Database $db) {
    $this->assertEquals([[ "number" => 42, "string" => "phpunit" ]], $db->select("SELECT * FROM `phpunit` WHERE `number` = ? LIMIT 1", "i", [ 42 ]));
  }

  /**
   * @depends testQuery
   * @expectedException \MovLib\Exception\DatabaseException
   */
  public function testInvalidSql(Database $db) {
    $db->select("SELECT ******** FROM `phpunit` WHERE `number` = ? LIMIT 1", "i", [ 42 ]);
  }

}
