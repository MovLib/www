<?php

/* !
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

/**
 * Input float form element.
 *
 * <b>Do not use this input element for currencies!</b>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputFloat extends \MovLib\Partial\FormElement\AbstractInputNumber {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InputFloat";
  // @codingStandardsIgnoreEnd

  /**
   * Get the input float form element.
   *
   * @return string
   *   The input float form element.
   */
  public function __toString() {
    if (!isset($this->attributes["step"])) {
      $this->attributes["step"] = "any";
    }
    return parent::__toString();
  }

  /**
   * Validate the submitted float.
   *
   * @param string $value
   *   The user submitted float to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return float
   *   The valid and sanitized float.
   */
  protected function validateValue($value, &$errors) {
    // Make sure that the value is an actual integer value.
    if (($float = filter_var($value, FILTER_VALIDATE_FLOAT)) === false) {
      $errors[self::ERROR_INVALID] = $this->intl->t("The “{0}” field must be an integer.", [ $this->label ]);
      return $value;
    }

    // We validate the range in an extra block because we want to display proper error messages.
    if (isset($this->attributes["max"]) && $float > $this->attributes["max"]) {
      $errors[self::ERROR_MAX] = $this->intl->t("The “{0}” is too large, the value must be {1,number} or less.", [ $this->attributes["max"] ]);
    }
    elseif (isset($this->attributes["min"]) && $float < $this->attributes["min"]) {
      $errors[self::ERROR_MIN] = $this->intl->t("The “{0}” is too low, the value must be {1,number} or more.", [ $this->attributes["min"] ]);
    }

    return $float;
  }

}
