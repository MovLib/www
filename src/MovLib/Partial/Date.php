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
final class Date {


  // ------------------------------------------------------------------------------------------------------------------- Constants

  /**
   * Default W3C/SQL date format.
   *
   * @var string
   */
  const FORMAT_W3C = "Y-m-d";

  /**
   * Default W3C/SQL date regular expression that can be used for validation.
   *
   * <b>NOTE</b><br>
   * Both month and day are optional, this ensures that we're able to format partial dates as well.
   *
   * @var string
   */
  const REGEXP_W3C = "/[0-9]{4}(-[0-9]{2}){0,2}/";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Valid SQL date string (<code>"Y-m-d"</code>).
   *
   * @var string
   */
  public $date;

  /**
   * Associative array containing date formats for missing days.
   *
   * Structure: <code>[ "language_code" => [ "IntlDateFormatter_constant" => "format_string" ] ]</code>
   *
   * @var array
   */
  public static $dateFormats = [
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

  /**
   * The date's day part.
   *
   * @var string
   */
  protected $day;

  /**
   * The date's month part.
   *
   * @var string
   */
  protected $month;

  /**
   * The date's year part.
   *
   * @var string
   */
  protected $year;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format the given date as year.
   *
   * @param null|string $date [optional]
   *   Any valid SQL date string.
   * @param null|string $attributes [optional]
   *   Additional attributes that should be applied to the <code><time></code> element, already sent through the
   *   appropriate expansion method.
   * @return string
   *   The formatted date as year.
   */
  public function formatYear($date = null, $attributes = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(strpos($attributes, "datetime") === false, "The attributes of a date method cannot contain a datetime attribute.");
    // @codeCoverageIgnoreEnd
    // @devEnd
    if ($date) {
      $this->setDate($date);
    }
    return "<time datetime='{$this->year}'{$attributes}>{$this->year}</time>";
  }

  /**
   * Set the date.
   *
   * @param string $date
   *   Any valid SQL date string.
   * @return this
   */
  public function setDate($date) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(preg_match(self::REGEXP_W3C, $date) === 1, "Invalid date '{$date}'!");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Reset properties and directly return if the passed date is empty.
    $this->year = $this->month = $this->day = $this->date = null;
    if (empty($date)) {
      return $this;
    }

    // Split the given W3C/SQL date at its delimiter and export to object scope.
    $date = explode("-", $date, 3);

    // Set individual parts if available.
    if (isset($date[0])) {
      $this->year = str_pad($date[0], 4, "0");
    }
    if (isset($date[1])) {
      $this->month = str_pad($date[1], 2, "0");
    }
    if (isset($date[2])) {
      $this->day = str_pad($date[2], 2, "0");
    }

    // Put the parts together in a valid W3C/SQL format.
    $this->date = sprintf("%04d-%02d-%02d", $this->year, $this->month, $this->day);

    return $this;
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
   * Get the age of the date in years with approximation if dates are not complete.
   *
   * @param int|string $date [optional]
   *   A date/time string in a valid format as explained in {@link http://www.php.net/manual/en/datetime.formats.php Date
   *   and Time Formats} or an integer, which is treated as UNIX timestamp. Defaults to <code>"now"</code>.
   * @return string
   *   The age of the date in years (with approximation when dates are not complete) or <code>NULL</code> if years are missing.
   */
  public function getAge($date = "now") {
    $approximate = false;
    if (is_int($date)) {
      $date = "@{$date}";
    }
    // Parse the date and repair the value if month or day are missing.
    if (is_string($date) && $date != "now") {
      // Make sure date_parse() does not interpret incomplete dates as time strings.
      $dateInfo = date_parse("{$date} 00:00:00");
      if ($dateInfo["year"] === false) {
        return null;
      }
      if ($dateInfo["month"] === false || $dateInfo["month"] === 0) {
        $dateInfo["month"] = 1;
        $approximate = true;
      }
      if ($dateInfo["day"] === false || $dateInfo["day"] === 0) {
        $dateInfo["day"] = 1;
        $approximate = true;
      }
      $date = "{$dateInfo["year"]}-{$dateInfo["month"]}-{$dateInfo["day"]}";
    }
    $date   = new \DateTime($date);

    // Construct a valid representation of the internal date.
    $thisDate = "{$this->dateInfo["year"]}-";
    if ($this->dateInfo["month"] === 0) {
      $thisDate .= "01";
      $approximate = true;
    }
    else {
      $thisDate .= $this->dateInfo["month"];
    }
    if ($this->dateInfo["day"] === 0) {
      $thisDate .= "-01";
      $approximate = true;
    }
    else {
      $thisDate .= "-{$this->dateInfo["day"]}";
    }
    $thisDate = new \DateTime($thisDate);

    // Finally format the diff.
    $format = "%Y";
    if ($approximate === true) {
      $format = "~{$format}";
    }
    return $thisDate->diff($date)->format($format);
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
