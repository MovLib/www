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
 * Duration presentation.
 *
 * @link http://wiki.whatwg.org/wiki/Time_element#duration
 * @link http://tools.ietf.org/html/rfc5545
 * @link http://en.wikipedia.org/wiki/ISO_8601#Durations
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Duration extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Format the time exactly, e.g.: 1:00:00
   *
   * @var integer
   */
  const EXACT = 0;

  /**
   * Format the time in minutes, e.g.: 96 min.
   *
   * @var integer
   */
  const MINUTES = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The parsed duration as associative array.
   *
   * @see getdate()
   * @var array
   */
  protected $duration;

  /**
   * The text that is displayed to the user.
   *
   * @var string
   */
  protected $text;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new duration partial.
   *
   * @internal
   *   Keep calculations in constructor, the {@see __toString()} method cannot throw exceptions!
   * @param integer $seconds
   *   The duration in seconds.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   * @param integer $format [optional]
   *   How to format the text, use the class constants.
   */
  public function __construct($seconds, array $attributes = null, $format = self::EXACT) {
    $this->attributes = $attributes;
    $this->duration   = $seconds;

    // Format the parsed date accoring to RFC 5545 respectively ISO 8601.
    $this->attributes["datetime"] = "P";
    foreach ([ "D" => 86400, "H" => 3600, "M" => 60 ] as $type => $divisor) {
      $value    = floor($seconds / $divisor);
      $seconds -= $value * $divisor;
      if ($value > 0) {
        $this->attributes["datetime"] .= "{$value}{$type}";
      }
      if ($type == "D") {
        $this->attributes["datetime"] .= "T";
      }
    }

    // Format text depending on precision.
    if ($format === self::EXACT) {
      $this->attributes["datetime"] .= "{$seconds}S";
      // @todo Right now the number formatter only supports English for durations!
      $this->text = (new \NumberFormatter("en_US", \NumberFormatter::DURATION))->format($this->duration);
    }
    // @todo Can't we translate the unit with Intl ICU? Couldn't find anything.
    else {
      $this->text = $this->intl->t("{0,number,integer} min.", [ ceil($this->duration / 60) ]);
    }
  }

  /**
   * Get the string representation of the duration.
   *
   * @return string
   *   The string representation of the duration.
   */
  public function __toString() {
    return "<time{$this->expandTagAttributes($this->attributes)}>{$this->text}</time>";
  }

}
