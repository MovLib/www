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

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for all database related tasks.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Tool\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory where backups are stored.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathBackup = "/private/backup";

  /**
   * The directory containing the migration scripts.
   *
   * @see Database::__construct()
   * @var string
   */
  protected $pathMigration = "/conf/migration";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function configure() {
    global $kernel;
    $this->setName("database");
    $this->pathBackup    = "{$kernel->documentRoot}{$this->pathBackup}";
    $this->pathMigration = "{$kernel->documentRoot}{$this->pathMigration}";
    $this->setDescription("Perform various database related tasks.");
    $this->addInputOption("backup", InputOption::VALUE_NONE, "Create backup of the complete database.");
    $this->addInputOption("migration", InputOption::VALUE_NONE, "Run all migration scripts.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    throw new \RuntimeException("Not implemented yet!");
    return $options;
  }

}
