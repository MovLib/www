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
 * HTML input type hidden form element.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputHidden {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Global identifier to access this element.
   *
   * @var string
   */
  public $id;

  /**
   * The data that is stored in the value attribute of this element.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new input hidden form element.
   *
   * @param string $id
   *   The global identifier.
   * @param mixed $value
   *   The data that should be stored in the value attribute.
   */
  public function __construct($id, $value) {
    $this->id = $id;
    $this->value = $value;
  }

  /**
   * Get string representation of this form element.
   *
   * @return string
   *   String representation of this form element.
   */
  public function __toString() {
    return "<input name='{$this->id}' type='hidden' value='{$this->value}'>";
  }

}
