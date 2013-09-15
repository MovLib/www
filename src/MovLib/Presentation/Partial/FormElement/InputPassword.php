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
namespace MovLib\Presentation\Partial\FormElement;

/**
 * HTML input type password form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputPassword extends \MovLib\Presentation\Partial\FormElement\Input {

  /**
   * Instantiate new HTML input form element of type password.
   *
   * @param array $attributes [optional]
   *   Additional attributes that should be set on this form element, defaults to no additional attributes.
   * @param string $id [optional]
   *   The global unique identifier of this form element.
   */
  public function __construct(array $attributes = null, $id = "password") {
    parent::__construct($id, $attributes);
    $this->attributes["type"] = "password";
    $this->required(); // A password field is always required!

    // Only a presenter is allowed to prefill a password input!
    if (!empty($this->attributes["value"]) && empty($attributes["value"])) {
      unset($this->attributes["value"]);
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    if (!$this->label) {
      $this->label = $i18n->t("Password");
    }
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your password");
    }
    if (!isset($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your password in this field.");
    }
    return parent::__toString();
  }

}
