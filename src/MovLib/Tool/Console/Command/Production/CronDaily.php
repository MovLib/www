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

use \MovLib\Data\Temporary;
use \MovLib\Exception\DatabaseException;
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
class CronDaily extends \MovLib\Tool\Console\Command\AbstractCommand {
  use \MovLib\Data\TraitShell;

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("cron-daily");
    $this->setDescription("Cron jobs that should run on a daily basis.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->purgeTemporaryTable()->purgeTemporaryUploads();
    return $options;
  }

  /**
   * Purge all data from the temporary table.
   *
   * @global \MovLib\Tool\Database $db
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function purgeTemporaryTable() {
    global $db;
    try {
      $db->query("DELETE FROM `tmp` WHERE DATEDIFF(CURRENT_TIMESTAMP, `created`) > 0 AND `ttl` = '{$db->escapeString(Temporary::TMP_TTL_DAILY)}'");
    }
    catch (DatabaseException $e) {
      error_log($e);
      throw $e;
    }
    return $this;
  }

  /**
   * Purge all files from temporary uploads folder.
   *
   * @return this
   */
  public function purgeTemporaryUploads() {
    $directory = ini_get("upload_tmp_dir");
    $this->shellExecute("find '{$directory}' -type f -mtime +1 -exec rm -f {} \\;");
    return $this;
  }

}
