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

use \Locale;
use \MessageFormatter;
use \MovLib\Data\Collator;

/**
 * The i18n model loads and and updated translations and retrieves translated data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m:2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class I18n {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Custom language code for non linguistic content.
   *
   * Used in languages.
   *
   * @see \MovLib\Stub\Data\Language
   * @see \MovLib\Presentation\Partial\Language
   * @var string
   */
  const CODE_NON_LINGUISTIC = "--";

  /**
   * Custom language code for other content.
   *
   * Used in languages and subtitles.
   *
   * @see \MovLib\Stub\Data\Language
   * @see \MovLib\Presentation\Partial\Language
   * @see \MovLib\Stub\Data\Subtitle
   * @see \MovLib\Presentation\Partial\Subtitle
   * @var string
   */
  const CODE_OTHER = "&&";

  /**
   * Custom language code for commentary content.
   *
   * Used in subtitles.
   *
   * @see \MovLib\Stub\Data\Subtitle
   * @see \MovLib\Presentation\Partial\Subtitle
   * @var string
   */
  const CODE_COMMENTARY = "@@";

  /**
   * Custom language code for fact content.
   *
   * Used in subtitles.
   *
   * @see \MovLib\Stub\Data\Subtitle
   * @see \MovLib\Presentation\Partial\Subtitle
   * @var string
   */
  const CODE_FACT = "§§";


  // ------------------------------------------------------------------------------------------------------------------- Properties


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

  /**
   * Used to cache translations.
   *
   * @see I18n::getTranslations()
   * @see I18n::translate()
   * @var array
   */
  protected static $translations = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new i18n model instance.
   *
   * @global \MovLib\Kernel $kernel
   * @param $locale [optional]
   *   The desired locale, if no locale is passed the following procedure is executed:
   *   <ol>
   *     <li>Check if the server set a language code (access via subdomain)</li>
   *     <li>Check if the user has provided an <code>HTTP_ACCEPT_LANGUAGE</code> header</li>
   *     <li>Use default locale</li>
   *   </ol>
   */
  public function __construct($locale = null) {
    global $kernel;

    // Export default locale and language code to class scope.
    $this->defaultLocale       = Locale::getDefault();
    $this->defaultLanguageCode = "{$this->defaultLocale[0]}{$this->defaultLocale[1]}";

    // Use server determined language code if no locale was passed to the constructor.
    if (!$locale && isset($_SERVER["LANGUAGE_CODE"]) && isset($kernel->systemLanguages[$_SERVER["LANGUAGE_CODE"]])) {
      $this->locale       = $kernel->systemLanguages[$_SERVER["LANGUAGE_CODE"]];
      $this->languageCode = $_SERVER["LANGUAGE_CODE"];
    }
    // If a locale was passed to the constructor, export to class scope.
    else {
      $len = strlen($locale);
      if ($len === 2) {
        // @devStart
        // @codeCoverageIgnoreStart
        if (!isset($kernel->systemLanguages[$locale])) {
          throw new \InvalidArgumentException("Unsupported language code '{$locale}' passed to i18n constructor");
        }
        // @codeCoverageIgnoreEnd
        // @devEnd
        $this->locale       = $kernel->systemLanguages[$locale];
        $this->languageCode = $locale;
      }
      else {
        // @devStart
        // @codeCoverageIgnoreStart
        if ($len !== 5) {
          throw new \LogicException("A locale consists of five characters, the language code followed by the country code, e.g.: 'de_AT'");
        }
        if (!isset($kernel->systemLanguages["{$locale[0]}{$locale[1]}"])) {
          throw new \InvalidArgumentException("Unsupported locale '{$locale}' passed to i18n constructor");
        }
        // @codeCoverageIgnoreEnd
        // @devEnd
        $this->locale       = $locale;
        $this->languageCode = "{$locale[0]}{$locale[1]}";
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a message without translation, very useful for inline number formatting.
   *
   * @param string $message
   *   The message to format.
   * @param array $args
   *   The message arguments.
   * @return string
   *   The formatted message.
   */
  public function format($message, array $args) {
    return MessageFormatter::formatMessage($this->locale, $message, $args);
  }

  /**
   * Get number formatted in human readable form.
   *
   * @param integer $bytes
   *   The number to format.
   * @return string
   *   Number formatted in human readable form.
   */
  public function formatBytes($bytes) {
    // https://en.wikipedia.org/wiki/Mebibyte
    if ($bytes >= 1048576) {
      $bytes = ceil($bytes / 1048576);
      $title = "Mebibyte";
      $abbr  = "MiB";
    }
    // https://en.wikipedia.org/wiki/Kibibyte
    elseif ($bytes >= 1024) {
      $bytes = ceil($bytes / 1024);
      $title = "Kibibyte";
      $abbr  = "KiB";
    }
    // https://en.wikipedia.org/wiki/Byte
    else {
      $title = "Byte";
      $abbr  = "B";
    }
    return MessageFormatter::formatMessage($this->locale, "{0,number,integer} <abbr title='{$title}'>{$abbr}</abbr>", [ $bytes ]);
  }

  /**
   * Get collator for the current locale.
   *
   * @staticvar array $collators
   *   Cache for collator instances.
   * @return \MovLib\Data\Collator
   *   If instantiating of the collator failed (e.g. non supported locale).
   */
  public function getCollator() {
    static $collators = [];
    if (!isset($collators[$this->locale])) {
      $collators[$this->locale] = new Collator($this->locale);
    }
    return $collators[$this->locale];
  }

  /**
   * Get translations from file.
   *
   * @param string $filename
   *   The name of the file that contains the translations.
   * @return array
   *   The translations from the file.
   */
  public function getTranslations($filename) {
    // Build absolute path to the translation file.
    $file = "dr://var/i18n/{$this->locale}/{$filename}.php";

    // Only try to load the translation file's content if we have not cached entry for it and it actually exists. Some
    // things don't need translation in the default locale (e.g. routes) and others do (e.g. time zones).
    if (!isset(self::$translations[$this->locale][$filename]) && is_file($file)) {
      self::$translations[$this->locale][$filename] = require_once $file;
    }

    return self::$translations[$this->locale][$filename];
  }

  /**
   * Insert message pattern.
   *
   * @global \MovLib\Data\Database $db
   * @param string $message
   *   The message to insert.
   * @param array $options [optional]
   *   Associative array with additional options. Currently only the <code>"comment"</code> option is supported.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insertMessage($message, $options = null) {
    global $db;
    if (empty($db->query("SELECT `message_id` FROM `messages` WHERE `message` = ? LIMIT 1", "s", [ $message ])->get_result()->fetch_assoc())) {
      if (!isset($options["comment"])) {
        $options["comment"] = null;
      }
      $db->query("INSERT INTO `messages` (`message`, `comment`, `dyn_translations`) VALUES (?, ?, '')", "ss", [ $message, $options["comment"] ]);
    }
    return $this;
  }

  /**
   * Translate and format given message with given context.
   *
   * @param string $pattern
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param null|array $args
   *   The message formatter arguments.
   * @param string $context
   *   The translations context (e.g. <code>"routes/singular"</code> or <code>"countries"</code>) if you pass
   *   <code>NULL</code> the database is asked.
   * @return string
   *   The translated and formatted pattern.
   */
  public function translate($pattern, $args, $context) {
    // Only attempt to translate the pattern if we have no translation already cached.
    if (!isset(self::$translations[$this->locale][$context][$pattern])) {
      // Fetch translations from database for message context.
      if ($context == "messages") {
        global $db;
        list(self::$translations[$this->locale][$context][$pattern]) = $db->query(
          "SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR) FROM `messages` WHERE `message` = ? LIMIT 1",
          "ss",
          [ $this->languageCode, $pattern ]
        )->get_result()->fetch_row();
      }
      // Fetch translations from file for everything else, we don't need the returned value because we're directly
      // accessing the internal caching array.
      else {
        $this->getTranslations($context);
      }

      // Check if we have a translation for this pattern. If we have none this either means that the pattern is in the
      // default locale and has no translations (e.g. time zones have translations in the default locale as well, but
      // most other things have no default locale translations). We insert the given pattern in this case to speed up
      // later look ups.
      if (empty(self::$translations[$this->locale][$context][$pattern])) {
        self::$translations[$this->locale][$context][$pattern] = $pattern;
      }
    }

    if ($args) {
      return MessageFormatter::formatMessage($this->locale, self::$translations[$this->locale][$context][$pattern], $args);
    }
    return self::$translations[$this->locale][$context][$pattern];
  }

  /**
   * Translate and format singular route.
   *
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   The message formatter arguments.
   * @return string
   *   The translated and formatted singular route.
   */
  public function r($route, array $args = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($route) || !is_string($route)) {
      throw new \LogicException("\$route cannot be empty and must of type string");
    }
    if ($route == "/") {
      throw new \LogicException("Translating the root route '/' makes no sense");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this->translate($route, $args, "routes/singular");
  }

  /**
   * Translate and format plural route.
   *
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   The message formatter arguments.
   * @return string
   *   The translated and formatted plural route.
   */
  public function rp($route, array $args = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($route) || !is_string($route)) {
      throw new \LogicException("\$route cannot be empty and must of type string");
    }
    if ($route == "/") {
      throw new \LogicException("Translating the root route '/' makes no sense");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this->translate($route, $args, "routes/plural");
  }

  /**
   * Format and translate given message.
   *
   * @global \MovLib\Data\Database $db
   * @staticvar array $messages
   * @param string $message
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   Numeric array of arguments that should be inserted into <var>$message</var>.
   * @return string
   *   The formatted and translated <var>$message</var>.
   * @throws \IntlException
   * @throws \MovLib\Exception\DatabaseException
   */
  public function t($message, array $args = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($message) || !is_string($message)) {
      throw new \LogicException("\$message cannot be empty and must be of type string");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this->translate($message, $args, "messages");
  }

  /**
   * Get the translated and formatted plural message.
   *
   * <b>NOTE</b><br>
   * You can use this method to auto-translate any simple plural form that has one English translation for 1 and one
   * translation for 0 and >= 2. Other languages may have more plural forms (e.g. Russian where e.g. 1, 21, 61 share
   * singular form). If you have to translate a complicated plural form that has more than the two aformentioned English
   * translations use the default translation method {@see I18n::t} by writing the full Intl ICU string.
   *
   * @param string $plural
   *   The message's plural form to format and translate.
   * @param string $singular [optional]
   *   The message's singular form to format and translate, defaults to <code>NULL</code> which means that the given
   *   <var>$plural</var> is also used for the singular form (e.g. the English word <i>Series</i> has no singular form).
   * @param integer|float $count [optional]
   *   The message's count, defaults to <code>1</code>.
   * @param array $args [optional]
   *   The message's arguments to insert into placeholder in <var>$plural</var> or <var>$singular</var>. Defaults to
   *   <code>NULL</code> (no replacements).
   * @return string
   *   The translated and formatted plural message.
   */
  public function tp($plural, $singular = null, $count = 1, array $args = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($plural) || is_string($plural) === false) {
      throw new \LogicException("\$plural cannot be empty and must be of type string");
    }
    if (isset($singular) && (empty($singular) || is_string($singular) === false)) {
      throw new \LogicException("\$singular cannot be empty and must be of type string");
    }
    if (is_numeric($count) === false) {
      throw new \LogicException("\$count must be numeric");
    }
    if (isset($args["@count"])) {
      throw new \LogicException("You cannot have a '@count' key in the arguments passed to I18n::tp()");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (!$singular) {
      $singular = $plural;
    }
    $args["@count"] = $count;
    return $this->translate("{@count, plural, one{{$singular}} other{{$plural}}}", $args, "messages");
  }

}
