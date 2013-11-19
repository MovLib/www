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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * Represents a HTML select form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Select  extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The select's options.
   *
   * @var array
   */
  public $options;

  /**
   * The selected value.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new select form element.
   *
   * @param string $id
   *   The select's global identifier.
   * @param string $label
   *   The select's label text.
   * @param array|\ArrayAccess $options
   *   The select's available options as associative array where the key is the content of the option's value-attribute
   *   and the array's value the option's display text.
   * @param mixed $value [optional]
   *   The selected option's value, defaults to <code>NULL</code> (no option is selected).
   * @param array $attributes [optional]
   *   Additional attributes for the textarea, defaults to <code>NULL</code> (no additional attributes).
   * @param string $help [optional]
   *   The textarea's help text, defaults to <code>NULL</code> (no help text).
   * @param boolean $helpPopup
   *   Whether the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
   */
  public function __construct($id, $label, $options, $value = null, array $attributes = null, $help = null, $helpPopup = true) {
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
    $this->options = $options;
    $this->value   = isset($_POST[$this->id]) ? $_POST[$this->id] : $value;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    //  The first child option element of a select element with a required attribute and without a multiple attribute,
    //  and whose size is 1, must have either an empty value attribute, or must have no text content.
    $emptyValue = empty($this->value);
    $selected   = $emptyValue ? " selected" : null;
    if (in_array("required", $this->attributes)) {
      $options = "<option disabled{$selected}>{$i18n->t("Please Select …")}</option>";
    }
    else {
      $options = "<option{$selected} value=''>{$i18n->t("None")}</option>";
    }

    foreach ($this->options as $value => $option) {
      $attributes = [];
      if (is_array($option)) {
        $attributes = $option[1];
        $option     = $option[0];
      }
      $attributes["value"] = $value;
      if (!$emptyValue && $this->value == $value) {
        $attributes[] = "selected";
      }
      $options .= "<option{$this->expandTagAttributes($attributes)}>{$option}</option>";
    }

    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><select{$this->expandTagAttributes($this->attributes)}>{$options}</select></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” select element is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    if (!isset($this->options[$this->value])) {
      throw new ValidationException($i18n->t("The submitted value {0} is not a valid option.", [ $this->placeholder($this->value) ]));
    }

    return $this;
  }

}
