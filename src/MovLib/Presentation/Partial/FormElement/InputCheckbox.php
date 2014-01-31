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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this checkbox is checked or not.
   *
   * @var boolean
   */
  public $checked = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new checkbox input.
   *
   * @param string $id
   *   The checkbox's unique global identifier.
   * @param string $label
   *   The checkbox's translated label text.
   * @param array $attributes [optional]
   *   The checkbox's additional attributes that should be applied, the following default attributes are always set:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"type"</code> is set to <code>"checkbox"</code></li>
   *     <li><code>"value"</code> is set to <var>$id</var> if no value is given</li>
   *   </ul>
   */
  public function __construct($id, $label, array $attributes = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($this->attributes["checked"]) && !is_bool($this->attributes["checked"])) {
      throw new \InvalidArgumentException("The checkbox' checked attribute must be of type boolean");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct($id, $label, $attributes);
    $this->attributes["name"] = $this->id;
    $this->attributes["type"] = "checkbox";
    if (isset($this->attributes["checked"])) {
      $this->checked = $this->attributes["checked"];
    }
    if (($checked = $this->filterInput($this->id))) {
      $this->checked = (boolean) $checked;
    }
    if (!isset($this->attributes["value"])) {
      $this->attributes["value"] = $this->id;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the rendered checkbox.
   *
   * @return string
   *   The rendered checkbox.
   */
  protected function render() {
    return "{$this->help}<p><label class='checkbox'><input{$this->expandTagAttributes($this->attributes)}>{$this->label}</label></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;
    if ($this->required === true && $this->checked === false) {
      throw new ValidationException($i18n->t("The “{0}” checkbox is mandatory.", [ $this->label ]));
    }
    return $this;
  }

}
