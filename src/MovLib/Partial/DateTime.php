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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "DateTime";
  // @codingStandardsIgnoreEnd


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
    $this->timezone  = $timezone;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the date and time formatted in the current locale.
   *
   * @param \MovLib\Component\DateTime $dateTime
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
  public function format(\MovLib\Component\DateTime $dateTime, array $attributes = null, $dateType = \IntlDateFormatter::MEDIUM, $timeType = \IntlDateFormatter::MEDIUM, $timezone = null, $locale = null) {
    $timezone || ($timezone = $this->timezone);
    $locale   || ($locale   = $this->intl->locale);
    $time = new \IntlDateFormatter($locale, $dateType, $timeType, $timezone);
    $attributes["datetime"] = (string) $dateTime;
    return "<time{$this->presenter->expandTagAttributes($attributes)}>{$time->format($dateTime)}</time>";
  }

  /**
   * Get a relative string representation of the time.
   *
   * The usage of magic numbers is intended, these calculations will never change!
   *
   * @link http://stackoverflow.com/questions/11
   * @param \MovLib\Component\DateTime $dateTime
   *   The date and time to format.
   * @param \MovLib\Component\DateTime $requestDateTime
   *   The date and time of the request.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the <code><time></code> element.
   * @param string $timezone [optional]
   *   The timezone to use for calculation, defaults to the timezone passed to the constructor (if any) and than to the
   *   default server timezone.
   * @return string
   *   Relative string representation of the date and time.
   */
  public function formatRelative(\MovLib\Component\DateTime $dateTime, \MovLib\Component\DateTime $requestDateTime, array $attributes = [], $timezone = null) {
    $requestDateTime->setTimezone($timezone ?: $this->timezone);
    $interval = $requestDateTime->diff($dateTime, true);

    // The diff method returns FALSE upon failure, account for this by simply returning the original date and time
    // formatted in the current locale.
    if ($interval === false) {
      return $this->format($dateTime, null, null, null, $timezone);
    }
    elseif ($interval->y > 0) {
      $formatted = $this->intl->t("{0,plural,=1{last year}other{# years ago}}", $interval->y);
    }
    elseif ($interval->m > 0) {
      $formatted = $this->intl->t("{0,plural,=1{last month}other{# months ago}}", $interval->m);
    }
    elseif ($interval->d > 0) {
      $formatted = $this->intl->t("{0,plural,=1{today}=2{yesterday}other{# days ago}}", $interval->d);
    }
    elseif ($interval->h > 0) {
      $formatted = $this->intl->t("{0,plural,=1{an hour ago}other{# hours ago}}", $interval->h);
    }
    elseif ($interval->i > 0) {
      $formatted = $this->intl->t("{0,plural,=1{a minute ago}other{# minutes ago}}", $interval->i);
    }
    else {
      $formatted = $this->intl->t("{0,plural,=1{one second ago}other{# seconds ago}}", $interval->s);
    }

    $attributes["datetime"] = (string) $dateTime;
    return "<time{$this->presenter->expandTagAttributes($attributes)}>{$formatted}</time>";
  }

}
