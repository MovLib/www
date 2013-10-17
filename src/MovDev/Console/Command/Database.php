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
use \MovLib\Data\UserExtended as User;
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
  use \MovDev\TraitUtilities;


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
  const OPTION_ALL_SHORTCUT = "a";

  /**
   * Option to create users.
   *
   * @var string
   */
  const OPTION_CREATE_USERS = "create-users";

  /**
   * Option shortcut for creating users.
   *
   * @var string
   */
  const OPTION_CREATE_USERS_SHORTCUT = "u";

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
  const OPTION_GIT_SHORTCUT = "g";

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
  const OPTION_SEED_SHORTCUT = "s";


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
    parent::configure();
    $this
      ->addOption(self::OPTION_ALL, self::OPTION_ALL_SHORTCUT, InputOption::VALUE_NONE, "Run all migrations and import all seed data (Ignores all other options).")
      ->addOption(self::OPTION_CREATE_USERS, self::OPTION_CREATE_USERS_SHORTCUT, InputOption::VALUE_REQUIRED, "Create specified amout of valid random users.")
      ->addOption(self::OPTION_GIT, self::OPTION_GIT_SHORTCUT, InputOption::VALUE_OPTIONAL, "Create history repositories.")
      ->addOption(self::OPTION_SEED, self::OPTION_SEED_SHORTCUT, InputOption::VALUE_OPTIONAL, "Import seed data file(s).")
    ;
  }

  /**
   * Helper function to (re)create git repositories.
   *
   * @param string $type
   *  All repositories of this type are created.
   * @return this
   */
  protected function createRepositories($type) {
    $path = "{$_SERVER["DOCUMENT_ROOT"]}/history/{$type}";
    if (is_dir($path)) {
      exec("rm -rf {$path}");
    }

    $class = ucfirst($type);
    $class = new ReflectionClass("\\MovLib\\Data\\History\\{$class}");
    if ($result = $this->database->query("SELECT `{$type}_id` FROM `{$type}s`")->get_result()->fetch_all(MYSQLI_ASSOC)) {
      $c = count($result);
      for ($i = 0; $i < $c; ++$i) {
        $history = $class->newInstance($result[$i]["{$type}_id"]);
        $commitHash = $history->createRepository();
        $this->database->query("UPDATE `{$type}s` SET `commit` = '{$commitHash}' WHERE `{$type}_id` = {$result[$i]["{$type}_id"]}");
      }
    }
    return $this;
  }

  /**
   * Helper method to generate random user names.
   *
   * @return string
   *   A random user name.
   */
  private function _randomUsername() {
    static $allNames = [];
    $randomName = "";
    $characters = array_merge(range("A", "Z"), range("a", "z"), range("0", "9"));
    $c          = count($characters) - 1;
    for ($i = 0; $i < 8; ++$i) {
      $randomName .= $characters[mt_rand(0, $c)];
    }
    if (in_array($randomName, $allNames)) {
      $randomName = $this->_randomUsername();
    }
    $allNames[] = $randomName;
    return $randomName;
  }

  /**
   * Create <var>$amount</var> random users.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $amount
   *   The amount of users to generate.
   * @return this
   */
  protected function createUsers($amount) {
    global $i18n;
    if (!is_numeric($amount) || $amount <= 0) {
      $this->createUsers($this->ask("You must enter a positive numeric value!"));
    }

    $this->write("Preparing to create {$amount} users ...");
    $user = new User();
    $insertValues = $insertTypes = $insertParams = $usernamesWithAvatars = null;
    $this->progress->start($this->output, $amount);

    $this->write("Creating {$amount} random users ...");
    for ($i = 0; $i < $amount; ++$i) {
      $username              = $this->_randomUsername();
      $avatarName            = $this->invoke($user, "filename", [ $username ]);
      $insertValues         .= "(?, FROM_UNIXTIME(?), ?, '', ?, ?, ?, '{$i18n->defaultLanguageCode}'),";
      $insertTypes          .= "ssssss";
      $insertParams[]        = $avatarName;
      if ($i % 6 !== 0) {
        $insertParams[]                    = time();
        $insertParams[]                    = "jpg";
        $usernamesWithAvatars[$avatarName] = $username;
      }
      else {
        $insertParams[] = $insertParams[] = null;
      }
      $insertParams[] = "{$username}@movlib.org";
      $insertParams[] = $username;
      $insertParams[] = $this->invoke($user, "passwordHash", [ User::getRandomPassword() ]);
      $this->progress->advance();
    }
    $insertValues = rtrim($insertValues, ",");
    $this->progress->finish();

    $this->write("Inserting users into database ...");
    $this->database->query("INSERT INTO `users` (`avatar_name`, `avatar_changed`, `avatar_extension`, `dyn_profile`, `email`, `name`, `password`, `system_language_code`) VALUES {$insertValues}", $insertTypes, $insertParams);

    if (($c = count($usernamesWithAvatars))) {
      $this->write("Generating avatar images (every 6th user has no avatar) ...");
      $dim = User::IMAGE_STYLE_SPAN_02;
      $tmp = sys_get_temp_dir() . "/movdev-command-create-users.jpg";
      $this->exec("convert -size {$dim}x{$dim} xc: +noise Random {$tmp}", "Could not create random avatar!");
      $this->setProperty($user, "imageExtension", "jpg");

      $this->progress->start($this->output, $c);
      foreach ($usernamesWithAvatars as $avatarName => $username) {
        $stmt = $this->database->query("SELECT `user_id` FROM `users` WHERE `name` = ?", "s", [ $username ]);
        $stmt->bind_result($user->id);
        $stmt->fetch();
        $this->setProperty($user, "imageName", $avatarName);
        $this->invoke($user, "convert", [ $tmp, User::IMAGE_STYLE_SPAN_02 ]);
        $this->invoke($user, "convert", [ $tmp, User::IMAGE_STYLE_SPAN_01 ]);
        $this->progress->advance();
      }
      unlink($tmp);
      $this->progress->finish();
    }

    return $this->write("Successfully created {$amount} random users!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $argv;
    $this->setIO($input, $output);
    $options = $this->input->getOptions();
    if ($options[self::OPTION_ALL]) {
      try {
        $this->write("Importing schema ...");
        if (($schema = file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/db/movlib.sql")) === false) {
          $this->exitOnError("Could not read schema!");
        }
        $this->database->queries($schema);
        $this->write("Importing Intl ICU translations for countries and languages ...");
        $this->importIntlTranslations();
        $this->write("Importing time zone translations ...");
        $this->importTimeZones();
        $this->importSeeds();
        $this->write("Importing uploads ...");
        $this->importSeedUploads();
        $this->write("All Successfull!", self::MESSAGE_TYPE_INFO);
      }
      catch (DatabaseException $e) {
        $this->exitOnError([ "Couldn't import schema!", "MariaDB error: {$this->database->getMySQLi()->error} ({$this->database->getMySQLi()->errno})" ]);
      }
    }
    elseif ($options[self::OPTION_CREATE_USERS]) {
      $this->createUsers($options[self::OPTION_CREATE_USERS]);
    }
    elseif (array_search("--" . self::OPTION_GIT, $argv) || array_search("-" . self::OPTION_GIT_SHORTCUT, $argv)) {
      empty($options[self::OPTION_GIT]) ? $this->git() : $this->git($options[self::OPTION_GIT]);
    }
    elseif (array_search("--" . self::OPTION_SEED, $argv) || array_search("-" . self::OPTION_SEED_SHORTCUT, $argv)) {
      empty($options[self::OPTION_SEED]) ? $this->importSeedsInteractive() : $this->importSeed($options[self::OPTION_SEED]);
      $this->write("Importing uploads ...");
      $this->importSeedUploads();
      $this->write("All Successfull!", self::MESSAGE_TYPE_INFO);
    }
    else {
      $this->exitOnError("Not implemented yet!");
    }
  }

  /**
   * Create git repositories.
   *
   * @param string $type [optional]
   *   If supplied, only repositories of this type (e.g. movie) are created, otherwise all repositories are created.
   * @return this
   */
  protected function git($type = null) {
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
  protected function importIntlTranslations() {
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
   * Import a single seed.
   *
   * @param string $name
   *   The name of the seed to import.
   * @param boolean $truncate [optional]
   *   Whetever to truncate the table or not.
   * @return this
   */
  protected function importSeed($name, $truncate = true) {
    if (!isset($this->seedScripts[$name])) {
      $choices = implode(", ", array_keys($this->seedScripts));
      return $this->write("Invalid seed name '{$name}'. Possible choices are: {$choices}", self::MESSAGE_TYPE_ERROR);
    }
    $this->database->transactionStart()->query("SET foreign_key_checks = 0");
    $this->write("Importing seed '{$name}' ...");
    if ($truncate === true) {
      try {
        $this->write("Truncating table '{$name}' ...");
        $this->database->query("TRUNCATE TABLE `{$name}`");
      }
      catch (DatabaseException $e) {
        $this->exitOnError("Couldn't truncate table '{$name}'!", $e->getTraceAsString());
      }
    }
    if (($queries = file_get_contents($this->seedScripts[$name])) === false) {
      $this->exitOnError("Couldn't read '{$this->seedScripts[$name]}'!");
    }
    try {
      $this->database->queries($queries)->transactionCommit();
      $this->write("Seed '{$name}' import successful!");
    }
    catch (DatabaseException $e) {
      $this->exitOnError("Seed '{$name}' import failed!", $e->getTraceAsString());
    }
    return $this;
  }

  /**
   * Import all seed uploads.
   *
   * @return this
   */
  protected function importSeedUploads() {
    $this->exec("sudo movcli fixperm {$_SERVER["DOCUMENT_ROOT"]}/uploads", "Could not fix permissions on uploads folder!");
    $this->exec("rm -rf {$_SERVER["DOCUMENT_ROOT"]}/uploads/*", "Could not delete existing files in uploads folder!");
    $this->exec("cp -R {$_SERVER["DOCUMENT_ROOT"]}/db/seeds/uploads/* {$_SERVER["DOCUMENT_ROOT"]}/uploads/", "Could not copy all seed uploads to the uploads folder!");
    $this->exec("sudo movcli fixperm {$_SERVER["DOCUMENT_ROOT"]}/uploads", "Could not fix permissions on uploads folder!");
    return $this;
  }

  /**
   * Import seeds.
   *
   * @return this
   */
  protected function importSeeds() {
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
   * Run seed imports with an interactive dialogue.
   *
   * @return this
   */
  protected function importSeedsInteractive() {
    do {
      $this->importSeed($this->askWithChoices("Please select a seed to import.", null, array_keys($this->seedScripts)));
    }
    while ($this->askConfirmation("Do you want to import another seed?"));
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
  protected function importTimeZones() {
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
   * Rollback all changes.
   *
   * @return this
   */
  protected function rollback() {
    if ($this->database->transactionActive === true) {
      $this->database->transactionRollback();
    }
    return $this;
  }

}
