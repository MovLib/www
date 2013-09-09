<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\FormElement;

use \MovLib\Exception\ValidatorException;

/**
 * HTML input type email form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmail extends \MovLib\Presentation\FormElement\Input {

  /**
   * Instantiate new HTML input form element of type email.
   *
   * @param array $attributes [optional]
   *   Additional attributes that should be set on this form element, defaults to no additional attributes.
   * @param string $id [optional]
   *   The global unique identifier of this form element.
   * @param string $defaultValue [optional]
   *   The default value of this form element, defaults to empty string.
   */
  public function __construct(array $attributes = null, $id = "email", $defaultValue = "") {
    parent::__construct($id, $attributes, $defaultValue);
    $this->attributes["pattern"] = "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$";
    $this->attributes["type"] = "email";
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    if (!$this->label) {
      $this->label = $i18n->t("Email Address");
    }
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your email address");
    }
    if (!isset($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your email address in this field.");
    }
    return parent::__toString();
  }

  /**
   * Validate the user submitted email address.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param int $inputType
   *   One of PHP's built in <var>INPUT_*</var> constants.
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate($inputType) {
    global $i18n;
    $filtered = filter_input($inputType, $this->id, FILTER_VALIDATE_EMAIL);

    // Check if the filter failed or if the filtered input is empty.
    if ($filtered === false || empty($filtered)) {
      throw new ValidatorException($i18n->t("The email address does not appear to be valid."));
    }

    // Check if input is valid UTF-8.
    if (preg_match("//u", $filtered) == false) {
      throw new ValidatorException($i18n->t("The email address contains illegal characters."));
    }

    $this->value = $filtered;
    return $this;
  }

}
