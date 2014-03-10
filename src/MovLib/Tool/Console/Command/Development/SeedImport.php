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

use \Elasticsearch\Client as ElasticClient;
use \MovLib\Exception\DatabaseException;
use \MovLib\Tool\Console\Command\Production\FixPermissions;
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
  use \MovLib\Data\TraitShell;


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
   * Command option to import ElasticSearch index and types.
   *
   * @var string
   */
  const OPTION_ELASTIC = "elastic";

  /**
   * Command option to import Intl ICU data.
   *
   * @var string
   */
  const OPTION_ICU = "icu";

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
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("seed-import");
    $this->setDescription("Import the complete seed data or only specific data via options.");
    // History needs a custom shortcut because '-h' is already defined by Symfony.
    $this->addOption(self::OPTION_HISTORY, "hi", InputOption::VALUE_NONE, "Create all history repositories.");
    $this->addInputOption(self::OPTION_DATABASE, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, "Import specific database data.");
    $this->addInputOption(self::OPTION_ELASTIC, InputOption::VALUE_NONE, "Create ElasticSearch index and types.");
    $this->addInputOption(self::OPTION_ICU, InputOption::VALUE_NONE, "Import ICU data.");
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
   * @throws \RuntimeException
   */
  public function databaseImport(array $scriptNames = null) {
    global $db;
    $queries = $scripts = null;

    // If no scripts where specified for import Import all available scripts.
    if (empty($scriptNames)) {
      $truncate = false;
      $scripts  = $this->databaseScripts;
    }
    // Otherwise go through all specified scripts and check if they are available.
    else {
      // Be sure to truncate if we're importing some specific scripts.
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

    // Build the queries for all scripts.
    if (!empty($scripts)) {
      foreach ($scripts as $table => $script) {
        $this->write("Importing database data for table '{$table}' ...");

        // We're only truncating if we're importing specific scripts.
        if ($truncate === true) {
          $queries .= "TRUNCATE TABLE `{$table}`;";
        }

        // Concatenate all scripts to one singel big query.
        if (($queries .= file_get_contents($script)) === false) {
          throw new \RuntimeException("Couldn't read '{$script}'!");
        }
      }
    }

    // Only continue if we have at least a single query to execute.
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
   * Create elastic search index and types.
   *
   * @return this
   */
  public function elasticImport() {
    $this->write("Deleting old ElasticSearch indices and creating new ...");

    $elasticClient = new ElasticClient();

    // Delete all indices and create movlib index.
    $elasticClient->indices()->delete();
    $elasticClient->indices()->create([ "index" => "movlib" ]);

    // Create movie type.
    $elasticClient->create([ "index" => "movlib", "type" => "movie", "body" => [
      "titles" => [ "type" => "string", "analyzer" => "simple" ],
      "year"   => [ "type" => "short", "index" => "not_analyzed" ],
    ]]);

    // Create person type.
    $elasticClient->create([ "index" => "movlib", "type" => "person", "body" => [
      "names" => [ "type" => "string", "analyzer" => "simple" ],
    ]]);

    return $this->write("Done, ElasticSearch is ready for indexing!", self::MESSAGE_TYPE_INFO);
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $kernel;
    $options = parent::execute($input, $output);

    $this->seedPath = "{$kernel->documentRoot}{$this->seedPath}";
    foreach (glob("{$this->seedPath}/" . self::OPTION_DATABASE . "/*.sql") as $seedScript) {
      $this->databaseScripts[basename($seedScript, ".sql")] = $seedScript;
    }

    // Only go through the public directory, the user images don't have private files.
    foreach (glob("{$this->seedPath}/" . self::OPTION_UPLOAD . "/public/*", GLOB_ONLYDIR) as $uploadDirectory) {
      $this->uploadDirectories[basename($uploadDirectory)] = $uploadDirectory;
    }

    $all     = true;
    foreach ([ self::OPTION_DATABASE, self::OPTION_ELASTIC, self::OPTION_HISTORY, self::OPTION_ICU, self::OPTION_UPLOAD ] as $option) {
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
        $this->shExecute("rm -rf '{$path}'");
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
   * Import all ICU translations.
   *
   * @return this
   */
  public function icuImport() {
    $this->write("Importing ICU translations ...");

    // Prepare ICU environment variables.
    $this->shExecute("icu-config --version", $version);
    $version = trim(strtr($version[0], ".", "-"));
    $source  = "/usr/local/src/icu-{$version}/source/data";

    // Generate the various ICU translations.
    $this->icuImportCountries($source);
    $this->icuImportCurrency($source);
    $this->icuImportLanguages($source);

    return $this->write("Imported all ICU translations.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Generate, load and delete resource bundle.
   *
   * @param string $source
   *   Absolute path to the resource source files.
   * @param string $locale
   *   The locale for which this resource bundle should be generated.
   * @param string $languageCode
   *   The language code for which this resource bundle should be generated.
   * @return \ResourceBundle
   *   The loaded resource bundle.
   * @throws \RuntimeException
   */
  protected function icuGetResourceBundle($source, $locale, $languageCode) {
    // Create absolute paths to source file and resource bundle.
    $src = is_file("{$source}/{$locale}.txt") ? "{$source}/{$locale}.txt" : "{$source}/{$languageCode}.txt";

    // Generate the resource bundle for this locale.
    $destination = sys_get_temp_dir();
    $this->shExecute("genrb -R -e UTF-8 -d {$destination} {$src}");

    // Load the generated resource bundle and delete the resource bundle files.
    $rb = new \ResourceBundle($locale, $destination, true);
    $this->shExecute("rm {$destination}/*.res");
    return $rb;
  }

  /**
   * Debug method to export the contents of a ICU resource bundle.
   *
   * @param \ResourceBundle $rb
   *   The resource bundle to export.
   * @return string
   *   The exported resource bundle as string (best written to file because it's usually very long).
   */
  protected function icuExportResourceBundle(\ResourceBundle $rb) {
    ob_start();
    foreach ($rb as $k => $v) {
      if (is_object($v)) {
        var_dump($k);
        $this->icuExportResourceBundle($v);
      }
      else {
        var_dump($k, $v);
      }
    }
    return ob_get_clean();
  }

  /**
   * Import ICU country translations.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $source
   *   Absolute path to ICU source resources.
   * @return this
   */
  protected function icuImportCountries($source) {
    global $kernel;
    $source      .= "/region";
    $destination  = "{$kernel->pathTranslations}/country";
    $codes        = [
      "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ",
      "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ",
      "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ",
      "DE", "DJ", "DK", "DM", "DO", "DZ",
      "EC", "EE", "EG", "EH", "ER", "ES", "ET",
      "FI", "FJ", "FK", "FM", "FO", "FR",
      "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY",
      "HK", "HM", "HN", "HR", "HT", "HU",
      "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT",
      "JE", "JM", "JO", "JP",
      "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ",
      "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY",
      "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ",
      "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ",
      "OM",
      "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY",
      "QA",
      "RE", "RO", "RS", "RU", "RW",
      "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ",
      "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ",
      "UA", "UG", "UM", "US", "UY", "UZ",
      "VA", "VC", "VE", "VG", "VI", "VN", "VU",
      "WF", "WS",
      "YE", "YT",
      "ZA", "ZM", "ZW",
    ];
    $this->icuWriteTranslations($destination, function (&$translation, $locale) use ($codes) {
      $sortArray = [];
      foreach ($codes as $code) {
        $sortArray[$code] = \Locale::getDisplayRegion("xx-{$code}", $locale);
      }
      (new \Collator($locale))->asort($sortArray);
      foreach ($sortArray as $code => $name) {
        $translation .= "  \"{$code}\" => \"{$name}\",\n";
      }
    }, "ICU country translations.");
    return $this;
  }

  /**
   * Import ICU currency translations.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $source
   *   Absolute path to ICU source resources.
   * @return this
   */
  protected function icuImportCurrency($source) {
    global $kernel;
    $source      .= "/curr";
    $destination  = "{$kernel->pathTranslations}/currency";
    $codes        = [
      "ADP", "AED", "AFN", "ALL", "AMD", "ANG", "AOA", "ARS", "ATS", "AUD", "AWG", "AZN",
      "BAM", "BBD", "BDT", "BEF", "BGN", "BHD", "BIF", "BMD", "BND", "BOB", "BRL", "BSD", "BTN", "BWP", "BYR", "BZD",
      "CAD", "CDF", "CHF", "CLP", "CNY", "COP", "CRC", "CUC", "CUP", "CVE", "CYP", "CZK",
      "DEM", "DJF", "DKK", "DOP", "DZD",
      "EEK", "EGP", "ERN", "ESP", "ETB", "EUR",
      "FIM", "FJD", "FKP", "FRF",
      "GBP", "GEL", "GHS", "GIP", "GMD", "GNF", "GRD", "GTQ", "GYD",
      "HKD", "HNL", "HRK", "HTG", "HUF",
      "IDR", "IEP", "ILS", "INR", "IQD", "IRR", "ISK", "ITL",
      "JMD", "JOD", "JPY",
      "KES", "KGS", "KHR", "KMF", "KPW", "KRW", "KWD", "KYD", "KZT",
      "LAK", "LBP", "LKR", "LRD", "LSL", "LTL", "LUF", "LVL", "LYD",
      "MAD", "MDL", "MGA", "MKD", "MMK", "MNT", "MOP", "MRO", "MTL", "MUR", "MVR", "MWK", "MXN", "MXV", "MYR", "MZN",
      "NAD", "NGN", "NIO", "NLG", "NOK", "NPR", "NZD",
      "OMR",
      "PAB", "PEN", "PGK", "PHP", "PKR", "PLN", "PTE", "PYG",
      "QAR",
      "RON", "RSD", "RUB", "RWF",
      "SAR", "SBD", "SCR", "SDG", "SEK", "SGD", "SHP", "SIT", "SKK", "SLL", "SOS", "SRD", "SSP", "STD", "SYP", "SZL",
      "THB", "TJS", "TMT", "TND", "TOP", "TRY", "TTD", "TWD", "TZS",
      "UAH", "UGX", "USD", "UYU", "UZS",
      "VEF", "VND", "VUV",
      "WST",
      "XAF", "XCD", "XOF", "XPF",
      "YER",
      "ZAR", "ZMW",
    ];
    $this->icuWriteTranslations($destination, function (&$translation, $locale, $languageCode) use ($codes, $source) {
      $rb = $this->icuGetResourceBundle($source, $locale, $languageCode);
      foreach ($codes as $code) {
        $name         = $rb["Currencies"][$code][1];
        $symbol       = $rb["Currencies"][$code][0];
        $translation .= "  \"{$code}\" => [ \"name\" => \"{$name}\", \"symbol\" => \"{$symbol}\" ],\n";
      }
    }, "ICU currency translations.");
    return $this;
  }

  /**
   * Import ICU language translations.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $source
   *   Absolute path to ICU source resources.
   * @return this
   */
  protected function icuImportLanguages($source) {
    global $kernel;
    $source      .= "/lang";
    $destination  = "{$kernel->pathTranslations}/language";
    $codes        = [
      "aa", "ab", "ae", "af", "ak", "am", "an", "ar", "as", "av", "ay", "az",
      "ba", "be", "bg", "bh", "bi", "bm", "bn", "bo", "br", "bs",
      "ca", "ce", "ch", "co", "cr", "cs", "cu", "cv", "cy",
      "da", "de", "dv", "dz",
      "ee", "el", "en", "eo", "es", "et", "eu",
      "fa", "ff", "fi", "fj", "fo", "fr", "fy",
      "ga", "gd", "gl", "gn", "gu", "gv",
      "ha", "he", "hi", "ho", "hr", "ht", "hu", "hy", "hz",
      "ia", "id", "ie", "ig", "ii", "ik", "io", "is", "it", "iu",
      "ja", "jv",
      "ka", "kg", "ki", "kj", "kk", "kl", "km", "kn", "ko", "kr", "ks", "ku", "kv", "kw", "ky",
      "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv",
      "mg", "mh", "mi", "mk", "ml", "mn", "mr", "ms", "mt", "my",
      "na", "nb", "nd", "ne", "ng", "nl", "nn", "no", "nr", "nv", "ny",
      "oc", "oj", "om", "or", "os",
      "pa", "pi", "pl", "ps", "pt",
      "qu",
      "rm", "rn", "ro", "ru", "rw",
      "sa", "sc", "sd", "se", "sg", "si", "sk", "sl", "sm", "sn", "so", "sq", "sr", "ss", "st", "su", "sv", "sw",
      "ta", "te", "tg", "th", "ti", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty",
      "ug", "uk", "ur", "uz",
      "ve", "vi", "vo",
      "wa", "wo",
      "xh",
      "yi", "yo",
      "za", "zh", "zu",
    ];
    $this->icuWriteTranslations($destination, function (&$translation, $locale) use ($codes) {
      global $i18n;
      $sortArray = [];
      foreach ($codes as $code) {
        $sortArray[$code] = \Locale::getDisplayLanguage($code, $locale);
      }
      (new \Collator($locale))->asort($sortArray);
      foreach ($sortArray as $code => $name) {
        $native       = \Locale::getDisplayLanguage($code, $code);
        $translation .= "  \"{$code}\" => [ \"name\" => \"{$name}\", \"native\" => \"{$native}\" ],\n";
      }
      // Add special code for no linguistic content (from ISO 639-2), always keep this entry at the end of the list.
      $translation .= "  \"xx\" => [ \"name\" => \"{$i18n->t("No Language")}\", \"native\" => \"-\" ],\n";
    }, "ICU language translations.");
    return $this;
  }

  /**
   * ICU helper method to iterate over all available system languages and write the translated file.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @staticvar string $scaffold
   *   Used for caching of the scaffold file.
   * @param string $destination
   *   Absolute path to the directory where the translated files should be stored.
   * @param callable $callback
   *   The callback method that is called on each iteration.
   * @param string $comment
   *   The class comment for the docBlock.
   * @return this
   */
  protected function icuWriteTranslations($destination, callable $callback, $comment) {
    global $kernel;
    static $scaffold = null;
    if (!$scaffold) {
      $scaffold = file_get_contents("{$kernel->pathTranslations}/scaffold.php");
    }
    foreach ($kernel->systemLanguages as $languageCode => $locale) {
      $translation = "return [\n";
      $callback($translation, $locale, $languageCode);
      $translation .= "];\n// @codeCoverageIgnoreEnd\n";
      file_put_contents("{$destination}/{$locale}.php", str_replace("{classComment}", $comment, $scaffold) . $translation);
    }
    return $this;
  }

  /**
   * Import all seed data.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return this
   * @throws \MovLib\Exception\DatabaseExeption
   * @throws \ErrorException
   * @throws \RuntimeException
   */
  public function seedImport() {
    global $kernel;

    // Array containing the names of all tasks that should be executed.
    $tasks = [
      "databaseImport",
      "elasticImport",
      "icuImport",
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
    $this->shExecute("mysql < {$kernel->documentRoot}/conf/mariadb/movlib.sql", $output);
    $this->progressAdvance();
    foreach ($tasks as $task) {
      $this->$task()->progressAdvance();
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
   * @throws \RuntimeException
   */
  public function uploadImport(array $directoryNames = null) {
    global $kernel;
    foreach ([ "private", "public" ] as $visibility) {
      $directories     = null;
      $seedDirectory   = "{$this->seedPath}/" . self::OPTION_UPLOAD . "/{$visibility}";
      $uploadDirectory = "{$kernel->documentRoot}/{$visibility}/" . self::OPTION_UPLOAD;
      if (empty($directoryNames)) {
        $directories = $this->uploadDirectories;
        $this->uploadMoveImages($seedDirectory, $uploadDirectory);
      }
      else {
        $c = count($directoryNames);
        for ($i = 0; $i < $c; ++$i) {
          if (isset($this->uploadDirectories[$directoryNames[$i]])) {
            $directories[$directoryNames[$i]] = $this->uploadDirectories[$directoryNames[$i]];
            $this->uploadMoveImages("{$seedDirectory}/{$directoryNames[$i]}", "{$uploadDirectory}/{$directoryNames[$i]}");
          }
          else {
            throw new \InvalidArgumentException("No directory with name '{$directoryNames[$i]}' found!");
          }
        }
      }
    }

    // Fix permissions if executed as root.
    if ($this->checkPrivileges(false) === true) {
      (new FixPermissions())->fixPermissions();
    }

    return $this->write("Successfully imported upload data for '" . implode("', '", array_keys($directories)) . "'.", self::MESSAGE_TYPE_INFO);
  }

  /**
   * Helper method to delete and move seed upload files.
   *
   * @param string $from
   *   Absolute path to the source directory.
   * @param string $to
   *   Absolute path to the target directory.
   * @return this
   */
  protected function uploadMoveImages($from, $to) {
    if (is_dir($from)) {
      $this->shExecute("mkdir -p {$to}");
      $this->shExecute("rm -rf {$to}/*");
      $this->shExecute("cp -R {$from}/* {$to}");
    }
    return $this;
  }

}
