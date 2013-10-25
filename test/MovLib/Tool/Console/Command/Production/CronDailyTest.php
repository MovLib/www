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
namespace MovLib\Tool\Console\Command\Production;

use \MovLib\Tool\Console\Command\Production\CronDaily;
use \Symfony\Component\Console\Input\StringInput;
use \Symfony\Component\Console\Output\NullOutput;

/**
 * @coversDefaultClass \MovLib\Tool\Console\Command\Production\CronDaily
 * @author Skeleton Generator
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CronDailyTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Tool\Console\Command\Production\CronDaily */
  protected $cronDaily;

  protected $filename;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->cronDaily = new CronDaily();
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  public function helperAssertTemporaryTableData() {
    global $db;
    $this->assertEmpty($db->query("SELECT * FROM `tmp` WHERE `key` = 'phpunit'")->get_result()->fetch_all());
  }

  public function helperAssertTemporaryUploadData() {
    $this->assertFileNotExists($this->filename);
  }

  public function helperCreateTemporaryTableData() {
    global $db;
    $db->query("INSERT INTO `tmp` (`key`, `created`, `data`, `ttl`) VALUES ('phpunit', FROM_UNIXTIME(?), 'PHPUnit', '@daily')", "s", [ strtotime("-2 days") ]);
  }

  public function helperCreateTemporaryUploadData() {
    $this->filename = tempnam(ini_get("upload_tmp_dir"), "phpunit");
    touch($this->filename, strtotime("-2 days"));
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->assertEquals("cron-daily", $this->cronDaily->getName());
  }

  /**
   * @covers ::configure
   */
  public function testConfigure() {
    $this->assertNotEmpty($this->cronDaily->getDescription());
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $this->helperCreateTemporaryTableData();
    $this->helperCreateTemporaryUploadData();
    $this->invoke($this->cronDaily, "execute", [ new StringInput(""), new NullOutput() ]);
    $this->helperAssertTemporaryTableData();
    $this->helperAssertTemporaryUploadData();
  }

  /**
   * @covers ::purgeTemporaryTable
   */
  public function testPurgeTemporaryTable() {
    global $db;
    $this->helperCreateTemporaryTableData();
    $this->assertChaining($this->cronDaily, $this->cronDaily->purgeTemporaryTable());
    $this->helperAssertTemporaryTableData();
  }

  /**
   * @covers ::purgeTemporaryUploads
   */
  public function testPurgeTemporaryUploads() {
    $this->helperCreateTemporaryUploadData();
    $this->assertChaining($this->cronDaily, $this->cronDaily->purgeTemporaryUploads());
    $this->helperAssertTemporaryUploadData();
  }

}
