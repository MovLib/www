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
namespace MovLib\Data;

/**
 * Defines the date object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Date extends \DateTime {


  // ------------------------------------------------------------------------------------------------------------------- Constants

  /**
   * Default SQL date format.
   *
   * @var string
   */
  const W3C_DATE = "Y-m-d";

  /**
   * Default SQL date regular expression that can be used for validation.
   *
   * <b>NOTE</b><br>
   * Both month and day are optional, this ensures that we're able to format partial dates as well.
   *
   * @var string
   */
  const W3C_DATE_REGEXP = "[0-9]{4}(-[0-9]{1,2}){0,2}";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The date's day part, or <code>NULL</code> if the date doesn't contain the day part.
   *
   * @var null|string
   */
  public $day;

  /**
   * The date's month part, or <code>NULL</code> if the date doesn't contain the month part.
   *
   * @var null|string
   */
  public $month;

  /**
   * The date's year part.
   *
   * @var string
   */
  public $year;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date.
   *
   * @param string $date
   *   Any valid SQL date string.
   * @throws \InvalidArgumentException
   */
  public function __construct($date = "now") {
    // Check if we have a partial date or a perfect W3C date without any time parts.
    if (preg_match("/" . self::W3C_DATE_REGEXP . "/", $date) === 1) {
      // Split the date into year, month, and day parts.
      $date = explode("-", $date, 3);

      // We'll always have a year, the regular expression check above ensures that for us.
      $this->year = $date[0];

      // But we don't know if we have a month and/or day part.
      foreach ([ 1 => "month", 2 => "day" ] as $delta => $property) {
        if (isset($date[$delta]) && $date[$delta] != 0) {
          $this->$property = str_pad($date[$delta], 2, "0", STR_PAD_LEFT);
        }
      }

      // Now we can safely use the date parts to instantiate PHP's DateTime object. We have to ensure that month and
      // day, in case they're NULL, have correct values, therefore we use 1 in those cases. We also add the time parts
      // to ensure that absolutely nothing can go wrong.
      parent::__construct(sprintf("%04s-%'0'11s-%'0'11s 00:00:00", $this->year, $this->month, $this->day));
    }
    // If we got anything else let PHP handle the date/time and try to export it afterwards into class scope.
    else {
      parent::__construct($date);
      list($this->year, $this->month, $this->day) = explode("-", $this->date, 3);
    }
  }

  /**
   * Get the string representation of the date.
   *
   * @return string
   *   The string representation of the date.
   */
  public function __toString() {
    return sprintf("%04s-%02s-%02s", $this->year, $this->month, $this->day);
  }

}
