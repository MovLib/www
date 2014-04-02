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
namespace MovLib\Console\Command\Install;

use \Collator;

/**
 * Seed currencies.
 *
 * The translated languages are used for audio formats of the various releases and contain two special language codes.
 * One for silent movies and one for other languages. The first one should be obvious and the later is meant for dead
 * languages or other special language, like <i>Hebrew</i> in <i>The Passion Of The Christ</i> or <i>Klingon</i> in
 * <i>Star Trek</i>. Users have to specify these special languages in the notes section of a release.
 *
 * We don't use ISO 639-2 in our application because nearly no software works together with them (W3C standards and
 * parsers built upon them, Intl ICU, ...).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedCurrencies extends \MovLib\Console\Command\Install\AbstractIntlCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * All available ISO 4217 currency codes.
   *
   * @var array
   */
  protected $codes = [
    "ADP", "AED", "AFN", "ALL", "AMD", "ANG", "AOA", "ARS", "ATS", "AUD", "AWG", "AZN",
    "BAM", "BBD", "BDT", "BEF", "BGN", "BHD", "BIF", "BMD", "BND", "BOB", "BRL", "BSD", "BTN", "BWP", "BYR", "BZD",
    "CAD", "CDF", "CHF", "CLP", "CNY", "COP", "CRC", "CUC", "CUP", "CVE", "CYP", "CZK",
    "DEM", "DJF", "DKK", "DOP", "DZD",
    "EEK", "EGP", "ERN", "ESP", "ETB", "EUR",
    "FIM", "FJD", "FKP", "FRF",
    "GBP", "GEL", "GHS", "GIP", "GMD", "GNF", "GRD", "GTQ", "GYD",
    "HKD", "HNL", "HRK", "HTG", "HUF",
    "IDR", "IEP", "ILS", "INR", "IQD", "IRR", "ISK", "ITL",
    "JMD", "JOD", "JPY",
    "KES", "KGS", "KHR", "KMF", "KPW", "KRW", "KWD", "KYD", "KZT",
    "LAK", "LBP", "LKR", "LRD", "LSL", "LTL", "LUF", "LVL", "LYD",
    "MAD", "MDL", "MGA", "MKD", "MMK", "MNT", "MOP", "MRO", "MTL", "MUR", "MVR", "MWK", "MXN", "MXV", "MYR", "MZN",
    "NAD", "NGN", "NIO", "NLG", "NOK", "NPR", "NZD",
    "OMR",
    "PAB", "PEN", "PGK", "PHP", "PKR", "PLN", "PTE", "PYG",
    "QAR",
    "RON", "RSD", "RUB", "RWF",
    "SAR", "SBD", "SCR", "SDG", "SEK", "SGD", "SHP", "SIT", "SKK", "SLL", "SOS", "SRD", "SSP", "STD", "SYP", "SZL",
    "THB", "TJS", "TMT", "TND", "TOP", "TRY", "TTD", "TWD", "TZS",
    "UAH", "UGX", "USD", "UYU", "UZS",
    "VEF", "VND", "VUV",
    "WST",
    "XAF", "XCD", "XOF", "XPF",
    "YER",
    "ZAR", "ZMW",
  ];

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("seed-currencies");
    $this->setDescription("Seed currencies");
  }

  /**
   * {@inheritdoc}
   */
  protected function translate() {
    // PHP doesn't contain the currency's name's.
    $resourceBundle = $this->getResourceBundle("curr");

    $currencies = [];
    foreach ($this->codes as $code) {
      $currencies[$code] = $resourceBundle["Currencies"][$code][1];
    }

    (new Collator($this->intl->locale))->asort($currencies);

    $translations = null;
    foreach ($currencies as $code => $name) {
      $translations .= '"' . $code . '"=>(object)["code"=>"' . $code . '","name"=>"' . $name . '","symbol"=>"' . $resourceBundle["Currencies"][$code][0] . '"],';
    }

    return $translations;
  }

}
