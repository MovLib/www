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
namespace MovLib\Data;

use \DateTimeZone;
use \IntlDateFormatter;
use \Locale;
use \MovLib\Exception\DatabaseException;
use \MovLib\Utility\CollatorExtended;
use \MovLib\Data\Delayed\Logger;
use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;

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
class I18n extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Return array sorted by ID.
   *
   * @see \MovLib\Data\I18n::getLanguages()
   * @see \MovLib\Data\I18n::getCountries()
   * @var string
   */
  const KEY_ID = "id";

  /**
   * Return array sorted by ISO alpha-2 code.
   *
   * @see \MovLib\Data\I18n::getLanguages()
   * @see \MovLib\Data\I18n::getCountries()
   * @var string
   */
  const KEY_CODE = "code";

  /**
   * Return array sorted by name.
   *
   * @see \MovLib\Data\I18n::getLanguages()
   * @see \MovLib\Data\I18n::getCountries()
   * @var string
   */
  const KEY_NAME = "name";


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The system's default locale.
   *
   * @var string
   */
  public $defaultLocale;

  /**
   * The system's default language code.
   *
   * @var string
   */
  public $defaultLanguageCode;

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


  // ------------------------------------------------------------------------------------------------------------------- Private Properties


  /**
   * Extended collator for locale aware sorting.
   *
   * @see \MovLib\Data\I18n::getCollator()
   * @var \MovLib\Utility\CollatorExtended
   */
  private $collator;

  /**
   * Associative array that will be filled with all info on all available countries on demand.
   *
   * @see \MovLib\Data\I18n::getCountries()
   * @var array
   */
  private $countries;

  /**
   * Associative array that will be filled with all info on all available languages on demand.
   *
   * @see \MovLib\Data\I18n::getLanguages()
   * @var array
   */
  private $languages;

  /**
   * Associative array that will be filled with all info on supported system language on demand.
   *
   * @see \MovLib\Data\I18n::getSystemLanguages()
   * @var array
   */
  private $systemLanguages;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new i18n model instance.
   *
   * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
   * @global \MovLib\Model\SessionModel $user
   * @param $locale [optional]
   *   The desired locale, if no locale is passed the following procedure is executed:
   *   <ol>
   *     <li>Check if the server set a language code (access via subdomain)</li>
   *     <li>Check if the user has provided an <code>HTTP_ACCEPT_LANGUAGE</code> header</li>
   *     <li>Use default locale</li>
   *   </ol>
   */
  public function __construct($locale = null) {
    global $session;
    // Always export defaults first.
    $this->defaultLocale = Locale::getDefault();
    $this->defaultLanguageCode = "{$this->defaultLocale[0]}{$this->defaultLocale[1]}";
    // To understand the following code it's important to understand comparison operations and assignments. If you don't
    // understand the following code be sure to read more about both these topics before attempting to change anything
    // here!
    if (!$locale) {
      // Use language code from subdomain if present.
      (isset($_SERVER["LANGUAGE_CODE"]) && ($this->locale = $GLOBALS["movlib"]["locales"][$_SERVER["LANGUAGE_CODE"]]))
      // Use the best matching value from the user's submitted HTTP accept language header.
      || (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($locale = Locale::acceptFromHttp($_SERVER["HTTP_ACCEPT_LANGUAGE"])) && isset($GLOBALS["movlib"]["locales"]["{$locale[0]}{$locale[1]}"]) && ($this->locale = $locale));
    }
    // If we still have no locale, use defaults.
    if (!$this->locale) {
      $this->locale = $this->defaultLocale;
      $this->languageCode = $this->defaultLanguageCode;
    }
    // The language code can only be a two-letter code in contrast to the locale which can be a two-letter code or a
    // five-letter code (including separator and country).
    else {
      $this->languageCode = "{$this->locale[0]}{$this->locale[1]}";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Format the given message and translate it to the display locale.
   *
   * @param string $context
   *   The context in which we should translate the message.
   * @param string $pattern
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args
   *   Numeric array of arguments that should be inserted into <var>$pattern</var>.
   * @param array $options
   *   Associative array of options to alter the behaviour of this method. Available options are:
   *   <ul>
   *     <li><var>"comment"</var> You can pass along a comment that will be stored along this pattern for translators
   *     to help them understand how they should translate it, defaults to no comment.</li>
   *     <li><var>"language_code"</var> Set the language code into which the message should be translated, defaults to
   *     the current display language code.</li>
   *   </ul>
   * @return string
   *   The formatted and translated (if applicable) message.
   */
  public function formatMessage($context, $pattern, $args, array &$options) {
    if (empty($options["language_code"])) {
      $options["language_code"] = $this->languageCode;
    }
    if ($options["language_code"] != $this->defaultLanguageCode) {
      $result = $this->select("SELECT COLUMN_GET(`dyn_translations`, '{$options["language_code"]}' AS BINARY) AS `translation` FROM `{$context}s` WHERE `{$context}` = ? LIMIT 1", "s", [ $pattern ]);
      if (empty($result[0]["translation"])) {
        Logger::log("Could not find {$options["language_code"]} translation for {$context}: '{$pattern}'", E_NOTICE);
        // @todo Remove this line after translation extractor was written.
        if (php_sapi_name() === "cli") {
          $this->insertPattern($context, $pattern, $options);
        }
        elseif ($context != "route") {
          DelayedMethodCalls::stack($this, "insertPattern", [ $context, $pattern, $options ]);
        }
      }
      else {
        $pattern = $result[0]["translation"];
      }
    }
    if ($args) {
      return msgfmt_format_message($options["language_code"], $pattern, $args);
    }
    return $pattern;
  }

  /**
   * Format the given timestamp for output.
   *
   * @link http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
   * @todo Allow override of used locale to format the date.
   * @param int $timestamp
   *   The timestamp to format.
   * @param null|string $timezone
   *   One of the {@link http://www.php.net/manual/en/timezones.php PHP timezone identifiers}. Defaults to system
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
   * @see \MovLib\Data\I18n::getLanguages()
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
        $this->languageCode == $this->defaultLanguageCode ? "" : "COLUMN_GET(`dyn_translations`, '{$this->languageCode}' AS CHAR(255)) AS ",
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
        $this->languageCode == $this->defaultLanguageCode ? "" : "COLUMN_GET(`dyn_translations`, '{$this->languageCode}' AS CHAR(255)) AS ",
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
   *   </ul>
   * @return \MovLib\Data\I18n
   */
  public function insertPattern($context, $pattern, array $options) {
    $this->affectedRows = 0;
    if ($this->affectedRows === 0) {
      // Maybe we already inserted this translation by a prior call to this method. This can happen if the same new
      // pattern occurs more than once on the same page.
      $result = $this->select("SELECT `{$context}_id` FROM `{$context}s` WHERE `{$context}` = ? LIMIT 1", "s", [ $pattern ]);
      if (!isset($result[0]["route_id"])) {
        if (isset($options["comment"])) {
          $this->query("INSERT INTO `{$context}s` (`{$context}`, `comment`, `dyn_translations`) VALUES (?, ?, '')", "ss", [ $pattern, $options["comment"] ]);
        }
        else {
          $this->query("INSERT INTO `{$context}s` (`{$context}`, `dyn_translations`) VALUES (?, '')", "s", [ $pattern ]);
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
   */
  public function insertOrUpdateTranslation($context, $id, $languageCode, $translation) {
    $this->query(
      "UPDATE `{$context}s` SET `dyn_translations` = COLUMN_ADD(COLUMN_CREATE(?, ?), ?, ?) WHERE `{$context}_id` = ?",
      "ssssd",
      [ $languageCode, $translation, $languageCode, $translation, $id ]
    );
    // If affected rows is zero the translation was already present and exactly the same as was asked to update.
    if ($this->affectedRows === -1) {
      $exception = new DatabaseException("Could not insert nor update {$languageCode} translation for {$context} with ID '{$id}'");
      Logger::logException($exception);
      throw $exception;
    }
    return $this;
  }

  /**
   * Format and translate the given route.
   *
   * @see \MovLib\Data\I18n::formatMessage()
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   Numeric array of arguments that should be inserted into <var>$route</var>.
   * @param array $options [optional]
   *   Associative array of options to alter the behaviour of this method. Available options are:
   *   <ul>
   *     <li><var>"absolute"</var> If set to <code>FALSE</code> only the formatted and translated <var>$route</var>
   *     without protocol and host will be returned, defaults to <code>TRUE</code>.</li>
   *     <li><var>"comment"</var> You can pass along a comment that will be stored along this pattern for translators
   *     to help them understand how they should translate it, defaults to no comment.</li>
   *     <li><var>"language_code"</var> Set the language code into which the message should be translated, defaults to
   *     the current display language code.</li>
   *   </ul>
   * @return string
   *   The formatted and translated <var>$route</var>.
   * @throws \IntlException
   */
  public function r($route, array $args = null, array $options = []) {
    return $this->formatMessage("route", $route, $args, $options);
  }

  /**
   * Format and translate the given message.
   *
   * @see \MovLib\Data\I18n::formatMessage()
   * @param string $message
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   Numeric array of arguments that should be inserted into <var>$message</var>.
   * @param array $options [optional]
   *   Associative array of options to alter the behaviour of this method. Available options are:
   *   <ul>
   *     <li><var>"comment"</var> You can pass along a comment that will be stored along this pattern for translators
   *     to help them understand how they should translate it, defaults to no comment.</li>
   *     <li><var>"language_code"</var> Set the language code into which the message should be translated, defaults to
   *     the current display language code.</li>
   *   </ul>
   * @return string
   *   The formatted and translated <var>$message</var>.
   * @throws \IntlException
   */
  public function t($message, array $args = null, array $options = []) {
    return $this->formatMessage("message", $message, $args, $options);
  }

}
