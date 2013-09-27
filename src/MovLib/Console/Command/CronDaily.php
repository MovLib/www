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
namespace MovLib\Console\Command;

use \MovLib\Console\Command\AbstractCommand;
use \MovLib\Data\Delayed\Logger;
use \MovLib\Exception\DatabaseException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Cron jobs that should run on a daily basis.
 *
 * Add the following line to your crontab after creating the symbolic link to the <code>movcli.php</code> file in your
 * local bin path: <code>@daily movcli cron-daily</code>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class CronDaily extends AbstractCommand {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("cron-daily");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Cron jobs that should run on a daily basis.");
  }

  /**
   * Purge all data from the temporary table with <code>"@daily"</code> time to life entry.
   *
   * @return this
   */
  protected function purgeTemporaryTable() {
    try {
      (new \MovDev\Database())->query("DELETE FROM `tmp` WHERE DATEDIFF(CURRENT_TIMESTAMP, 'created') > 0 AND `ttl` = '@daily'");
    }
    catch (DatabaseException $e) {
      $message = "Cron @daily failed.\n\nDatabaseException Stacktrace:\n {$e->getTraceAsString()}";
      Logger::stack($message, Logger::FATAL);
      $this->exitOnError($message);
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setIO($input, $output);
    $this->purgeTemporaryTable();
  }

}
