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

use \MovLib\Presentation\Validation\PlainText;

/**
 * HTML input type text form element.
 *
 * In contrast to the default input element, this is specialized for plain text input. The user submitted string is
 * sanitized. No validation!
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/association-of-controls-and-forms.html#attr-fe-inputmode
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputText extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {
  use \MovLib\Presentation\Partial\FormElement\TraitReadonly;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form element's value.
   *
   * @var null|string
   */
  public $value;

  /**
   * Instance of the validation class that should be used for validation.
   *
   * @var \MovLib\Presentation\Validation\InterfaceValidation
   */
  public $validator;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type text.
   *
   * @param string $id
   *   The form element's global identifier.
   * @param string $label
   *   The form element's label content.
   * @param string $value [optional]
   *   The form element's default value.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id, $label, $value = null, array $attributes = null, array $labelAttributes = null) {
    parent::__construct($id, $attributes, $label, $labelAttributes);
    $this->attributes["type"] = "text";
    $this->value = isset($_POST[$this->id]) ? $_POST[$this->id] : $value;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    // No need to set the value if the value is empty or NULL.
    if (!empty($this->value)) {
      $this->attributes["value"] = $this->value;
    }
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    $this->value = empty($this->validator) ? (new PlainText($this->value))->validate() : $this->validator->set($this->value)->validate();
    return $this;
  }

}
