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
final class Intl {


  // ------------------------------------------------------------------------------------------------------------------- Constants


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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The default language code.
   *
   * @var string
   */
  public $defaultLanguageCode;

  /**
   * The default locale.
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
   * All available system locales.
   *
   * @var array
   */
  public $systemLocales;

  /**
   * Used to cache translations.
   *
   * @see Intl::getTranslations()
   * @see Intl::translate()
   * @var array
   */
  protected static $translations = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new intl object.
   *
   * @param \MovLib\Core\Config $config
   *   The active config instance.
   * @param string $language
   *   An ISO 639-1 code (preferred) or a locale, defaults to <code>NULL</code> (default system locale from config will
   *   be used).
   * @throws \InvalidArgumentException
   *   If <var>$locale</var> contains a malformed or unsupported locale/language code.
   */
  final public function __construct(\MovLib\Core\Config $config, $language = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(
      !empty($config->defaultLanguageCode) && !empty($config->defaultLocale) && !empty($config->locales),
      "Please execute `movadmin seed-languages` (or simply `make` in the document root)."
    );
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->defaultLanguageCode = $config->defaultLanguageCode;
    $this->defaultLocale       = $config->defaultLocale;
    $this->systemLocales       = $config->locales;
    if (!$language) {
      $language = $config->defaultLanguageCode;
    }
    $this->setLocale($language);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a message without translation, very useful for inline number formatting.
   *
   * @param string $message
   *   The message to format.
   * @param mixed $args
   *   The message arguments.
   * @return string
   *   The formatted message.
   */
  public function format($message, $args) {
    return \MessageFormatter::formatMessage($this->locale, $message, (array) $args);
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
   * Format the given value with the given currency.
   *
   * @param mixed $value
   *   The value to format.
   * @param string $currencyCode
   *   The currency code that should be used to format the value.
   * @param string $locale [optional]
   *   Use a different locale for this formatted currency.
   * @return string
   *   The formatted currency.
   */
  public function formatCurrency($value, $currencyCode, $locale = null) {
    static $fmts = [];
    if (!$locale) {
      $locale = $this->locale;
    }
    if (empty($fmts[$locale])) {
      $fmts[$locale] = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
    }
    return $fmts[$locale]->formatCurrency($value, $currencyCode);
  }

  /**
   * Get translations from file.
   *
   * @param string $filename
   *   The name of the file that contains the translations.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return array
   *   The translations from the file or an empty array if no translations are available.
   * @throws \IntlException
   */
  public function getTranslations($filename, $locale = null) {
    try {
      if (!$locale) {
        $locale = $this->locale;
      }

      // Nothing to do if we already have the translations cached for this entry.
      if (isset(self::$translations[$locale][$filename])) {
        return self::$translations[$locale][$filename];
      }

      // Build absolute path to the translation file.
      $file = "dr://var/intl/{$locale}/{$filename}.php";

      // Only load the translation file if it really exists, some things don't need translation in the default locale
      // (e.g. routes) and others do (e.g. time zones).
      if (is_file($file)) {
        return (self::$translations[$locale][$filename] = require $file);
      }

      // No cached entry and no file was loaded, create an empty index in the cache to speed up later look ups.
      return (self::$translations[$locale][$filename] = []);
    }
    catch (\Exception $e) {
      throw new \IntlException("Couldn't get translations for '{$filename}'.", null, $e);
    }
  }

  /**
   * Translate and format singular route.
   *
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, default to <code>NULL</code> and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted singular route.
   * @throws \IntlException
   */
  public function r($route, $args = null, $locale = null) {
    if ($route == "/") {
      return "/";
    }
    if ($route === (array) $route) {
      $routeArray = $route;
      $route = "";
      $c = count($routeArray);
      for ($i = 0; $i < $c; ++$i) {
        $route .= $this->translate($routeArray[$i], $args, "routes/singular", $locale);
      }
      return $route;
    }
    return $this->translate($route, $args, "routes/singular", $locale);
  }

  /**
   * Translate and format route or query key.
   *
   * This is the new routing method that combines handling of plural and singular routes and allows totally automated
   * building of routes based on their parts. This allows us to minimize the translation effort that it takes to
   * translate routes. There is a simply rule that applies to any route, it's unique an can only present a single page,
   * not only for SEO reasons, but also for class hierarchy reasons. We use this fact and the fact that singular route
   * parts always have some kind of message formatter placeholder appended to combine singular and plural forms of
   * routes into a single file. This minimizes IO and we can combine as many translations as possible, thus only have
   * to translte keys a single time.
   *
   * @todo Most route parts match translations that we already have in the messages, we should utilize this fact to
   *       auto-translate as many route parts as possible. Passing a translation through
   *       {@see \MovLib\Core\FileSystem::sanitizeFilename()} will always give us a correct translation.
   *
   * @staticvar array $routes
   *   Used to cache translated route patterns.
   * @param string $route
   *   The route or query key pattern to translate.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, defaults to <code>NULL</code> and the message
   *   formatter isn't used at all. You can pass either a single scalar value or an array.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted route or query key.
   * @throws \ErrorException
   *   If the given route is empty or a part of the route is empty.
   */
  public function route($route, $args = null, $locale = null) {
    static $routes = [];
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($route), "A route cannot be empty!");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Nothing to do if this is the index route.
    if ($route == "/") {
      return $route;
    }

    // The route is a query key if it isn't starting with a slash.
    if ($route{0} != "/") {
      return $this->translate($route, $args, "routes", $locale);
    }

    // We need another level of caching above the translate method because we don't want to repeat the building of the
    // route parts on each request.
    $locale || ($locale = $this->locale);
    if (empty($routes[$locale][$route])) {
      // Ensure that the array for the offset is actually present and directly create the entry.
      empty($routes) && ($routes[$locale] = []);
      $routes[$locale][$route] = "";

      // Split the given route at the only character that we can be certain of that it exists and initialize the
      // variables that we need to build the translated version of this route.
      $parts = explode("/", $route);
      $c     = count($parts);
      $token = null;

      // The first element is always empty because a route always starts with a slash, also see above condition that
      // checks if the first character is actually a slash. Therefore we directly decrease the counter variable before
      // we enter the first loop and jump right over that index.
      while (--$c) {
        // If this part of the route starts with a curly brace it's an Intl placeholder token that we want to append to
        // the upcoming part of the route.
        if ($parts[$c]{0} == "{") {
          $token = "/{$parts[$c]}";
        }
        // If it isn't starting with a curly brace it's an actual translateable string that we have to pass to our
        // translate method. Note that we're going backwards here, therefore we have to append the already translated
        // route parts to the untranslated route parts to re-generate the original order.
        else {
          $routes[$locale][$route] = "{$this->translate("/{$parts[$c]}{$token}", null, "routes", $locale)}{$routes[$locale][$route]}";
          // Reset the token!
          $token = null;
        }
      }
    }

    // We have to replace the arguments ourself, because we're caching already translated routes. Pretty much the same
    // as is happening in our translate method.
    if ($args) {
      return \MessageFormatter::formatMessage($locale, $routes[$locale][$route], (array) $args);
    }
    return $routes[$locale][$route];
  }

  /**
   * Translate and format plural route.
   *
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, default to <code>NULL</code> and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted plural route.
   * @throws \IntlException
   */
  public function rp($route, $args = null, $locale = null) {
    if ($route == "/") {
      return "/";
    }
    return $this->translate($route, $args, "routes/plural", $locale);
  }

  /**
   * Set the locale.
   *
   * @param string $language
   *   An ISO 639-1 code (preferred) or a locale, defaults to <code>NULL</code> (default system locale from config will
   *   be used).
   * @return this
   * @throws \InvalidArgumentException
   */
  public function setLocale($language) {
    if (isset($this->systemLocales[$language])) {
      $this->locale       = $this->systemLocales[$language];
      $this->languageCode = $language;
    }
    elseif (in_array($language, $this->systemLocales)) {
      $this->locale       = $language;
      $this->languageCode = "{$language[0]}{$language[1]}";
    }
    else {
      throw new \InvalidArgumentException(
        "\$locale ({$language}) must be a valid system locale: " . implode(", ", $this->systemLocales)
      );
    }
    return $this;
  }

  /**
   * Format and translate given message.
   *
   * @param string $message
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, default to <code>NULL</code> and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The formatted and translated <var>$message</var>.
   * @throws \IntlException
   */
  public function t($message, $args = null, $locale = null) {
    return $this->translate($message, $args, "messages", $locale);
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
    $c = count($titleCase) - 1;
    for ($i = 0; $i <= $c; ++$i) {
      if (($i === 0 || $i === $c || mb_strlen($titleCase[$i]) > 4) && mb_strtoupper($titleCase[$i]) != $titleCase[$i]) {
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
   * @param string $plural
   *   The message's plural form to format and translate.
   * @param string $singular [optional]
   *   The message's singular form to format and translate, defaults to <code>NULL</code> which means that the given
   *   <var>$plural</var> is also used for the singular form (e.g. the English word <i>Series</i> has no singular form).
   * @param integer|float $count [optional]
   *   The message's count, defaults to <code>1</code>.
   * @param mixed $args [optional]
   *   The arguments that should be passed to the message formatter, default to <code>NULL</code> and the message
   *   formatter isn't used at all. You can pass either a single scaler value or an array.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted plural message.
   * @throws \IntlException
   */
  public function tp($plural, $singular = null, $count = 1, $args = null, $locale = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($plural));
    assert(is_string($plural));
    if (isset($singular)) {
      assert(!empty($singular));
      assert(is_string($singular));
    }
    assert(is_numeric($count));
    assert(empty($args["@count"]));
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (empty($singular)) {
      $singular = $plural;
    }
    if ($args) {
      $args = (array) $args;
    }
    $args["@count"] = $count;
    return $this->translate("{@count, plural, one{{$singular}} other{{$plural}}}", $args, "messages", $locale);
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
   * @param null|string $locale
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted pattern.
   * @throws \IntlException
   */
  public function translate($pattern, $args, $context, $locale) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($pattern), "The pattern cannot be empty.");
    assert(strpos("'", $pattern) === false, "Always use the real English apostrophe ( ’ )!");
    assert(strpos('"', $pattern) === false, "Always use the real English quotation marks ( “ opening, ” closing)!");
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
    assert(strip_tags($pattern) == $pattern, "HTML is not allowed in translation patterns.");
    assert(!isset($args) || !empty($args));
    assert(!empty($context));
    assert(is_string($context));
    assert(!isset($locale) || preg_match("/[a-z]{2}[_\-][a-z]{2}/i", $locale));
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      if (!$locale) {
        $locale = $this->locale;
      }
      // Only attempt to translate the pattern if we have no cached translation.
      if (empty(self::$translations[$locale][$context][$pattern])) {
        $this->getTranslations($context, $locale);

        // Check if we have a translation for this pattern. If we have none this either means that the pattern is in the
        // default locale and has no translations (e.g. time zones have translations in the default locale as well, but
        // most other things have no default locale translations). We insert the given pattern in this case to speed up
        // later look ups.
        if (empty(self::$translations[$locale][$context][$pattern])) {
          self::$translations[$locale][$context][$pattern] = $pattern;
        }
      }

      if ($args) {
        return \MessageFormatter::formatMessage($locale, self::$translations[$locale][$context][$pattern], (array) $args);
      }
      return self::$translations[$locale][$context][$pattern];
    }
    catch (\Exception $e) {
      throw new \IntlException("Couldn't translate '{$pattern}'.", null, $e);
    }
  }

  /**
   * Transliterate from any script to the current locale.
   *
   * @staticvar array $transliterators
   *   Used to cache transliterator instances.
   * @param string $text
   *   The text to transliterate.
   * @param null|string $locale [optional]
   *   Use a different locale for this translation.
   * @return null|string
   *   <code>NULL</code> if the transliterated string is an exact match of the given <var>$text</var>, otherwise the
   *   transliterated text is returned.
   * @throws \IntlException
   */
  public function transliterate($text, $locale = null) {
    static $transliterators = [];
    if (!$locale) {
      $locale = $this->locale;
    }
    if (empty($transliterators[$locale])) {
      $transliterators[$locale] = \Transliterator::create("Any-{$locale}");
    }
    if (($transliterated = $transliterators[$locale]->transliterate($text)) != $text) {
      return $transliterated;
    }
  }

}
