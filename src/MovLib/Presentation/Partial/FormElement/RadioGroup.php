<?php

/* !
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

use \MovLib\Exception\ValidationException;

/**
 * Fieldset with multiple input radio form elements.
 *
 * @link http://www.w3.org/TR/wai-aria/roles#radiogroup
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/fieldset
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RadioGroup extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * The radios in the group.
   *
   * @var array
   */
  protected $choices;

  /**
   * Instantiate new fieldset with input form elements of type radio.
   *
   * @param string $id
   *   The fieldset's global identifier.
   * @param mixed $value
   *   The fieldset's initial value.
   * @param array $choices
   *   Associative array containing the value and label of each radio element, example:
   *   <code>[ "f" => $i18n->t("Female"), "m" => $i18n->t("Male") ]</code>
   */
  public function __construct($id, $value, array $choices) {
    $this->id = $id;
    $this->value = isset($_POST[$this->id]) ? $_POST[$this->id] : $value;
    foreach ($choices as $choiceValue => $choiceLabel) {
      $this->choices[$choiceValue] = [
        "attributes" => [
          "id"       => "{$id}-{$choiceValue}",
          "name"     => $id,
          "required",
          "tabindex" => $this->getTabindex(),
          "type"     => "radio",
          "value"    => $choiceValue
        ],
        "label" => $choiceLabel,
      ];
    }
    if (isset($this->choices[$this->value])) {
      $this->choices[$this->value]["attributes"][] = "checked";
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    $this->attributes["id"] = $this->id;
    $this->attributes["aria-expanded"] = "true";
    $this->attributes["aria-required"] = "false"; // @todo Do we need support for radio groups without default values?
    $this->attributes["role"] = "radiogroup";
    $choices = null;
    foreach ($this->choices as $value => $choice) {
      $choices .= "<label class='radio inline'><input{$this->expandTagAttributes($choice["attributes"])}>{$choice["label"]}</label>";
    }
    return "{$this->help}<fieldset{$this->expandTagAttributes($this->attributes)}><legend{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</legend>{$choices}</fieldset>";
  }


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;
    if (array_key_exists($_POST[$this->id], $this->choices) === false) {
      throw new ValidationException($i18n->t("The submitted value {0} is not a valid choice.", [ $this->placeholder($this->value) ]));
    }
    return $this;
  }

}
