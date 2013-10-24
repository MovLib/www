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
namespace MovLib\Console\Command;

use \MovLib\Console\Command\CronDaily;

/**
 * @coversDefaultClass \MovLib\Console\Command\CronDaily
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CronDailyTest extends \MovLib\TestCase {

  /**
   * @covers ::purgeTemporaryTable
   * @global \MovDev\Database $db
   */
  public function testPurgeTemporaryTable() {
    global $db;
    $db->query("INSERT INTO `tmp` (`key`, `created`, `data`, `ttl`) VALUES ('phpunit', FROM_UNIXTIME(?), 'PHPUnit', '@daily')", "s", [ strtotime("-2 days") ]);
    $this->assertTrue($this->exec("movcli cron-daily"));
    $this->assertEmpty($db->query("SELECT * FROM `tmp` WHERE `key` = 'phpunit'")->get_result()->fetch_all());
  }

  /**
   * @covers ::purgeTemporaryTable
   */
  public function testPurgeTemporaryTableChaining() {
    $command = new CronDaily();
    $this->assertChaining($command, $this->invoke($command, "purgeTemporaryTable"));
  }

  /**
   * @covers ::purgeTemporaryUploads
   */
  public function testPurgeTemporaryUploads() {
    $filename = tempnam(ini_get("upload_tmp_dir"), "phpunit");
    touch($filename, strtotime("-2 days"));
    $this->assertTrue($this->exec("movcli cron-daily"));
    $this->assertFileNotExists($filename);
  }

  /**
   * @covers ::purgeTemporaryUploads
   */
  public function testPurgeTemporaryUploadsChaining() {
    $command = new CronDaily();
    $this->assertChaining($command, $method->invoke($command, "purgeTemporaryUploads"));
  }

}
