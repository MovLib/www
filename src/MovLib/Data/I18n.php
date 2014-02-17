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
class I18n {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Extended collator for locale aware sorting.
   *
   * @see \MovLib\Data\I18n::getCollator()
   * @var \MovLib\Data\Collator
   */
  protected $collator;

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
    $this->defaultLocale       = \Locale::getDefault();
    $this->defaultLanguageCode = "{$this->defaultLocale[0]}{$this->defaultLocale[1]}";

    // Use server determined language code if no locale was passed to the constructor. We can safely assume that this
    // variable is always set.
    if (!$locale) {
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
    return \MessageFormatter::formatMessage($this->locale, $message, $args);
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
    return \MessageFormatter::formatMessage($this->locale, "{0,number,integer} <abbr title='{$title}'>{$abbr}</abbr>", [ $bytes ]);
  }

  /**
   * Get collator for the current locale.
   *
   * @return \MovLib\Data\Collator
   *   If instantiating of the collator failed (e.g. non supported locale).
   */
  public function getCollator() {
    if (!$this->collator) {
      $this->collator = new \MovLib\Data\Collator($this->locale);
    }
    return $this->collator;
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
   * Format and translate the given route for singular forms.
   *
   * @global \MovLib\Kernel $kernel
   * @staticvar array $routes
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   Array of arguments that should be inserted into <var>$route</var>.
   * @param string $locale [optional]
   *   Use this locale to translate the route instead of the current display locale. Must be a valid system locale!
   * @return string
   *   The formatted and translated <var>$route</var>.
   * @throws \ErrorException
   * @throws \IntlException
   */
  public function r($route, array $args = null, $locale = null) {
    global $kernel;
    static $routes = [];
    if (!$locale) {
      $locale = $this->locale;
    }

    // We only need to translate the route if it isn't in the default locale.
    if ($locale != $this->defaultLocale) {
      // Check if we already have the route translations for this locale cached.
      if (!isset($routes[$locale])) {
        $routes[$locale] = require "{$kernel->pathTranslations}/routes/{$locale}.php";
      }

      // @devStart
      // @codeCoverageIgnoreStart
      if (isset($routes[$locale][$route]))
      // @codeCoverageIgnoreEnd
      // @devEnd
      $route = $routes[$locale][$route];
    }

    if ($args) {
      return \MessageFormatter::formatMessage($locale, $route, $args);
    }
    return $route;
  }

  /**
   * Format and translate the given route for plural forms.
   *
   * @global \MovLib\Kernel $kernel
   * @staticvar array $routes
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   Array of arguments that should be inserted into <var>$route</var>.
   * @param string $locale [optional]
   *   Use this locale to translate the route instead of the current display locale. Must be a valid system locale!
   * @return string
   *   The formatted and translated <var>$route</var>.
   * @throws \ErrorException
   * @throws \IntlException
   */
  public function rp($route, array $args = null, $locale = null) {
    global $kernel;
    static $routes = [];
    if (!$locale) {
      $locale = $this->locale;
    }

    // We only need to translate the route if it isn't in the default locale.
    if ($locale != $this->defaultLocale) {
      // Check if we already have the route translations for this locale cached.
      if (!isset($routes[$locale])) {
        $routes[$locale] = require "{$kernel->pathTranslations}/routes/{$locale}.plural.php";
      }

      // @devStart
      // @codeCoverageIgnoreStart
      if (isset($routes[$locale][$route]))
      // @codeCoverageIgnoreEnd
      // @devEnd
      $route = $routes[$locale][$route];
    }

    if ($args) {
      return \MessageFormatter::formatMessage($locale, $route, $args);
    }
    return $route;
  }

  /**
   * Format and translate the given message.
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
    global $db;
    static $messages = [];

    if ($this->locale != $this->defaultLocale) {
      if (!isset($messages[$this->locale][$message])) {
        $result = $db->query("SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR) FROM `messages` WHERE `message` = ? LIMIT 1", "ss", [ $this->languageCode, $message ])->get_result()->fetch_row();
        if (!empty($result[0])) {
          $messages[$this->locale][$message] = $result[0];
          $message                           = $result[0];
        }
        else {
          $messages[$this->locale][$message] = $message;
        }
      }
      else {
        $message = $messages[$this->locale][$message];
      }
    }

    if ($args) {
      return \MessageFormatter::formatMessage($this->locale, $message, $args);
    }
    return $message;
  }

}
