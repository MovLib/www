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
namespace MovLib\Console\Command\Dev;

use \MovLib\Console\MySQLi;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * @todo Description of SeedImportNew
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SeedDatabase extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * URI to the SQL schema file.
   *
   * @var string
   */
  protected $schema = "dr://etc/mysql/movlib.sql";

  /**
   * URI to the SQL script directory.
   *
   * @var string
   */
  protected $scriptDirectory = "dr://etc/seed";

  /**
   * URI to the SQL script order file.
   *
   * @var string
   */
  protected $scriptOrder = "dr://etc/seed/SQL-script-order.php";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("seed-database");
    $this->setDescription("Seed database");
    $this->addArgument("script", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "The SQL scripts to import.", [ "schema", "all" ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $scripts = $input->getArgument("script");

    if (($key = array_search("schema", $scripts)) !== false) {
      $this->writeVerbose("Importing database schema <comment>{$this->schema}</comment>");
      $this->exec("mysql < '{$this->fs->realpath($this->schema)}'");
      $this->writeVerbose("Successfully importend database schema!", self::MESSAGE_TYPE_INFO);
      unset($scripts[$key]);
      $scripts = [ "all" ];
    }

    $order = require $this->scriptOrder;
    if (in_array("all", $scripts)) {
      $this->writeVerbose("Found special argument <comment>all</comment> importing all SQL scripts");
      $scripts = $order;
    }
    else {
      $this->writeVerbose("Ordering script arguments according to <comment>{$this->scriptOrder}</comment>");
      $scriptsClone = $scripts;
      $scripts      = [];
      $position     = 1;
      foreach ($order as $script) {
        if (in_array($script, $scriptsClone)) {
          $this->writeDebug("<comment>{$script}</comment> will be <info>{$position}</info>");
          $scripts[] = $script;
          ++$position;
        }
      }
    }

    $this->writeVerbose("Importing individual SQL scripts...");
    $mysqli = new MySQLi("movlib");
    try {
      foreach ($scripts as $script) {
        $script = "{$this->scriptDirectory}/{$script}.sql";
        $this->writeDebug("Importing <comment>{$script}</comment>");
        $mysqli->begin_transaction();
        $mysqli->query("SET foreign_key_checks = 0");
        $mysqli->multi_query(file_get_contents($script));
        do {
          $mysqli->use_result();
          if (($more = $mysqli->more_results())) {
            $mysqli->next_result();
          }
        }
        while ($more);
        $mysqli->query("SET foreign_key_checks = 1");
        $mysqli->commit();
      }
    }
    catch (\mysqli_sql_exception $e) {
      $mysqli->query("SET foreign_key_checks = 1");
      $mysqli->rollback();
      throw $e;
    }
    $this->writeVerbose("Successfully imported individual SQL scripts!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

}
