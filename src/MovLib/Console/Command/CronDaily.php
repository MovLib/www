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
use \MovLib\Exception\DatabaseException;
use \MovLib\Model\BaseModel;
use \MovLib\Utility\DelayedLogger;
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
   * Collection of all jobs that should be executed.
   *
   * @var array
   */
  private $jobs = [];

  /**
   * {@inheritDoc}
   */
  public function __construct() {
    parent::__construct("cron-daily");
    $this->jobs[] = "userActivationLinkGarbageCollection";
  }

  /**
   * {@inheritDoc}
   */
  protected function configure() {
    $this->setDescription("Cron jobs that should run on a daily basis.");
  }

  /**
   * Remove all expired activation links from the temporary database table.
   */
  private function userActivationLinkGarbageCollection() {
    try {
      (new BaseModel())->query("DELETE FROM `tmp` WHERE COLUMN_EXISTS(`dyn_data`, 'time') = 1 AND DATEDIFF(NOW(), COLUMN_GET(`dyn_data`, 'time' AS DATETIME)) > 0");
    } catch (DatabaseException $e) {
      $message = "Cron failed to execute user activation link garbage collection.\n\nDatabaseException Stacktrace:\n {$e->getTraceAsString()}";
      DelayedLogger::logNow($message, E_ERROR);
      $this->exitOnError($message);
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setIO($input, $output);
    $c = count($this->jobs);
    for ($i = 0; $i < $c; ++$i) {
      call_user_func([ $this, $this->jobs[$i] ]);
    }
  }

}
