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

use \MovLib\Console\MySQLi;
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
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("cron-daily");
    $this->setDescription("Execute cron jobs.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    try {
      $this->purgeTemporaryTable();
      $this->purgeTemporaryUploads();
    }
    catch (\Exception $e) {
      $this->log->error($e);
    }
    return (integer) isset($e);
  }

  /**
   * Purge all data from the temporary table.
   *
   * @return this
   */
  public function purgeTemporaryTable() {
    $ttl   = "@daily";
    $query = "DELETE FROM `tmp` WHERE DATEDIFF(CURRENT_TIMESTAMP, `created`) > 0 AND `ttl` = '{$ttl}'";
    $this->writeDebug("Purging temporary table for <comment>{$ttl}</comment> entries...");
    $this->writeDebug("mysql> <comment>{$query};</comment>");
    (new MySQLi($this->diContainer))->query($query);
    return $this;
  }

  /**
   * Purge all files from temporary uploads folder.
   *
   * @return this
   */
  public function purgeTemporaryUploads() {
    return $this
      ->writeVerbose("Purging temporary uploads directory...")
      ->exec("find '" . ini_get("upload_tmp_dir") . "' -type f -mtime +1 -exec rm -f {} \\;")
    ;
  }

}
