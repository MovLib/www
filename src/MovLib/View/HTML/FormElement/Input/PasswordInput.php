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
namespace MovLib\View\HTML\FormElement\Input;

use \MovLib\View\HTML\FormElement\Input\TextInput;

/**
 * Create new password input element.
 *
 * <b>IMPORTANT:</b> A password input element will only validate that the submitted password is not empty. Any further
 * validations have to be made in the presenter itself.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PasswordInput extends TextInput {

  /**
   * Instantiate new password input form element.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param array $attributes [optional]
   *   Set additional or overwrite the defaults.
   * @param string $name [optional]
   *   The global identifier for this instance.
   * @param string $label [optional]
   *   The already translated human readable label.
   */
  public function __construct($attributes = [], $name = "pass", $label = null) {
    global $i18n;
    parent::__construct($name, $label ?: $i18n->t("password"), $attributes);
    $this->attributes["type"] = "password";
    // Only allow a presenter to set the value of a password element!
    if (!empty($_POST[$this->id])) {
      unset($this->attributes["value"]);
    }
  }

  /**
   * Validate the user submitted password.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    global $i18n;
    if ($this->required === true && empty($_POST[$this->id])) {
      throw new ValidatorException($i18n->t("The {0} cannot be empty.", [ $this->label ]));
    }
    $this->value = $_POST[$this->id];
    return $this;
  }

}
