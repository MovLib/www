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
 * Defines methods to format date with time.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DateTime {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

  /**
   * The timezone to use for formatting.
   *
   * @var string
   */
  protected $timezone;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date and time partial.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
   * @param string $timezone [optional]
   *   The timezone identifier to use for formatting, defaults to <code>NULL</code> (the default timezone will be used).
   */
  public function __construct(\MovLib\Core\Intl $intl, \MovLib\Presentation\AbstractPresenter $presenter, $timezone = null) {
    $this->intl      = $intl;
    $this->presenter = $presenter;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the date and time formatted in the current locale.
   *
   * @param \MovLib\Data\DateTime $dateTime
   *   The date and time to format.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the <code><time></code> element, default to <code>NULL</code>.
   *   Note that the <code>"datetime"</code> will always be overwritten.
   * @param integer $dateType [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @param integer $timeType [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @param string $timezone [optional]
   *   Use a different timezone to format this date and time, defaults to <code>NULL</code> and first falls back to the
   *   timezone that was passed to the constructor and then to the default server timezone.
   * @param string $locale [optional]
   *   Use a different locale to format this date and time, defaults to <code>NULL</code> and the current locale will be
   *   used.
   * @return string
   *   The date and time formatted in the current locale.
   */
  public function format(\MovLib\Data\DateTime $dateTime, array $attributes = null, $dateType = \IntlDateFormatter::MEDIUM, $timeType = \IntlDateFormatter::MEDIUM, $timezone = null, $locale = null) {
    if (!$timezone) {
      $timezone = $this->timezone;
    }
    if (!$locale) {
      $locale = $this->intl->locale;
    }
    $time                   = new \IntlDateFormatter($locale, $dateType, $timeType, $timezone);
    $attributes["datetime"] = (string) $dateTime;
    return "<time{$this->presenter->expandTagAttributes($attributes)}>{$time->format($dateTime)}</time>";
  }

}
