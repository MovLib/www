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
namespace MovLib\Partial;

use \MovLib\Partial\FormElement\Select;

/**
 * Defines formatting methods to represent countries.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Country extends \MovLib\Core\Presentation\DependencyInjectionBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * All available countries in the current locale.
   *
   * @var array
   */
  public $countries;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\HTTP\Container $container) {
    parent::__construct($container);
    $this->countries = $this->intl->getTranslations("countries");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a country.
   *
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
   * @param string $countryCode
   *   The country's ISO 3166-1 alpha-2 code.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element. Note that the <code>"typeof"</code> attribute is
   *   always overwritten.
   * @param string $tag [optional]
   *   The tag that should be used to wrap the country, defaults to <code>"span"</code>.
   * @return string
   *   The formatted country.
   * @throws \ErrorException
   *   If the country code is invalid.
   */
  public function format($countryCode, array $attributes = [], $tag = "span") {
    $country = $this->countries[$countryCode];
    $attributes["typeof"] = "Country";
    return "<{$tag}{$this->expandTagAttributes($attributes)}><span property='name'>{$country->name}</span></{$tag}>";
  }

  /**
   * Format a country with flag icon.
   *
   * @param string $countryCode
   *   The country's ISO 3166-1 alpha-2 code.
   * @param boolean $nameVisible [optional]
   *   Whether the country's name should be visible or not, defaults to <code>FALSE</code> (the name isn't visible).
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element. Note that the <code>"typeof"</code> attribute is
   *   always overwritten.
   * @param string $tag [optional]
   *   The tag that should be used to wrap the country, defaults to <code>"span"</code>.
   * @return string
   *   The formatted country with flag icon.
   * @throws \ErrorException
   *   If the country code is invalid.
   */
  public function formatWithFlag($countryCode, $nameVisible = false, array $attributes = [], $tag = "span") {
    if ($nameVisible) {
      $name = "<span property='name'>{$this->countries[$countryCode]->name}</span>";
    }
    else {
      $name = "<meta property='name' content='{$this->htmlEncode($this->countries[$countryCode]->name)}'>";
    }
    $attributes["typeof"] = "Country";
    return
      "<{$tag}{$this->expandTagAttributes($attributes)}>" .
        "<img alt='{$this->htmlEncode($this->countries[$countryCode]->name)}' class='inline' height='11' property='image' src='{$this->fs->getExternalURL("asset://img/flag/{$countryCode}.png")}' width='16'>" .
        $this->countries[$countryCode]->name .
      "</{$tag}>"
    ;
  }

  /**
   * Format an array containing country objects.
   *
   * @param array $countries
   *   The country objects to format.
   * @param string $property [optional]
   *   The structured data property for each country.
   * @return string
   *   The formatted countries.
   */
  public function getList(array $countries, $property = null) {
    if (!empty($countries)) {
      if ($property) {
        $property = " property='{$property}'";
      }

      $list  = null;
      $comma = $this->intl->t(", ");

      /* @var $country \MovLib\Stub\Data\Country */
      foreach ($countries as $country) {
        if ($list) {
          $list .= $comma;
        }
        $list .=
          "<a href='{$this->intl->r("/country/{0}/movies", $country->code)}'{$property} typeof='Country'>" .
            "<span property='name'>{$country->name}</span>" .
          "</a>"
        ;
      }

      return $list;
    }
  }

  /**
   * Get select form element to select a country.
   *
   * @param string $value
   *   The form element's value.
   * @param array $attributes [optional]
   *   The form element's additional attributes.
   * @param string $id [optional]
   *   The form element's unique identifier, defaults to <code>"country"</code>.
   * @param string $label [optional]
   *   The form element's translated label, default to <code>$this->intl->t("Country")</code>.
   * @return \MovLib\Presentation\Partial\FormElement\Select
   *   The select form element to select a country.
   */
  public function getSelectFormElement(&$value, array $attributes = null, $id = "country", $label = null) {
    $options = [];
    /* @var $country \MovLib\Stub\Data\Country */
    foreach ($this->countries as $country) {
      $options[$country->code] = $country->name;
    }
    return new Select($this->container, $id, $label ?: $this->intl->t("Country"), $options, $value, $attributes);
  }

}
