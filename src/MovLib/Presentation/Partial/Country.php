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
 * Represents a single country in HTML.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Country extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Load the country from ID.
   *
   * @var int
   */
  const FROM_ID = "id";

  /**
   * Load the country from the code.
   *
   * @var string
   */
  const FROM_CODE = "code";


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
   * @param string $from
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value
   *   Data to identify the country, see the various <var>FROM_*</var> class constants.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   * @param string $tag [optional]
   *   The tag that should be used to wrap this country, defaults to <code>"span"</code>.
   */
  public function __construct($from, $value, array $attributes = null, $tag = "span") {
    $this->country    = new \MovLib\Data\Country($from, $value);
    $this->tag        = $tag;
    $this->attributes = $attributes;
  }

  /**
   * Get the string representation of the country.
   *
   * @return string
   */
  public function __toString() {
    $attributes = null;
    if ($this->attributes) {
      $attributes = $this->expandTagAttributes($this->attributes);
    }
    return "<{$this->tag} itemscope itemtype='http://schema.org/Country'{$attributes}><span itemprop='name'>{$this->country->name}</span></{$this->tag}>";
  }

}
