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
 * Represents a single currency and provides an interface to all available currencies.
 *
 * @link https://en.wikipedia.org/wiki/ISO_4217
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Currency {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The currency's ISO 4217 alpha-3 code.
   *
   * @var string
   */
  public $code;

  /**
   * The currency's translated full name.
   *
   * @var string
   */
  public $name;

  /**
   * The currency's symbol.
   *
   * @var string
   */
  public $symbol;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new currency.
   *
   * @param string $code
   *   The ISO 4217 alpha-3 code of the currency. You can also pass <code>NULL</code> which will create an empty
   *   instance.
   * @throws \OutOfBoundsException
   */
  public function __construct($code) {
    if ($code) {
      $currencies   = self::getCurrencies();
      if (!isset($currencies[$code])) {
        throw new \OutOfBoundsException("Couldn't find currency for code '{$code}'");
      }
      $this->code   = $code;
      $this->name   = $currencies[$code]["name"];
      $this->symbol = $currencies[$code]["symbol"];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format the given value.
   *
   * @param integer|float $value
   *   The value to format.
   * @return string
   *   The formatted value.
   */
  public function format($value) {
    return self::getFormatter()->formatCurrency($value, $this->code);
  }

  /**
   * Get the ISO 4217 alpha-3 code of the default locale.
   *
   * @return string
   *   The ISO 4217 alpha-3 code of the default locale.
   */
  public static function getDefaultCode() {
    return self::getFormatter($i18n->defaultLocale)->getTextAttribute(\NumberFormatter::CURRENCY_CODE);
  }

  /**
   * Get currency formatter for current locale.
   *
   * @staticvar array $nf
   *   Associative array used for caching.
   * @return \NumberFormatter
   *   Number formatter for the current locale.
   */
  public static function getFormatter() {
    static $nf = null;
    if (!isset($nf[$i18n->locale])) {
      $nf[$i18n->locale] = new \NumberFormatter($i18n->locale, \NumberFormatter::CURRENCY);
    }
    return $nf[$i18n->locale];
  }

  /**
   * Get all supported and translated currencies.
   *
   * @staticvar array $currencies
   *   Associative array used for caching.
   * @return array
   *   All supported and translated currencies.
   */
  public static function getCurrencies() {
    static $currencies = null;
    if (!isset($currencies[$i18n->locale])) {
      $currencies[$i18n->locale] = require "{$kernel->pathTranslations}/currency/{$i18n->locale}.php";
    }
    return $currencies[$i18n->locale];
  }

}
