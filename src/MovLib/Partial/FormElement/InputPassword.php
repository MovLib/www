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

/**
 * HTML input password form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class InputPassword extends \MovLib\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for weak password error message.
   *
   * @var integer
   */
  const ERROR_COMPLEXITY = 1;

  /**
   * Error code for too short password error message.
   *
   * @var integer
   */
  const ERROR_LENGTH = 2;

  /**
   * Minimum password length.
   *
   * @var int
   */
  const MIN_LENGTH = 8;

  /**
   * Regular expression pattern for password complexity validation (client and server side).
   *
   * @todo Replace the length with the class constant (available with PHP 5.6).
   * @var string
   */
  const PATTERN = "^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{8,}$";

  /**
   * The form element's type.
   *
   * @var string
   */
  const TYPE = "password";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the input password form element.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The input text form element.
   */
  public function __toString() {
    global $i18n;
    $this->attributes["pattern"] = self::PATTERN;
    $this->attributes["title"]   = $i18n->t("A password must contain lowercase and uppercase letters, numbers, and must be at least {0,number,integer} characters long.", [ self::MIN_LENGTH ]);
    return parent::__toString();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted raw password.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $rawPassword
   *   The user submitted raw password to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid raw password.
   */
  protected function validateValue($rawPassword, &$errors) {
    global $i18n;

    // Check that the password exceeds the minimum password length.
    if (mb_strlen($rawPassword) < self::MIN_LENGTH) {
      $errors[self::ERROR_LENGTH] = $i18n->t("The password is too short: it must be {0,number,integer} characters or more.", [ self::MIN_LENGTH ]);
    }

    // Check that the password is complex enough.
    if (preg_match("/" . self::PATTERN . "/", $rawPassword) == false) {
      $errors[self::ERROR_COMPLEXITY] = $i18n->t("The password is not complex enough: it must contain numbers plus lowercase and uppercase letters.");
    }

    return $rawPassword;
  }

}
