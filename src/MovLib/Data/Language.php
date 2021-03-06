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
 * Represents a single language and provides an interface to all available languages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Language {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Language";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The language's ISO 639-1 code.
   *
   * @var string
   */
  public $code;

  /**
   * The language's translated full name.
   *
   * @var string
   */
  public $name;

  /**
   * The language's native full name.
   *
   * @var string
   */
  public $native;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language.
   *
   * @param string $code
   *   The ISO 639-1 code of the country. You can also pass <code>NULL</code> which will create an empty instance or
   *   <code>"xx"</code> which is a custom code for the ISO 639-2 code <code>"zxx"</code> and declares the <em>absence
   *   of linguistic information</em>.
   * @throws \ErrorException
   */
  public function __construct($code) {
    if ($code) {
      $languages  = self::getLanguages();
      if (!isset($languages[$code])) {
        throw new \OutOfBoundsException("Couldn't find language for code '{$code}'");
      }
      $this->code   = $code;
      $this->name   = $languages[$code]["name"];
      $this->native = $languages[$code]["native"];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all supported and translated languages.
   *
   * @staticvar array $languages
   *   Associative array used for caching.
   * @return array
   *   All supported and translated languages.
   */
  public static function getLanguages() {
    static $languages = null;
    if (!isset($languages[$i18n->locale])) {
      $languages[$i18n->locale] = require "{$kernel->pathTranslations}/language/{$i18n->locale}.php";
    }
    return $languages[$i18n->locale];
  }

}
