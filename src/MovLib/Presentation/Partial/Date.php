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
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Date {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The date's {@see \DateTime} instance.
   *
   * @var \DateTime
   */
  protected $date;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date partial.
   *
   * @param string $date [optional]
   *   A date/time string in a valid format as explined in {@link http://www.php.net/manual/en/datetime.formats.php Date
   *   and Time Formats} or an integer, which is treated as UNIX timestamp. Defaults to <code>"now"</code>.
   */
  public function __construct($date = "now") {
    if (is_int($date)) {
      $date = "@{$date}";
    }
    $this->date = new \DateTime($date);
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
    return $this->date->format($format);
  }

  /**
   * Get the age of the date in years.
   *
   * @return integer
   *   The age of the date in years.
   */
  public function getAge() {
    return $this->date->diff(new \DateTime("now"))->format("%y");
  }

  /**
   * Get the localized formatted date.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param integer $datetype [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @return string
   *   The localized and formatted date.
   */
  public function intlFormat($datetype = \IntlDateFormatter::MEDIUM) {
    global $i18n, $session;
    return (new \IntlDateFormatter($i18n->locale, $datetype, \IntlDateFormatter::NONE, new \DateTimeZone($session->userTimeZoneId)))->format($this->date);
  }

}
