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
 * Defines methods to format dates.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait DateTrait {

  /**
   * Get the formatted date.
   *
   * @param \MovLib\Data\Date $date
   *   The date to format.
   * @param array $attributes [optional]
   *   Additional attributes for the <code><time></code> element.
   * @param integer $type [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @return string
   *   The formatted date.
   */
  final public function dateFormat(\MovLib\Data\Date $date, array $attributes = [], $type = \IntlDateFormatter::MEDIUM) {
    $attributes["datetime"] = $this->dateFormatISO8601($date);
    return "<time{$this->expandTagAttributes($attributes)}>{$this->intl->formatDate($date, $type)}</time>";
  }

  /**
   * Format given dates in a generic <code>"{from}-{to}"</code> form.
   *
   * @param \MovLib\Data\Date|null $dateFrom
   *   The from date or <code>NULL</code>.
   * @param \MovLib\Data\Date|null $dateTo
   *   The to date or <code>NULL</code>.
   * @param array $fromAttributes [optional]
   *   Additional attributes for the from <code><time></code> element. Note that any <code>"datetime"</code> key will be
   *   overwritten.
   * @param array $toAttributes [optional]
   *   Additional attributes for the to <code><time></code> element. Note that any <code>"datetime"</code> key will be
   *   overwritten.
   * @param boolean $yearsOnly [optional]
   *   Whether the output should only display years or not, defaults to <code>FALSE</code> (display complete date).
   * @return null|string
   *   The formatted dates or <code>NULL</code> if no dates where found for formatting.
   */
  final public function dateFormatFromTo(\MovLib\Data\Date $dateFrom = null, \MovLib\Data\Date $dateTo = null, array $fromAttributes = [], array $toAttributes = [], $yearsOnly = false) {
    if ($dateFrom || $dateTo) {
      $format = $yearsOnly ? "dateFormatYear" : "dateFormat";
      if ($dateFrom) {
        $date = $this->$format($dateFrom, $fromAttributes);
      }
      else {
        $date = $this->placeholder("unknown");
      }
      if ($dateTo) {
        $date = $this->intl->t("{0}–{1}", [ $date, $this->$format($dateTo, $toAttributes) ]);
      }
      return $date;
    }
  }

  /**
   * Get the formatted date according to {@link http://www.w3.org/TR/NOTE-datetime ISO 8601} standard.
   *
   * @return string
   *   The ISO 8601 formatted date.
   */
  final public function dateFormatISO8601(\MovLib\Data\Date $date) {
    if ($date->day) {
      return (string) $date;
    }
    if ($date->month) {
      return "{$date->year}-{$date->month}";
    }
    return $date->year;
  }

  /**
   * Format the year of the given string.
   *
   * @param \MovLib\Data\Date $date
   *   The date to format.
   * @param array $attributes [optional]
   *   Additional attributes for the <code><time></code> element. Note that any <code>"datetime"</code> key will be
   *   overwritten.
   * @return string
   *   The formatted year.
   */
  final public function dateFormatYear(\MovLib\Data\Date $date, array $attributes = []) {
    $attributes["datetime"] = $date->year;
    return "<time{$this->expandTagAttributes($attributes)}>{$date->year}</time>";
  }

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
  final public function dateGetAge(\MovLib\Data\Date $dateFrom, \MovLib\Data\Date $dateTo = null) {
    $format = "%Y";
    // We can only calculate the exact date if both dates have all date parts.
    if (isset($dateFrom->month) && isset($dateFrom->day) && isset($dateTo->month) && isset($dateTo->day)) {
      $format = $this->intl->t("~{0}", $format);
    }
    return $dateFrom->dateTime->diff($dateTo->dateTime)->format($format);
  }

}
