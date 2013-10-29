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
 * Represents a single system language.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SystemLanguage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The system language's domain.
   *
   * @var string
   */
  public $domain;

  /**
   * The system language's ISO alpha-2 code.
   *
   * @var string
   */
  public $languageCode;

  /**
   * The system language's locale.
   *
   * @var string
   */
  public $locale;

  /**
   * The system language's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The system language's name.
   *
   * @var string
   */
  public $nameNative;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new system language.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param string $languageCode
   *   This system language's language code.
   */
  public function __construct($languageCode) {
    global $i18n, $kernel;
    $this->domain       = "{$languageCode}.{$kernel->domainDefault}";
    $this->languageCode = $languageCode;
    $this->locale       = $kernel->systemLanguages[$languageCode];
    $this->name         = \Locale::getDisplayLanguage($this->languageCode, $i18n->locale);
    $this->nameNative   = \Locale::getDisplayLanguage($this->languageCode, $this->locale);
  }

}
