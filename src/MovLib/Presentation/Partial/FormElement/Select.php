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
class Select  extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * The select's options.
   *
   * @var array
   */
  public $options;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new select input element.
   *
   * @param string $id
   *   The form element's global identifier.
   * @param array $options
   *   The form element's options.
   * @param mixed $value [optional]
   *   The form element's default value.
   */
  public function __construct($id, array $options, $value = null) {
    parent::__construct($id, $value);
    $this->options = $options;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;

    //  The first child option element of a select element with a required attribute and without a multiple attribute,
    //  and whose size is 1, must have either an empty value attribute, or must have no text content.
    $selected = empty($this->value) ? " selected" : null;
    $options  = "<option{$selected} value=''>{$i18n->t("Please Select …")}</option>";

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
    if (!isset($this->options[$this->value])) {
      throw new ValidationException($i18n->t("The submitted value {0} is not a valid option.", [ $this->placeholder($this->value) ]));
    }
    return $this;
  }

}
