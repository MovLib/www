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
 * DateTime presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DateTime extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The Intl ICU date format to use for formatting.
   *
   * @var integer
   */
  public $dateFormat = \IntlDateFormatter::SHORT;

  /**
   * The time's {@see \DateTime} instance.
   *
   * @var \DateTime
   */
  protected $dateTime;

  /**
   * The Intl ICU time format to use for formatting.
   *
   * @var integer
   */
  public $timeFormat = \IntlDateFormatter::SHORT;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new time partial.
   *
   * @param string $time [optional]
   *   A date/time string in a valid format as explined in {@link http://www.php.net/manual/en/datetime.formats.php Date
   *   and Time Formats} or an integer, which is treated as UNIX timestamp. Defaults to <code>"now"</code>.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   */
  public function __construct($time = "now", array $attributes = null) {
    if (is_int($time)) {
      $time = "@{$time}";
    }
    $this->dateTime               = new \DateTime($time);
    $this->attributes             = $attributes;
    $this->attributes["datetime"] = $this->dateTime->format(\DateTime::W3C);
  }

  /**
   * Get the string representation of this (date)time.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return string
   *   The string representation of this (date)time.
   */
  public function __toString() {
    global $i18n, $session;
    $time = new \IntlDateFormatter($i18n->locale, $this->dateFormat, $this->timeFormat, $session->userTimeZone);
    return "<time{$this->expandTagAttributes($this->attributes)}>{$time->format($this->dateTime)}</time>";
  }

}
