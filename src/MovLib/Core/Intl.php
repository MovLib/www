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
namespace MovLib\Core;

/**
 * Defines the intl object.
 *
 * The intl object is responsible for everything that is related to translations and languages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m:2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Intl {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Intl";
  // @codingStandardsIgnoreEnd

  /**
   * Custom language code for non linguistic content (used in languages).
   *
   * @var string
   */
  const CODE_NON_LINGUISTIC = "--";

  /**
   * Custom language code for other content (used in languages and subtitles).
   *
   * @var string
   */
  const CODE_OTHER = "&&";

  /**
   * Custom language code for commentary content (used in subtitles).
   *
   * @var string
   */
  const CODE_COMMENTARY = "@@";

  /**
   * Custom language code for fact content (used in subtitles).
   *
   * @var string
   */
  const CODE_FACT = "§§";

  /**
   * The default ISO 639-1 alpha-2 language code.
   *
   * @var string
   */
  const DEFAULT_CODE = "en";

  /**
   * The default Intl ICU locale.
   *
   * @var string
   */
  const DEFAULT_LOCALE = "en_US";


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * {@see \NumberFormatter} cache.
   *
   * @var array
   */
  public static $numberFormatters = [];

  /**
   * All available system languages.
   *
   * Associative array containing all available system languages where the key is the ISO 639-1 code and the value the
   * corresponding Intl ICU locale.
   *
   * @var array
   */
  public static $systemLanguages = [
    "de" => "de_AT",
    "en" => "en_US",
  ];

  /**
   * Used to cache translations.
   *
   * @see ::getTranslations
   * @see ::translate
   * @var array
   */
  protected static $translations = [];


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Current ISO 639-1 alpha-2 code.
   *
   * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
   * @var string
   */
  public $code;

  /**
   * Default ISO 639-1 alpha-2 code.
   *
   * @see ::DEFAULT_CODE
   * @var string
   */
  public $defaultCode;

  /**
   * Default Intl ICU locale.
   *
   * @see ::DEFAULT_LOCALE
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
   * Current Intl ICU locale.
   *
   * @var string
   */
  public $locale;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new intl object.
   *
   * @param string $code
   *   ISO 639-1 alpha-2 system language code to use.
   * @throws \IntlException
   *   If <code>$code</code> isn't a valid system language's code.
   */
  public function __construct($code) {
    // Export default language code and locale to class scope. Providing public properties allows classes to directly
    // embed the code and locale in strings.
    $this->defaultCode   = self::DEFAULT_CODE;
    $this->defaultLocale = self::DEFAULT_LOCALE;

    // Export current language code and locale to class scope. This will fail if the provided code isn't within the
    // static system languages array because we try to access a non existent offset.
    try {
      $this->code   = $code;
      $this->locale = self::$systemLanguages[$code];
    }
    catch (\Exception $e) {
      $valid = implode(", ", array_keys(self::$systemLanguages));
      throw new \IntlException("The given code '{$code}' must be a valid system language's ISO 639-1 alpha-2 code. Valid codes are: {$valid}", null, $e);
    }
  }

  /**
   * Implements <code>serialize()</code> callback.
   *
   * @link http://php.net/language.oop5.magic#object.sleep
   * @return array
   *   Array containing the names of the properties that should be serialized.
   */
  public function __sleep() {
    return [ "code" ];
  }

  /**
   * Implements <code>unserialize()</code> callback.
   *
   * @link http://php.net/language.oop5.magic#object.wakeup
   */
  public function __wakeup() {
    $this->defaultCode   = self::DEFAULT_CODE;
    $this->defaultLocale = self::DEFAULT_LOCALE;
    $this->locale        = self::$systemLanguages[$this->code];
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Get instance in the current language.
   *
   * The <i>current language</i> is either the language provided by the server or the default language as defined in
   * this class. If you need an instance with a specific language simply instantiate it.
   *
   * @staticvar \MovLib\Core\Intl $instance
   *   Used to cache the instance in the current language.
   * @return \MovLib\Core\Intl
   *   Instance in the current language.
   */
  public static function getInstance() {
    static $instance;
    return $instance ?: ($instance = new Intl(isset($_SERVER["LANGUAGE_CODE"]) ? $_SERVER["LANGUAGE_CODE"] : self::DEFAULT_CODE));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format message.
   *
   * @see \MessageFormatter::formatMessage
   * @param string $message
   *   The message to format.
   * @param mixed $args
   *   The message arguments.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return string
   *   The formatted message.
   * @throws \IntlException
   *   If message formatting fails.
   */
  public function format($message, $args, $code = null) {
    return \MessageFormatter::formatMessage(self::$systemLanguages[$code ?: $this->code], $message, (array) $args);
  }

  /**
   * Get number formatted in human readable form.
   *
   * @param integer $bytes
   *   The number to format.
   * @return string
   *   Number formatted in human readable form.
   * @throws \IntlException
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
    return $this->format("{0,number,integer} <abbr title='{$title}'>{$abbr}</abbr>", [ $bytes ]);
  }

  /**
   * Format currency.
   *
   * @param mixed $amount
   *   The amount to format.
   * @param string $currency
   *   The currency's ISO 4217 alpha-3 code.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return string
   *   The formatted duration.
   * @throws \IntlException
   */
  public function formatCurrency($amount, $currency, $code = null) {
    $numberFormatter = $this->getNumberFormatter(\NumberFormatter::CURRENCY, $code);
    $formatted       = $numberFormatter->formatCurrency($amount, $currency);
    if ($formatted === false) {
      throw new \IntlException($numberFormatter->getErrorMessage(), $numberFormatter->getErrorCode());
    }
    return $formatted;
  }

  /**
   * Format decimal.
   *
   * @param mixed $decimal
   *   The decimal to format.
   * @param integer $minFraction [optional]
   *   The minimum fraction digits, defaults to <code>2</code>.
   * @param integer $maxFraction [optional]
   *   The maximum fraction digits, defaults to <code>6</code>.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return string
   *   The formatted duration.
   * @throws \IntlException
   */
  public function formatDecimal($decimal, $minFraction = 2, $maxFraction = 6, $code = null) {
    $numberFormatter = $this->getNumberFormatter(\NumberFormatter::DECIMAL, $code);
    $formatted       = $numberFormatter->format($decimal, \NumberFormatter::TYPE_DOUBLE);
    $numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $minFraction);
    $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $maxFraction);
    if ($formatted === false) {
      throw new \IntlException($numberFormatter->getErrorMessage(), $numberFormatter->getErrorCode());
    }
    return $formatted;
  }

  /**
   * Format duration.
   *
   * @param mixed $duration
   *   The duration to format.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return string
   *   The formatted duration.
   * @throws \IntlException
   */
  public function formatDuration($duration, $code = null) {
    // @todo The duration formatter only supports the English locale as of now. This is no problem for German.
    $numberFormatter = $this->getNumberFormatter(\NumberFormatter::DURATION, self::DEFAULT_CODE);
    $formatted       = $numberFormatter->format($duration, \NumberFormatter::TYPE_INT64);
    if ($formatted === false) {
      throw new \IntlException($numberFormatter->getErrorMessage(), $numberFormatter->getErrorCode());
    }
    return $formatted;
  }

  /**
   * Format integer.
   *
   * @param mixed $number
   *   The number to format.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return string
   *   The formatted integer.
   * @throws \IntlException
   */
  public function formatInteger($number, $code = null) {
    $numberFormatter = $this->getNumberFormatter(\NumberFormatter::DECIMAL, $code);
    $formatted       = $numberFormatter->format($number, \NumberFormatter::TYPE_INT64);
    if ($formatted === false) {
      throw new \IntlException($numberFormatter->getErrorMessage(), $numberFormatter->getErrorCode());
    }
    return $formatted;
  }

  /**
   * Format percentage.
   *
   * @param mixed $fraction
   *   The fraction to format.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return string
   *   The formatted integer.
   * @throws \IntlException
   */
  public function formatPercentage($fraction, $code = null) {
    $numberFormatter = $this->getNumberFormatter(\NumberFormatter::PERCENT, $code);
    $formatted       = $numberFormatter->format($fraction, \NumberFormatter::TYPE_DOUBLE);
    if ($formatted === false) {
      throw new \IntlException($numberFormatter->getErrorMessage(), $numberFormatter->getErrorCode());
    }
    return $formatted;
  }

  /**
   * Get {@see \NumberFormatter} for the current language.
   *
   * @param integer $style
   *   One of the {@link http://php.net/class.numberformatter#intl.numberformatter-constants.unumberformatstyle format
   *   style} constants.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to use for formatting, defaults to <code>NULL</code> and the
   *   language of the current instance is used for formatting.
   * @return \NumberFormatter
   *   {@see \NumberFormatter} for the current language.
   */
  public function getNumberFormatter($style, $code = null) {
    $code || ($code = $this->code);
    if (empty(self::$numberFormatters[$code][$style])) {
      self::$numberFormatters[$code][$style] = new \NumberFormatter(self::$systemLanguages[$code], $style);
    }
    return self::$numberFormatters[$code][$style];
  }

  /**
   * Get translations from file.
   *
   * @param string $filename
   *   The name of the file that contains the translations.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to get the translations for, defaults to <code>NULL</code> and the
   *   current instance's language is used.
   * @return array
   *   The translations from the file or an empty array if no translations are available.
   * @throws \IntlException
   */
  public function getTranslations($filename, $code = null) {
    $code || ($code = $this->code);
    try {
      // Nothing to do if we already have the translations cached for this entry.
      if (isset(self::$translations[$code][$filename])) {
        return self::$translations[$code][$filename];
      }

      // Build absolute path to the translation file.
      $file = "dr://var/intl/{$code}/{$filename}.php";

      // Only load the translation file if it really exists, some things don't need translation in the default locale
      // (e.g. routes) and others do (e.g. time zones).
      if (is_file($file)) {
        return (self::$translations[$code][$filename] = require $file);
      }

      // No cached entry and no file was loaded, create an empty index in the cache to speed up later look ups.
      return (self::$translations[$code][$filename] = []);
    }
    catch (\Exception $e) {
      throw new \IntlException("Couldn't get translations for '{$filename}'.", null, $e);
    }
  }

  /**
   * Translate and format route.
   *
   * @param string $route
   *   The route or query key pattern to translate.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, defaults to an empty array and the message
   *   formatter isn't used at all. You can pass either a single scalar value or an array.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to translate the route to, defaults to <code>NULL</code> and the
   *   current instance's language is used.
   * @return string
   *   The translated and formatted route or query key.
   * @throws \ErrorException
   *   If the given route is empty or a part of the route is empty.
   */
  public function r($route, $args = [], $code = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($route), "A route cannot be empty!");
    // @codeCoverageIgnoreEnd
    // @devEnd
    // Nothing to do if this is the index route.
    if ($route === "/") {
      return $route;
    }
    return $this->translate($route, $args, "routes", $code);
  }

  /**
   * Format and translate given message.
   *
   * @param string $message
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, defaults to an empty array and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to translate the message to, defaults to <code>NULL</code> and the
   *   current instance's language is used.
   * @return string
   *   The formatted and translated <var>$message</var>.
   * @throws \IntlException
   */
  public function t($message, $args = [], $code = null) {
    return $this->translate($message, $args, "messages", $code);
  }

  /**
   * Attempt to create an automated titlecase converter.
   *
   * We use a very simplified rule of thumb for all languages:
   * <ul>
   *   <li>All uppercase words are <b>never</b> touched at all</li>
   *   <li>First and last words are always title cased</li>
   *   <li>Words with more thant four characters are title cased</li>
   * </ul>
   *
   * @link http://grammar.about.com/od/grammarfaq/f/capitalstitle.htm
   * @param string $title
   *   The title to convert.
   * @return string
   *   The title in titlecase.
   */
  public function titleCase($title) {
    if (empty($title)) {
      return $title;
    }
    $titleCase = explode(" ", $title);
    $c         = count($titleCase) - 1;
    for ($i = 0; $i <= $c; ++$i) {
      if (($i === 0 || $i === $c || mb_strlen($titleCase[$i]) > 4) && mb_strtoupper($titleCase[$i]) !== $titleCase[$i]) {
        $titleCase[$i] = mb_convert_case($titleCase[$i], MB_CASE_TITLE);
      }
    }
    return implode(" ", $titleCase);
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
   * @param integer|float $count
   *   The message's count, note that you cannot use offset <code>0</code> in the <var>$args</var> if you use this
   *   method. If you need to force the plural form for whatever reason simply pass <code>-1</code> because this value
   *   is safe in all languages and will force the plural form.
   * @param string $plural
   *   The message's plural form to format and translate.
   * @param string $singular [optional]
   *   The message's singular form to format and translate, defaults to <code>NULL</code> which means that the given
   *   <var>$plural</var> is also used for the singular form (e.g. the English word <i>Series</i> has no singular form).
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, defaults to an empty array and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to translate the singular/plural to, defaults to <code>NULL</code>
   *   and the current instance's language is used.
   * @return string
   *   The translated and formatted plural message.
   * @throws \IntlException
   */
  public function tp($count, $plural, $singular = null, $args = [], $code = null) {
    $singular || ($singular = $plural);
    $args && ($args = (array) $args);
    $args[0] = $count;
    return $this->translate("{0,plural,=1{{$singular}}other{{$plural}}}", $args, "messages", $code);
  }

  /**
   * Translate and format given message with given context.
   *
   * @param string $pattern
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, default to <code>NULL</code> and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $context
   *   The translations context (e.g. <code>"routes/singular"</code> or <code>"countries"</code>) if you pass
   *   <code>NULL</code> the database is asked.
   * @param null|string $code
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted pattern.
   * @throws \IntlException
   */
  public function translate($pattern, $args, $context, $code) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($pattern), "The pattern cannot be empty.");
    assert(strpos($pattern, "'") === false, "Always use the real English apostrophe ( ’ )!");
    assert(strpos($pattern, '"') === false, "Always use the real English quotation marks ( “ opening, ” closing)!");
    $placeholderPattern = "\{[0-9a-z_\- ]*\}";
    assert(
      preg_match("/^\s*{$placeholderPattern}(\s*{$placeholderPattern})*\s*$/i", $pattern) !== 1,
      "A translation that contains placeholder tokens only doesn't make sense. Don't misuse the message formatter to " .
      "format you strings, the right tool for the job! You may use sprintf() for fancy formatting."
    );
    assert(
      preg_match("/(plural|select)/", $pattern) === 1 || preg_match("/\{[a-z0-9_]*[A-Z|\-| ]+[a-z0-9_]*\}/", $pattern) !== 1,
      "Always use snake case for intl placeholder tokens."
    );
    assert(
      strpos($pattern, "...") === false,
      "Don't simply use three dots in a row, use the actual Unicode character with proper semantic: …"
    );
    assert(strip_tags($pattern) === $pattern, "HTML is not allowed in translation patterns.");
    // Allow passing NULL, 0, "false", false, ... etc.
    assert(!$args || !empty($args), "Message formatter arguments cannot be empty if given.");
    assert(!empty($context), "Translation context cannot be empty.");
    assert(is_string($context), "Translation context must be of type string.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $code || ($code = $this->code);
    // Only attempt to translate the pattern if we have no cached translation.
    if (empty(self::$translations[$code][$context][$pattern])) {
      $this->getTranslations($context, $code);

      // Check if we have a translation for this pattern. If we have none this either means that the pattern is in the
      // default locale and has no translations (e.g. time zones have translations in the default locale as well, but
      // most other things have no default locale translations). We insert the given pattern in this case to speed up
      // later look ups.
      if (empty(self::$translations[$code][$context][$pattern])) {
        self::$translations[$code][$context][$pattern] = $pattern;
      }
    }

    if (empty($args)) {
      return self::$translations[$code][$context][$pattern];
    }
    return \MessageFormatter::formatMessage(self::$systemLanguages[$code], self::$translations[$code][$context][$pattern], (array) $args);
  }

  /**
   * Transliterate from any script to the current locale.
   *
   * @staticvar array $transliterators
   *   Used to cache transliterator instances.
   * @param string $text
   *   The text to transliterate.
   * @param string $code [optional]
   *   The system language's ISO 639-1 alpha-2 code to transliterate to, defaults to <code>NULL</code> and the current
   *   instances language is used.
   * @return string
   *   The transliterated string.
   * @throws \IntlException
   */
  public function transliterate($text, $code = null) {
    static $transliterators = [];
    $code || ($code = $this->code);
    if (empty($transliterators[$code])) {
      $transliterators[$code] = \Transliterator::create("Any-{$this::$systemLanguages[$code]}");
    }
    return $transliterators[$code]->transliterate($text);
  }

}
