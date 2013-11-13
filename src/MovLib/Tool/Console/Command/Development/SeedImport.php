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

use \MovLib\Data\UnixShell as sh;
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
   * Associative array containing all history supported types.
   * 
   * Key have to be plural, value sigular.
   *
   * @var array
   */
  protected $historyTypes = [
    "movies" => "movie"
  ];

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
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function __construct() {
    global $kernel;
    parent::__construct("seed-import");
    $this->seedPath = "{$kernel->documentRoot}{$this->seedPath}";
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
    $this->addOption(self::OPTION_HISTORY, "hi", InputOption::VALUE_NONE, "Create all history repositories.");
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
   * @throws \ErrorException
   * @throws \MovLib\Exception\FileSystemException
   */
  public function databaseImport(array $scriptNames = null) {
    global $db;
    $queries = $scripts = null;

    if (empty($scriptNames)) {
      $truncate = false;
      $scripts  = $this->databaseScripts;
    }
    else {
      $truncate = true;
      foreach ($scriptNames as $scriptName) {
        try {
          $scripts[$scriptName] = $this->databaseScripts[$scriptName];
        }
        catch (\ErrorException $e) {
          throw new \InvalidArgumentException("No script with name '{$scriptName}' found!", null, $e);
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
        $db->queries($queries, false);
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
   * Create history repositories.
   *
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $type [optional]
   *   If supplied, only repositories of this type (e.g. <code>"movie"</code>) are created, otherwise all repositories
   *   are created.
   * @return this
   * @throws \InvalidArgumentException
   */
  public function historyImport() {
    global $db, $kernel;
    
    foreach ($this->historyTypes as $typePlural => $typeSingular) {
      // Remove complete history repository if it's present in the file system.
      $path = "{$kernel->documentRoot}/private/history/{$typeSingular}";
      if (is_dir($path)) {
        sh::execute("rm -rf '{$path}'");
      }

      // Creat new repository for each database entry we have.
      if (($result = $db->query("SELECT `{$typeSingular}_id` FROM `{$typePlural}`")->get_result())) {
        $class   = new \ReflectionClass("\\MovLib\\Data\\History\\" . ucfirst($typeSingular));
        $queries = null;
        while ($row = $result->fetch_row()) {
          $commitHash = $db->escapeString($class->newInstance($row[0])->createRepository());
          $queries   .= "UPDATE `{$typePlural}` SET `commit` = '{$commitHash}' WHERE `{$typeSingular}_id` = {$row[0]};";
        }
        if ($queries) {
          $db->queries($queries);
        }
      }
    }

    return $this;
  }

  /**
   * Import the Intl ICU translations for countries and languages.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \ErrorException
   */
  public function importIntlICUCountriesAndLanguages() {
    global $kernel, $db, $i18n;
    $this->write("Importing Intl ICU translations for countries and languages ...");

    // Contains all country and basic language codes that our application shall know about.
    $seed = [
      "countries" => [ "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "ZA", "ZM", "ZW" ],
      "languages" => [ "ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy", "as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi", "bs", "br", "bg", "my", "ca", "ch", "ce", "ny", "zh", "cv", "kw", "co", "cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee", "fo", "fj", "fi", "fr", "ff", "gl", "ka", "de", "el", "gn", "gu", "ht", "ha", "he", "hz", "hi", "ho", "hu", "ia", "id", "ie", "ga", "ig", "ik", "io", "is", "it", "iu", "ja", "jv", "kl", "kn", "kr", "ks", "kk", "km", "ki", "rw", "ky", "kv", "kg", "ko", "ku", "kj", "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv", "gv", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mh", "mn", "na", "nv", "nb", "nd", "ne", "ng", "nn", "no", "ii", "nr", "oc", "oj", "cu", "om", "or", "os", "pa", "pi", "fa", "pl", "ps", "pt", "qu", "rm", "rn", "ro", "ru", "sa", "sc", "sd", "se", "sm", "sg", "sr", "gd", "sn", "si", "sk", "sl", "so", "st", "es", "su", "sw", "ss", "sv", "ta", "te", "tg", "th", "ti", "bo", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy", "wo", "fy", "xh", "yi", "yo", "za", "zu" ]
    ];

    // Create local copy of all available system languages and remove the default language from the array, we only have
    // to translate into other languages than the default language.
    $systemLanguages = $kernel->systemLanguages;
    unset($systemLanguages[$i18n->defaultLanguageCode]);

    $queries = null;
    foreach ($seed as $table => $codes) {
      $queries .= "TRUNCATE TABLE `{$table}`; INSERT INTO `{$table}` (`code`, `name`, `dyn_translations`) VALUES ";
      foreach ($codes as $code) {
        $dynTranslations = null;
        foreach ($systemLanguages as $languageCode => $locale) {
          $dynTranslations .= "'{$languageCode}', '{$this->intlTranslate($table, $code, $locale)}',";
        }
        if (empty($dynTranslations)) {
          $dynTranslations = "''";
        }
        else {
          $dynTranslations = "COLUMN_CREATE(" . rtrim($dynTranslations, ",") . ")";
        }
        $queries .= "('{$code}', '{$this->intlTranslate($table, $code, $i18n->defaultLocale)}', {$dynTranslations}),";
      }
      $queries = rtrim($queries, ",") . ";";
    }

    if ($queries) {
      try {
        $db->transactionStart();
        $db->queries($queries, false);
        $db->transactionCommit();
      }
      catch (DatabaseException $e) {
        $db->transactionRollback();
        throw $e;
      }
    }

    return $this->write("Successfully imported Intl ICU translations for countries and languages!", self::MESSAGE_TYPE_INFO);
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
        return $db->escapeString(\Locale::getDisplayRegion("{$i18n->defaultLanguageCode}-{$data}", $locale));

      case "languages":
        return $db->escapeString(\Locale::getDisplayLanguage($data, $locale));
    }
    throw new \InvalidArgumentException;
  }

  /**
   * Import all seed data.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return this
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \ErrorException
   * @throws \MovLib\Exception\FileSystemException
   */
  public function seedImport() {
    global $kernel;

    // Array containing the names of all tasks that should be executed.
    $tasks = [
      "importIntlICUCountriesAndLanguages",
      "databaseImport",
      "uploadImport",
    ];

    // The two additional operations are for the schema import itself.
    $this->write("Importing all seed data ...")->progressStart(count($tasks) + 2);
    if (!file_exists("{$kernel->documentRoot}/conf/mariadb/movlib.sql")) {
      throw new FileSystemException("Couldn't read schema!");
    }
    $this->progressAdvance();
    // We have to execute this in the shell directly, because our database object always tries to connect to the default
    // database, which might not exist yet!
    sh::execute("mysql < {$kernel->documentRoot}/conf/mariadb/movlib.sql");
    $this->progressAdvance();
    foreach ($tasks as $task) {
      $this->{$task}()->progressAdvance();
    }
    return $this->progressFinish()->write("Successfully imported seed data!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Import one ore more upload seed data.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param array $directoryNames [optional]
   *   Numeric array containing the upload directory data names that should be imported, if left empty (<code>NULL</code>)
   *   all seed data will be imported.
   * @return this
   * @throws \ErrorException
   * @throws \MovLib\Exception\FileSystemException
   */
  public function uploadImport(array $directoryNames = null) {
    global $kernel;
    $directories           = null;
    $seedUploadDirectory   = "{$this->seedPath}/" . self::OPTION_UPLOAD;
    $publicUploadDirectory = "{$kernel->documentRoot}/public/" . self::OPTION_UPLOAD;
    if (empty($directoryNames)) {
      $directories = $this->uploadDirectories;
      sh::execute("rm -rf {$publicUploadDirectory}/*");
      sh::execute("cp -R {$seedUploadDirectory}/* {$publicUploadDirectory}");
    }
    else {
      $c = count($directoryNames);
      for ($i = 0; $i < $c; ++$i) {
        if (isset($this->uploadDirectories[$directoryNames[$i]])) {
          $directories[$directoryNames[$i]] = $this->uploadDirectories[$directoryNames[$i]];
          sh::execute("rm -rf {$publicUploadDirectory}/{$directoryNames[$i]}/*");
          sh::execute("cp -R {$seedUploadDirectory}/{$directoryNames[$i]}/* {$publicUploadDirectory}/{$directoryNames[$i]}");
        }
        else {
          throw new \InvalidArgumentException("No directory with name '{$directoryNames[$i]}' found!");
        }
      }
    }
    return $this->write("Successfully imported upload data for '" . implode("', '", array_keys($directories)) . "'.", self::MESSAGE_TYPE_INFO);
  }

}
