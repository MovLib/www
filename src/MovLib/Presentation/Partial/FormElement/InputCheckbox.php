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
 * HTML input type checkbox form element.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputCheckbox extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {

  /**
   * The form element's value.
   *
   * @var boolean
   */
  public $value;

  /**
   * @inheritdoc
   */
  public function __construct($id, $label, array $attributes = null, $value = false) {
    parent::__construct($id, $label, $attributes);
    $this->attributes["type"] = "checkbox";
    $this->value              = $value;
    if (isset($_POST[$this->id])) {
      $this->value = (boolean) $_POST[$this->id];
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    if ($this->value === true) {
      $this->attributes[] = "checked";
    }
    return "{$this->help}<p><label class='checkbox'><input{$this->expandTagAttributes($this->attributes)}>{$this->label}</label></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;
    if (in_array("required", $this->attributes) && $this->value === false) {
      throw new ValidationException($i18n->t("The “{0}” checkbox is mandatory.", [ $this->label ]));
    }
    return $this;
  }

}
