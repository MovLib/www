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

use \MovLib\Presentation\Partial\Country;

/**
 * Description of Place
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 */
class Place extends \MovLib\Presentation\AbstractBase {

  protected $attributes;

  /**
   *
   * @var \MovLib\Data\Place
   */
  protected $place;
  protected $tag;

  public function __construct($place, array $attributes = [], $tag = "span") {
    $this->attributes             = $attributes;
    $this->attributes["typeof"] = "Place";
    $this->place                  = $place;
    $this->tag                    = $tag;
  }

  public function __toString() {
      "<{$this->tag}{$this->expandTagAttributes($this->attributes)}>" .
        "<span property='geo' typeof='http://schema.org/GeoCoordinates'>" .
          "<meta property='latitude' content='{$this->place->latitude}'>" .
          "<meta property='longitude' content='{$this->place->longitude}'>" .
        "</span>" .
        $this->intl->t("{0}, {1}", [
          "<span property='name'>{$this->place->name}</span>",
          new Country($this->place->countryCode, [ "property" => "containedIn" ]),
        ]) .
      "</{$this->tag}>"
    ;
  }

}
