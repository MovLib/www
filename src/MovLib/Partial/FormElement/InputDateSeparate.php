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

use \MovLib\Component\Date;

/**
 * Date input consisting of three number input elements for day, month and year.
 *
 * @property \MovLib\Component\Date $value
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateSeparate extends \MovLib\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for an invalid date.
   *
   * @var integer
   */
  const ERROR_INVALID = 1;

  /**
   * Error code for an invalid year.
   *
   * @var integer
   */
  const ERROR_INVALID_YEAR = 2;

  /**
   * Error code for an invalid month.
   *
   * @var integer
   */
  const ERROR_INVALID_MONTH = 3;

  /**
   * Error code for an invalid day.
   *
   * @var integer
   */
  const ERROR_INVALID_DAY = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Attributes for Date input fields.
   *
   * @var array
   */
  protected $inputDateAttributes;


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
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, \MovLib\Component\Date &$value = null, array $attributes = null, array $options = null) {
    parent::__construct($container, $id, $label, $value, $attributes);
    if (isset($options["year_max"])) {
      // @devStart
      // @codeCoverageIgnoreStart
      if ((integer) $options["year_max"] > 9999) {
        throw new \LogicException("The year_max attribute have to be smaller than 10000.");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $this->yearMax = (integer) $options["year_max"];
    }
    else {
      $this->yearMax = 9999;
    }
    if (isset($options["year_min"])) {
      // @devStart
      // @codeCoverageIgnoreStart
      if ((integer) $options["year_min"] < 1000) {
        throw new \LogicException("The year_min attribute have to be bigger than 999.");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $this->yearMin = (integer) $options["year_min"];
    }
    else {
      $this->yearMin = 1000;
    }
    // @devStart
    // @codeCoverageIgnoreStart
    if ($this->yearMin > $this->yearMax) {
      throw new \LogicException("The year_min attribute have to be smaller than the year_max attribute.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $this->inputDateAttributes = [
      "y" => [
        "id"          => "{$this->id}-year",
        "max"         => $this->yearMax,
        "min"         => $this->yearMin,
        "name"        => "{$this->id}[year]",
        "placeholder" => $this->intl->t("yyyy"),
        "type"        => "number",
        "value"       => isset($this->value->year)? $this->value->year : null,
      ],
      "m" => [
        "id"          => "{$this->id}-month",
        "name"        => "{$this->id}[month]",
        "max"         => 12,
        "min"         => 1,
        "placeholder" => $this->intl->t("mm"),
        "type"        => "number",
        "value"       => isset($this->value->month)? $this->value->month : null,
      ],
      "d" => [
        "id"          => "{$this->id}-day",
        "max"         => 310,
        "min"         => 1,
        "name"        => "{$this->id}[day]",
        "placeholder" => $this->intl->t("dd"),
        "type"        => "number",
        "value"       => isset($this->value->day)? $this->value->day : null,
      ],
    ];
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
      return
        "{$this->required}{$this->helpPopup}<fieldset{$this->expandTagAttributes($this->attributes)}>" .
          "<legend>{$this->label}</legend>" .
          "<label class='s s2'><span class='vh'>{$this->intl->t("Year")}</span><input{$this->expandTagAttributes($this->inputDateAttributes["y"])}></label>" .
          "<label class='s s1'><span class='vh'>{$this->intl->t("Month")}</span><input{$this->expandTagAttributes($this->inputDateAttributes["m"])}></label>" .
          "<label class='s s1'><span class='vh'>{$this->intl->t("Day")}</span><input{$this->expandTagAttributes($this->inputDateAttributes["d"])}></label>" .
        "</fieldset>"
      ;

    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function validate(&$errors) {
    parent::validate($errors);
    if (isset($errors[self::ERROR_INVALID_DAY]) || isset($errors[self::ERROR_INVALID])) {
      $this->inputDateAttributes["d"]["class"] = "invalid";
      $this->inputDateAttributes["d"]["aria-invalid"] = true;
    }
    if (isset($errors[self::ERROR_INVALID_MONTH]) || isset($errors[self::ERROR_INVALID])) {
      $this->inputDateAttributes["m"]["class"] = "invalid";
      $this->inputDateAttributes["m"]["aria-invalid"] = true;
    }
    if (isset($errors[self::ERROR_INVALID_YEAR]) || isset($errors[self::ERROR_INVALID])) {
      $this->inputDateAttributes["y"]["class"] = "invalid";
      $this->inputDateAttributes["y"]["aria-invalid"] = true;
    }
    return $this;
  }

  /**
   * Validate the submitted Date.
   *
   * @param array $value
   *   The user submitted date to validate.
   * @param null|array $errors
   *   Parameter to collect error messages.
   * @return \MovLib\Component\Date|null
   *   The valid date or null if there is no date.
   */
  protected function validateValue($value, &$errors) {
    // Validate mandatory year if required
    if (isset($this->attributes["required"]) ) {
      if (empty($value["year"])) {
        $errors[self::ERROR_REQUIRED] = $this->intl->t("The “{0}” is required.", [ $this->label ]);
      }
    }
    elseif (empty($value["year"]) && empty($value["month"]) && empty($value["day"])) {
      return null;
    }

    // Month and year are mandatory when a day is present.
    if (!empty($value["day"]) && (empty($value["month"]))) {
      $errors[self::ERROR_INVALID_MONTH] = $this->intl->t("Month is mandatory in “{0}” date.", [ $this->label ]);
    }
    if (!empty($value["day"]) && (empty($value["year"]))) {
      $errors[self::ERROR_INVALID_YEAR] = $this->intl->t("Year is mandatory in “{0}” date.", [ $this->label ]);
    }

    // Year is mandatory when a month is present.
    if (!empty($value["month"]) && empty($value["year"])) {
      $errors[self::ERROR_INVALID_YEAR] = $this->intl->t("Year is mandatory in “{0}” date.", [ $this->label ]);
    }

    if (!empty($value["day"])) {
      if ($value["day"] > 31) {
        $errors[self::ERROR_INVALID_DAY] = $this->intl->t("The day {0} must not be greater than {1}.", [ $value["day"], 31 ]);
      }
      elseif ($value["day"] < 1) {
        $errors[self::ERROR_INVALID_DAY] = $this->intl->t("The day {0} must not be less than {1}.", [ $value["day"], 1 ]);
      }
    }
    else {
      $value["day"] = "00";
    }

    if (!empty($value["month"])) {
      if ($value["month"] > 12) {
        $errors[self::ERROR_INVALID_MONTH] = $this->intl->t("The month {0} must not be greater than {1}.", [ $value["month"], 12 ]);
      }
      elseif ($value["month"] < 1) {
        $errors[self::ERROR_INVALID_MONTH] = $this->intl->t("The month {0} must not be less than {1}.", [ $value["month"], 1 ]);
      }
    }
    else {
      $value["month"] = "00";
    }

    if (!empty($value["year"])) {
      if ($value["year"] > $this->yearMax) {
        $errors[self::ERROR_INVALID_YEAR] = $this->intl->t("The year {0} must not be greater than {1}.", [ $value["year"], $this->yearMax ]);
      }
      elseif ($value["year"] < $this->yearMin) {
        $errors[self::ERROR_INVALID_YEAR] = $this->intl->t("The year {0} must not be less than {1}.", [ $value["year"], $this->yearMin ]);
      }

      if (empty($errors)) {
        $date = new Date("{$value["year"]}-{$value["month"]}-{$value["day"]}");
        // Check if parsing the date according to the format failed.
        if ($date === false || (($dateErrors = $date->getLastErrors()) && ($dateErrors["error_count"] !== 0 || $dateErrors["warning_count"] !== 0))) {
          $errors[self::ERROR_INVALID] = $this->intl->t("The “{0}” date is invalid, only the following format is valid: {format}.", [ $this->label, "format" => Date::W3C_DATE ]);
        }
        else {
          return $date;
        }
      }
      return (object) $value;
    }

    return null;
  }

}
