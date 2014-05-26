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
use \MovLib\Exception\CountVerificationException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\StringInput;
use \Symfony\Component\Console\Output\NullOutput;
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
//      $this->purgeTemporaryTable();
//      $this->purgeTemporaryUploads();
      $this->verifyCounts();
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
    (new MySQLi("movlib"))->query($query);
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

  /**
   * Verify the various count columns for entities in the database.
   *
   * @return this
   */
  public function verifyCounts() {
    // Execute all available count verifications.
    $this->writeVerbose("Verifying and updating entity counts...");
    $input = new StringInput("");
    $output = new NullOutput();

    /* @var $fileinfo \SplFileInfo */
    foreach (new \RegexIterator(new \DirectoryIterator("dr://src/MovLib/Console/Command/Install/Count"), "/\.php/") as $fileinfo) {
      $command = "\\MovLib\\Console\\Command\\Install\\Count\\{$fileinfo->getBasename(".php")}";
      $reflector = new \ReflectionClass($command);
      if ($reflector->isInstantiable() && $reflector->isSubclassOf("\\MovLib\\Console\\Command\\Install\\Count\\AbstractEntityCountCommand")) {
        /* @var $countCommand \MovLib\Console\Command\Install\Count\AbstractEntityCountCommand */
        $countCommand = new $command($this->container);
        $this->writeDebug("Verifying <comment>{$countCommand->entityName}</comment> counts...");
        try {
          $countCommand->run($input, $output);
          $this->writeDebug(
            "<comment>{$countCommand->entityName}</comment> counts verified successfully.",
            self::MESSAGE_TYPE_INFO
          );
        } catch (CountVerificationException $e) {
          $this->log->critical($e, $e->errors);
        }
      }
    }
  }

}
