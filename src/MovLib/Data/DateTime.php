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
 * Defines the date and time object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DateTime extends \DateTime {

  const FORMAT_DATABASE = "Y-m-d\TH:i:s";

  /**
   * Get the string representation of the date and time.
   *
   * @return string
   *   The string representation of the date and time.
   */
  public function __toString() {
    return $this->format(self::FORMAT_DATABASE);
  }

  /**
   * Format the date and time according to the intl rules of the given locale.
   *
   * @param string $locale
   *   The locale to use for formatting.
   * @param null|string $timezone [optional]
   *   The timezone that should be used to display the date and time, defaults to <code>NULL</code> and the system's
   *   default timezone will be used.
   * @param integer $dateType [optional]
   *   How to format the date part, any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @param integer $timeType [optional]
   *   How to format the time part, any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @return string
   *   The date and time according to the intl rules of the given locale.
   */
  public function formatIntl($locale, $timezone = null, $dateType = \IntlDateFormatter::MEDIUM, $timeType = \IntlDateFormatter::MEDIUM) {
    return (new \IntlDateFormatter($locale, $dateType, $timeType, $timezone))->format($this);
  }

}
