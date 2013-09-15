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
 * HTML button form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Button extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The button's attributes array.
   *
   * @var array
   */
  public $attributes;

  /**
   * The button's content.
   *
   * @var string
   */
  public $content;

  /**
   * The button's state.
   *
   * @var boolean
   */
  public $disabled = false;

  /**
   * The button's global unique identifier.
   *
   * @var string
   */
  public $id;

  /**
   * The button's validation status.
   *
   * @var boolean
   */
  public $valid = true;

  /**
   * The button's value after validation.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML button form element.
   *
   * @param string $id
   *   The global unique identifier of this form element.
   * @param string $content
   *   The (HTML) content of the button.
   * @param array $attributes [optional]
   *   Additional attributes that should be set on this form element, defaults to no additional attributes.
   */
  public function __construct($id, $content, array $attributes = null) {
    $this->id = $id;
    $this->content = $content;
    $this->attributes = $attributes;
    $this->attributes["id"] = $id;
    $this->attributes["name"] = $id;
    $this->attributes["tabindex"] = $this->getTabindex();
    $this->addClass("button", $this->attributes);
  }

  /**
   * Get the button's string representation.
   *
   * @return string
   *   The button's string representation.
   */
  public function __toString() {
    return "<button{$this->expandTagAttributes($this->attributes)}>{$this->content}</button>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Disable the button.
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
   * Mark button as invalid.
   *
   * @return this
   */
  public function invalid() {
    $this->attributes["aria-invalid"] = "true";
    $this->addClass("invalid", $this->attributes);
    $this->valid = false;
    return $this;
  }

  /**
   * The button's validation method.
   *
   * Buttons can be used within the elements array of a form, but the value is not validated by default because it's
   * not clear what kind of data a button contains.
   *
   * @return this
   */
  public function validate() {
    $this->value = $_POST[$this->id];
    return $this;
  }

}