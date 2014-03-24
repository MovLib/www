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

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Database related administrative tasks.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory where backups are stored.
   *
   * @var string
   */
  protected $pathBackup = "dr://var/backups";

  /**
   * The directory containing the migration scripts.
   *
   * @var string
   */
  protected $pathMigration = "dr://var/migrations";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function configure() {
    $this->setName("database");
    $this->setDescription("Perform various database related tasks.");
    //$this->addOption("backup", "b", InputOption::VALUE_NONE, "Create backup of the complete database.");
    $this->addOption("optimize-tables", "ot", InputOption::VALUE_NONE, "Optimize all tables of all databases.");
    //$this->addOption("migration", "m", InputOption::VALUE_NONE, "Run all migration scripts.");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    foreach ($input->getOptions() as $option) {
      $method = str_replace("-", "", $option);
      if (method_exists($this, $method)) {
        $this->{$method}();
      }
    }
    return 0;
  }

  /**
   * Optimize all tables of all databases.
   *
   * @return this
   */
  protected function optimizeTables() {
    $this->exec("mysqlcheck --optimize --all-databases --verbose");
  }

}
