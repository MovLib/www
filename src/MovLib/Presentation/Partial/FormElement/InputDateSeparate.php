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

use \MovLib\Presentation\Partial\FormElement\InputNumber;

/**
 * Date input consisting of three number input elements for day, month and year.
 *
 * @todo Extend and unify with InputDate
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDateSeparate extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {

  /**
   * The date's day field.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputNumber
   */
  protected $day;

  /**
   * The date's month field.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputNumber
   */
  protected $month;

  /**
   * The date's year field.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputNumber
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
    $this->attributes["class"] = isset($this->attributes["class"]) ? $this->attributes["class"] . " date-separate" : "date-separate";
    // @todo: Hard code HTML for number inputs.
    $this->day                 = new InputNumber("{$this->id}-day", $i18n->t("Day"), [ "max" => 31, "min" => 1 ]);
    $this->month               = new InputNumber("{$this->id}-month", $i18n->t("Month"), [ "max" => 12, "min" => 1 ]);
    $this->year                = new InputNumber("{$this->id}-year", $i18n->t("Year"), [ "max" => date("Y", strtotime("now")), "min" => 1800 ]);
  }

  /**
   * @inheritdoc
   */
  protected function render() {
    return "{$this->help}<fieldset{$this->expandTagAttributes($this->attributes)}><legend>{$this->label}</legend>{$this->day}{$this->month}{$this->year}</fieldset>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    $this->day->validate();
    $this->month->validate();
    $this->year->validate();
    // @todo: Validate date.
  }

}
