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
class InputDate extends \MovLib\Presentation\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The maximum date.
   *
   * @var \DateTime
   */
  protected $max;

  /**
   * The minimum date.
   *
   * @var \DateTime
   */
  protected $min;


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
    parent::__construct($id, $label, $attributes);
    $this->attributes["data-format"] = "Y-m-d";
    $this->attributes["type"]        = "date";

    foreach ([ "max", "min" ] as $property) {
      if (isset($this->attributes[$property])) {
        $this->{$property} = \DateTime::createFromFormat(DATE_W3C, $this->attributes[$property]);
        // @devStart
        // @codeCoverageIgnoreStart
        if ($this->{$property} === false || ($errors = $this->{$property}->getLastErrors() && ($errors["error_count"] !== 0 || $errors["warning_count"] !== 0))) {
          throw new \LogicException("The {$property} attribute must be a valid date in W3C format");
        }
        // @codeCoverageIgnoreEnd
        // @devEnd
      }
    }

    // Placeholders are not permitted on date inputs by the HTML standard.
    if (isset($this->attributes["placeholder"])) {
      unset($this->attributes["placeholder"]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the user submitted date.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” date is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    $date = \DateTime::createFromFormat(DATE_W3C, $this->value);

    if ($date === false || ($errors = $date->getLastErrors() && ($errors["error_count"] !== 0 || $errors["warning_count"] !== 0))) {
      throw new ValidationException($i18n->t("The “{0}” date is invalid.", [ $this->label ]));
    }

    if ($this->max) {
      if ($date > $this->max) {
        throw new ValidationException($i18n->t("The date {0} must not be greater than {1}.", [
          $i18n->formatDate($date, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
          $i18n->formatDate($this->max, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
        ]));
      }
    }

    if ($this->min) {
      if ($date < $this->min) {
        throw new ValidationException($i18n->t("The date {0} must not be less than {1}.", [
          $i18n->formatDate($date, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
          $i18n->formatDate($this->min, null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE),
        ]));
      }
    }

    return $this;
  }

}
