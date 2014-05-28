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

use \MovLib\Component\Date;

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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InputDate";
  // @codingStandardsIgnoreEnd


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
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, \MovLib\Component\Date &$value, array $attributes = null) {
    assert(empty($attributes["placeholder"]), "Date input's aren't allowed to have a placeholder attribute");
    foreach ([ "max", "min", "value" ] as $attribute) {
      if (isset($attribute[$attribute])) {
        assert($attribute[$attribute] instanceof Date, "The attributes max, min, and value must be an instance of \\MovLib\\Component\\Date.");
      }
    }
    parent::__construct($container, $id, $label, $value, $attributes);
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * Get the input date form element.
   *
   * @return string
   *   The input date form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
      // @codeCoverageIgnoreEnd
      // @devEnd
      foreach ([ "max", "min", "value" ] as $attribute) {
        if (isset($this->attributes[$attribute])) {
          $this->attributes[$attribute] = $this->attributes[$attribute]->format(Date::W3C_DATE);
        }
      }
      return parent::__toString();
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
  protected function validateValue($date, &$errors) {
    // Try to parse the date according to the W3C standard.
    $date = new Date($date);

    // Check if parsing the date according to the format failed.
    if ($date === false || (($dateErrors = $date->getLastErrors()) && ($dateErrors["error_count"] !== 0 || $dateErrors["warning_count"] !== 0))) {
      $errors = $this->intl->t("The “{0}” date is invalid, only the following format is valid: {format}.", [ $this->label, "format" => Date::FORMAT_W3C ]);
    }

    // Validate maximum date value if present and only if we have no errors so far.
    if (!$errors && isset($this->attributes["max"])) {
      if ($date > $this->attributes["max"]) {
        $errors = $this->intl->t(
          "The date {0} must not be greater than {1}.",
          [ $date->formatIntl($this->intl->locale), $this->attributes["max"]->formatIntl($this->intl->locale) ]
        );
      }
    }

    // Validate minimum date value if present and only if we have no errors so far.
    if (!$errors && isset($this->attributes["min"])) {
      if ($date < $this->attributes["min"]) {
        $errors = $this->intl->t(
          "The date {0} must not be less than {1}.",
          [ $date->formatIntl($this->intl->locale), $this->attributes["min"]->formatIntl($this->intl->locale) ]
        );
      }
    }

    return $date;
  }

}
