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

use \MovLib\Utility\Sanitizer;
use \MovLib\View\HTML\FormElement\AbstractFormElement;

/**
 * Represents the most basic input element, the <code><input type="text"></code>.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class TextInput extends AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Public Properties


  /**
   * The help text of this element.
   *
   * @var string
   */
  public $help;

  /**
   * The human readable label describing this element.
   *
   * @var string
   */
  public $label;

  /**
   * Attributes array of the label element.
   *
   * @var array
   */
  public $labelAttributes;

  /**
   * Flag indicating if this element is required or not.
   *
   * @var boolean
   */
  public $required = false;

  /**
   * Flag indicating if the value of this element is valid or not.
   *
   * @var boolean
   */
  public $valid = true;

  /**
   * The value of this element after it was validated. Please note that this will be null if validation failed.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new text input form element.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param string $name
   *   The global name of this form element.
   * @param string $label
   *   The human readable global name of this form element.
   * @param array $attributes [optional]
   *   Array with attributes that should be applied to this element.
   */
  public function __construct($name, $label, $attributes = []) {
    global $i18n;
    $this->id = $name;
    $this->label = ucfirst($label);
    $this->labelAttributes = [ "for" => $name ];
    $this->attributes = array_merge([
      "id"       => $name,
      "name"     => $name,
      "tabindex" => $this->getTabindex(),
    ], $attributes);
    if (empty($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your {0}", [ $label ]);
    }
    if (empty($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your {0} in this field.", [ $label ]);
    }
    if (!empty($_POST[$this->id])) {
      $this->attributes["value"] = Sanitizer::escapeSingleQuotes($_POST[$this->id]);
    }
  }

  /**
   * Get string representation of this form element.
   *
   * @return string
   *   The string representation of this form element.
   */
  public function __toString() {
    return "<label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label>{$this->help()}<input{$this->expandTagAttributes($this->attributes)}>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the help of this element (if any).
   *
   * @return string
   *   The help of this element or an empty string if no help text was set.
   */
  public function help() {
    return empty($this->help) ? "" : "";
  }

  /**
   * Mark this form element as required.
   *
   * A form element that is marked as required will also fail during validation if the submitted value is empty.
   * Browsers supporting this feature will prevent form submission if this element is empty.
   *
   * @return this
   */
  public function required() {
    $this->attributes["aria-required"] = "true";
    $this->attributes[] = "required";
    $this->required = true;
    return $this;
  }

  /**
   * Validate user input after submission.
   *
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    $this->value = $_POST[$this->attributes["name"]];
    return $this;
  }

}
