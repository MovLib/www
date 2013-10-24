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
 * HTML button form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Button extends \MovLib\Presentation\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The button's content.
   *
   * @var string
   */
  public $content;


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
    parent::__construct($id, null, $attributes);
    $this->content = $content;
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
   * The button's validation method.
   *
   * Buttons can be used within the elements array of a form, but the value is not validated by default because it's
   * not clear what kind of data a button contains.
   *
   * @return this
   */
  public function validate() {
    return $this;
  }

}
