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
 * DB command for all database related tasks.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @todo Implement for backup/restore
 */
class DB extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The mode for running all scripts in the respective context.
   */
  const MODE_ALL = "all";

  /**
   * The mode for running migrations up to a point in time.
   */
  const MODE_DATE = "date";

  /**
   * The mode for running a single script in the respective context.
   */
  const MODE_SINGLE = "single";

  /**
   * Option for running all migrations and imports.
   */
  const OPTION_ALL = "all";

  /**
   * Option shortcut for running all migrations and imports.
   */
  const OPTION_SHORTCUT_ALL = "a";

  /**
   * Option for running a backup.
   */
  const OPTION_BACKUP = "backup";

  /**
   * Option shortcut for running a backup.
   */
  const OPTION_SHORTCUT_BACKUP = "b";

  /**
   * Option for running migration(s).
   */
  const OPTION_MIGRATION = "migration";

  /**
   * Option for running a restore.
   */
  const OPTION_RESTORE = "restore";

  /**
   * Option shortcut for running a restore.
   */
  const OPTION_SHORTCUT_RESTORE = "r";

  /**
   * Option shortcut for running migration(s).
   */
  const OPTION_SHORTCUT_MIGRATION = "m";

  /**
   * Option for importing seed(s).
   */
  const OPTION_SEED = "seed";

  /**
   * Option shortcut for importing seed(s).
   */
  const OPTION_SHORTCUT_SEED = "s";

  /**
   * Constant for the special Intl ICU seed.
   */
  const SEED_INTL = "intl_translations";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory containing the migration scripts.
   *
   * @var string
   */
  private $migrationDir;

  /**
   * The directory containing the seed scripts.
   *
   * @var string
   */
  private $seedDir;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("db");
    $this->migrationDir = "{$_SERVER["DOCUMENT_ROOT"]}/db/migrations";
    $this->seedDir = "{$_SERVER["DOCUMENT_ROOT"]}/db/seeds";
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setDescription("Execute database tasks.")
      ->addOption(self::OPTION_ALL, self::OPTION_SHORTCUT_ALL, InputOption::VALUE_NONE, "Run all migrations and import all seed data (Ignores all other options).")
      ->addOption(self::OPTION_BACKUP, self::OPTION_SHORTCUT_BACKUP, InputOption::VALUE_NONE, "Perform a backup of the database (Ignores all other options).")
      ->addOption(self::OPTION_RESTORE, self::OPTION_SHORTCUT_RESTORE, InputOption::VALUE_NONE, "Perform a backup of the database (Ignores all other options).")
      ->addOption(self::OPTION_MIGRATION, self::OPTION_SHORTCUT_MIGRATION, InputOption::VALUE_NONE, "Run migration(s).")
      ->addOption(self::OPTION_SEED, self::OPTION_SHORTCUT_SEED, InputOption::VALUE_NONE, "Import seed data file(s).");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setIO($input, $output);
    $options = $this->input->getOptions();
    if ($options[self::OPTION_ALL]) {
      $this->runMigrations(false);
      $this->runSeeds(false);
      return;
    }
    if ($options[self::OPTION_BACKUP]) {
      /** @todo Implement */
      return;
    }
    if ($options[self::OPTION_RESTORE]) {
      /** @todo Implement */
      return;
    }
    if ($options[self::OPTION_MIGRATION]) {
      $this->runMigrations();
    }
    if ($options[self::OPTION_SEED]) {
      $this->runSeeds();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Helper Methods


  /**
   * Import the Intl ICU translations for countries and languages.
   *
   * @return this
   */
  private function importIntlTranslations() {
    $this->write("Importing Intl ICU tranlations for countries and languages.");
    $this->system(
      "php {$_SERVER["DOCUMENT_ROOT"]}/bin/translation_importer.php",
      "Could not import Intl ICU translations for countries and languages!",
      [ "exit_on_error" => false ]
    );
    return $this;
  }

  /**
   * Import one or several SQL scripts into the database.
   *
   * @param string|array $scripts
   *   The absolute path(s) to the script(s) to import either as string or array.
   * @return this
   */
  private function importSqlScripts($scripts) {
    $command = "mysql {$GLOBALS["movlib"]["default_database"]} <";
    if (is_array($scripts)) {
      foreach ($scripts as $script) {
        $this->write("Importing script '{$script}'.");
        $this->system("{$command} {$script}", "Import of script '{$script}' failed!", [ "exit_on_error" => false ]);
      }
    }
    else {
      $this->write("Importing script '{$scripts}'.");
      $this->system("{$command} {$scripts}", "Import of script '{$scripts}' failed!", [ "exit_on_error" => false ]);
    }
    return $this;
  }

  /**
   * Run migrations according to the <code>MODE_*</code> constants.
   *
   * The flag <var>$interactive</var> determines whether the user is prompted for the mode or if all migrations should be run.
   *
   * @param boolean $interactive [optional]
   *   Determines whether the user should be asked for the mode (<code>TRUE</code>) or not (<code>FALSE</code>).
   *   Defaults to <code>TRUE</code>, which means that all migrations are run.
   * @return this
   */
  private function runMigrations($interactive = true) {
    // Gather all available migration scripts for autocompletion and execution.
    $migrationChoices = [];
    $migrationScripts = [];
    foreach (glob("{$this->migrationDir}/*.sql") as $file) {
      $migrationChoices[] = basename($file);
      $migrationScripts[] = $file;
    }
    $c = count($migrationChoices);

    if ($interactive === true) {
      $answers = [
        self::MODE_ALL    => "Run all migrations (default).",
        self::MODE_SINGLE => "Run a single migration (You can do that several times).",
        self::MODE_DATE   => "Run migrations up to a specified point in time.",
      ];
      $answer = $this->askWithChoices("Please select a migration mode", self::MODE_ALL, array_keys($answers), array_values($answers));
    }
    else {
      $answer = self::MODE_ALL;
    }
    switch ($answer) {
      case self::MODE_ALL:
        $this->importSqlScripts($migrationScripts);
        break;
      case self::MODE_SINGLE:
        do {
          $answer = $this->askWithChoices("Please select a migration to run.", null, $migrationChoices);
          if (($i = array_search($answer, $migrationChoices)) === false) {
            $this->write("Unknown migration '{$answer}'. Possible migrations are: " . implode(", ", $migrationChoices) . ".", self::MESSAGE_TYPE_ERROR);
          }
          else {
            $this->importSqlScripts($migrationScripts[$i]);
          }
        } while ($this->askConfirmation("Do you want to run another migration?"));
        break;
      case self::MODE_DATE:
        $answer = $this->askWithChoices("Please select a migration snapshot to set up the schema.", $migrationChoices[$c - 1], $migrationChoices);
        if (($length = array_search($answer, $migrationChoices)) === false) {
          $this->write("Unknown migration '{$answer}'. Possible migrations are: " . implode(", ", $migrationChoices) . ".", self::MESSAGE_TYPE_ERROR);
        }
        $this->importSqlScripts(array_slice($migrationScripts, 0, ++$length));
        break;
      default:
        $this->exitOnError("Unknown migration mode '{$answer}'. Possible modes are: " . implode(", ", array_keys($answers)) . ".");
        break;
    }
    return $this;
  }

  /**
   * Run seed imports according to the <code>MODE_*</code> constants.
   *
   * The flag <var>$interactive</var> determines whether the user is prompted for the mode or if all seeds should be imported.
   *
   * @param boolean $interactive [optional]
   *   Determines whether the user should be asked for the mode (<code>TRUE</code>) or not (<code>FALSE</code>).
   *   Defaults to <code>TRUE</code>, which means that all seeds are imported.
   * @return this
   */
  private function runSeeds($interactive = true) {
    // Gather all available seed data scripts for autocompletion and execution.
    $seedScripts = [ self::SEED_INTL => ""  ];
    foreach (glob("{$this->seedDir}/*.sql") as $file) {
      $seedScripts[basename($file)] = $file;
    }
    if ($interactive === true) {
      $answers = [
        self::MODE_ALL  => "Import all seed data in the correct order (default).",
        self::MODE_SINGLE => "Import a single seed script (You can do that several times).
          Many of these scripts have dependencies, therefore it is HIGHLY recommended to import all seed data at once.",
      ];
      $answer = $this->askWithChoices("Please select a seed import mode.", self::MODE_ALL, array_keys($answers), array_values($answers));
    } else {
      $answer = self::MODE_ALL;
    }
    switch ($answer) {
      case self::MODE_ALL:
        $this->importIntlTranslations();
        chdir($this->seedDir);
        $this->importSqlScripts("{$this->seedDir}/load_all.txt");
        break;
      case self::MODE_SINGLE:
        do {
          $answer = $this->askWithChoices("Please select a seed to import.", null, array_keys($seedScripts));
          if (array_key_exists($answer, $seedScripts) === false) {
            $this->write("Unknown seed '{$answer}'. Possible seeds are: " . implode(", ", array_values($seedScripts)) . ".", self::MESSAGE_TYPE_ERROR);
          }
          else {
            if ($answer === self::SEED_INTL) {
              $this->importIntlTranslations();
            }
            else {
              $this->importSqlScripts($seedScripts[$answer]);
            }
          }
        } while ($this->askConfirmation("Do you want to import another seed?"));
        break;
      default:
        $this->exitOnError("Unknown seed import mode '{$answer}'. Possible modes are: " . implode(", ", array_keys($answers)) . ".");
        break;
    }
    return $this;
  }

}
