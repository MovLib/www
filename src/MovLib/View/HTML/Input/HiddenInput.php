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
namespace MovLib\View\HTML\Input;

use \MovLib\View\HTML\Input\AbstractInput;

/**
 * Hidden input element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HiddenInput extends AbstractInput {

  /**
   * Instantiate new hidden form element. Please note that hidden form elements are always readonly form elements.
   *
   * @param string $name
   *   The name of this form element that will be used as global identifier.
   * @param mixed $value
   *   The value of the form element.
   * @param array $attributes [optional]
   *   Custom attributes that should be applied to the element.
   */
  public function __construct($name, $value, array $attributes = null) {
    $this->id = $name;
    $this->attributes = $attributes;
    $this->attributes["id"] = $name;
    $this->attributes["name"] = $name;
    $this->attributes["type"] = "hidden";
    $this->attributes["value"] = $value;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    return "<input{$this->expandTagAttributes($this->attributes)}>";
  }

}
