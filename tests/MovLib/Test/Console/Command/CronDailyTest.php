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
namespace MovLib\Test\Console\Command;

/**
 * @coversDefaultClass \MovLib\Console\Command\CronDaily
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class CronDailyTest extends \MovLib\Test\TestCase {

  /**
   * @covers ::purgeTemporaryTable
   * @global \MovDev\Database $db
   */
  public function testPurgeTemporaryData() {
    global $db;
    $db->query("INSERT INTO `tmp` (`key`, `created`, `data`, `ttl`) VALUES ('phpunit', FROM_UNIXTIME(?), 'PHPUnit', '@daily')", "s", [ strtotime("-2 days") ]);
    $this->assertTrue($this->exec("movcli cron-daily"));
    $this->assertEmpty($db->query("SELECT `key` FROM `tmp` WHERE DATEDIFF(CURRENT_TIMESTAMP, 'created') > 0 AND `ttl` = '@daily'")->get_result()->fetch_all());
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

}
