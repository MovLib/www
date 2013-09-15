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
 * HTML input submit form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputSubmit extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * Global identifier to access this element.
   *
   * @var string
   */
  public $id;

  /**
   * Array containing all attributes of this form element.
   *
   * @var array
   */
  public $attributes = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML input form element of type submit.
   *
   * @param array $attributes [optional]
   *   Additional attributes that should be set on this form element, defaults to no additional attributes.
   * @param string $id
   *   The global unique identifier of this form element.
   */
  public function __construct(array $attributes = null, $id = "submit") {
    $this->id = $id;
    $this->attributes = $attributes;
    $this->attributes["tabindex"] = $this->getTabindex();
    $this->attributes["type"] = "submit";
    $this->addClass("button", $this->attributes);
  }

  /**
   * Get string representation of this form element.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The string representation of this form element.
   */
  public function __toString() {
    global $i18n;
    if (empty($this->attributes["value"])) {
      $this->attributes["value"] = $i18n->t("Submit");
    }
    return "<input{$this->expandTagAttributes($this->attributes)}>";
  }

}
