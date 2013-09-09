<?php

/* !
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

/**
 * Default HTML input form element (HTML default is type text).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Input extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * Global identifier to access this element.
   *
   * @var string
   */
  public $id;

  /**
   * Array containing all attributes of this form element.
   *
   * @var array
   */
  public $attributes = [];


  /**
   * The default value of this form element.
   *
   * Will be used if this field is not required and the user didn't submit any value (e.g. GMT if the user should choose
   * a timezone).
   *
   * @var string
   */
  public $defaultValue;

  /**
   * Flag indicating if this element is disabled or not. Please note that disabled elements will not be submitted by
   * the browser.
   *
   * @var boolean
   */
  public $disabled = false;

  /**
   * The help text of this element.
   *
   * @var string
   */
  public $help;

  /**
   * The label text of the input element.
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
   * Flag indicating if this element is readonly or not.
   *
   * @var boolean
   */
  public $readonly = false;

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
   * @param string $id
   *   The global identifier of the input element.
   * @param array $attributes [optional]
   *   Additional attributes for this input element, the following attributes are set automatically:
   *   <ul>
   *     <li><code>"id"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"name"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex</li>
   *   </ul>
   *   The <code>"value"</code> will be overwritten by any user submitted <var>$_POST[$id]</var>.
   * @param string $defaultValue [optional]
   *   The default value of this form element. This will be used if the element is not required and no value was
   *   submitted by the user (e.g. GMT if the user should choose a timezone).
   */
  public function __construct($id, array $attributes = null, $defaultValue = "") {
    $this->id = $id;
    $this->attributes = $attributes;
    $this->attributes["id"] = $id;
    $this->attributes["name"] = $id;
    $this->attributes["role"] = "textbox";
    $this->attributes["tabindex"] = $this->getTabindex();
    $this->defaultValue = $defaultValue;
    $this->labelAttributes = [ "for" => $id ];

    // We want to prefill all input elements with the user submitted data if e.g. there is an error during submission
    // and the form is displayed again.
    if (!empty($_POST[$this->id])) {
      $this->attributes["value"] = $this->checkPlain($_POST[$this->id]);
    }
    elseif (!empty($_GET[$this->id])) {
      $this->attributes["value"] = $this->checkPlain($_GET[$this->id]);
    }
    elseif (empty($this->attributes["value"]) && !empty($defaultValue)) {
      $this->attributes["value"] = $this->checkPlain($defaultValue);
    }
  }

  /**
   * Get string representation of this form element.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The string representation of this form element.
   */
  public function __toString() {
    return "<label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label>{$this->help()}<input{$this->expandTagAttributes($this->attributes)}>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Disable this form element.
   *
   * @return this
   */
  public function disable() {
    $this->attributes["aria-disabled"] = "true";
    $this->attributes[] = "disabled";
    $this->disabled = true;
    return $this;
  }

  /**
   * Get the help of this element (if any).
   *
   * @return string
   *   The help of this element or an empty string if no help text was set.
   */
  public function help() {
    return empty($this->help) ? "" : "<span class='form-help popup-container'><i class='icon icon--help-circled'></i><small class='popup'>{$this->help}</small></span>";
  }

  /**
   * Mark this form element as invalid.
   *
   * @return this;
   */
  public function invalid() {
    $this->addClass("invalid", $this->attributes);
    $this->attributes["aria-invalid"] = "true";
    $this->valid = false;
    return $this;
  }

  /**
   * Mark this form element as read only.
   *
   * @return this
   */
  public function readyonly() {
    $this->attributes["aria-readonly"] = "true";
    $this->attributes[] = "readonly";
    $this->readonly = true;
    return $this;
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
   * Validate the user submitted data.
   *
   * @return this
   */
  public function validate($inputType) {
    global $i18n;
    $filtered = filter_input($inputType, $this->id);
    if (preg_match("//u", $filtered) === false) {
      throw new ValidatorException($i18n->t("One or more field contains illegal characters."));
    }
    $this->value = $filtered;
    return $this;
  }

}
