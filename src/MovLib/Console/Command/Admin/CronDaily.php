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
namespace MovLib\Console\Command\Admin;

use \MovLib\Core\Log;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\ShellException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Cron jobs that should run on a daily basis.
 *
 * Add the following line to your crontab after creating the symbolic link to the <code>movlib.php</code> file in your
 * local bin path: <code>@daily movlib cron-daily</code>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class CronDaily extends \MovLib\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("cron-daily");
    $this->setDescription("Execute cron jobs.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->purgeTemporaryTable();
    $this->purgeTemporaryUploads();
    return 0;
  }

  /**
   * Purge all data from the temporary table.
   *
   * @global \MovLib\Core\Database $db
   * @return this
   */
  public function purgeTemporaryTable() {
    global $db;
    try {
      $this->writeVerbose("Purging temporary database table...");

      $daily = "DELETE FROM `tmp` WHERE DATEDIFF(CURRENT_TIMESTAMP, `created`) > 0 AND `ttl` = '@daily'";
      $this->writeDebug("mysql> <comment>{$daily};</comment>");
      $db->query($daily);
    }
    catch (DatabaseException $e) {
      Log::error($e);
    }
    return $this;
  }

  /**
   * Purge all files from temporary uploads folder.
   *
   * @return this
   */
  public function purgeTemporaryUploads() {
    try {
      $this->writeVerbose("Purging temporary uploads directory...");
      $this->exec("find '" . ini_get("upload_tmp_dir") . "' -type f -mtime +1 -exec rm -f {} \\;");
    }
    catch (ShellException $e) {
      Log::error($e);
    }
    return $this;
  }

}
