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
namespace MovLib\Model;

use \DateTimeZone;
use \IntlDateFormatter;
use \Locale;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\ErrorException;
use \MovLib\Model\BaseModel;
use \MovLib\Utility\CollatorExtended;
use \MovLib\Utility\DelayedLogger;
use \MovLib\Utility\DelayedMethodCalls;

/**
 * @todo Description of I18nModel
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m:2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class I18nModel extends BaseModel {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Return array sorted by ID.
   *
   * @see \MovLib\Model\I18nModel::getLanguages()
   * @see \MovLib\Model\I18nModel::getCountries()
   * @var string
   */
  const KEY_ID = "id";

  /**
   * Return array sorted by ISO alpha-2 code.
   *
   * @see \MovLib\Model\I18nModel::getLanguages()
   * @see \MovLib\Model\I18nModel::getCountries()
   * @var string
   */
  const KEY_CODE = "code";

  /**
   * Return array sorted by name.
   *
   * @see \MovLib\Model\I18nModel::getLanguages()
   * @see \MovLib\Model\I18nModel::getCountries()
   * @var string
   */
  const KEY_NAME = "name";


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The languages direction.
   *
   * @todo Implement right-to-left language detection.
   * @var string
   */
  public $direction = "ltr";

  /**
   * ISO 639-1 alpha-2 language code. Supported language codes are defined via nginx configuration.
   *
   * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
   * @var string
   */
  public $languageCode;

  /**
   * Locale for the current language code, used for Intl ICU related classes and functions (e.g. collators).
   *
   * @var string
   */
  public $locale;

  /**
   * Numeric array containing all supported ISO 639-1 alpha-2 language codes.
   *
   * The supported language codes are directly related to the nginx configuration. If this array contains a language
   * code the deployment script will generate a server (in Apache httpd terms <code>virtual host</code>) with routes for
   * this code.
   *
   * @var array
   */
  public static $supportedLanguageCodes = [ "en", "de" ];


  // ------------------------------------------------------------------------------------------------------------------- Private Properties


  /**
   * Extended collator for locale aware sorting.
   *
   * @see \MovLib\Model\I18nModel::getCollator()
   * @var \MovLib\Utility\CollatorExtended
   */
  private $collator;

  /**
   * Associative array that will be filled with all info on all available countries on demand.
   *
   * @see \MovLib\Model\I18nModel::getCountries()
   * @var array
   */
  private $countries;

  /**
   * Associative array that will be filled with all info on all available languages on demand.
   *
   * @see \MovLib\Model\I18nModel::getLanguages()
   * @var array
   */
  private $languages;

  /**
   * Associative array that will be filled with all info on supported system language on demand.
   *
   * @see \MovLib\Model\I18nModel::$supportedLanguageCodes
   * @see \MovLib\Model\I18nModel::getSystemLanguages()
   * @var array
   */
  private $systemLanguages;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new i18n model instance.
   *
   * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @param $locale
   *   [Optional] The desired locale, if no locale is passed the following procedure is executed:
   *   <ol>
   *     <li>Check if the server sent a language code.</li>
   *     <li>Check if the user is logged in and has a preferred language.</li>
   *     <li>Check if the user has provided an <code>HTTP_ACCEPT_LANGUAGE</code> header.</li>
   *     <li>Use default locale.</li>
   *   </ol>
   */
  public function __construct($locale = null) {
    global $user;
    if ($locale === null) {
      (isset($_SERVER["LANGUAGE_CODE"]) && $locale = $_SERVER["LANGUAGE_CODE"])
      || (isset($user) && $user->isLoggedIn === true && !empty($user->languageId) && $locale = $this->getLanguages()[$user->languageId]["code"])
      || (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($tmpLocale = Locale::acceptFromHttp($_SERVER["HTTP_ACCEPT_LANGUAGE"])) && in_array($tmpLocale[0] . $tmpLocale[1], self::$supportedLanguageCodes) && $locale = $tmpLocale)
      || ($locale = self::getDefaultLocale());
    }
    // The locale can be a two-letter code - only specifying the language - or five-letter - including the country.
    $this->locale = $locale;
    // The language code can only be a two-letter code.
    $this->languageCode = $locale[0] . $locale[1];
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Get the default ISO 639-1 alpha-2 language code extracted from <code>php.ini</code>.
   *
   * @static $defaultLanguageCode
   *   Used to cache default language code among all instances.
   * @return string
   *   The default ISO 639-1 alpha-2 language code.
   */
  public static function getDefaultLanguageCode() {
    static $defaultLanguageCode = null;
    if ($defaultLanguageCode === null) {
      $defaultLanguageCode = self::getDefaultLocale();
      $defaultLanguageCode = $defaultLanguageCode[0] . $defaultLanguageCode[1];
    }
    return $defaultLanguageCode;
  }

  /**
   * Get the default locale (e.g. <em>en-US</em>).
   *
   * @static $defaultLocale
   *   Used to cache default locale among all instances.
   * @return string
   *   The default locale.
   */
  public static function getDefaultLocale() {
    static $defaultLocale = null;
    if ($defaultLocale === null) {
      $defaultLocale = Locale::getDefault();
    }
    return $defaultLocale;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Formats the given message and translates it if not displaying default locale.
   *
   * @param type $context
   * @param type $pattern
   * @param type $args
   * @param type $options
   * @return type
   */
  public function formatMessage($context, $pattern, $args, $options) {
    $languageCode = isset($options["language_code"]) ? $options["language_code"] : $this->languageCode;
    if ($languageCode !== self::getDefaultLanguageCode()) {
      try {
        $result = $this->select(
          "SELECT
            COLUMN_GET(`dyn_translations`, '{$languageCode}' AS BINARY) AS `translation`
          FROM `{$context}s`
            WHERE `{$context}` = ?
            LIMIT 1",
          "s",
          [ $pattern ]
        )[0];
        if ($result["translation"] !== null) {
          $pattern = $result["translation"];
        }
      } catch (ErrorException $e) {
        unset($e);
        DelayedLogger::log("Could not find {$languageCode} translation for {$context}: '{$pattern}'", E_NOTICE);
        DelayedMethodCalls::stack($this, "insertPattern", [ $context, $pattern, $options ]);
      }
    }
    if ($args) {
      return msgfmt_format_message($this->languageCode, $pattern, $args);
    }
    return $pattern;
  }

  /**
   * Format the given timestamp for output.
   *
   * @link http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
   * @param int $timestamp
   *   The timestamp to format.
   * @param null|string $timezone
   *   One of the PHP timezone identifiers ({@link http://www.php.net/manual/en/timezones.php}). Defaults to system
   *   default timezone from <code>php.ini</code> configuration file.
   * @param int $datetype
   *   One of the <code>IntlDateFormatter</code> constants.
   * @param int $timetype
   *   One of the <code>IntlDateFormatter</code> constants.
   * @return string|boolean
   *   The formatted string or, if an error occurred, <code>FALSE</code>.
   * @throws \Exception
   *   If the supplied timezone is not recognised as a valid timezone.
   */
  public function formatDate($timestamp, $timezone = null, $datetype = IntlDateFormatter::LONG, $timetype = IntlDateFormatter::LONG) {
    $timezone = $timezone ?: ini_get("date.timezone");
    $fmt = new IntlDateFormatter($this->locale, $datetype, $timetype, new DateTimeZone($timezone));
    return $fmt->format($timestamp);
  }

  /**
   * Get the unique ID of the current language.
   *
   * @see \MovLib\Model\I18nModel::getLanguages()
   * @return int
   *   The unique ID of the current language.
   */
  public function getLanguageId() {
    return $this->getLanguages(self::KEY_CODE)[$this->languageCode]["id"];
  }

  /**
   * Get array containing all languages.
   *
   * The names are translated to the current language of this i18n model instance.
   *
   * <em>NOTE:</em> The translations for the languages cannot be empty, we get them from Intl ICU.
   *
   * @staticvar array $countries
   *   Used to cache the array.
   * @param string $key
   *   [Optional] Get array of languages sorted by ID, ISO alpha-2 code, or name. Use the <var>KEY_*</var> class
   *   constants.
   * @return array
   *   Associative array containing all languages in the form:
   *   <pre>array(
   *   "id" => $id => array("id" => $id, "code" => $code, "name" => $name),
   *   "code" => $code => array("id" => $id, "code" => $code, "name" => $name),
   *   "name" => $name => array("id" => $id, "code" => $code, "name" => $name),
   *   )</pre>
   */
  public function getLanguages($key = self::KEY_ID) {
    if (!$this->languages) {
      $query = sprintf(
        "SELECT `language_id` AS `%s`, `iso_alpha-2` AS `%s`, %s`%s` FROM `languages` ORDER BY `%s`",
        self::KEY_ID,
        self::KEY_CODE,
        $this->languageCode === self::getDefaultLanguageCode() ? "" : "COLUMN_GET(`dyn_translations`, '{$this->languageCode}' AS CHAR(255)) AS ",
        self::KEY_NAME,
        self::KEY_ID
      );
      foreach ($this->selectAll($query) as $l) {
        $l = [
          self::KEY_ID   => $l[self::KEY_ID],
          self::KEY_CODE => $l[self::KEY_CODE],
          self::KEY_NAME => $l[self::KEY_NAME],
        ];
        $this->languages[self::KEY_ID][$l[self::KEY_ID]] = $l;
        $this->languages[self::KEY_CODE][$l[self::KEY_CODE]] = $l;
        $this->languages[self::KEY_NAME][$l[self::KEY_NAME]] = $l;
      }
      ksort($this->languages[self::KEY_CODE]);
      $this->getCollator()->ksort($this->languages[self::KEY_NAME]);
    }
    return $this->languages[$key];
  }

  /**
   * Get a sorted associative array of all supported system languages.
   *
   * @param string $key
   *   [Optional] Sort by language ID, ISO alpha-2 code, or name. Use the <var>KEY_*</var> class constants.
   * @return array
   *   Associative array containing all supported system languages.
   */
  public function getSystemLanguages($key = self::KEY_ID) {
    if (!$this->systemLanguages) {
      $languages = $this->getLanguages(self::KEY_CODE);
      $c = count(self::$supportedLanguageCodes);
      for ($i = 0; $i < $c; ++$i) {
        $l = [
          self::KEY_ID   => $languages[self::$supportedLanguageCodes[$i]][self::KEY_ID],
          self::KEY_CODE => $languages[self::$supportedLanguageCodes[$i]][self::KEY_CODE],
          self::KEY_NAME => $languages[self::$supportedLanguageCodes[$i]][self::KEY_NAME],
        ];
        $this->systemLanguages[self::KEY_ID][$l[self::KEY_ID]] = $l;
        $this->systemLanguages[self::KEY_CODE][$l[self::KEY_CODE]] = $l;
        $this->systemLanguages[self::KEY_NAME][$l[self::KEY_NAME]] = $l;
      }
      ksort($this->systemLanguages[self::KEY_CODE]);
      $this->getCollator()->ksort($this->systemLanguages[self::KEY_NAME]);
    }
    return $this->systemLanguages[$key];
  }

  /**
   * Get collator for the current locale.
   *
   * @return \MovLib\Utility\CollatorExtended
   * @throws \IntlException
   *   If instantiating of the collator failed (e.g. non supported locale).
   */
  public function getCollator() {
    if (!$this->collator) {
      $this->collator = new CollatorExtended($this->locale);
    }
    return $this->collator;
  }

  /**
   * Get array containing all countries.
   *
   * The names are translated to the current language of this i18n model instance.
   *
   * <em>NOTE:</em> The translations for the countries cannot be empty, we get them from Intl ICU.
   *
   * @staticvar array $countries
   *   Used to cache the array.
   * @param string $key
   *   [Optional] Get array of countries sorted by ID, ISO alpha-2 code, or name. Use the <var>KEY_*</var> class
   *   constants.
   * @return array
   *   Associative array containing all countries in the form:
   *   <pre>array(
   *   "id" => $id => array("id" => $id, "code" => $code, "name" => $name),
   *   "code" => $code => array("id" => $id, "code" => $code, "name" => $name),
   *   "name" => $name => array("id" => $id, "code" => $code, "name" => $name),
   *   )</pre>
   */
  public function getCountries($key = self::KEY_ID) {
    if (!$this->countries) {
      $query = sprintf(
        "SELECT `country_id` AS `%s`, `iso_alpha-2` AS `%s`, %s`%s` FROM `countries` ORDER BY `%s`",
        self::KEY_ID,
        self::KEY_CODE,
        $this->languageCode === self::getDefaultLanguageCode() ? "" : "COLUMN_GET(`dyn_translations`, '{$this->languageCode}' AS CHAR(255)) AS ",
        self::KEY_NAME,
        self::KEY_ID
      );
      $result = $this->selectAll($query);
      foreach ($result as $c) {
        $c = [
          self::KEY_ID   => $c[self::KEY_ID],
          self::KEY_CODE => $c[self::KEY_CODE],
          self::KEY_NAME => $c[self::KEY_NAME],
        ];
        $this->countries[self::KEY_ID][$c[self::KEY_ID]] = $c;
        $this->countries[self::KEY_CODE][$c[self::KEY_CODE]] = $c;
        $this->countries[self::KEY_NAME][$c[self::KEY_NAME]] = $c;
      }
      ksort($this->countries[self::KEY_CODE]);
      $this->getCollator()->ksort($this->countries[self::KEY_NAME]);
    }
    return $this->countries[$key];
  }

  /**
   * Insert a pattern for translation into the database table identified by context.
   *
   * @param string $context
   *   The context of this translation, either <em>message</em> or <em>route</em>.
   * @param string $pattern
   *   The pattern to insert.
   * @param array $options
   *   Associative array to overwrite the default options used in this method. Possible keys are:
   *   <ul>
   *     <li><code>comment</code>: default is <code>NULL</code>.</li>
   *     <li><code>old_pattern</code>: default is <code>NULL</code>.</li>
   *   </ul>
   * @return \MovLib\Model\I18nModel
   */
  public function insertPattern($context, $pattern, $options) {
    $issetComment = isset($options["comment"]);
    $this->affectedRows = 0;
    if (isset($options["old_pattern"])) {
      if ($issetComment) {
        $this->update("{$context}s", "ssss", [ $context => $pattern, "comment" => $options["comment"] ], [ "old_pattern" => $options["old_pattern"] ]);
      }
      else {
        $this->update("{$context}s", "sss", [ $context => $pattern ], [ "old_pattern" => $options["old_pattern"] ]);
      }
    }
    if ($this->affectedRows === 0) {
      try {
        // Maybe we already inserted this translation by a prior call to this method. This can happen if the same new
        // pattern occurres more than once on the same page.
        $this->select("SELECT `{$context}_id` FROM `{$context}s` WHERE `{$context}` = ? LIMIT 1", "s", [ $pattern ])[0];
      } catch (ErrorException $e) {
        if ($issetComment) {
          $this->insert("{$context}s", "sss", [ $context => $pattern, "comment" => $options["comment"], "dyn_translations" => "" ]);
        }
        else {
          $this->insert("{$context}s", "ss", [ $context => $pattern, "dyn_translations" => "" ]);
        }
      }
    }
    unset($this->affectedRows);
    return $this;
  }

  /**
   * Insert a translation of pattern into the database table identified by context.
   *
   * @param string $context
   *   The context of this translation, either <em>message</em> or <em>route</em>.
   * @param int $id
   *   The unique ID of the pattern for which we should insert a translation.
   * @param string $languageCode
   *   The ISO 639-1 alpha-2 language code that identifies the translation's language.
   * @param string $translation
   *   The translated pattern.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   *   If inserting or updating failed.
   */
  public function insertOrUpdateTranslation($context, $id, $languageCode, $translation) {
    $this
      ->prepareAndBind(
        "UPDATE `{$context}s`
        SET `dyn_translations` = COLUMN_ADD(COLUMN_CREATE(?, ?), ?, ?)
        WHERE `{$context}_id` = ?",
        "ssssd", [ $languageCode, $translation, $languageCode, $translation, $id ]
      )
      ->execute()
      ->close()
    ;
    // If affected rows is zero the translation was already present and exactly the same as was asked to update.
    if ($this->affectedRows === -1) {
      $exception = new DatabaseException("Could not insert nor update {$languageCode} translation for {$context} with ID '{$id}'");
      DelayedLogger::logException($exception);
      throw $exception;
    }
    return $this;
  }

  /**
   * Translate the given route.
   *
   * @link http://userguide.icu-project.org/formatparse/messages
   * @param string $route
   *   A simple string that should be translated or an advanced Intl ICU pattern. Read the official Intl ICU
   *   documentation for more information on how to create translation patterns.
   * @param array $args
   *   [Optional] Array of values to insert.
   * @param string $options
   *   [Optional] Associative array to overwrite the default options used in this method in the form:
   *   <ul>
   *     <li><code>language_code</code>: default is to use the current display language code.</li>
   *     <li><code>comment</code>: default is <code>NULL</code>.</li>
   *     <li><code>old_pattern</code>: default is <code>NULL</code>.</li>
   *   </ul>
   * @return string
   *   URI: The absolute translated route.
   * @throws \MovLib\Exception\IntlException
   *   If formatting the message with the given <var>$args</var> fails (only if any were passed).
   */
  public function r($route, $args = null, $options = null) {
    if (isset($options["language_code"])) {
      if (isset($_SERVER["LANGUAGE_CODE"])) {
        $serverName = str_replace($_SERVER["LANGUAGE_CODE"], $options["language_code"], $_SERVER["SERVER_NAME"]);
      }
      else {
        $serverName = "{$options["language_code"]}.{$_SERVER["SERVER_NAME"]}";
      }
    }
    else {
      $serverName = $_SERVER["SERVER_NAME"];
    }
    if ($route !== "/") {
      $route = $this->formatMessage("route", $route, $args, $options);
    }
    return "https://{$serverName}{$route}";
  }

  /**
   * Translate the given message.
   *
   * @param string $message
   *   A simple string that should be translated or an advanced Intl ICU pattern. Read the official Intl ICU
   *   documentation for more information on how to create translation patterns.
   *
   *   <a href="http://userguide.icu-project.org/formatparse/messages">Formatting Messages</a>
   * @param array $args
   *   [Optional] Array of values to insert.
   * @param string $options
   *   [Optional] Associative array to overwrite the default options used in this method in the form:
   *   <ul>
   *     <li><code>language_code</code>: default is to use the current display language code.</li>
   *     <li><code>comment</code>: default is <code>NULL</code>.</li>
   *     <li><code>old_pattern</code>: default is <code>NULL</code>.</li>
   *   </ul>
   * @return string
   *   The translated and formatted message.
   * @throws \MovLib\Exception\IntlException
   *   If formatting the message with the given <var>$args</var> fails (only if any were passed).
   */
  public function t($message, $args = null, $options = null) {
    return $this->formatMessage("message", $message, $args, $options);
  }

}
