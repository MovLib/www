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

use \mysqli;
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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The directory containing the migration scripts.
   *
   * @var string
   */
  private $migrationDir;

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
  private $seedDir;

  /**
   * Associative array containing the seed names without extension as keys and the script paths as values.
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
    $this->migrationDir = "{$_SERVER["DOCUMENT_ROOT"]}/db/migrations";
    $this->seedDir = "{$_SERVER["DOCUMENT_ROOT"]}/db/seeds";
    foreach (glob("{$this->seedDir}/*.sql") as $file) {
      $this->seedScripts[basename($file, ".sql")] = $file;
    }
    $this->mysqli = new mysqli();
    $this->mysqli->real_connect();
    $this->mysqli->select_db($GLOBALS["movlib"]["default_database"]);
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setDescription("Execute database tasks.")
      ->addOption(self::OPTION_BACKUP, self::OPTION_SHORTCUT_BACKUP, InputOption::VALUE_NONE, "Perform a backup of the database (Ignores all other options).")
      ->addOption(self::OPTION_RESTORE, self::OPTION_SHORTCUT_RESTORE, InputOption::VALUE_NONE, "Perform a backup of the database (Ignores all other options).")
      ->addOption(self::OPTION_MIGRATION, self::OPTION_SHORTCUT_MIGRATION, InputOption::VALUE_NONE, "Run migration(s).");
    if (DEV) {
      $this->addOption(self::OPTION_ALL, self::OPTION_SHORTCUT_ALL, InputOption::VALUE_NONE, "Run all migrations and import all seed data (Ignores all other options).");
      $this->addOption(self::OPTION_SEED, self::OPTION_SHORTCUT_SEED, InputOption::VALUE_OPTIONAL, "Import seed data file(s).");
    }
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $argv;
    $this->setIO($input, $output);
    $options = $this->input->getOptions();
    if ($options[self::OPTION_ALL]) {
      $this->write("Importing schema.");
      $this->importSqlScripts("{$_SERVER["DOCUMENT_ROOT"]}/db/movlib.sql");
      $this->write("Import successful.", self::MESSAGE_TYPE_INFO);
      $this->write("Importing Intl translations for countries and languages.");
      $this->importIntlTranslations();
      $this->write("Import successful.", self::MESSAGE_TYPE_INFO);
      $this->write("Importing seeds.");
      $this->importSeeds();
      return;
    }
    if ($options[self::OPTION_BACKUP]) {
      /** @todo Implement */
      $this->exitOnError("Not implemented yet!");
      return;
    }
    if ($options[self::OPTION_RESTORE]) {
      /** @todo Implement */
      $this->exitOnError("Not implemented yet!");
      return;
    }
    if ($options[self::OPTION_MIGRATION]) {
      $this->runMigrations();
    }
    if (array_search("--" . self::OPTION_SEED, $argv) || array_search("-" . self::OPTION_SHORTCUT_SEED, $argv)) {
      if (!empty($options[self::OPTION_SEED])) {
        $this->importSeeds(true, $options[self::OPTION_SEED]);
      }
      else {
        $this->runSeedsInteractive();
      }
    }
  }

  protected function rollback() {
    if (isset($this->mysqli)) {
      $this->mysqli->close();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Helper Methods


  /**
   * Import the Intl ICU translations for countries and languages.
   *
   * @return this
   */
  private function importIntlTranslations() {
    global $i18n;
    /**
     * Contains all country and basic language codes that our application shall know about.
     *
     * @var array
     */
    $codes = [
      "countries" => [ "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "ZA", "ZM", "ZW" ],
      "languages" => [ "ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy", "as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi", "bs", "br", "bg", "my", "ca", "ch", "ce", "ny", "zh", "cv", "kw", "co", "cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee", "fo", "fj", "fi", "fr", "ff", "gl", "ka", "de", "el", "gn", "gu", "ht", "ha", "he", "hz", "hi", "ho", "hu", "ia", "id", "ie", "ga", "ig", "ik", "io", "is", "it", "iu", "ja", "jv", "kl", "kn", "kr", "ks", "kk", "km", "ki", "rw", "ky", "kv", "kg", "ko", "ku", "kj", "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv", "gv", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mh", "mn", "na", "nv", "nb", "nd", "ne", "ng", "nn", "no", "ii", "nr", "oc", "oj", "cu", "om", "or", "os", "pa", "pi", "fa", "pl", "ps", "pt", "qu", "rm", "rn", "ro", "ru", "sa", "sc", "sd", "se", "sm", "sg", "sr", "gd", "sn", "si", "sk", "sl", "so", "st", "es", "su", "sw", "ss", "sv", "ta", "te", "tg", "th", "ti", "bo", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy", "wo", "fy", "xh", "yi", "yo", "za", "zu" ]
    ];

    /**
     * Helper function to translate country names.
     *
     * @global \MovLib\Model\I18nModel $i18n
     * @param string $country_code
     *   The ISO 3166-1 alpha-2 country code.
     * @param string $locale
     *   The desired locale or ISO 639-1 alpha-2 language code.
     * @return string
     *   The country's name translated to the desired locale.
     */
    $countries = function ($country_code, $locale) {
      global $i18n;
      return \Locale::getDisplayRegion("{$i18n->defaultLanguageCode}-{$country_code}", $locale);
    };

    /**
     * Helper function to translate language names.
     *
     * @param string $language_code
     *   The ISO 639-1 alpha-2 language code.
     * @param string $locale
     *   The desired locale or ISO 639-1 alpha-2 language code.
     * @return string
     *   The language's name translated to the desired locale.
     */
    $languages = function ($language_code, $locale) {
      return \Locale::getDisplayLanguage($language_code, $locale);
    };

    // Get rid of the default language code in the supported language codes array.
    foreach ($GLOBALS["movlib"]["locales"] as $language_code => $locale) {
      if ($language_code != $i18n->defaultLanguageCode) {
        $supported_language_codes[] = $language_code;
      }
    }

    // Insert data into database.
    foreach ($codes as $table => $data) {
      $data_count = count($data) - 1;
      $values = "";
      $bind_param_args = [ "" ];
      $names = [];
      for ($i = 0; $i <= $data_count; ++$i) {
        $names[$i]["_"] = call_user_func(${$table}, $data[$i], $i18n->defaultLanguageCode);
        $values .= "(?, ?, COLUMN_CREATE(";
        $bind_param_args[0] .= "ss";
        $bind_param_args[] = &$data[$i];
        $bind_param_args[] = &$names[$i]["_"];
        $comma = "";
        $supported_language_codes_count = count($supported_language_codes);
        for ($j = 0; $j < $supported_language_codes_count; ++$j) {
          $names[$i][$j] = call_user_func(${$table}, $data[$i], $supported_language_codes[$j]);
          $values .= "{$comma}?, ?";
          $bind_param_args[0] .= "ss";
          $bind_param_args[] = &$supported_language_codes[$j];
          $bind_param_args[] = &$names[$i][$j];
          $comma = ", ";
        }
        $values .= "))";
        if ($i < $data_count) {
          $values .= ", ";
        }
      }
      if (($stmt = $this->mysqli->prepare(
        "INSERT
          INTO `{$table}` (`iso_alpha-2`, `name`, `dyn_translations`)
          VALUES {$values}
          ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)
        "
      )) === false) {
        $this->exitOnError("Could not insert Intl ICU translations: {$this->mysqli->error} ({$this->mysqli->errno})");
      }
      if (call_user_func_array([ $stmt, "bind_param" ], $bind_param_args) === false) {
        $stmt->close();
        $this->exitOnError("Could not insert Intl ICU translations: {$stmt->error} ({$stmt->errno})");
      }
      $stmt->execute();
      $stmt->close();
    }
    // Insert the "Silent" language, because it is not present in the language list of Intl.
    if (($stmt = $this->mysqli->prepare(
      "INSERT
        INTO `languages` (`iso_alpha-2`, `name`, `dyn_translations`)
        VALUES ('xx', 'Silent', COLUMN_CREATE('de', 'Stumm'))
        ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)
      "
    )) === false) {
      $this->exitOnError("Could not insert Intl ICU translations: {$this->mysqli->error} ({$this->mysqli->errno})");
    }
    $stmt->execute();
    $stmt->close();
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
      if (array_key_exists($seedName, $this->seedScripts) === false) {
        $this->write("Invalid seed name '{$seedName}'. Possible choices are: " . implode(", ", array_keys($this->seedScripts)), self::MESSAGE_TYPE_ERROR);
        return $this;
      }
      $seeds[$seedName] = $this->seedScripts[$seedName];
    }
    else {
      $seeds = $this->seedScripts;
    }
    $this->mysqli->query("SET foreign_key_checks = 0");
    foreach ($seeds as $table => $script) {
      $this->write("Importing seed script '{$script}'.");
      if ($truncate === true) {
        $this->write("Truncating table `{$table}`.");
        $success = $this->mysqli->query("TRUNCATE TABLE `{$table}`");
        if ($success === false) {
          $this->exitOnError("Could not truncate table `{$table}`: {$this->mysqli->error} ({$this->mysqli->errno})!");
        }
      }
      if (($query = file_get_contents($script)) === false) {
        $this->exitOnError("Could not read '{$script}'!");
        continue;
      }
      $success = $this->mysqli->multi_query($query);
      while($success) {
        if ($this->mysqli->more_results() === false) {
          break;
        }
        $success = $this->mysqli->next_result();
      }
      if ($this->mysqli->errno !== 0) {
        $this->exitOnError("Execution of seed '{$script}' failed: {$this->mysqli->error} ({$this->mysqli->errno})!");
      }
      $this->write("Import successful.", self::MESSAGE_TYPE_INFO);
    }
    $this->mysqli->query("SET foreign_key_checks = 1");
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
   * Run migrations.
   */
  private function runMigrations($interactive = true) {
    /** @todo Implement upgrading with migrations. */
  }

  /**
   * Run seed imports with an interactive dialogue.
   *
   * @return this
   */
  private function runSeedsInteractive() {
    do {
      $answer = $this->askWithChoices("Please select a seed to import.", null, array_keys($this->seedScripts));
      $this->importSeeds(true, $answer);
    } while ($this->askConfirmation("Do you want to import another seed?"));
    return $this;
  }

}
