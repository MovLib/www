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

use \MovLib\Exception\DatabaseException;

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
final class Intl extends \MovLib\Core\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Custom language code for non linguistic content.
   *
   * Used in languages.
   *
   * @see \MovLib\Stub\Data\Language
   * @see \MovLib\Presentation\Partial\Language
   * @see \MovLib\Tool\Console\Command\Install\SeedLanguages
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
   * @see \MovLib\Tool\Console\Command\Install\SeedLanguages
   * @see \MovLib\Stub\Data\Subtitle
   * @see \MovLib\Presentation\Partial\Subtitle
   * @see \MovLib\Tool\Console\Command\Install\SeedSubtitles
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
   * @see \MovLib\Tool\Console\Command\Install\SeedSubtitles
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
   * @see \MovLib\Tool\Console\Command\Install\SeedSubtitles
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
   * Instantiate new I18n object.
   *
   * @param string $locale [optional]
   *   The locale of this instance.
   * @param string $defaultLocale
   *   The default system locale.
   * @param array $systemLocales
   *   All available locales of this system, the associative array's keys must be the language code and the value the
   *   locale.
   * @throws \InvalidArgumentException
   */
  public function __construct($locale, $defaultLocale, array $systemLocales) {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "locale", "defaultLocale" ] as $param) {
      if (empty(${$param}) || !is_string(${$param})) {
        throw new \InvalidArgumentException("\${$param} cannot be empty and must be of type string.");
      }
    }
    if (empty($systemLocales)) {
      throw new \InvalidArgumentException("\$systemLocales cannot be empty.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->defaultLocale       = $defaultLocale;
    $this->defaultLanguageCode = "{$defaultLocale[0]}{$defaultLocale[1]}";
    $this->systemLocales       = $systemLocales;
    $this->setLocale($locale);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


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

      // No cached entry and not file was loaded, create an empty index in the cache to speed up later look ups.
      return (self::$translations[$locale][$filename] = []);
    }
    catch (\Exception $e) {
      throw new \IntlException("Couldn't get translations for '{$filename}'.", null, $e);
    }
  }

  /**
   * Insert message pattern.
   *
   * @param string $message
   *   The message to insert.
   * @param array $comment [optional]
   *   Comment that should be added to the message, a comment should help translators to understand how they should
   *   translate the message. Usually your messages should be self-explanatory. Therefore this parameter defaults to
   *   <code>NULL</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insertMessage($message, $comment = null) {
    $this->insertMessages([[ "message" => $message, $comment => null ]]);
    return $this;
  }

  /**
   * Insert multiple messages at once.
   *
   * @param array $messages
   *   Array arrays that contain the key <code>"message"</code> and optionally <code>"comment"</code>.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function insertMessages(array $messages) {
    if (!($stmt = $this->mysqli->prepare("INSERT INTO `messages` (`message`, `comment`, `dyn_translations`) VALUES (?, ?, '')"))) {
      throw new DatabaseException;
    }
    foreach ($messages as list($message, $comment)) {
      if (!$stmt->bind_param("ss", $message, empty($comment) ? null : $comment) || !$stmt->execute()) {
        throw new DatabaseException;
      }
    }
    $stmt->close();
    return $this;
  }

  /**
   * Translate and format singular route.
   *
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   The message formatter arguments.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted singular route.
   * @throws \IntlException
   */
  public function r($route, array $args = null, $locale = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if ($route == "/") {
      throw new \LogicException("Translating the root route '/' makes no sense");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this->translate($route, $args, "routes/singular", $locale);
  }

  /**
   * Translate and format plural route.
   *
   * @param string $route
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   The message formatter arguments.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted plural route.
   * @throws \IntlException
   */
  public function rp($route, array $args = null, $locale = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if ($route == "/") {
      throw new \LogicException("Translating the root route '/' makes no sense");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return $this->translate($route, $args, "routes/plural", $locale);
  }

  /**
   * Set the locale.
   *
   * @param string $locale [optional]
   *   The locale to set, you can also pass a language code.
   * @return this
   * @throws \InvalidArgumentException
   */
  public function setLocale($locale) {
    if (isset($this->systemLocales[$locale])) {
      $this->locale       = $this->systemLocales[$locale];
      $this->languageCode = $locale;
    }
    elseif (in_array($locale, $this->systemLocales)) {
      $this->locale       = $locale;
      $this->languageCode = "{$locale[0]}{$locale[1]}";
    }
    else {
      throw new \InvalidArgumentException(
        "\$locale ({$locale}) must be a valid system locale: " . implode(", ", $this->systemLocales)
      );
    }
    return $this;
  }

  /**
   * Format and translate given message.
   *
   * @param string $message
   *   The translation pattern in {@link http://userguide.icu-project.org/formatparse/messages ICU message format}.
   * @param array $args [optional]
   *   Numeric array of arguments that should be inserted into <var>$message</var>.
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The formatted and translated <var>$message</var>.
   * @throws \IntlException
   */
  public function t($message, array $args = null, $locale = null) {
    return $this->translate($message, $args, "messages", $locale);
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
   * @param string $locale [optional]
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted plural message.
   * @throws \IntlException
   */
  public function tp($plural, $singular = null, $count = 1, array $args = null, $locale = null) {
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
    $args["@count"] = $count;
    return $this->translate("{@count, plural, one{{$singular}} other{{$plural}}}", $args, "messages", $locale);
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
   * @param null|string $locale
   *   Use a different locale for this translation.
   * @return string
   *   The translated and formatted pattern.
   * @throws \IntlException
   */
  public function translate($pattern, $args, $context, $locale) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($pattern));
    assert(is_string($pattern));
    if (isset($args)) {
      assert(!empty($args));
    }
    assert(!empty($context));
    assert(is_string($context));
    if (isset($locale)) {
      assert(preg_match("/[a-z]{2}[_\-][a-z]{2}/i", $locale));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    try {
      if (!$locale) {
        $locale = $this->locale;
      }
      // Only attempt to translate the pattern if we have no translation already cached.
      if (empty(self::$translations[$locale][$context][$pattern])) {
        // Fetch translations from database for message context.
        if ($context == "messages") {
          $stmt = $this->query(
            "SELECT COLUMN_GET(`dyn_translations`, ? AS CHAR) FROM `messages` WHERE `message` = ? LIMIT 1",
            "ss",
            [ "{$locale[0]}{$locale[1]}", $pattern ]
          );
          list(self::$translations[$locale][$context][$pattern]) = $stmt->get_result()->fetch_row();
          $stmt->close();
        }
        // Fetch translations from file for everything else, we don't need the returned value because we're directly
        // accessing the internal caching array.
        else {
          $this->getTranslations($context, $locale);
        }

        // Check if we have a translation for this pattern. If we have none this either means that the pattern is in the
        // default locale and has no translations (e.g. time zones have translations in the default locale as well, but
        // most other things have no default locale translations). We insert the given pattern in this case to speed up
        // later look ups.
        if (empty(self::$translations[$locale][$context][$pattern])) {
          self::$translations[$locale][$context][$pattern] = $pattern;
        }
      }

      if ($args) {
        return \MessageFormatter::formatMessage($locale, self::$translations[$locale][$context][$pattern], $args);
      }
      return self::$translations[$locale][$context][$pattern];
    }
    catch (\Exception $e) {
      throw new \IntlException("Couldn't translate '{$pattern}'.", null, $e);
    }
  }

}
