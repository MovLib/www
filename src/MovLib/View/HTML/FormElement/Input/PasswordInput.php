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
   * There is absolutely nothing that we could do at this point. Passwords always need special validation and that's
   * something the presenter should take care of. An automated approach is senseless as we have no clue about where this
   * form element is implemented and what it's purpose is. We simply copy over the submitted password to class scope
   * and are done with it.
   *
   * Note that the empty check is performed by <code>AbstractFormView</code>.
   *
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    $this->value = $_POST[$this->id];
    return $this;
  }

}
