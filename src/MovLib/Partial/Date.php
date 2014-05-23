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
 * Defines the date partial object.
 *
 * The date partial works with {@see \MovLib\Component\Date} objects and has methods that allow automated formatting with the
 * appropriate structured data attributes and HTML tags.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Date {


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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date partial object.
   *
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
   */
  public function __construct(\MovLib\Core\Intl $intl, \MovLib\Presentation\AbstractPresenter $presenter) {
    $this->intl      = $intl;
    $this->presenter = $presenter;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the formatted date.
   *
   * @param \MovLib\Component\Date $date
   *   The date to format.
   * @param array $attributes [optional]
   *   Additional attributes for the <code><time></code> element.
   * @param integer $type [optional]
   *   Any of the {@see \IntlDateFormatter} constants, defaults to {@see \IntlDateFormatter::MEDIUM}.
   * @return string
   *   The formatted date.
   */
  public function format(\MovLib\Component\Date $date, array $attributes = [], $type = \IntlDateFormatter::MEDIUM) {
    $attributes["datetime"] = $this->formatISO8601($date);
    return "<time{$this->presenter->expandTagAttributes($attributes)}>{$date->formatIntl($this->intl->locale, $type)}</time>";
  }

  /**
   * Format given dates in a generic <code>"{from}-{to}"</code> form.
   *
   * @param \MovLib\Component\Date|null $dateFrom
   *   The from date or <code>NULL</code>.
   * @param \MovLib\Component\Date|null $dateTo
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
  public function formatFromTo(\MovLib\Component\Date $dateFrom = null, \MovLib\Component\Date $dateTo = null, array $fromAttributes = [], array $toAttributes = [], $yearsOnly = false) {
    if ($dateFrom || $dateTo) {
      $format = $yearsOnly ? "formatYear" : "format";
      if ($dateFrom) {
        $date = $this->$format($dateFrom, $fromAttributes);
      }
      else {
        $date = $this->presenter->placeholder("unknown");
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
  public function formatISO8601(\MovLib\Component\Date $date) {
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
   * @param \MovLib\Component\Date $date
   *   The date to format.
   * @param array $attributes [optional]
   *   Additional attributes for the <code><time></code> element. Note that any <code>"datetime"</code> key will be
   *   overwritten.
   * @param array $route [optional]
   *   An attributes array that defines an HTML anchor, if present the year will be enclosed in this link, defaults to
   *   <code>NULL</code>.
   * @return string
   *   The formatted year.
   */
  public function formatYear(\MovLib\Component\Date $date, array $attributes = [], array $route = null) {
    $year = $attributes["datetime"] = $date->year;
    if ($route) {
      // @devStart
      // @codeCoverageIgnoreStart
      assert(!empty($route["href"]), "The href attribute of a route cannot be empty!");
      // @codeCoverageIgnoreEnd
      // @devEnd
      $year = "<a{$this->presenter->expandTagAttributes($route)}>{$year}</a>";
    }
    return "<time{$this->presenter->expandTagAttributes($attributes)}>{$year}</time>";
  }

  /**
   * Get the age of the date in years with approximation if dates are not complete.
   *
   * @param \MovLib\Component\Date $dateFrom
   *   The date when the entity started.
   * @param \MovLib\Component\Date $dateTo [optional]
   *   The date when the entity ended, defaults to <code>NULL</code> and the current date is used.
   * @return string
   *   The age of the date in years (with approximation when dates are not complete) or <code>NULL</code> if year part
   *   is missing.
   */
  public function getAge(\MovLib\Component\Date $dateFrom, \MovLib\Component\Date $dateTo = null) {
    $format = "%Y";
    if (!$dateTo) {
      $dateTo = new \MovLib\Component\Date();
    }
    // We can only calculate the exact date if both dates have all date parts.
    if (empty($dateFrom->month) || empty($dateFrom->day) || empty($dateTo->month) || empty($dateTo->day)) {
      $format = $this->intl->t("~{0}", $format);
    }
    return $dateFrom->diff($dateTo)->format($format);
  }

}
