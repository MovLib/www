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
 * Represents a single country and provides an interface to all available countries.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Country {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The country's ISO 3166-1 alpha-2 code.
   *
   * @var string
   */
  public $code;

  /**
   * The country's translated full name.
   *
   * @var string
   */
  public $name;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new country.
   *
   * @param string $code
   *   The ISO 3166-1 alpha-2 code of the country. You can also pass <code>NULL</code> which will create an empty
   *   instance.
   * @throws \ErrorException
   */
  public function __construct($code) {
    if ($code) {
      $this->code = $code;
      $this->name = self::getCountries()[$code];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all supported and translated countries.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @staticvar array $countries
   *   Associative array used for caching.
   * @param array $filter [optional]
   *   Associative array where the keys are the country codes that should be returned.
   * @return array
   *   All supported and translated countries.
   */
  public static function getCountries(array $filter = null) {
    global $i18n, $kernel;
    static $countries = null;

    // Fetch (pre-sorted) and cache all available country's for the current display language.
    if (!isset($countries[$i18n->locale])) {
      $countries[$i18n->locale] = require "{$kernel->documentRoot}/private/icu/country/{$i18n->locale}.php";
    }

    // Filter the result if a filter was defined and make sure that we keep the sorting.
    if ($filter) {
      $filtered = [];
      foreach ($countries[$i18n->locale] as $code => $name) {
        if (isset($filter[$code])) {
          $filtered[$code] = $name;
        }
      }
      return $filtered;
    }

    return $countries[$i18n->locale];
  }

}
