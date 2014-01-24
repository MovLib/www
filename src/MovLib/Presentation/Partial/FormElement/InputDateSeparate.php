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

namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * Date input consisting of three number input elements for day, month and year.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateSeparate extends \MovLib\Presentation\Partial\FormElement\InputDate {

  /**
   * The date's day field.
   *
   * @var integer
   */
  protected $day;

  /**
   * The date's month field.
   *
   * @var integer
   */
  protected $month;

  /**
   * The required flag.
   *
   * @var boolean
   */
  protected $required = false;

  /**
   * The date's year field.
   *
   * @var integer
   */
  protected $year;

  /**
   * Instantiate new date input with day, month and year fields.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $id
   *   The date's global unique identifier.
   * @param string $label
   *   The date's translated label text.
   * @param array $attributes [optional]
   *   The date's additional attributes.
   */
  public function __construct($id, $label, array $attributes = null) {
    global $i18n;
    parent::__construct($id, $label, $attributes);
    unset($this->attributes["name"]);
    unset($this->attributes["type"]);
    if (isset($this->attributes["required"])) {
      $this->required = true;
      unset($this->attributes["required"]);
    }
    if (isset($this->attributes["max"])) {
      unset($this->attributes["max"]);
    }
    if (isset($this->attributes["min"])) {
      unset($this->attributes["min"]);
    }
    $this->attributes["class"] = isset($this->attributes["class"]) ? $this->attributes["class"] . " date-separate" : "date-separate";
    if (isset($_POST["{$this->id}-day"])) {
      $this->day = (integer) $_POST["{$this->id}-day"];
    }
    if (isset($_POST["{$this->id}-month"])) {
      $this->month = (integer) $_POST["{$this->id}-month"];
    }
    if (isset($_POST["{$this->id}-year"])) {
      $this->year = (integer) $_POST["{$this->id}-year"];
    }
  }

  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    // @todo: Continue with form building and date styling.
    return "{$this->help}<fieldset{$this->expandTagAttributes($this->attributes)}><legend>{$this->label}</legend><p>" .
      "<label class='vh' for='{$this->id}-day'>{$i18n->t("Day")}</label><input id='{$this->id}-day' name='{$this->id}-day' placeholder='{$i18n->t("dd")}' type='number' value='{$this->day}'>" .
      "<label class='vh' for='{$this->id}-month'>{$i18n->t("Month")}</label><input id='{$this->id}-month' name='{$this->id}-month' placeholder='{$i18n->t("mm")}' type='number' value='{$this->month}'>" .
      "<label class='vh' for='{$this->id}-year'>{$i18n->t("Year")}</label><input id='{$this->id}-year' name='{$this->id}-year' placeholder='{$i18n->t("yyyy")}' type='number' value='{$this->year}'>" .
    "</p></fieldset>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    // Evaluate The fields for empty values, 0 is also considered empty.
    $dayEmpty   = empty($this->day) || $this->day === 0;
    $monthEmpty = empty($this->month) || $this->month === 0;
    $yearEmpty  = empty($this->year) || $this->year === 0;

    // Validate if the field is mandatory.
    if ($dayEmpty && $monthEmpty && $yearEmpty) {
      if ($this->required === true) {
        throw new ValidationException($i18n->t("The “{0}” date is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Month and year are mandatory when a day is present.
    if (!$dayEmpty && ($monthEmpty || $yearEmpty)) {
      throw new ValidationException($i18n->t("Month and Year are mandatory in “{0}” date", [ $this->label ]));
    }

    // Year is mandatory when a month is present.
    if (!$monthEmpty && $yearEmpty) {
      throw new ValidationException($i18n->t("Year is mandatory in “{0}” date", [ $this->label ]));
    }

    // Construct the date value for validation and store the actual value.
    // We have all fields.
    if (!$monthEmpty && !$dayEmpty) {
      $actualValue = "{$this->year}-{$this->month}-{$this->day}";
      $this->value = $actualValue;
    }
    // The day is missing.
    elseif (!$monthEmpty) {
      $actualValue = "{$this->year}-{$this->month}";
      // We need a valid day for the validation, use value 1.
      $this->value = "{$actualValue}-1";
    }
    // Month and day are missing, the year is always present at this point.
    else {
      $actualValue    = "{$this->year}";
      // We need valid month and day for the validation, use value 1 for both.
      $this->value = "{$actualValue}-1-1";
    }

    // Make use of the full date validation and set the value to the actual one.
    parent::validate();
    $this->value = $actualValue;
    return $this;
  }

}
