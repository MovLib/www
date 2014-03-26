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

use \MovLib\Exception\ValidationException;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new date input with day, month and year fields.
   *
   * @param string $id
   *   The date's global unique identifier.
   * @param string $label
   *   The date's translated label text.
   * @param null|string $value [optional]
   *   The date's default value, date in W3C format, defaults to no default value.
   * @param array $attributes [optional]
   *   The date's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"class"</code> is set to or extended with <code>"date-separate"</code></li>
   *   </ul>
   * @param array $options [optional]
   *   Associative array containing additional options:
   *   <ul>
   *     <li><code>"year_max"</code> set this to the maximum year that can be entered, defaults to <code>9999</code></li>
   *     <li><code>"year_min</code> set this to the minimum year that can be entered, defaults to <code>0</code></li>
   *   </ul>
   */
  public function __construct($id, $label, $value = null, array $attributes = null, array $options = null) {
    parent::__construct($id, $label, $attributes);

    if (isset($options["year_max"])) {
      $this->yearMax = $options["year_max"];
    }
    if (isset($options["year_min"])) {
      $this->yearMin = $options["year_min"];
    }

    // Check if we have POST data and prepare date parts array for looping.
    $dateParts = [ "year", "month", "day" ];

    // Export possibly set value to class scope.
    if ($value) {
      $this->value = $value;
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
    }

    // Export possibly submitted POST data to class scope and override exported values.
    foreach ($dateParts as $property) {
      $this->{$property} = $this->filterInput("{$this->id}-{$property}", $this->{$property}, FILTER_VALIDATE_INT);
    }
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
        "y" => [],
        "m" => [],
        "d" => [],
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
      return (string) new \MovLib\Presentation\Partial\Alert("<pre>{$e}</pre>", "Error Rendering Element", \MovLib\Presentation\Partial\Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the rendered date separate input element.
   *
   * @return string
   *   The rendered date separate input element.
   */
  protected function render() {
    // Always add CSS class for proper styling.
    $this->addClass("date-separate", $this->attributes);

    return
      "{$this->helpPopup}<fieldset{$this->expandTagAttributes($this->attributes)}><legend>{$this->label}</legend><p>" .
        "<label class='s s1'>" .
          "<span class='vh'>{$this->intl->t("Day")}</span>" .
          "<input id='{$this->id}-day' max='31' min='1' name='{$this->id}-day' placeholder='{$this->intl->t("dd")}' type='number' value='{$this->day}'>" .
        "</label>" .
        "<label class='s s1'>" .
          "<span class='vh'>{$this->intl->t("Month")}</span>" .
          "<input id='{$this->id}-month' max='12' min='1' name='{$this->id}-month' placeholder='{$this->intl->t("mm")}' type='number' value='{$this->month}'>" .
        "</label>" .
        "<label class='s s2'>" .
          "<span class='vh'>{$this->intl->t("Year")}</span>" .
          "<input id='{$this->id}-year' max='{$this->yearMax}' min='{$this->yearMin}' name='{$this->id}-year' placeholder='{$this->intl->t("yyyy")}' type='number' value='{$this->year}'>" .
        "</label>" .
      "</p></fieldset>"
    ;
  }

  /**
   * Validate the user submitted date.
   *
   * @return this
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    // Validate if the field is mandatory.
    if (!$this->day && !$this->month && !$this->year) {
      if ($this->required === true) {
        throw new ValidationException($this->intl->t("The “{0}” date is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Month and year are mandatory when a day is present.
    if ($this->day && (!$this->month || !$this->year)) {
      throw new ValidationException($this->intl->t("Month and Year are mandatory in “{0}” date.", [ $this->label ]));
    }

    // Year is mandatory when a month is present.
    if ($this->month && !$this->year) {
      throw new ValidationException($this->intl->t("Year is mandatory in “{0}” date.", [ $this->label ]));
    }

    // Always validate the year right away against the request range.
    if ($this->year > $this->yearMax) {
      throw new ValidationException("The year {0} must not be greater than {1}.", [ $this->year, $this->yearMax ]);
    }
    if ($this->year < $this->yearMin) {
      throw new ValidationException("The year {0} must not be less than {1}.", [ $this->year, $this->yearMin ]);
    }

    // Validate the given date depending on submitted parts.
    if ($this->month && $this->day) {
      $this->value = "{$this->year}-{$this->month}-{$this->day}";
      $date        = \DateTime::createFromFormat(DATE_W3C, $this->value);
      if ($date === false || ($errors = $date->getLastErrors() && ($errors["error_count"] !== 0 || $errors["warning_count"] !== 0))) {
        throw new ValidationException($this->intl->t("The “{0}” date is invalid.", [ $this->label ]));
      }
    }
    elseif ($this->month) {
      if ($this->month > 12) {
        throw new ValidationException("The month {0} must not be greater than {1}.", [ $this->month, 12 ]);
      }
      if ($this->month < 1) {
        throw new ValidationException("The month {0} must not be less than {1}.", [ $this->month, 1 ]);
      }
      $this->value = "{$this->year}-{$this->month}-00";
    }
    else {
      $this->value = "{$this->year}-00-00";
    }

    return $this;
  }

}
