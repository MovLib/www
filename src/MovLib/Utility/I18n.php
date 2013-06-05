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
namespace MovLib\Utility;

use \Collator;
use \IntlException;
use \Locale;
use \MovLib\Exception\I18nException;
use \MovLib\Utility\Log;
use \ResourceBundle;

/**
 * Translation (i18n) related methods.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class I18n {


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * code the deployment script will generate a server (in Apache httpd terms <tt>virtual host</tt>) with routes for
   * this code.
   *
   * @var array
   */
  public static $supportedLanguageCodes = [ "en", "de" ];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Create new I18n instance.
   *
   * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @param $locale
   *   [Optional] The desired locale, if no locale is passed the following procedure is executed:
   *   <ol>
   *     <li>Check if the user is logged in and has a preferred locale, if so redirect to dashboard.</li>
   *     <li>Check if the server sent a language code.</li>
   *     <li>Check if the user has provided an <tt>HTTP_ACCEPT_LANGUAGE</tt> header.</li>
   *     <li>Use default locale.</li>
   *   </ol>
   */
  public function __construct($locale = null) {
    global $user;
    if ($locale === null) {
      // If the user is logged in and has a preferred locale redirect to home.
      if (!isset($_SERVER["LANGUAGE_CODE"]) && $user->isLoggedIn && ($locale = $user->getLanguageCode())) {
        HTTP::redirect("/", 302, "{$locale}.{$_SERVER["SERVER_NAME"]}");
      }
      // If this is a subdomain, use that language code.
      if (isset($_SERVER["LANGUAGE_CODE"])) {
        $locale = $_SERVER["LANGUAGE_CODE"];
      }
      // If the user sent info on his preferred language via the appropriate HTTP header, use that language.
      elseif (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($tmpLocale = Locale::acceptFromHttp($_SERVER["HTTP_ACCEPT_LANGUAGE"])) && in_array($tmpLocale[0] . $tmpLocale[1], self::$supportedLanguageCodes)) {
        $locale = $tmpLocale;
      }
      // If no selection was possible, use the default locale.
      else {
        $locale = Locale::getDefault();
      }
    }
    $this->locale = $locale;
    $this->languageCode = $locale[0] . $locale[1];
    $this->defaultLocale = Locale::getDefault();
    $this->defaultLanguageCode = $this->defaultLocale[0] . $this->defaultLocale[1];
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Static Methods


  /**
   * Get the default ISO 639-1 alpha-2 language code extracted from <tt>php.ini</tt>.
   *
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
   * Translate the given pattern.
   *
   * @param string $route
   *   A simple string that should be translated or an advanced Intl ICU pattern. Read the official Intl ICU
   *   documentation for more information on how to create translation patterns.
   *
   *   <a href="http://userguide.icu-project.org/formatparse/messages">Formatting Messages</a>
   * @param array $args
   *   [Optional] Array of values to insert.
   * @param string $comment
   *   [Optional] A comment to include along with the pattern for translators.
   *
   *   <b>IMPORTANT!</b> This variable is unused in this class and only used during translation extraction. The reason
   *   why the declaration includes the parameter is easy, code completion and for new developers who might miss this
   *   feature if it would be undocumented at this place.
   * @param string $oldRoute
   *   [Optional] The old pattern of this translation if updating from old branch to new branch.
   *
   *   <b>IMPORTANT!</b> This variable is unused in this class and only used during translation extraction. The reason
   *   why the declaration includes the parameter is easy, code completion and for new developers who might miss this
   *   feature if it would be undocumented at this place.
   * @return string
   *   The translated and formatted message.
   * @throws \IntlException
   *   If fetching the translations and/or pattern from the current resource bundle failed.
   * @throws \MovLib\Exception\ErrorException
   *   If formatting the message with the given <var>$args</var> fails (only if any were passed).
   * @throws \MovLib\Exception\I18nException
   *   If the desired resource bundle does not exist. This will also add a log entry to the fatal log.
   */
  public function r($route, $args = null, $comment = null, $oldRoute = null) {
    return $this->formatMessage("Routes", $route, $args);
  }

  /**
   * Translate the given pattern.
   *
   * @param string $message
   *   A simple string that should be translated or an advanced Intl ICU pattern. Read the official Intl ICU
   *   documentation for more information on how to create translation patterns.
   *
   *   <a href="http://userguide.icu-project.org/formatparse/messages">Formatting Messages</a>
   * @param array $args
   *   [Optional] Array of values to insert.
   * @param string $comment
   *   [Optional] A comment to include along with the pattern for translators.
   *
   *   <b>IMPORTANT!</b> This variable is unused in this class and only used during translation extraction. The reason
   *   why the declaration includes the parameter is easy, code completion and for new developers who might miss this
   *   feature if it would be undocumented at this place.
   * @param string $oldMessage
   *   [Optional] The old pattern of this translation if updating from old branch to new branch.
   *
   *   <b>IMPORTANT!</b> This variable is unused in this class and only used during translation extraction. The reason
   *   why the declaration includes the parameter is easy, code completion and for new developers who might miss this
   *   feature if it would be undocumented at this place.
   * @return string
   *   The translated and formatted message.
   * @throws \IntlException
   *   If fetching the translations and/or pattern from the current resource bundle failed.
   * @throws \MovLib\Exception\ErrorException
   *   If formatting the message with the given <var>$args</var> fails (only if any were passed).
   * @throws \MovLib\Exception\I18nException
   *   If the desired resource bundle does not exist. This will also add a log entry to the fatal log.
   */
  public function t($message, $args = null, $comment = null, $oldMessage = null) {
    return $this->formatMessage("Translations", $message, $args);
  }

  /**
   * Get collator for the current language.
   *
   * @staticvar \Collator $collator
   *   Used to cache the collator.
   * @return \Collator
   * @throws \IntlException
   *   If instantiating of the collator failed (e.g. non supported locale).
   */
  public function getCollator() {
    static $collator = null;
    if ($collator === null) {
      $collator = new Collator($this->locale);
    }
    return $collator;
  }

  /**
   * Get all Intl ICU supported countries.
   *
   * <b>IMPORTANT!</b> The countries are sorted by their translated name for the current locale, not by code!
   *
   * Official ISO 3166-1 alpha-2 country codes: <pre>[ "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "ZA", "ZM", "ZW" ]</pre>
   *
   * @staticvar array $countries
   *   Used to cache the generated array.
   * @return array
   *   Associative array containing all countries. The key is the code and the value the translated name.
   * @throws \IntlException
   *   If fetching the countries from the current resource bundle failed.
   * @throws \MovLib\Exception\I18nException
   *   If the desired resource bundle does not exist. This will also add a log entry to the fatal log.
   */
  public function getCountries() {
    static $countries = null;
    if ($countries === null) {
      $countries = [];
      foreach ($this->getResourceBundle()->get("Countries") as $code => $country) {
        $countries[$code] = $country;
      }
      $this->getCollator()->asort($countries);
    }
    return $countries;
  }

  /**
   * Get all Intl ICU supported languages.
   *
   * <b>IMPORTANT!</b> The languages are sorted by their translated name for the current locale, not by code!
   *
   * @staticvar array $languages
   *   Used to cache the generated array.
   * @return array
   *   Associative array containing all languages. The key is the code and the value the translated name.
   * @throws \IntlException
   *   If fetching the languages from the current resource bundle failed.
   * @throws \MovLib\Exception\I18nException
   *   If the desired resource bundle does not exist. This will also add a log entry to the fatal log.
   */
  public function getLanguages() {
    static $languages = null;
    if ($languages === null) {
      $languages = [];
      foreach ($this->getResourceBundle()->get("Languages") as $code => $language) {
        $languages[$code] = $language;
      }
      $this->getCollator()->asort($languages);
    }
    return $languages;
  }


  // ------------------------------------------------------------------------------------------------------------------- Private Methods


  /**
   *
   * @param string $context
   *   The context is the offset within the Intl ICU data file where the translations are situated for this language.
   *
   * @param string $pattern
   *   A simple string that should be translated or an advanced Intl ICU pattern. Read the official Intl ICU
   *   documentation for more information on how to create translation patterns.
   *
   *   <a href="http://userguide.icu-project.org/formatparse/messages">Formatting Messages</a>
   * @param array $args
   *   [Optional] Array of values to insert.
   * @return string
   *   The translated and formatted message.
   * @throws \IntlException
   *   If fetching the translations and/or pattern from the current resource bundle failed.
   * @throws \MovLib\Exception\ErrorException
   *   If formatting the message with the given <var>$args</var> fails (only if any were passed).
   * @throws \MovLib\Exception\I18nException
   *   If the desired resource bundle does not exist. This will also add a log entry to the fatal log.
   */
  private function formatMessage($context, $pattern, $args) {
    // Only load the resource bundle if the current locale differs from our default locale.
    $message = $this->languageCode === $this->defaultLanguageCode ? $pattern : $this->getResourceBundle()->get($context)->get($pattern);
    // Only call the formatting function if there is something to replace within the pattern.
    if ($args) {
      return msgfmt_format_message($this->languageCode, $message, $args);
    }
    return $message;
  }

  /**
   * Get the ICU resource bundle for the current language.
   *
   * @staticvar \ResourceBundle $rb
   *   Used to cache the loaded resource bundle. We do not export the bundle to class scope because loading of the
   *   bundle happens on demand and not directly upon instantiation of the class itself.
   * @return \ResourceBundle
   * @throws \MovLib\Exception\I18nException
   *   If the desired resource bundle does not exist. This will also add a log entry to the fatal log.
   */
  private function getResourceBundle() {
    static $rb = null;
    if ($rb === null) {
      $bundle = $_SERVER["DOCUMENT_ROOT"] . "/translations/{$this->languageCode}";
      $ext = ".dat";
      try {
        $rb = new ResourceBundle($this->languageCode, $bundle);
      } catch (IntlException $e) {
        // Loading the bundle sometimes fails for no reason. Try again if the file exists.
        if (!file_exists($bundle . $ext)) {
          $e = new I18nException("Loading of resource bundle '{$this->languageCode}{$ext}' failed!", 0, $e);
          Log::logException($e, Log::LEVEL_FATAL);
          throw $e;
        }
        $rb = $this->getResourceBundle();
      }
    }
    return $rb;
  }

}
