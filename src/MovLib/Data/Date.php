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
final class Date {


  // ------------------------------------------------------------------------------------------------------------------- Constants

  /**
   * Default SQL date format.
   *
   * @var string
   */
  const FORMAT = "Y-m-d";

  /**
   * Default SQL date regular expression that can be used for validation.
   *
   * <b>NOTE</b><br>
   * Both month and day are optional, this ensures that we're able to format partial dates as well.
   *
   * @var string
   */
  const REGEXP = "[0-9]{4}(-[0-9]{2}){0,2}";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The date as valid SQL date string.
   *
   * @var string
   */
  public $date;

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
  public function __construct($date) {
    if (empty($date) || preg_match("/" . self::REGEXP . "/", $date) !== 1) {
      throw new \InvalidArgumentException("Invalid date '{$date}'!");
    }
    $date = explode("-", $date, 3);
    $this->year = $date[0];
    foreach ([ 1 => "month", 2 => "day" ] as $delta => $property) {
      if (isset($date[$delta])) {
        $this->$property = str_pad($date[$delta], 2, "0");
      }
    }
    $this->date = "{$this->year}-{$this->month}-{$this->day}";
  }

  /**
   * Get the string representation of the date.
   *
   * @return string
   *   The string representation of the date.
   */
  public function __toString() {
    return $this->date;
  }

}
