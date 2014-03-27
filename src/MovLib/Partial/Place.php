<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Partial;

use \MovLib\Partial\Country;

/**
 * Description of Place
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 */
class Place {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  protected $attributes;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

  /**
   *
   * @var \MovLib\Data\Place
   */
  protected $place;
  protected $tag;

  public function __construct(\MovLib\Presentation\AbstractPresenter $presenter, \MovLib\Core\Intl $intl, $place, array $attributes = [], $tag = "span") {
    $this->attributes             = $attributes;
    $this->attributes["typeof"]   = "Place";
    $this->place                  = $place;
    $this->tag                    = $tag;
    $this->presenter              = $presenter;
    $this->intl                   = $intl;
  }

  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
    return
      "<{$this->tag}{$this->presenter->expandTagAttributes($this->attributes)}>" .
        "<span property='geo' typeof='http://schema.org/GeoCoordinates'>" .
          "<meta property='latitude' content='{$this->place->latitude}'>" .
          "<meta property='longitude' content='{$this->place->longitude}'>" .
        "</span>" .
        $this->intl->t("{0}, {1}", [
          "<span property='name'>{$this->place->name}</span>",
          new Country($this->presenter, $this->intl, $this->place->countryCode, [ "property" => "containedIn" ]),
        ]) .
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
