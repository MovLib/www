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

/**
 * Represents a single country in HTML and provides an interface to all available countries.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Country extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The country to present.
   *
   * @var \MovLib\Data\Country
   */
  protected $country;

  /**
   * The HTML tag to wrap the country.
   *
   * @var string
   */
  protected $tag;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new country partial.
   *
   * @param string $code
   *   The ISO 3166-1 alpha-2 code of the country.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   * @param string $tag [optional]
   *   The tag that should be used to wrap this country, defaults to <code>"span"</code>.
   */
  public function __construct($code, array $attributes = null, $tag = "span") {
    $this->attributes             = $attributes;
    $this->attributes[]           = "itemscope";
    $this->attributes["itemtype"] = "http://schema.org/Country";
    $this->country                = new \MovLib\Data\Country($code);
    $this->tag                    = $tag;
  }

  /**
   * Get the string representation of the country.
   *
   * @return string
   */
  public function __toString() {
    return "<{$this->tag}{$this->expandTagAttributes($this->attributes)}><span itemprop='name'>{$this->country->name}</span></{$this->tag}>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the string represntation of the country including a small flag icon.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return string
   *   The string represntation of the country including a small flag icon.
   */
  public function getFlag() {
    global $i18n, $kernel;
    return "<{$this->tag}{$this->expandTagAttributes($this->attributes)}><img alt='{$i18n->t("Flag of {country_name}", [
      "country_name" => $this->country->name
    ])}' height='11' itemprop='image' src='{$kernel->getAssetURL("flag/{$this->country->code}", "png")}' width='16'><meta itemprop='name' content='{$this->country->name}'></{$this->tag}>";
  }

  /**
   * Get all supported and translated countries.
   *
   * @return array
   *   All supported and translated countries.
   */
  public static function getCountries() {
    return \MovLib\Data\Country::getCountries();
  }

}
