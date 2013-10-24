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
namespace MovLib\Tool\Console\Command\Development;

use \InvalidArgumentException;
use \Locale;
use \MovLib\Data\SystemLanguages;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\FileSystemException;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * The seed import command can be used during development to import the complete seed data or only specific stuff.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedImport extends \MovLib\Tool\Console\Command\Development\AbstractDevelopmentCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Command option to create history repositories.
   *
   * @var string
   */
  const OPTION_HISTORY = "history";

  /**
   * Command option to import database data.
   *
   * @var string
   */
  const OPTION_DATABASE = "database";

  /**
   * Command option to import upload data.
   *
   * @var string
   */
  const OPTION_UPLOAD = "upload";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing the database insert script name (without extension) as key and the absolute path to
   * the script as value.
   *
   * @see SeedImport::__construct()
   * @var array
   */
  protected $databaseScripts = [];

  /**
   * Absolute path to the seed path (document root is added in constructor).
   *
   * @see SeedImport::__construct()
   * @var string
   */
  protected $seedPath = "/conf/seed";

  /**
   * Associative array containing the upload directory names as key and the absolute path to the directory as value.
   *
   * @see SeedImport::__construct()
   * @var array
   */
  protected $uploadDirectories = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new seed import command.
   *
   * @global \MovLib\Tool\Configuration $config
   */
  public function __construct() {
    global $config;
    parent::__construct("seed-import");
    $this->seedPath = "{$config->documentRoot}{$this->seedPath}";
    foreach (glob("{$this->seedPath}/" . self::OPTION_DATABASE . "/*.sql") as $seedScript) {
      $this->databaseScripts[basename($seedScript, ".sql")] = $seedScript;
    }
    foreach (glob("{$this->seedPath}/" . self::OPTION_UPLOAD . "/*", GLOB_ONLYDIR) as $uploadDirectory) {
      $this->uploadDirectories[basename($uploadDirectory)] = $uploadDirectory;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Import the complete seed data or only specific data via options.");
    // History needs a custom shortcut because '-h' is already defined by Symfony.
    $this->addOption(self::OPTION_HISTORY, "hi", InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Import specific history data.");
    $this->addInputOption(self::OPTION_DATABASE, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Import specific database data.");
    $this->addInputOption(self::OPTION_UPLOAD, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Import specific upload data.");
  }

  /**
   * Import one or more database seed data.
   *
   * @global \MovLib\Tool\Database $db
   * @param array $scriptNames [optional]
   *   Numeric array containing the database seed data names that should be imported, if left empty (<code>NULL</code>)
   *   all seed data will be imported.
   * @return this
   * @throws \MovLib\Exception\ErrorException
   * @throws \MovLib\Exception\FileSystemException
   */
  public function databaseImport(array $scriptNames = null) {
    global $db;
    $queries = $scripts = null;
    if (empty($scriptNames)) {
      $truncate = false;
      $scripts = $this->databaseScripts;
    }
    else {
      $truncate = true;
      $c = count($scriptNames);
      for ($i = 0; $i < $c; ++$i) {
        if (isset($this->databaseScripts[$scriptNames[$i]])) {
          $scripts[$scriptNames[$i]] = $this->databaseScripts[$scriptNames[$i]];
        }
        else {
          throw new InvalidArgumentException("No script with name '{$scriptNames[$i]}' found!");
        }
      }
    }
    if (!empty($scripts)) {
      foreach ($scripts as $table => $script) {
        $this->write("Importing database data for table '{$table}' ...");
        if ($truncate === true) {
          $queries .= "TRUNCATE TABLE `{$table}`;";
        }
        if (($queries .= file_get_contents($script)) === false) {
          throw new FileSystemException("Couldn't read '{$script}'!");
        }
      }
    }
    if (!empty($queries)) {
      try {
        $db->transactionStart();
        $db->queries("SET foreign_key_checks = 0; {$queries} SET foreign_key_checks = 1;");
        $db->transactionCommit();
      }
      catch (DatabaseException $e) {
        $db->transactionRollback();
        throw $e;
      }
    }
    return $this->write("Successfully imported database data for '" . implode("', '", array_keys($scripts)) . "'.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $all     = true;
    foreach ([ self::OPTION_DATABASE, self::OPTION_HISTORY, self::OPTION_UPLOAD ] as $option) {
      if ($options[$option]) {
        $this->{"{$option}Import"}($options[$option]);
        $all = false;
      }
    }
    if ($all === true) {
      $this->seedImport();
    }
  }

  /**
   * Import the Intl ICU translations for countries and languages.
   *
   * @global \MovLib\Tool\Configuration $config
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \MovLib\Exception\ErrorException
   */
  public function importIntlICUCountriesAndLanguages() {
    global $db, $i18n;
    $this->write("Importing Intl ICU translations for countries and languages ...");

    // Contains all country and basic language codes that our application shall know about.
    $codes = [
      "countries" => [ "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "ZA", "ZM", "ZW" ],
      "languages" => [ "ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy", "as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi", "bs", "br", "bg", "my", "ca", "ch", "ce", "ny", "zh", "cv", "kw", "co", "cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee", "fo", "fj", "fi", "fr", "ff", "gl", "ka", "de", "el", "gn", "gu", "ht", "ha", "he", "hz", "hi", "ho", "hu", "ia", "id", "ie", "ga", "ig", "ik", "io", "is", "it", "iu", "ja", "jv", "kl", "kn", "kr", "ks", "kk", "km", "ki", "rw", "ky", "kv", "kg", "ko", "ku", "kj", "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv", "gv", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mh", "mn", "na", "nv", "nb", "nd", "ne", "ng", "nn", "no", "ii", "nr", "oc", "oj", "cu", "om", "or", "os", "pa", "pi", "fa", "pl", "ps", "pt", "qu", "rm", "rn", "ro", "ru", "sa", "sc", "sd", "se", "sm", "sg", "sr", "gd", "sn", "si", "sk", "sl", "so", "st", "es", "su", "sw", "ss", "sv", "ta", "te", "tg", "th", "ti", "bo", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy", "wo", "fy", "xh", "yi", "yo", "za", "zu" ]
    ];

    $systemLanguages = new SystemLanguages();
    unset($systemLanguages[$i18n->defaultLanguageCode]);
    foreach ($codes as $table => $data) {
      $query = "INSERT INTO `{$table}` (`iso_alpha-2`, `name`, `dyn_translations`) VALUES ";
      $c     = count($data);
      for ($i = 0; $i < $c; ++$i) {
        $dynTranslations = null;
        /* @var $systemLanguage \MovLib\Data\SystemLanguage */
        foreach ($systemLanguages as $locale => $systemLanguage) {
          $dynTranslations .= "'{$systemLanguage->languageCode}', '{$this->intlTranslate($table, $data[$i], $locale)}',";
        }
        if (empty($dynTranslations)) {
          $dynTranslations = "''";
        }
        else {
          $dynTranslations = "COLUMN_CREATE(" . rtrim($dynTranslations, ",") . ")";
        }
        $query .= "('{$data[$i]}', '{$this->intlTranslate($table, $data[$i], $i18n->defaultLocale)}', {$dynTranslations}),";
      }
      if (!empty($query)) {
        $query = rtrim($query, ",") . " ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)";
        try {
          $db->transactionStart();
          $db->query($query);
          $db->transactionCommit();
        }
        catch (DatabaseException $e) {
          $db->transactionRollback();
          throw $e;
        }
      }
    }
    return $this->write("Successfully imported Intl ICU translations for countries and languages!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Import all time zones and their translations.
   *
   * @link https://en.wikipedia.org/wiki/List_of_tz_database_time_zones
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \MovLib\Exception\ErrorException
   */
  public function importTimeZones() {
    global $db, $i18n;
    $this->write("Importing time zone translations ...");
    $systemLanguages = new SystemLanguages();
    $timeZoneIds = timezone_identifiers_list();
    $c = count($timeZoneIds);
    $translations = [];
    /* @var $systemLanguage \MovLib\Data\SystemLanguage */
    foreach ($systemLanguages as $locale => $systemLanguage) {
      if ($locale == $i18n->defaultLocale) {
        // @todo These translations aren't quite correct! Create translation file!
        for ($i = 0; $i < $c; ++$i) {
          $translations[$locale][$timeZoneIds[$i]] = strtr($timeZoneIds[$i], "_", " ");
        }
        unset($systemLanguages[$locale]);
      }
      else {
        $fh = fopen("{$this->seedPath}/" . self::OPTION_DATABASE . "/time_zones_{$systemLanguage->languageCode}.txt", "r");
        while (($line = fgets($fh)) !== false) {
          list($timeZoneId, $translation) = explode(";", $line);
          $translations[$locale][$timeZoneId] = $translation;
        }
      }
    }
    if (!empty($translations)) {
      $query = "INSERT INTO `messages` (`message`, `dyn_translations`) VALUES ";
      for ($i = 0; $i < $c; ++$i) {
        $dynTranslations = null;
        /* @var $systemLanguage \MovLib\Data\SystemLanguage */
        foreach ($systemLanguages as $locale => $systemLanguage) {
          if (empty($translations[$locale][$timeZoneIds[$i]])) {
            continue;
          }
          $dynTranslations .= "'{$systemLanguage->languageCode}', '{$db->escapeString($translations[$locale][$timeZoneIds[$i]])}',";
        }
        if (empty($dynTranslations)) {
          $dynTranslations = "''";
        }
        else {
          $dynTranslations = "COLUMN_CREATE(" . rtrim($dynTranslations, ",") . ")";
        }
        $query .= "('{$db->escapeString($translations[$i18n->defaultLocale][$timeZoneIds[$i]])}', {$dynTranslations}),";
      }
      if (!empty($query)) {
        try {
          $db->transactionStart();
          $db->query(rtrim($query, ","));
          $db->transactionCommit();
        }
        catch (DatabaseException $e) {
          $db->transactionRollback();
          throw $e;
        }
      }
    }
    return $this->write("Successfully imported time zone translations!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Translate the given <var>$data</var> with Intl ICU.
   *
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param string $type
   *   The type of data, one of <code>"countries"</code>, <code>"languages"</code>.
   * @param string $data
   *   The data to translate.
   * @param string $locale
   *   The target locale.
   * @return string
   *   The translated <var>$data</var>.
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \InvalidArgumentException
   */
  public function intlTranslate($type, $data, $locale) {
    global $db, $i18n;
    switch ($type) {
      case "countries":
        return $db->escapeString(Locale::getDisplayRegion("{$i18n->defaultLanguageCode}-{$data}", $locale));

      case "languages":
        return $db->escapeString(Locale::getDisplayLanguage($data, $locale));
    }
    throw new InvalidArgumentException;
  }

  /**
   * Import all seed data.
   *
   * @global \MovLib\Tool\Configuration $config
   * @global \MovLib\Tool\Database $db
   * @return this
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \MovLib\Exception\ErrorException
   * @throws \MovLib\Exception\FileSystemException
   */
  public function seedImport() {
    global $config, $db;

    // Array containing the names of all tasks that should be executed.
    $tasks = [
      "importIntlICUCountriesAndLanguages",
      "importTimeZones",
      "databaseImport",
      "uploadImport",
    ];

    // The two additional operations are for the schema import itself.
    $this->write("Importing all seed data ...")->progressStart(count($tasks) + 2);
    if (($schema = file_get_contents("{$config->documentRoot}/conf/mariadb/movlib.sql")) === false) {
      throw new FileSystemException("Couldn't read schema!");
    }
    $this->progressAdvance();
    $db->queries($schema);
    $this->progressAdvance();
    foreach ($tasks as $task) {
      $this->{$task}()->progressAdvance();
    }
    return $this->progressFinish()->write("Successfully imported seed data!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Import one ore more upload seed data.
   *
   * @global \MovLib\Tool\Configuration $config
   * @param array $directoryNames [optional]
   *   Numeric array containing the upload directory data names that should be imported, if left empty (<code>NULL</code>)
   *   all seed data will be imported.
   * @return this
   * @throws \MovLib\Exception\ErrorException
   * @throws \MovLib\Exception\FileSystemException
   */
  public function uploadImport(array $directoryNames = null) {
    global $config;
    $directories           = null;
    $seedUploadDirectory   = "{$this->seedPath}/" . self::OPTION_UPLOAD;
    $publicUploadDirectory = "{$config->documentRoot}/public/" . self::OPTION_UPLOAD;
    if (empty($directoryNames)) {
      $directories = $this->uploadDirectories;
      $this->exec("rm -rf {$publicUploadDirectory}/*");
      $this->exec("cp -R {$seedUploadDirectory}/* {$publicUploadDirectory}");
    }
    else {
      $c = count($directoryNames);
      for ($i = 0; $i < $c; ++$i) {
        if (isset($this->uploadDirectories[$directoryNames[$i]])) {
          $directories[$directoryNames[$i]] = $this->uploadDirectories[$directoryNames[$i]];
          $this->exec("rm -rf {$publicUploadDirectory}/{$directoryNames[$i]}/*");
          $this->exec("cp -R {$seedUploadDirectory}/* {$publicUploadDirectory}/$directoryNames[$i]");
        }
        else {
          throw new InvalidArgumentException("No directory with name '{$directoryNames[$i]}' found!");
        }
      }
    }
    return $this->write("Successfully imported upload data for '" . implode("', '", array_keys($directories)) . "'.", self::MESSAGE_TYPE_INFO);
  }

}
