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
 * <b>IMPORTANT!</b> The input form element of type password doesn't have a value property, instead you have to access
 * the password via the global POST array. This is intentional, we want to make sure that developers realize that the
 * password isn't validated or sanitized at all, it's the pure raw user submitted data, without any alternations and
 * it should never be altered in any way (beside the hashing wish happens in the Data layer).
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputPassword extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {
  use \MovLib\Presentation\Partial\FormElement\TraitReadonly;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type password.
   *
   * @param string $id [optional]
   *   The form element's global identifier, defaults to <code>"password"</code>.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param string $label [optional]
   *   The form element's label content, defaults to <code>$i18n->t("Password")</code>.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id = "password", array $attributes = null, $label = null, array $labelAttributes = null) {
    global $i18n;
    parent::__construct($id, $attributes, $label, $labelAttributes);
    $this->attributes["type"] = "password";
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your password");
    }
    if (!isset($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your password in this field.");
    }
    if (!$this->label) {
      $this->label = $i18n->t("Password");
    }

    // A password field is always required!
    $this->required();
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Password's aren't validated!
   *
   * It's easier to implement an empty method at this point, rather than checking for the existence of this method in
   * <code>\MovLib\Presentation\Partial\Form</code>, in particular if you realize that this is the absolutely only
   * form element (beside the special hidden and action elements, but they aren't validated) that would not have the
   * validate method, plus the password element is used very little compared to other form elements.
   *
   * @return this
   */
  public function validate() {
    return $this;
  }

}
