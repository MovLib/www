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
 * Date presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Date extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * Internal information about the date, the output of the {@link http://www.php.net/manual/en/function.date-parse.php
   *  date_parse} function.
   *
   * @var array
   */
  public $dateInfo;

  /**
   * The date's string representation in <code>"Y-m-d"</code> format.
   *
   * @var string
   */
  public $dateValue;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date partial.
   *
   * @param string $date [optional]
   *   A date/time string in a valid format as explained in {@link http://www.php.net/manual/en/datetime.formats.php Date
   *   and Time Formats} or an integer, which is treated as UNIX timestamp. Defaults to <code>"now"</code>.
   */
  public function __construct($date = "now") {
    if (is_int($date)) {
      $date = date("Y-m-d", $date);
    }
    elseif ($date == "now") {
      $date = date("Y-m-d", strtotime("now"));
    }
    $this->dateInfo = date_parse($date);
    $this->dateValue = "{$this->dateInfo["year"]}-{$this->dateInfo["month"]}-{$this->dateInfo["day"]}";
  }

  /**
   * Get the formatted date.
   *
   * @param string $format [optional]
   *   The format string, see {@link http://www.php.net/manual/en/function.date.php} for formatting options, defaults to
   *   <code>"Y-m-d"</code> ({@link http://www.ietf.org/rfc/rfc3339.txt RFC3339},
   *   {@link https://en.wikipedia.org/wiki/ISO_8601 ISO 8601}, {@link http://www.w3.org/TR/NOTE-datetime W3CDTF}).
   * @return string
   *   The formatted date.
   */
  public function format($format = "Y-m-d") {
    return $this->dateValue->format($format);
  }

  /**
   * Format a date as <code><time></code> tag with additional attributes.
   *
   * @param array $attributes [optional]
   *   Additional attributes to apply. <code>"datetime"</code> will be overridden!
   * @return type
   */
  public function formatSchemaProperty(array $attributes = []) {
    $attributes["datetime"] = $this->dateValue;
    return "<time{$this->expandTagAttributes($attributes)}>{$this->intlFormat()}</time>";
  }

  /**
   * Get the age of the date in years.
   *
   * @param \DateTime $date [optional]
   *   A date/time string in a valid format as explained in {@link http://www.php.net/manual/en/datetime.formats.php Date
   *   and Time Formats} or an integer, which is treated as UNIX timestamp. Defaults to <code>"now"</code>.
   * @return integer
   *   The age of the date in years or <code>NULL</code> if this operation is not permitted (e.g. day is missing).
   */
  public function getAge($date = "now") {
    if (is_int($date)) {
      $date = "@{$date}";
    }
    $date = new \DateTime($date);
    $errors = \DateTime::getLastErrors();
    if ($errors["warning_count"] === 0 && $errors["error_count"] === 0 && $this->dateInfo["month"] !== 0 && $this->dateInfo["month"] !== 0) {
      return (new \DateTime($this->dateValue))->diff($date)->format("%y");
    }
  }

  /**
   * Get the localized formatted date.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param integer $datetype [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @return string
   *   The localized and formatted date.
   */
  public function intlFormat($datetype = \IntlDateFormatter::MEDIUM) {
    global $i18n;
    // Month and day are empty, return year.
    if ($this->dateInfo["month"] === 0 && $this->dateInfo["day"] === 0) {
      return $this->dateInfo["year"];
    }
    // Day is missing, use format strings provided by this class.
    if ($this->dateInfo["month"] !== 0 && $this->dateInfo["day"] === 0) {
      return (new \IntlDateFormatter(
        $i18n->locale,
        $datetype,
        \IntlDateFormatter::NONE,
        null,
        null,
        self::$dateFormats[$i18n->languageCode][$datetype])
      )->format(new \DateTime("{$this->dateInfo["year"]}-{$this->dateInfo["month"]}-01"));
    }
    // Everything is there, let Intl do its magic.
    return (new \IntlDateFormatter($i18n->locale, $datetype, \IntlDateFormatter::NONE))->format(new \DateTime($this->dateValue));
  }

}
