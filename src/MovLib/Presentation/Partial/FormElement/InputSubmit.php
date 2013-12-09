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

/**
 * HTML input submit form element.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputSubmit extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form elements attributes.
   *
   * @var array
   */
  public $attributes;

  /**
   * Global identifier to access this element.
   *
   * @var string
   */
  public $id;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type submit.
   *
   * @param string $value
   *   The translated text of the input element.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied.
   * @param string $id [optional]
   *   The global unique identifier.
   */
  public function __construct($value, array $attributes = null, $id = "submit") {
    $this->attributes          = $attributes;
    $this->attributes["id"]    = $id;
    $this->attributes["type"]  = "submit";
    $this->attributes["value"] = $value;
    $this->id                  = $id;
  }

  /**
   * Get string representation of this form element.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The string representation of this form element.
   */
  public function __toString() {
    return "<input{$this->expandTagAttributes($this->attributes)}>";
  }

}
