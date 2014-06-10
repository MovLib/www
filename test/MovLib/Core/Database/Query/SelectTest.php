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
namespace MovLib\Core\Database\Query;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Database\Query\Select;

/**
 * @coversDefaultClass \MovLib\Core\Database\Query\Select
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SelectTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * @var \MovLib\Core\Database\Connection
   */
  protected $connection;

  /**
   * @var \MovLib\Core\Database\Query\Select
   */
  protected $select;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->connection = Database::getConnection();
    $this->select     = new Select($this->connection);
  }

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass() {
    self::tearDownAfterClass();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    self::tearDownAfterClass();
  }

  /**
   * {@inheritdoc}
   */
  public static function tearDownAfterClass() {
    Database::getConnection()->real_query("DROP TABLE IF EXISTS `phpunit`");
  }


  // ------------------------------------------------------------------------------------------------------------------- Select::fetchInto


  /**
   * @covers ::fetchInto
   */
  public function testFetchIntoInstanceEquality() {
    $this->connection->real_query("CREATE TABLE `phpunit` (`id` INT)");
    $this->connection->real_query("INSERT INTO `phpunit` (`id`) VALUES (42)");

    $expected     = new SelectTestObject();
    $actual       = clone $expected;

    $this->select->select("id")->from("phpunit")->fetchInto($actual);
    $expected->id = 42;

    $this->assertEquals($expected, $actual);
  }


  // ------------------------------------------------------------------------------------------------------------------- Select::fetchObject


  /**
   * @covers ::fetchObject
   */
  public function testFetchObjectComposing() {
    $this->connection->real_query("CREATE TABLE `phpunit` (`id` INT, `composite_id` INT)");
    $this->connection->real_query("INSERT INTO `phpunit` (`id`, `composite_id`) VALUES (1, 42)");

    $expected                = new SelectTestObject("bar");
    $expected->id            = 1;
    $expected->composite     = new SelectTestComposite("foo");
    $expected->composite->id = 42;

    $this->assertEquals($expected, $this->select
      ->select("p.id")
      ->addComposite("composite", static::class . "Composite", [ "foo" ])
      ->select([ "p.composite", "id" ])
      ->from("phpunit", "p")
      ->fetchObject(static::class . "Object", [ "bar" ])
    );
  }

  /**
   * <b>NOTE</b><br>
   * The select query has to return the object with the ID 1 because the fetchObject() automatically adds a limit 1
   * clause and only returns the first object from the result, plus InnoDB / XtraDB always sorts it's results by the
   * primary key if no order by clause is specified.
   *
   * @covers ::fetchObject
   */
  public function testFetchObjectMultiple() {
    $this->connection->real_query("CREATE TABLE `phpunit` (`id` INT AUTO_INCREMENT PRIMARY KEY)");
    $this->connection->real_query("INSERT INTO `phpunit` VALUES " . rtrim(str_repeat("(),", 10), ","));

    $expected     = new SelectTestObject();
    $expected->id = 1;

    $this->assertEquals($expected, $this->select->select("id")->from("phpunit")->fetchObject(static::class . "Object"));
  }

}
