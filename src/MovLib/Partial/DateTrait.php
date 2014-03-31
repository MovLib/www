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
 * Date presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait DateTrait {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing date formats for missing days.
   *
   * Structure: <code>[ "language_code" => [ "IntlDateFormatter_constant" => "format_string" ] ]</code>
   *
   * @var array
   */
  private static $dateFormats = [
    "de" => [
      \IntlDateFormatter::NONE        => "yyyyMM hh:mm a",
      \IntlDateFormatter::SHORT       => "MM.yy",
      \IntlDateFormatter::MEDIUM      => "MM.y",
      \IntlDateFormatter::LONG        => "MMMM y",
      \IntlDateFormatter::FULL        => "MMMM y",
      \IntlDateFormatter::TRADITIONAL => "MMMM y",
      \IntlDateFormatter::GREGORIAN   => "MMMM y",
    ],
    "en" => [
      \IntlDateFormatter::NONE        => "yyyyMM hh:mm a",
      \IntlDateFormatter::SHORT       => "M/yy",
      \IntlDateFormatter::MEDIUM      => "MMM, y",
      \IntlDateFormatter::LONG        => "MMMM, y",
      \IntlDateFormatter::FULL        => "MMMM, y",
      \IntlDateFormatter::TRADITIONAL => "MMMM, y",
      \IntlDateFormatter::GREGORIAN   => "MMMM, y",
    ],
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the age of the date in years with approximation if dates are not complete.
   *
   * @param \MovLib\Data\Date $dateFrom
   *   The date when the entity started.
   * @param \MovLib\Data\Date $dateTo [optional]
   *   The date when the entity ended, defaults to <code>NULL</code> and the current date is used.
   * @return string
   *   The age of the date in years (with approximation when dates are not complete) or <code>NULL</code> if year part
   *   is missing.
   */
  public function dateGetAge(\MovLib\Data\Date $dateFrom, \MovLib\Data\Date $dateTo = null) {
    $approx = false;
    $dates  = (object) [ "from" => $dateFrom, "to" => $dateTo ];
    foreach ($dates as $delta => $date) {
      if ($date) {
        foreach ([ "month", "day" ] as $prop) {
          if (!$date->$prop) {
            $approx      = true;
            $date->$prop = "01";
          }
        }
      }
      else {
        $date = "now";
      }
      $dates->$delta = new \DateTime((string) $date);
    }
    $format = "%Y";
    if ($approx) {
      $format = $this->intl->t("~{0}", $format);
    }
    return $dates->from->diff($dates->to)->format($format);
  }


  // ------------------------------------------------------------------------------------------------------------------- Old Methods


  /**
   * Format a date as <code><time></code> tag with additional attributes.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param array $attributes [optional]
   *   Additional attributes to apply, <code>"datetime"</code> will be overridden!
   * @return string
   *   Formatted date as <code><time></code> tag with additional attributes.
   */
  public function format(\MovLib\Core\Intl $intl, array $attributes = []) {
    $attributes["datetime"] = $this->iso8601Format();
    $formatted = $this->intlFormat($intl);
    return "<time{$this->presenter->expandTagAttributes($attributes)}>{$formatted}</time>";
  }

  /**
   * Get the formatted date according to {@link http://www.w3.org/TR/NOTE-datetime ISO 8601} standard.
   *
   * @return string
   *   The ISO 8601 formatted date.
   */
  public function iso8601Format() {
    if ($this->dateInfo["month"] === 0) {
      return $this->dateInfo["year"];
    }
    if ($this->dateInfo["day"] === 0) {
      return "{$this->dateInfo["year"]}-{$this->dateInfo["month"]}";
    }
    return $this->dateValue;
  }

  /**
   * Get the localized formatted date.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param integer $datetype [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @return string
   *   The localized and formatted date.
   */
  public function intlFormat(\MovLib\Core\Intl $intl, $datetype = \IntlDateFormatter::MEDIUM) {
    // Month and day are empty, return year.
    if ($this->dateInfo["month"] === 0 && $this->dateInfo["day"] === 0) {
      return $this->dateInfo["year"];
    }

    $date = $this->dateValue;
    $fmt  = new \IntlDateFormatter($intl->locale, $datetype, \IntlDateFormatter::NONE);

    // Day is missing, use format strings provided by this class.
    if ($this->dateInfo["month"] !== 0 && $this->dateInfo["day"] === 0) {
      $fmt->setPattern(self::$dateFormats[$intl->languageCode][$datetype]);
      $date = "{$this->dateInfo["year"]}-{$this->dateInfo["month"]}-01";
    }

    // Everything is there, let Intl do its magic.
    return $fmt->format(new \DateTime($date));
  }

}
