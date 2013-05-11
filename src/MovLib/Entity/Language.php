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
namespace MovLib\Entity;

use \MovLib\Exception\LanguageException;

/**
 * Description of Language
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Language {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The default gettext domain.
   *
   * @var string
   */
  const DOMAIN = "movlib";

  /**
   * The default gettext encoding.
   *
   * @var string
   */
  const ENCODING = "utf-8";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * List of all available locales.
   *
   * This array contains all details about all available locales. Only include locales that are actually installed on
   * the system.
   *
   * @var array
   */
  private $locales;

  /**
   * The ISO 639-1:2002 two letter language code.
   *
   * @link https://en.wikipedia.org/wiki/ISO_639-1
   * @var string
   */
  private $code;

  /**
   * The locale name as used by the operating system.
   *
   * Enter <code>locale -a</code> in the shell to list all available locales. To install a new locale enter <code>dpkg-
   * reconfigure locales</code>.
   *
   * @link http://wiki.debian.org/Locale
   * @var string
   */
  private $locale;

  /**
   * The direction of the language.
   *
   * @var string
   */
  private $dir;

  /**
   * The native name of the language.
   *
   * @var string
   */
  private $name;

  /**
   * The name of the language translated to the currently displayed language.
   *
   * @var string
   */
  private $translatedName;


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Initialize the locale environment.
   *
   * @throws LanguageException
   *   If the desired local is not available.
   */
  public function __construct() {
    $this->locales = [
      "en" => [ "name" => "English", "translatedName" => _("English"), "locale" => "en_US.UTF-8", "dir" => "ltr" ],
      "de" => [ "name" => "Deutsch", "translatedName" => _("German"), "locale" => "de_DE.UTF-8", "dir" => "ltr" ],
    ];

    $this->code = !empty($_SERVER["LANGUAGE_CODE"]) ? $_SERVER["LANGUAGE_CODE"] : "en";

    if (isset($this->locales[$this->code]) === false) {
      throw new LanguageException("Desired language code '{$this->code}' is not defined.");
    }

    foreach ($this->locales[$this->code] as $key => $value) {
      $this->{$key} = $value;
    }

    bindtextdomain(self::DOMAIN, $_SERVER["DOCUMENT_ROOT"] . "/translations");
    bind_textdomain_codeset(self::DOMAIN, self::ENCODING);
    textdomain(self::DOMAIN);
    putenv("LANG={$this->locale}");
    putenv("LC_ALL={$this->locale}");
    putenv("LANGUAGE={$this->locale}");

    if (setlocale(LC_ALL, $this->locale) === false) {
      throw new LanguageException("Desired locale '{$this->locale}' is not installed on this system.");
    }
  }

  /**
   * Get the array that contains all available locales.
   *
   * @return array
   */
  public function getLocales() {
    return $this->locales;
  }

  /**
   * Get the ISO 639-1:2002 two letter code of the language.
   *
   * @link https://en.wikipedia.org/wiki/ISO_639-1
   * @return string
   */
  public function getCode() {
    return $this->code;
  }

  /**
   * Get the systems locale name of the language.
   *
   * @return string
   */
  public function getLocale() {
    return $this->locale;
  }

  /**
   * Get the direction of the language.
   *
   * @return string
   */
  public function getDirection() {
    return $this->dir;
  }

  /**
   * Get the native name of the language (e.g. English, Deutsch, ...).
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get the translated name of the language (e.g. in English: German for Deutsch).
   *
   * @return string
   */
  public function getTranslatedName() {
    return $this->translatedName;
  }

}
