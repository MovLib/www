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
namespace MovLib\Partial\FormElement;

use \MovLib\Presentation\Partial\Date;

/**
 * Input date form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDate extends \MovLib\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The form element's type.
   *
   * @var string
   */
  const TYPE = "date";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct($id, $label, &$value, array $attributes = null) {
    if (isset($attributes["placeholder"])) {
      throw new \LogicException("Date input's aren't allowed to have a placeholder attribute");
    }
    foreach ([ "max", "min", "value" ] as $attribute) {
      if (isset($attributes[$attribute])) {
        if (is_string($attributes[$attribute]) && preg_match(Date::REGEXP_W3C, $attributes[$attribute]) == false) {
          throw new \InvalidArgumentException("Date {$attribute} must be in W3C format if passed as string");
        }
        elseif (!($attributes[$attribute] instanceof \DateTime)) {
          throw new \InvalidArgumentException("Date {$attribute} must be either a string in W3C format or a \\DateTime instance");
        }
      }
    }
    parent::__construct($id, $label, $value, $attributes);
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * Get the input date form element.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The input date form element.
   */
  public function __toString() {
    foreach ([ "max", "min", "value" ] as $attribute) {
      if (isset($this->attributes[$attribute]) && $this->attributes[$attribute] instanceof \DateTime) {
        $this->attributes[$attribute] = $this->attributes[$attribute]->format(Date::FORMAT_W3C);
      }
    }
    return parent::__toString();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted date.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $text
   *   The user submitted text to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid and sanitized text.
   */
  protected function validateValue($date, &$errors) {
    global $i18n;

    // Try to parse the date according to the W3C standard.
    $date = \DateTime::createFromFormat(Date::FORMAT_W3C, $date);

    // Check if parsing the date according to the format failed.
    if ($date === false || (($errors = $date->getLastErrors()) && ($errors["error_count"] !== 0 || $errors["warning_count"] !== 0))) {
      $errors = $i18n->t("The “{0}” date is invalid, only the following format is valid: {format}.", [ $this->label, "format" => Date::FORMAT_W3C ]);
    }

    // Validate maximum date value if present and only if we have no errors so far.
    if (!$errors && isset($this->attributes["max"])) {
      $max = \DateTime::createFromFormat(Date::FORMAT_W3C, $this->attributes["max"]);
      if ($date > $max) {
        $errors = $i18n->t("The date {0} must not be greater than {1}.", [
          \IntlDateFormatter::create($i18n->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)->format($date),
          \IntlDateFormatter::create($i18n->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)->format($max),
        ]);
      }
    }

    // Validate minimum date value if present and only if we have no errors so far.
    if (!$errors && isset($this->attributes["min"])) {
      $min = \DateTime::createFromFormat(Date::FORMAT_W3C, $this->attributes["min"]);
      if ($date < $min) {
        $errors = $i18n->t("The date {0} must not be less than {1}.", [
          \IntlDateFormatter::create($i18n->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)->format($date),
          \IntlDateFormatter::create($i18n->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE)->format($min),
        ]);
      }
    }

    return $date;
  }

}
