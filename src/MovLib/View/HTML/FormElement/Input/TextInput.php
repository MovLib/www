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

  private $label;

  public $labelAttributes;

  public $help;

  private $valid = true;

  private $required = false;

  public function __construct($name, $label, $attributes = null) {
    global $i18n;
    $this->id = $name;
    $this->label = ucfirst($label);
    $this->labelAttributes = [ "for" => $name ];
    $this->attributes = $attributes;
    $this->attributes["id"] = $name;
    $this->attributes["name"] = $name;
    $this->attributes["tabindex"] = $this->getTabindex();
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

  public function __toString() {
    return "<label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label>{$this->help()}<input{$this->expandTagAttributes($this->attributes)}>";
  }

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

  public function validate() {

  }

}
