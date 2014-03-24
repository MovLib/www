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
namespace MovLib\Presentation\Partial;

use \MovLib\Presentation\Partial\FormElement\Select;

/**
 * Represents a single currency in HTML and provides an interface to all available currencies in the current locale.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Currency extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The currency to present.
   *
   * @var \MovLib\Data\Currency
   */
  protected $currency;

  /**
   * The HTML tag to wrap the country.
   *
   * @var string
   */
  protected $tag;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new currency partial.
   *
   * @todo Implement schema.org mark-up for marketplace listings.
   * @param string $code
   *   The ISO 3166-1 alpha-2 code of the country.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   * @param string $tag [optional]
   *   The tag that should be used to wrap this country, defaults to <code>"span"</code>.
   */
  public function __construct($code, array $attributes = null, $tag = "span") {
    $this->attributes = $attributes;
    $this->currency   = new \MovLib\Data\Currency($code);
    $this->tag        = $tag;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format the given value.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar array $currencies
   *   Array used to cache the created HTML currencies.
   * @param integer|float $value
   *   The value to format.
   * @return string
   *   The formatted value.
   */
  public function format($value) {
    global $i18n;
    static $currencies = null;

    // If we created this string before, re-use it.
    if (isset($currencies[$i18n->locale][$this->currency->code])) {
      return $currencies[$i18n->locale][$this->currency->code];
    }

    // Get the formatted string from our parent with the desired locale and replace the currency symbol with appropriate
    // HTML mark-up.
    $currencies[$i18n->locale][$this->currency->code] = str_replace(
      $this->currency->symbol,
      "<abbr title='{$this->currency->name}'>{$this->currency->symbol}</abbr>",
      $this->currency->format($value)
    );

    return $currencies[$i18n->locale][$this->currency->code];
  }

  /**
   * Get select form element to select a currency.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $value
   *   The form element's value.
   * @param array $attributes [optional]
   *   The form element's additional attributes.
   * @param string $id [optional]
   *   The form element's unique identifier, defaults to <code>"currency"</code>.
   * @param string $label [optional]
   *   The form element's translated label, default to <code>$i18n->t("Currency")</code>.
   * @return \MovLib\Presentation\Partial\FormElement\Select
   *   The select form element to select a currency.
   */
  public static function getSelectFormElement(&$value, array $attributes = null, $id = "currency", $label = null) {
    global $i18n;
    return new Select($id, $label ?: $i18n->t("Currency"), $i18n->getTranslations("currencies"), $value, $attributes);
  }

}
