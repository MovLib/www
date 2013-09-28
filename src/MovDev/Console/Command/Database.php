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
namespace MovDev\Console\Command;

use \Locale;
use \MovLib\Exception\DatabaseException;
use \ReflectionClass;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import schema and seed data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Database extends \MovLib\Console\Command\Database {


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

    /**
   * Option for creating git repositories.
   *
   * @var string
   */
  const OPTION_GIT = "git";

  /**
   * Option shortcut for creating git repositories.
   *
   * @var string
   */
  const OPTION_SHORTCUT_GIT = "g";


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
  public function __construct(){
    parent::__construct();
    $this->seedPath = "{$_SERVER["DOCUMENT_ROOT"]}/db/seeds";
    foreach (glob("{$this->seedPath}/*.sql") as $file) {
      $this->seedScripts[basename($file, ".sql")] = $file;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->addOption(self::OPTION_ALL, self::OPTION_SHORTCUT_ALL, InputOption::VALUE_NONE, "Run all migrations and import all seed data (Ignores all other options).")
      ->addOption(self::OPTION_SEED, self::OPTION_SHORTCUT_SEED, InputOption::VALUE_OPTIONAL, "Import seed data file(s).")
      ->addOption(self::OPTION_GIT, self::OPTION_SHORTCUT_GIT, InputOption::VALUE_OPTIONAL, "Create history repositories.")
    ;
  }

  /**
   * Create git repositories.
   *
   * @param string $type [optional]
   *   If supplied, only repositories of this type (e.g. movie) are created, otherwise all repositories are created.
   * @return this
   */
  private function git($type = null) {
    $supportedTypes = [ "movie" ];

    if ($type) {
      if (!in_array($type, $supportedTypes)) {
        $supportedTypes = implode(", ", $supportedTypes);
        $this->exitOnError("Not a valid type! valid types are {$supportedTypes}");
      }
      $this->createRepositories($type);
    }
    else {
      foreach ($supportedTypes as $type) {
        $this->createRepositories($type);
      }
    }

    exec("chmod -R 777 {$_SERVER["DOCUMENT_ROOT"]}/history/*");

    return $this;
  }

  /**
   * Helper function to (re)create git repositories.
   *
   * @param string $type
   *  All repositories of this type are created.
   * @return this
   */
  private function createRepositories($type) {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$type}";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }

    $class = ucfirst($type);
    $class = new ReflectionClass("\\MovLib\\Data\\History\\{$class}");
    if ($result = $this->query("SELECT `{$type}_id` FROM `{$type}s`")) {
      while ($row = $result->fetch_assoc()) {
        $history = $class->newInstance($row["{$type}_id"]);
        $commitHash = $history->createRepository();
        $this->query("UPDATE `{$type}s` SET `commit` = '{$commitHash}' WHERE `{$type}_id` = {$row["{$type}_id"]}");
      }
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $argv;
    $this->setIO($input, $output);
    $options = $this->input->getOptions();
    if ($options[self::OPTION_ALL]) {
      $this->write("Importing schema ...");

      if (($schema = file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/db/movlib.sql")) === false) {
        $this->exitOnError("Could not read schema!");
      }

      try {
        $this->database->transactionStart(MYSQLI_TRANS_START_READ_WRITE);
        $this->database->queries($schema);
      }
      catch (DatabaseException $e) {
        $this->exitOnError("Couldn't import schema!", $e->getTraceAsString());
      }

      $this->write("Importing Intl ICU translations for countries and languages ...");
      $this->importIntlTranslations();
      $this->write("Importing time zone translations ...");
      $this->importTimeZones();
      $this->importSeeds();
//      $this->write("Creating git repositories ...");
//      $this->git();
      try {
        $this->database->transactionCommit();
      }
      catch (DatabaseException $e) {
        $this->exitOnError("Couldn't commit schema and seeds", $e->getTraceAsString());
      }
      $this->write("All Successfull!", self::MESSAGE_TYPE_INFO);
    }
    elseif (array_search("--" . self::OPTION_SEED, $argv) || array_search("-" . self::OPTION_SHORTCUT_SEED, $argv)) {
      empty($options[self::OPTION_SEED]) ? $this->runSeedsInteractive() : $this->importSeeds(true, $options[self::OPTION_SEED]);
    }
    elseif (array_search("--" . self::OPTION_GIT, $argv) || array_search("-" . self::OPTION_SHORTCUT_GIT, $argv)) {
      empty($options[self::OPTION_GIT]) ? $this->git() : $this->git($options[self::OPTION_GIT]);
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
    return $this->database->escapeString(Locale::getDisplayRegion("{$i18n->defaultLanguageCode}-{$countryCode}", $locale));
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
    return $this->database->escapeString(Locale::getDisplayLanguage($languageCode, $locale));
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
      try {
        $this->database->query($query);
      }
      catch (DatabaseException $e) {
        $this->exitOnError("Could not import Intl ICU translations for {$table}!", $e->getTraceAsString());
      }
    }

    // Insert the "Silent" language, because it is not present in the languages list of Intl ICU.
    try {
      $this->database->query("INSERT INTO `languages` (`iso_alpha-2`, `name`, `dyn_translations`) VALUES ('xx', 'Silent', COLUMN_CREATE('de', 'Stumm')) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)");
    }
    catch (DatabaseException $e) {
      $this->exitOnError("Could not import Intl ICU translation for silent language!", $e->getTraceAsString());
    }

    return $this;
  }

  /**
   * Import seeds.
   *
   * @return this
   */
  private function importSeeds() {
    try {
      $queries = null;
      foreach ($this->seedScripts as $table => $script) {
        $this->write("Importing seed '{$table}' ...");
        if (($queries .= file_get_contents($script)) === false) {
          $this->exitOnError("Could not read '{$script}'!");
        }
      }
      if ($queries) {
        $this->database->queries("SET foreign_key_checks = 0;\n{$queries}\nSET foreign_key_checks = 1;");
      }
    }
    catch (DatabaseException $e) {
      $this->exitOnError("Seeds import failed!", $e->getTraceAsString());
    }
    return $this;
  }

  /**
   * Import a single seed.
   *
   * @param string $name
   *   The name of the seed to import.
   * @param boolean $truncate [optional]
   *   Whetever to truncate the table or not.
   * @return this
   */
  private function importSeed($name, $truncate = true) {
    if (!isset($this->seedScripts[$name])) {
      $choices = implode(", ", array_keys($this->seedScripts));
      return $this->write("Invalid seed name '{$name}'. Possible choices are: {$choices}", self::MESSAGE_TYPE_ERROR);
    }
    $this->database->transactionStart()->query("SET foreign_key_checks = 0");
    $this->write("Importing seed '{$table}' ...");
    if ($truncate === true) {
      try {
        $this->write("Truncating table '{$table}' ...");
        $this->database->query("TRUNCATE TABLE `{$table}`");
      }
      catch (DatabaseException $e) {
        $this->exitOnError("Couldn't truncate table '{$table}'!", $e->getTraceAsString());
      }
    }
    if (($queries = file_get_contents($this->seedScripts[$name])) === false) {
      $this->exitOnError("Couldn't read '{$this->seedScripts[$name]}'!");
    }
    try {
      $this->database->queries($queries)->transactionCommit();
      $this->write("Seed '{$name}' import successful!", $e->getTraceAsString());
    }
    catch (DatabaseException $e) {
      $this->exitOnError("Seed '{$name}' import failed!", $e->getTraceAsString());
    }
    return $this;
  }

  /**
   * Import all time zones and their translations.
   *
   * @link https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
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
        if (($fileContent = file_get_contents("{$this->seedPath}/time_zones/{$languageCode}.txt")) === false) {
          $this->exitOnError("Could not read translation file for '{$languageCode}'!");
        }
        foreach (explode("\n", $fileContent) as $line) {
          list($zoneId, $translation) = explode(";", $line);
          $translations[$languageCode][$zoneId] = $translation;
        }
      }
    }

    $query = "INSERT INTO `messages` (`message`, `dyn_translations`) VALUES\n\t";
    for ($i = 0; $i < $c; ++$i) {
      $dynTranslations = null;
      foreach ($systemLanguages as $languageCode => $locale) {
        if (empty($translations[$languageCode][$timeZoneIds[$i]])) {
          continue;
        }
        if ($dynTranslations) {
          $dynTranslations .= ", ";
        }
        $translation = $this->database->escapeString($translations[$languageCode][$timeZoneIds[$i]]);
        $dynTranslations .= "'{$languageCode}', '{$translation}'";
      }
      $dynTranslations = $dynTranslations ? "COLUMN_CREATE({$dynTranslations})" : "''";
      if ($i !== 0) {
        $query .= ",\n\t";
      }
      $zoneId = $this->database->escapeString($timeZoneIds[$i]);
      $enTranslation = $this->database->escapeString($translations["en"][$timeZoneIds[$i]]);
      $query .= "('{$enTranslation}', {$dynTranslations})";
    }

    try {
      $this->database->query($query);
    }
    catch (DatabaseException $e) {
      $this->exitOnError("Could not import time zone translations!", $e->getTraceAsString());
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
      $this->importSeed($this->askWithChoices("Please select a seed to import.", null, array_keys($this->seedScripts)));
    }
    while ($this->askConfirmation("Do you want to import another seed?"));
    return $this;
  }

}
