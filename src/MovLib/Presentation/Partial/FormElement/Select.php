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

/**
 * Description of Select
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


  public function __construct($id, $label, array $options, $value = null, array $attributes = null, array $labelAttributes = null) {
    parent::__construct($id, $attributes, $label, $labelAttributes);
    $this->value = isset($_POST[$this->id]) && isset($options[$this->id]) ? $options[$this->id] : $value;
    $this->options = $options;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    $disabled = $this->required === true ? " disabled" : null;
    $selected = isset($this->value) ? null : " selected";
    $options = "<option{$disabled}{$selected}>{$i18n->t("Please Select …")}</option>";
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
    return $this;
  }

}
