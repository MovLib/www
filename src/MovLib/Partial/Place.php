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

use \MovLib\Partial\Country;

/**
 * Defines the place partial object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Place {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The place's country.
   *
   * @var \MovLib\Partial\Country
   */
  protected $country;

  /**
   * The place's country code.
   *
   * @var string
   */
  protected $countryCode;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The place to format.
   *
   * @var \MovLib\Data\Place\Place
   */
  protected $place;

  /**
   * The presenting presenter
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

  /**
   * The HTML tag to wrap the place.
   *
   * @var string
   */
  protected $tag;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Instantiate new place partial.
   *
   * @param \MovLib\Presentation\AbstractPresenter $container
   *   The presenting presenter.
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param \MovLib\Data\Place $place
   *   The place to format.
   * @param array $attributes [optional]
   *   Additional attributes to apply.
   * @param string $tag [optional]
   *   The HTML tag to wrap the place.
   */
  public function __construct(\MovLib\Core\HTTP\Container $container, \MovLib\Data\Place\Place $place, array $attributes = [], $tag = "span") {
    $attributes["typeof"] = "Place";
    $this->attributes     = $attributes;
    $this->country        = new Country($container);
    $this->countryCode    = $place->countryCode;
    $this->intl           = $container->intl;
    $this->place          = $place;
    $this->presenter      = $container->presenter;
    $this->tag            = $tag;
  }

  /**
   * Get the place's string representation.
   *
   * @return string
   *   The place's string representation.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
    return
      "<{$this->tag}{$this->presenter->expandTagAttributes($this->attributes)}>" .
        "<span property='geo' typeof='GeoCoordinates'>" .
          "<meta property='latitude' content='{$this->place->latitude}'>" .
          "<meta property='longitude' content='{$this->place->longitude}'>" .
        "</span>" .
        $this->intl->t("{0}, {1}", [ "<span property='name'>{$this->place->name}</span>", $this->country->format($this->countryCode, [ "property" => "containedIn" ]) ]) .
      "</{$this->tag}>"
    ;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) $e;
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
