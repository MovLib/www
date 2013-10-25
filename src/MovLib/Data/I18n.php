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
namespace MovLib\Data;

use \DateTimeZone;
use \IntlDateFormatter;
use \Locale;
use \MessageFormatter;
use \MovLib\Data\Collator;
use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;

/**
 * The i18n model loads and and updated translations and retrieves translated data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m:2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Extended collator for locale aware sorting.
   *
   * @see \MovLib\Data\I18n::getCollator()
   * @var \MovLib\Data\Collator
   */
  private $collator;

  /**
   * The system's default language code.
   *
   * @var string
   */
  public $defaultLanguageCode;

  /**
   * The system's default locale.
   *
   * @var string
   */
  public $defaultLocale;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new i18n model instance.
   *
   * @global \MovLib\Configuration $config
   * @param $locale [optional]
   *   The desired locale, if no locale is passed the following procedure is executed:
   *   <ol>
   *     <li>Check if the server set a language code (access via subdomain)</li>
   *     <li>Check if the user has provided an <code>HTTP_ACCEPT_LANGUAGE</code> header</li>
   *     <li>Use default locale</li>
   *   </ol>
   */
  public function __construct($locale = null) {
    global $config;
    // Always export defaults first.
    $this->defaultLocale = Locale::getDefault();
    $this->defaultLanguageCode = "{$this->defaultLocale[0]}{$this->defaultLocale[1]}";
    // To understand the following code it's important to understand comparison operations and assignments. If you don't
    // understand the following code be sure to read more about both these topics before attempting to change anything
    // here!
    if (!$locale) {
      // Use language code from subdomain if present.
      (isset($_SERVER["LANGUAGE_CODE"]) && ($locale = $config->systemLanguages[$_SERVER["LANGUAGE_CODE"]]))
      // Use the best matching value from the user's submitted HTTP accept language header.
      || (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && (strlen($localeTmp = $_SERVER["HTTP_ACCEPT_LANGUAGE"]) > 1) && isset($config->systemLanguages["{$localeTmp[0]}{$localeTmp[1]}"]) && ($locale = $config->systemLanguages["{$localeTmp[0]}{$localeTmp[1]}"]));
    }
    // If we still have no locale, use defaults.
    if (!$locale) {
      $this->locale = $this->defaultLocale;
      $this->languageCode = $this->defaultLanguageCode;
    }
    // The language code can only be a two-letter code in contrast to the locale which can be a two-letter code or a
    // five-letter code (including separator and country).
    else {
      $this->locale = $locale;
      $this->languageCode = "{$locale[0]}{$locale[1]}";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
   * Format the given message and translate it to the display locale.
   *
   * @param string $context
   *   The context in which we should translate the message.
   * @param string $pattern
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args
   *   Numeric array of arguments that should be inserted into <var>$pattern</var>.
   * @param array $options [optional]
   *   Associative array of options to alter the behaviour of this method. Available options are:
   *   <ul>
   *     <li><var>"comment"</var> You can pass along a comment that will be stored along this pattern for translators
   *     to help them understand how they should translate it, defaults to no comment.</li>
   *     <li><var>"language_code"</var> Set the language code into which the message should be translated, defaults to
   *     the current display language code.</li>
   *   </ul>
   * @return string
   *   The formatted and translated (if applicable) message.
   * @throws \IntlException
   */
  public function formatMessage($context, $pattern, $args, $options = null) {
    $languageCode = isset($options["language_code"]) ? $options["language_code"] : $this->languageCode;
    if ($languageCode != $this->defaultLanguageCode) {
      $result = $this->query(
        "SELECT COLUMN_GET(`dyn_translations`, '{$languageCode}' AS BINARY) AS `translation` FROM `{$context}s` WHERE `{$context}` = ? LIMIT 1",
        "s",
        [ $pattern ]
      )->get_result()->fetch_assoc();
      if (empty($result["translation"])) {
        // @todo remove the following lines after the translation extractor has been created. NOTE that routes are
        //       already handled via MovCli. We should also write an extended I18n class for MovCli and move all
        //       methods that aren't needed in this class to the extended version. Many methods are only from interest
        //       for that extended version (e.g. everything that has to do with inserting, updating and deleting).
        if ($context === "message") {
          DelayedMethodCalls::stack($this, "insertMessage", [ $pattern, $options ]);
        }
      }
      else {
        $pattern = $result["translation"];
      }
    }
    if ($args) {
      return MessageFormatter::formatMessage($languageCode, $pattern, $args);
    }
    return $pattern;
  }

  /**
   * Get collator for the current locale.
   *
   * @return \MovLib\Data\Collator
   *   If instantiating of the collator failed (e.g. non supported locale).
   */
  public function getCollator() {
    if (!$this->collator) {
      $this->collator = new Collator($this->locale);
    }
    return $this->collator;
  }

  /**
   * Get sorted array with all system languages translated to the current locale.
   *
   * The array can directly be used together with a Navigation partial. The languages are sorted by their name in the
   * current locale and each link's text contains the native name in parentheses.
   *
   * @global \MovLib\Configuration $config
   * @return array
   *   Sorted array with all system languages translated to the current locale.
   */
  public function getSystemLanguageLinks() {
    global $config;
    $links = [];
    foreach ($this->getSystemLanguages() as $languageCode => $displayLanguage) {
      $translatedDisplayLanguage = Locale::getDisplayLanguage($languageCode, $languageCode);
      if ($this->languageCode == $languageCode) {
        $links[] = [ "#", "<b>{$translatedDisplayLanguage}</b>", [ "class" => "active" ]];
      }
      else {
        $links[] = [
          "{$_SERVER["SCHEME"]}://{$languageCode}.{$config->domainDefault}{$_SERVER["PATH_INFO"]}",
          "{$displayLanguage} ({$translatedDisplayLanguage})",
          [ "lang" => $languageCode, "title" => $this->t("Read this page in {0}.", [ $displayLanguage ]) ]
        ];
      }
    }
    return $links;
  }

  /**
   * Get sorted associative array with all system languages translated to the current locale.
   *
   * The returned array has the ISO alpha-2 code as key and the value is the translated language's display name.
   * <b>Example for locale <code>"de_AT"</code>:</b>
   * <pre>[
   *   "en" => "Englisch",
   *   "de" => "Deutsch",
   * ];</pre>
   *
   * @global \MovLib\Configuration $config
   * @return array
   *   Sorted associative array with all system languages for the current locale.
   */
  public function getSystemLanguages() {
    global $config;
    $translated = [];
    foreach ($config->systemLanguages as $locale) {
      $translated["{$locale[0]}{$locale[1]}"] = Locale::getDisplayLanguage($locale, $this->locale);
    }
    $this->getCollator()->asort($translated);
    return $translated;
  }

  /**
   * Get all time zones in the current locale.
   *
   * The returned array is associative and the keys are the time zone IDs (e.g. <code>"Europe/Vienna"</code>) and the
   * values the translated string in the current locale.
   *
   * @return array
   *   Get all time zones in the current locale.
   */
  public function getTimeZones() {
    $timezones = DateTimeZone::listIdentifiers();
    $translated = [];
    $c = count($timezones);
    for ($i = 0; $i < $c; ++$i) {
      $translated[$timezones[$i]] = $this->t(strtr($timezones[$i], "_", " "));
    }
    $this->getCollator()->asort($translated, Collator::SORT_STRING);
    return $translated;
  }

  /**
   * Insert message pattern.
   *
   * @param string $message
   *   The message to insert.
   * @param array $options [optional]
   *   Associative array with additional options. Currently only the <code>"comment"</code> option is supported.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insertMessage($message, $options = null) {
    if (empty($this->query("SELECT `message_id` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $message ])->get_result()->fetch_assoc())) {
      if (!isset($options["comment"])) {
        $options["comment"] = null;
      }
      $this->query("INSERT INTO `messages` (`message`, `comment`, `dyn_translations`) VALUES (?, ?, '')", "ss", [ $message, $options["comment"] ]);
    }
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
      "UPDATE `{$context}s` SET `dyn_translations` = COLUMN_ADD(`dyn_translations`, ?, ?) WHERE `{$context}_id` = ?",
      "ssd",
      [ $languageCode, $translation, $id ]
    );
    return $this;
  }

  /**
   * Insert raoute pattern.
   *
   * @param string $route
   *   The route to insert.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insertRoute($route) {
    if (empty($this->query("SELECT `route_id` FROM `routes` WHERE `route` = ? LIMIT 1", "s", [ $route ])->get_result()->fetch_assoc())) {
      $this->query("INSERT INTO `routes` (`route`, `dyn_translations`) VALUES (?, '')", "s", [ $route ]);
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
  public function r($route, array $args = null, array $options = null) {
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
  public function t($message, array $args = null, array $options = null) {
    return $this->formatMessage("message", $message, $args, $options);
  }

}
