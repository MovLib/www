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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * Represents a HTML select form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Select  extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {
  use \MovLib\Presentation\Partial\FormElement\TraitReadonly;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The select's options.
   *
   * @var array
   */
  public $options;

  /**
   * The select's value.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML select form element.
   *
   * @param string $id
   *   The form element's global identifier.
   * @param string $label
   *   The form element's label content.
   * @param array $options
   *   The options of this select element as associative array where the key is the value of the option and the value
   *   the text, e.g.: <pre>array("value" => "text"); // <option value='value'>text</option></pre>
   * @param string $value [optional]
   *   The form element's default value.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id, $label, array $options, $value = null, array $attributes = null, array $labelAttributes = null) {
    parent::__construct($id, $attributes, $label, $labelAttributes);
    $this->options = $options;
    $this->value   = $value;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    $options = null;

    // If a select element only has one option to choose from and is required no default option can be present.
    if ($this->required === false) {
      $selected = isset($this->value) ? null : " selected";
      $options .= "<option{$selected} value=''>{$i18n->t("Please Select …")}</option>";
    }

    // Create HTML option for each option.
    foreach ($this->options as $value => $option) {
      $selected = $this->value == $value ? " selected" : null;
      $options .= "<option{$selected} value='{$value}'>{$option}</option>";
    }

    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><select{$this->expandTagAttributes($this->attributes)}>{$options}</select></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;
    if (!isset($_POST[$this->id]) || !isset($this->options[$_POST[$this->id]])) {
      throw new ValidationException($i18n->t("The submitted value {0} is not a valid option.", [ $this->placeholder($this->value) ]));
    }
    $this->value = $_POST[$this->id];
    return $this;
  }

}
