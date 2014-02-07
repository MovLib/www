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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;
use \MovLib\Presentation\Partial\Date;

/**
 * HTML input type date form element.
 *
 * The input date form element has it's very own validation method and doesn't utilize a validation class. The
 * validation process can't be changed. The rules regarding the format of the various attributes are fixed by the W3C
 * and this form element will only accept exaclty that.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDate extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The date input's date value in W3C format.
   *
   * @var string
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type date.
   *
   * @param string $id
   *   The date's global unique identifier.
   * @param string $label
   *   The date's translated label text.
   * @param array $attributes [optional]
   *   The date's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"type"</code> is set to <code>"date"</code></li>
   *   </ul>
   *   You <b>should not</b> override any of the default attributes.
   */
  public function __construct($id, $label, array $attributes = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($this->attributes["placeholder"])) {
      throw new \LogicException("Date input's are not allowed to have a placeholder attribute");
    }
    foreach ([ "max", "min", "value" ] as $attribute) {
      if (isset($attributes[$attribute]) && preg_match(Date::REGEXP_W3C, $attributes[$attribute]) === false) {
        throw new \LogicException("Date input's attribute {$attribute} must be in W3C date format (" . Date::FORMAT_W3C . ")");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct($id, $label, $attributes);
    if (isset($this->attributes["value"])) {
      $this->value = $this->attributes["value"];
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the rendered date input.
   *
   * @return string
   *   The rendered date input.
   */
  protected function render() {
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)} name='{$this->id}' type='date'></p>";
  }

  /**
   * Validate the user submitted date.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n;

    // Get the user input.
    $date = $this->filterInput($this->id);

    // Only continue if an actual value was submitted.
    if (empty($date)) {
      $this->value = null;
      if ($this->required === true) {
        throw new ValidationException($i18n->t("The “{0}” date is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Try to parse the submitted date.
    $date = \DateTime::createFromFormat(Date::FORMAT_W3C, $date);
    if ($date === false || (($errors = $date->getLastErrors()) && ($errors["error_count"] !== 0 || $errors["warning_count"] !== 0))) {
      throw new ValidationException($i18n->t("The “{0}” date is invalid.", [ $this->label ]));
    }

    if (isset($this->attributes["max"])) {
      $max = \DateTime::createFromFormat(Date::FORMAT_W3C, $this->attributes["max"]);
      if ($date > $max) {
        throw new ValidationException($i18n->t("The date {0} must not be greater than {1}.", [
          $i18n->formatDate($date, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
          $i18n->formatDate($max, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
        ]));
      }
    }

    if (isset($this->attributes["min"])) {
      $min = \DateTime::createFromFormat(Date::FORMAT_W3C, $this->attributes["min"]);
      if ($date < $min) {
        throw new ValidationException($i18n->t("The date {0} must not be less than {1}.", [
          $i18n->formatDate($date, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
          $i18n->formatDate($min, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
        ]));
      }
    }

    // Export submitted date to class scope.
    $this->value = $date;

    return $this;
  }

}
