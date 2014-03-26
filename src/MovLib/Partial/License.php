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

/**
 * Represents a single license in HTML and provides an interface to all available licenses.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class License extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The license to present.
   *
   * @var \MovLib\Data\License
   */
  protected $license;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new license partial.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $id
   *   The unique license identifier.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   */
  public function __construct($id, array $attributes = null) {
    $this->attributes           = $attributes;
    $this->license              = new \MovLib\Data\License($id);
    $this->attributes["href"]   = $this->license->url;
    $this->attributes["rel"]    = "license";
    $this->attributes["target"] = "_blank";
  }

  /**
   * Get the string representation of the license.
   *
   * @return string
   *   The string representation of the license.
   */
  public function __toString() {
    return
      "<abbr title='{$this->license->name}'><a{$this->expandTagAttributes($this->attributes)}>{$this->license->abbreviation}</a></abbr>"
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all supported and translated licenses.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar array $licenses
   *   Associative array used for caching.
   * @return array
   *   All supported and translated licenses.
   */
  public static function getLicenses() {
    global $i18n;
    static $licenses = null;

    // If we haven't built the array for this locale build it.
    if (!isset($licenses[$i18n->locale])) {
      $result = \MovLib\Data\License::getLicensesResult();
      while ($license = $result->fetch_assoc()) {
        $licenses[$i18n->locale][$license["id"]] = $i18n->t("{0} ({1})", [ $license["abbreviation"], $license["name"] ]);
      }
    }

    return $licenses[$i18n->locale];
  }

}
