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
 * Fieldset with multiple input radio form elements.
 *
 * @link http://www.w3.org/TR/wai-aria/roles#radiogroup
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/fieldset
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RadioGroup extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The radios in the group.
   *
   * @var array
   */
  protected $choices;

  /**
   * The value of the checked element.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new radio group form elements.
   *
   * @param string $id
   *   The radio group's global identifier.
   * @param string $legend
   *   The radio group's legend text.
   * @param array $choices
   *   The radio group's radio form elements as associative array where the array's key is the content of the radio
   *   input's value-attribute and the array's value the label text for the radio input.
   * @param mixed $value
   *   The value of the checked radio input in the group.
   * @param string $help [optional]
   *   The radio group's help text, defaults to <code>NULL</code> (no help text).
   * @param boolean $helpPopup
   *   Whetever the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
   */
  public function __construct($id, $legend, array $choices, $value, $help = null, $helpPopup = true) {
    parent::__construct($id, $legend, [
      "aria-expanded" => "true",
      "aria-required" => "false", // @todo Do we need support for radio groups without default values?
      "role"          => "radiogroup",
    ], $help, $helpPopup);
    $this->choices = $choices;
    $this->value   = isset($_POST[$this->id]) ? $_POST[$this->id] : $value;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    $choices = null;
    foreach ($this->choices as $value => $choice) {
      $checked  = $this->value == $value ? " checked" : null;
      $choices .= "<label class='radio inline'><input{$checked} id='{$this->id}-{$value}' name='{$this->id}' required tabindex='{$this->attributes["tabindex"]}' type='radio' value='{$value}'>{$choice}</label>";
    }
    unset($this->attributes["tabindex"]);
    return "{$this->help}<fieldset{$this->expandTagAttributes($this->attributes)}><legend>{$this->label}</legend>{$choices}</fieldset>";
  }


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;
    if (!isset($this->choices[$this->value])) {
      throw new ValidationException($i18n->t("The submitted value {0} is not a valid choice.", [ $this->placeholder($this->value) ]));
    }
    return $this;
  }

}
