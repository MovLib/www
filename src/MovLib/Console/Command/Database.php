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

use \Locale;
use \MovLib\Exception\AbstractException;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\FileSystemException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \mysqli;

/**
 * CLI commands for all database related tasks.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Option for running all migrations and imports.
   *
   * @var string
   */
  const OPTION_ALL = "all";

  /**
   * Option shortcut for running all migrations and imports.
   *
   * @var string
   */
  const OPTION_SHORTCUT_ALL = "a";

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

  /**
   * Option for importing seed(s).
   *
   * @var string
   */
  const OPTION_SEED = "seed";

  /**
   * Option shortcut for importing seed(s).
   *
   * @var string
   */
  const OPTION_SHORTCUT_SEED = "s";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory containing the migration scripts.
   *
   * @var string
   */
  private $migrationPath;

  /**
   * The mysqli connection to the database.
   *
   * @var \mysqli
   */
  private $mysqli;

  /**
   * The directory containing the seed scripts.
   *
   * @var string
   */
  private $seedPath;

  /**
   * Associative array containing the seed names without extension as keys and the seed absolute paths as values.
   *
   * @var array
   */
  private $seedScripts;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("db");
    $this->migrationPath = "{$_SERVER["DOCUMENT_ROOT"]}/db/migrations";
    $this->seedPath = "{$_SERVER["DOCUMENT_ROOT"]}/db/seeds";
    foreach (glob("{$this->seedPath}/*.sql") as $file) {
      $this->seedScripts[basename($file, ".sql")] = $file;
    }
    $this->mysqli = new mysqli();
    $this->mysqli->real_connect();
    $this->mysqli->select_db($GLOBALS["movlib"]["default_database"]);
  }

  /**
   * Commit all uncommited changes and close connection.
   */
  public function __destruct() {
    if ($this->mysqli && !$this->mysqli->errno) {
      if ($this->mysqli->commit() === false) {
        $this->exitOnError("FATAL ERROR! Could not commit changes: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
      if ($this->mysqli->close() === false) {
        $this->exitOnError("FATAL ERROR! Could not close connection: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
    }
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
    if (DEV) {
      $this
        ->addOption(self::OPTION_ALL, self::OPTION_SHORTCUT_ALL, InputOption::VALUE_NONE, "Run all migrations and import all seed data (Ignores all other options).")
        ->addOption(self::OPTION_SEED, self::OPTION_SHORTCUT_SEED, InputOption::VALUE_NONE, "Import seed data file(s).")
      ;
    }
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $argv;
    $this->setIO($input, $output)->mysqli->autocommit(false);;
    $options = $this->input->getOptions();
    if ($options[self::OPTION_ALL]) {
      $this->write("Importing schema ...");

      if (($schema = file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/db/movlib.sql")) === false) {
        $this->exitOnError("Could not read schema!");
      }

      $this->mysqli->multi_query($schema);
      do {
        if ($this->mysqli->errno) {
          $this->exitOnError("Could not import schema: {$this->mysqli->error} ({$this->mysqli->errno})");
        }
        $this->mysqli->use_result();
      }
      while ($this->mysqli->next_result() && $this->mysqli->more_results());

      $this
        ->write("Importing Intl ICU translations for countries and languages ...")
        ->importIntlTranslations()
        ->write("Importing time zone translations ...")
        ->importTimeZones()
        ->importSeeds()
        ->write("All Successfull!", self::MESSAGE_TYPE_INFO)
      ;
    }
    elseif (array_search("--" . self::OPTION_SEED, $argv) || array_search("-" . self::OPTION_SHORTCUT_SEED, $argv)) {
      empty($options[self::OPTION_SEED]) ? $this->runSeedsInteractive() : $this->importSeeds(true, $options[self::OPTION_SEED]);
    }
    else {
      $this->exitOnError("Not implemented yet!");
    }
  }

  /**
   * Helper function to translate country names.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param string $countryCode
   *   The ISO 3166-1 alpha-2 country code.
   * @param string $locale
   *   The desired locale or ISO 639-1 alpha-2 language code.
   * @return string
   *   The country's name translated to the desired locale.
   */
  private function _countries($countryCode, $locale) {
    global $i18n;
    return $this->mysqli->real_escape_string(Locale::getDisplayRegion("{$i18n->defaultLanguageCode}-{$countryCode}", $locale));
  }

  /**
   * Helper function to translate language names.
   *
   * @param string $languageCode
   *   The ISO 639-1 alpha-2 language code.
   * @param string $locale
   *   The desired locale or ISO 639-1 alpha-2 language code.
   * @return string
   *   The language's name translated to the desired locale.
   */
  private function _languages($languageCode, $locale) {
    return $this->mysqli->real_escape_string(Locale::getDisplayLanguage($languageCode, $locale));
  }

  /**
   * Import the Intl ICU translations for countries and languages.
   *
   * @return this
   */
  private function importIntlTranslations() {
    global $i18n;

    // Contains all country and basic language codes that our application shall know about.
    $codes = [
      "countries" => [ "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "ZA", "ZM", "ZW" ],
      "languages" => [ "ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy", "as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi", "bs", "br", "bg", "my", "ca", "ch", "ce", "ny", "zh", "cv", "kw", "co", "cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee", "fo", "fj", "fi", "fr", "ff", "gl", "ka", "de", "el", "gn", "gu", "ht", "ha", "he", "hz", "hi", "ho", "hu", "ia", "id", "ie", "ga", "ig", "ik", "io", "is", "it", "iu", "ja", "jv", "kl", "kn", "kr", "ks", "kk", "km", "ki", "rw", "ky", "kv", "kg", "ko", "ku", "kj", "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv", "gv", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mh", "mn", "na", "nv", "nb", "nd", "ne", "ng", "nn", "no", "ii", "nr", "oc", "oj", "cu", "om", "or", "os", "pa", "pi", "fa", "pl", "ps", "pt", "qu", "rm", "rn", "ro", "ru", "sa", "sc", "sd", "se", "sm", "sg", "sr", "gd", "sn", "si", "sk", "sl", "so", "st", "es", "su", "sw", "ss", "sv", "ta", "te", "tg", "th", "ti", "bo", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy", "wo", "fy", "xh", "yi", "yo", "za", "zu" ]
    ];

    $systemLanguages = $GLOBALS["movlib"]["locales"];
    unset($systemLanguages[$i18n->defaultLanguageCode]);
    $d = count($systemLanguages);

    // Insert data into database.
    foreach ($codes as $table => $data) {
      $query = "INSERT INTO `{$table}` (`iso_alpha-2`, `name`, `dyn_translations`) VALUES\n\t";
      $c = count($data);
      for ($i = 0; $i < $c; ++$i) {
        if ($i !== 0) {
          $query .= ",\n";
        }
        $dynTranslations = null;
        foreach ($systemLanguages as $languageCode => $locale) {
          if ($dynTranslations) {
            $dynTranslations .= ", ";
          }
          $dynTranslations .= "'$languageCode', '{$this->{"_{$table}"}($data[$i], $locale)}'";
        }
        $dynTranslations = $dynTranslations ? "COLUMN_CREATE({$dynTranslations})" : "''";
        $query .= "('{$data[$i]}', '{$this->{"_{$table}"}($data[$i], $i18n->defaultLanguageCode)}', {$dynTranslations})";
      }
      $query .= "ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)";
      if ($this->mysqli->query($query) === false) {
        $this->exitOnError("Could not import Intl ICU translations for {$table}: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
    }

    // Insert the "Silent" language, because it is not present in the languages list of Intl ICU.
    if ($this->mysqli->query(
      "INSERT INTO `languages` (`iso_alpha-2`, `name`, `dyn_translations`) VALUES ('xx', 'Silent', COLUMN_CREATE('de', 'Stumm')) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)"
    ) === false) {
      $this->exitOnError("Could not import Intl ICU translations for {$table}: {$this->mysqli->error} ({$this->mysqli->errno})");
    }

    return $this;
  }

  /**
   * Import seed scripts.
   *
   * @param boolean $truncate [optional]
   *   Flag to determine, whether the table(s) should be truncated before the inserts or not.
   *   Defaults to <code>FALSE</code>.
   * @param string $seedName [optional]
   *   If supplied, the seed with this name is imported, otherwise all seeds are imported.
   * @return this
   */
  private function importSeeds($truncate = false, $seedName = null) {
    if ($seedName) {
      if (!isset($this->seedScripts[$seedName])) {
        return $this->write("Invalid seed name '{$seedName}'. Possible choices are: " . implode(", ", array_keys($this->seedScripts)), self::MESSAGE_TYPE_ERROR);
      }
      $seeds[$seedName] = $this->seedScripts[$seedName];
      $successMessage = true;
    }
    else {
      $seeds = $this->seedScripts;
      $successMessage = false;
    }

    $this->mysqli->query("SET foreign_key_checks = 0");
    foreach ($seeds as $table => $script) {
      $this->write("Importing seed '{$table}' ...");

      // Try to truncate the table if it was requested.
      if ($truncate === true && $this->write("Truncating table `{$table}` ...")->mysqli->query("TRUNCATE TABLE `{$table}`") === false) {
        $this->exitOnError("Could not truncate table '{$table}': {$this->mysqli->error} ({$this->mysqli->errno})");
      }

      // Try to snatch the script for this table.
      if (($query = file_get_contents($script)) === false) {
        $this->exitOnError("Could not read '{$script}'!");
      }

      // Try to execute the script at once.
      $this->mysqli->multi_query($query);
      do {
        if ($this->mysqli->errno) {
          $this->exitOnError("Could not execute seed script for '{$table}': {$this->mysqli->error} ({$this->mysqli->errno})");
        }
        $this->mysqli->use_result();
      }
      while ($this->mysqli->next_result() && $this->mysqli->more_results());

      if ($successMessage === true) {
        $this->write("Seed '{$table}' imported successfully!", self::MESSAGE_TYPE_INFO);
      }
    }
    $this->mysqli->query("SET foreign_key_checks = 1");

    return $this;
  }

  /**
   * Import all time zones and their translations.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\FileSystemException
   */
  private function importTimeZones() {
    global $i18n;

    $systemLanguages = $GLOBALS["movlib"]["locales"];
    $timeZoneIds = timezone_identifiers_list();
    $c = count($timeZoneIds);
    $translations = [];

    foreach ($systemLanguages as $languageCode => $locale) {
      if ($languageCode == $i18n->defaultLanguageCode) {
        for ($i = 0; $i < $c; ++$i) {
          $translations[$i18n->defaultLanguageCode][$timeZoneIds[$i]] = strtr($timeZoneIds[$i], "_", " ");
        }
        unset($systemLanguages[$i18n->defaultLanguageCode]);
      }
      else {
        $translationPath = "{$this->seedPath}/time_zones/{$languageCode}.json";
        if (!is_file($translationPath)) {
          $this->exitOnError("Time zone translation file for '{$languageCode}' is missing!");
        }
        $translations[$languageCode] = json_decode(file_get_contents($translationPath), true);
      }
    }

    $queries = "INSERT INTO `messages` (`message`, `dyn_translations`) VALUES\n\t";
    for ($i = 0; $i < $c; ++$i) {
      $dynTranslations = null;
      foreach ($systemLanguages as $languageCode => $locale) {
        if (empty($translations[$languageCode][$timeZoneIds[$i]])) {
          continue;
        }
        if ($dynTranslations) {
          $dynTranslations .= ", ";
        }
        $translation = $this->mysqli->real_escape_string($translations[$languageCode][$timeZoneIds[$i]]);
        $dynTranslations .= "'{$languageCode}', '{$translation}'";
      }
      $dynTranslations = $dynTranslations ? "COLUMN_CREATE({$dynTranslations})" : "''";
      if ($i !== 0) {
        $queries .= ",\n\t";
      }
      $zoneId = $this->mysqli->real_escape_string($timeZoneIds[$i]);
      $enTranslation = $this->mysqli->real_escape_string($translations["en"][$timeZoneIds[$i]]);
      $queries .= "('{$enTranslation}', {$dynTranslations})";
    }

    if ($this->mysqli->query($queries) === false) {
      $this->exitOnError("Could not import time zone translations!");
    }

    return $this;
  }

  /**
   * Rollback all uncommited changes.
   *
   * @return this
   */
  public function rollback() {
    if ($this->mysqli && !$this->mysqli->errno) {
      if ($this->mysqli->rollback() === false) {
        $this->exitOnError("FATAL ERROR! Could not rollback changes: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
      if ($this->mysqli->close() === false) {
        $this->exitOnError("FATAL ERROR! Could not close connection: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
    }
    return $this;
  }

  /**
   * Run seed imports with an interactive dialogue.
   *
   * @return this
   */
  private function runSeedsInteractive() {
    do {
      $this->importSeeds(true, $this->askWithChoices("Please select a seed to import.", null, array_keys($this->seedScripts)));
    }
    while ($this->askConfirmation("Do you want to import another seed?"));
    return $this;
  }

}
