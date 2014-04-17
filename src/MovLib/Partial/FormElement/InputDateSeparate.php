<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Partial\FormElement;

/**
 * Date input consisting of three number input elements for day, month and year.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateSeparate extends \MovLib\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for missing mandatory field.
   *
   * @var integer
   */
  const ERROR_MISSING_MANDATORY = 1;

  /**
   * Error code for to big year.
   *
   * @var integer
   */
  const ERROR_YEAR_MAX = 2;

  /**
   * Error code for to small year.
   *
   * @var integer
   */
  const ERROR_YEAR_MIN = 3;

  /**
   * Error code for an invalid date.
   *
   * @var integer
   */
  const ERROR_INVALID = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  protected $month;
  protected $day;
  protected $year;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date input with day, month and year fields.
   *
   * {@inheritdoc}
   *
   * @param array $options [optional]
   *   Associative array containing additional options:
   *   <ul>
   *     <li><code>"year_max"</code> set this to the maximum year that can be entered, defaults to <code>9999</code></li>
   *     <li><code>"year_min</code> set this to the minimum year that can be entered, defaults to <code>0</code></li>
   *   </ul>
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $id, $label, &$value, array $attributes = null, array $options = null) {
    parent::__construct($diContainerHTTP, $id, $label, $value, $attributes);

    if (isset($options["year_max"])) {
      $this->yearMax = $options["year_max"];
    }
    if (isset($options["year_min"])) {
      $this->yearMin = $options["year_min"];
    }

    $this->log->debug($value);
    $this->log->debug($this->year);
    $this->exportDateProperties($value);
    $this->log->debug($this->year);
  }

  /**
   * Get the input date separate form element.
   *
   * @return string
   *   The input date separate form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd

      // Always add CSS class for proper styling.
      $this->addClass("date-separate", $this->attributes);

      $attributes = [
        "y" => [
          "id"          => "{$this->id}-year",
          "max"         => $this->yearMax,
          "min"         => $this->yearMin,
          "name"        => "{$this->id}[year]",
          "placeholder" => $this->intl->t("yyyy"),
          "type"        => "number",
          "value"       => $this->year,
        ],
        "m" => [
          "id"          => "{$this->id}-month",
          "name"        => "{$this->id}[month]",
          "max"         => 12,
          "min"         => 1,
          "placeholder" => $this->intl->t("mm"),
          "type"        => "number",
          "value"       => $this->month,
        ],
        "d" => [
          "id"          => "{$this->id}-day",
          "max"         => 31,
          "min"         => 1,
          "name"        => "{$this->id}[day]",
          "placeholder" => $this->intl->t("dd"),
          "type"        => "number",
          "value"       => $this->day,
        ],
      ];

      return
        "{$this->required}{$this->helpPopup}<fieldset{$this->expandTagAttributes($this->attributes)}>" .
          "<legend>{$this->label}</legend>" .
          "<label class='s s2'><span class='vh'>{$this->intl->t("Year")}</span><input{$this->expandTagAttributes($attributes["y"])}></label>" .
          "<label class='s s1'><span class='vh'>{$this->intl->t("Month")}</span><input{$this->expandTagAttributes($attributes["m"])}></label>" .
          "<label class='s s1'><span class='vh'>{$this->intl->t("Day")}</span><input{$this->expandTagAttributes($attributes["d"])}></label>" .
        "</fieldset>"
      ;

    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->callout("<pre>{$e}</pre>", "Stacktrace", "error");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function exportDateProperties($date) {
    // Check if we have POST data and prepare date parts array for looping.
    $dateParts = [ "year", "month", "day" ];

    // Export possibly set value to class scope.
    $this->value = $date;
    // @devStart
    // @codeCoverageIgnoreStart
    if (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $this->value) == false) {
      throw new \LogicException("The value attribute must be a valid date in W3C format");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    // Date format is always "Y-m-d".
    $date = explode("-", $this->value);
    foreach ($dateParts as $delta => $property) {
      if (isset($date[$delta])) {
        $date[$delta] = (integer) $date[$delta];
        // Only export to class scope if the date part is non zero.
        if ($date[$delta]) {
          $this->{$property} = $date[$delta];
        }
      }
    }

    // Export possibly submitted POST data to class scope and override exported values.
    foreach ($dateParts as $property) {
      $this->{$property} = $this->request->filterInput(INPUT_GET, "{$this->id}[{$property}]", $this->{$property}, FILTER_VALIDATE_INT);
    }

    return $this;
  }

  /**
   * Validate the submitted Date.
   *
   * @param string $date
   *   The user submitted date to validate.
   * @param null|array $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid date.
   */
  protected function validateValue($date, &$errors) {
    $this->exportDateProperties($date);
    // Month and year are mandatory when a day is present.
    if ($this->day && (!$this->month || !$this->year)) {
      $errors[self::ERROR_MISSING_MANDATORY] = $this->intl->t("Month and Year are mandatory in “{0}” date.", [ $this->label ]);
      return $date;
    }
    // Year is mandatory when a month is present.
    if ($this->month && !$this->year) {
      $errors[self::ERROR_MISSING_MANDATORY] = $this->intl->t("Year is mandatory in “{0}” date.", [ $this->label ]);
      return $date;
    }
    // Always validate the year right away against the request range.
    if ($this->year > $this->yearMax) {
      $errors[self::ERROR_YEAR_MAX] = $this->intl->t("The year {0} must not be greater than {1}.", [ $this->year, $this->yearMax ]);
      return $date;
    }
    if ($this->year < $this->yearMin) {
      $errors[self::ERROR_YEAR_MAX] = $this->intl->t("The year {0} must not be less than {1}.", [ $this->year, $this->yearMin ]);
      return $date;
    }

    // Validate the given date depending on submitted parts.
    if ($this->month && $this->day) {
      $this->value = "{$this->year}-{$this->month}-{$this->day}";
      $date        = \DateTime::createFromFormat(DATE_W3C, $this->value);
      if ($date === false || ($errors = $date->getLastErrors())) {
        $errors[self::ERROR_INVALID] = $this->intl->t("The “{0}” date is invalid.", [ $this->label ]);
        return $date;
      }
    }
    elseif ($this->month) {
      if ($this->month > 12) {
        $errors[self::ERROR_INVALID] = $this->intl->t("The month {0} must not be greater than {1}.", [ $this->month, 12 ]);
        return $date;
      }
      if ($this->month < 1) {
        $errors[self::ERROR_INVALID] = $this->intl->t("The month {0} must not be less than {1}.", [ $this->month, 1 ]);
        return $date;
      }
      $this->value = "{$this->year}-{$this->month}-00";
    }
    else {
      $this->value = "{$this->year}-00-00";
    }

    return $this;
  }

}
