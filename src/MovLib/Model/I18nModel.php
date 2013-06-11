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

use \Locale;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\ErrorException;
use \MovLib\Model\AbstractModel;
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
class I18nModel extends AbstractModel {


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
   * Create new i18n model instance.
   *
   * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @param $locale
   *   [Optional] The desired locale, if no locale is passed the following procedure is executed:
   *   <ol>
   *     <li>Check if the server sent a language code.</li>
   *     <li>Check if the user is logged in and has a preferred language.</li>
   *     <li>Check if the user has provided an <tt>HTTP_ACCEPT_LANGUAGE</tt> header.</li>
   *     <li>Use default locale.</li>
   *   </ol>
   */
  public function __construct($locale = null) {
    global $user;
    if ($locale === null) {
      (isset($_SERVER["LANGUAGE_CODE"]) && $locale = $_SERVER["LANGUAGE_CODE"])
      || ($user->isLoggedIn && $locale = $user->getLanguageCode())
      || (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($tmpLocale = Locale::acceptFromHttp($_SERVER["HTTP_ACCEPT_LANGUAGE"])) && in_array($tmpLocale[0] . $tmpLocale[1], self::$supportedLanguageCodes) && $locale = $tmpLocale)
      || ($locale = self::getDefaultLocale());
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
   * @todo Documentation
   * @param type $context
   * @param type $pattern
   * @param type $args
   * @param type $options
   * @return type
   */
  public function formatMessage($context, $pattern, $args, $options) {
    $languageCode = isset($options["language_code"]) ? $options["language_code"] : $this->languageCode;
    if ($languageCode !== $this->getDefaultLanguageCode()) {
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
   * Insert a pattern for translation into the database table identified by context.
   *
   * @param string $context
   *   The context of this translation, either <em>message</em> or <em>route</em>.
   * @param string $pattern
   *   The pattern to insert.
   * @param array $options
   *   Associative array to overwrite the default options used in this method. Possible keys are:
   *   <ul>
   *     <li><tt>comment</tt>: default is <tt>NULL</tt>.</li>
   *     <li><tt>old_pattern</tt>: default is <tt>NULL</tt>.</li>
   *   </ul>
   * @return \MovLib\Model\I18nModel
   */
  public function insertPattern($context, $pattern, $options) {
    $issetComment = isset($options["comment"]);
    $this->affectedRows = 0;
    if (isset($options["old_pattern"])) {
      if ($issetComment) {
        $this->update("{$context}s", "sss", [ $context => $pattern, "comment" => $options["comment"] ], [ "old_pattern" => $options["old_pattern"] ]);
      } else {
        $this->update("{$context}s", "ss", [ $context => $pattern ], [ "old_pattern" => $options["old_pattern"] ]);
      }
    }
    if ($this->affectedRows === 0) {
      try {
        // Maybe we already inserted this translation by a prior call to this method. This can happen if the same new
        // pattern occurres more than once on the same page.
        $this->select("SELECT `{$context}_id` FROM `{$context}s` WHERE `{$context}` = ? LIMIT 1", "s", [ $pattern ])[0];
      } catch (ErrorException $e) {
        unset($e);
        if ($issetComment) {
          $this->insert("{$context}s", "ss", [ $context => $pattern, "comment" => $options["comment"] ]);
        } else {
          $this->insert("{$context}s", "s", [ $context => $pattern ]);
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
   * @return $this
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
   * @param string $route
   *   A simple string that should be translated or an advanced Intl ICU pattern. Read the official Intl ICU
   *   documentation for more information on how to create translation patterns.
   *
   *   <a href="http://userguide.icu-project.org/formatparse/messages">Formatting Messages</a>
   * @param array $args
   *   [Optional] Array of values to insert.
   * @param string $options
   *   [Optional] Associative array to overwrite the default options used in this method in the form:
   *   <ul>
   *     <li><tt>language_code</tt>: default is to use the current display language code.</li>
   *     <li><tt>comment</tt>: default is <tt>NULL</tt>.</li>
   *     <li><tt>old_pattern</tt>: default is <tt>NULL</tt>.</li>
   *   </ul>
   * @return string
   *   URI: The absolute translated route.
   * @throws \MovLib\Exception\IntlException
   *   If formatting the message with the given <var>$args</var> fails (only if any were passed).
   */
  public function r($route, $args = null, $options = null) {
    if ($route !== "/") {
      $route = $this->formatMessage("route", $route, $args, $options);
    }
    return "https://{$_SERVER["SERVER_NAME"]}{$route}";
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
   *     <li><tt>language_code</tt>: default is to use the current display language code.</li>
   *     <li><tt>comment</tt>: default is <tt>NULL</tt>.</li>
   *     <li><tt>old_pattern</tt>: default is <tt>NULL</tt>.</li>
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
