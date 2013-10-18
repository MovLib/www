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

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI commands for all database related tasks.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Option for running a backup.
   *
   * @var string
   */
  const OPTION_BACKUP = "backup";

  /**
   * Option shortcut for running a backup.
   *
   * @var string
   */
  const OPTION_SHORTCUT_BACKUP = "b";

  /**
   * Option for running migration(s).
   *
   * @var string
   */
  const OPTION_MIGRATION = "migration";

  /**
   * Option shortcut for running migration(s).
   *
   * @var string
   */
  const OPTION_SHORTCUT_MIGRATION = "m";

  /**
   * Option for running a restore.
   *
   * @var string
   */
  const OPTION_RESTORE = "restore";

  /**
   * Option shortcut for running a restore.
   *
   * @var string
   */
  const OPTION_SHORTCUT_RESTORE = "r";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory containing the migration scripts.
   *
   * @var string
   */
  protected $migrationPath;

  /**
   * The database instance.
   *
   * @var \MovDev\Database
   */
  protected $database;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("db");
    $this->migrationPath = "{$_SERVER["DOCUMENT_ROOT"]}/db/migrations";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setDescription("Execute database tasks.")
      ->addOption(self::OPTION_BACKUP, self::OPTION_SHORTCUT_BACKUP, InputOption::VALUE_NONE, "Perform a backup of the database (Ignores all other options).")
      ->addOption(self::OPTION_RESTORE, self::OPTION_SHORTCUT_RESTORE, InputOption::VALUE_NONE, "Perform a backup of the database (Ignores all other options).")
      ->addOption(self::OPTION_MIGRATION, self::OPTION_SHORTCUT_MIGRATION, InputOption::VALUE_NONE, "Run migration(s).")
    ;
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setIO($input, $output)->exitOnError("Not implemented yet!");
  }

}
