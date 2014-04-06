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
final class Country extends \MovLib\Core\Presentation\Base {

  /**
   * Format a country.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
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
   */
  public function format(\MovLib\Core\Intl $intl, $countryCode, array $attributes = [], $tag = "span") {
    $country = $intl->getTranslations("countries")[$countryCode];
    $attributes["typeof"] = "Country";
    return "<{$tag}{$this->expandTagAttributes($attributes)}><span property='name'>{$country->name}</span></{$tag}>";
  }

  /**
   * Format a country with flag icon.
   *
   * @param \MovLib\Core\FileSystem $fs
   *   The active file system instance.
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
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
   */
  public function formatWithFlag(\MovLib\Core\FileSystem $fs, \MovLib\Core\Intl $intl, $countryCode, $nameVisible = false, array $attributes = [], $tag = "span") {
    $country = $intl->getTranslations("countries")[$countryCode];
    if ($nameVisible) {
      $name = "<span property='name'>{$country->name}</span>";
    }
    else {
      $name = "<meta property='name' content='{$this->htmlEncode($country->name)}'>";
    }
    $attributes["typeof"] = "Country";
    return
      "<{$tag}{$this->expandTagAttributes($attributes)}>" .
        "<img alt='{$this->htmlEncode($country->name)}' class='inline' height='11' property='image' src='{$fs->getExternalURL("asset://img/flag/{$countryCode}.png")}' width='16'>" .
        $name .
      "</{$tag}>"
    ;
  }

  /**
   * Get select form element to select a country.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
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
  public function getSelectFormElement(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, &$value, array $attributes = null, $id = "country", $label = null) {
    $options = [];
    /* @var $country \MovLib\Stub\Data\Country */
    foreach ($diContainerHTTP->intl->getTranslations("countries") as $country) {
      $options[$country->code] = $country->name;
    }
    return new Select($diContainerHTTP, $id, $label ?: $diContainerHTTP->intl->t("Country"), $options, $value, $attributes);
  }

}
