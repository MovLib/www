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

use \MovLib\Exception\HistoryException;
use \MovLib\Model\AbstractHistoryModel;
use \MovLib\Model\BaseModel;
use MovLib\Test\Model\TestHistoryModel;
use \MovLib\Utility\FileSystem;
use \PHPUnit_Framework_TestCase;
use \mysqli;

/**
 * Test the history utility class.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HistoryTest extends PHPUnit_Framework_TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The base model of all MovLib model classes.
   *
   * @var BaseModel
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
    $db->query("CREATE TABLE IF NOT EXISTS `test` (`test_id` INT, `title` TEXT, `body` TEXT)");
    $db->query("INSERT INTO `test` VALUES(42, 'foobar', 'foo bar baz')");
    $db->close();
  }

  /**
   * Fixture after all tests have been executed and class instance is destroyed.
   */
  public static function tearDownAfterClass() {
    $db = new mysqli();
    $db->real_connect();
    $db->select_db(BaseModel::DEFAULT_DB);
    $db->query("DROP TABLE IF EXISTS `test`");
    $db->close();

    $path = "{$_SERVER["DOCUMENT_ROOT"]}/history/test";
    if(is_dir($path)) {
      exec("rm -Rf {$path}");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests

  public function testWithoutId() {
    try {
      new TestHistoryModel(null);
    } catch (HistoryException $expected) {
      return;
    }
    $this->fail('An expected exception has not been raised (id is null).');
  }

  public function testGetShortName() {
    $this->assertEquals("test", (new TestHistoryModel(42))->getShortName());
  }

  public function testCreateRepositoryFolder() {
    (new TestHistoryModel(42))->createRepositoryFolder();
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/test/42");
  }

  public function testInitRepository() {
    (new TestHistoryModel(42))->initRepository();
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/test/42/.git/HEAD");
  }

  public function testWriteFiles() {
    $test = new TestHistoryModel(42);
    $test->writeFiles();
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/test/42/test.json");
    $this->assertStringEqualsFile("{$_SERVER["DOCUMENT_ROOT"]}/history/test/42/test.json",
      '[{"test_id":42,"title":"foobar","body":"foo bar baz"}]');
  }

  public function testCommit() {
    (new TestHistoryModel(42))->commit(20, "initial commit");
    $this->assertFileExists("{$_SERVER["DOCUMENT_ROOT"]}/history/test/42/.git/refs/heads/master");
  }

  public function testGetLastCommits() {
    $result = (new TestHistoryModel(42))->getLastCommits();
    $this->assertEquals(20, $result[0]["author_id"]);
    $this->assertEquals("initial commit", $result[0]["subject"]);
  }

}
