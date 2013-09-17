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

use \MovLib\Presentation\Partial\Help;

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
class RadioGroup extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The fieldset's attributes.
   *
   * @var array
   */
  public $attributes;

  /**
   * The radios in the group.
   *
   * @var array
   */
  protected $choices;

  /**
   * Flag indicating if this group is disabled or not.
   *
   * @var boolean
   */
  public $disabled = false;

  /**
   * The group's help.
   *
   * @see AbstractFormElement::setHelp
   * @var null|\MovLib\Presentation\Partial\Help
   */
  protected $help;

  /**
   * The group's global identifier.
   *
   * The ID is used for the ID and name attributes of the element.
   *
   * @var string
   */
  public $id;

  /**
   * The fieldset's legend content.
   *
   * @var string
   */
  public $legend;

  /**
   * The fieldset's legend attributes.
   *
   * @var array
   */
  public $legendAttributes;

  /**
   * Flag indicating if this element is required or not.
   *
   * Always <code>TRUE</code> for a groups only consisting of radio inputs. While the ARIA required attribute has to be
   * set to false, this has to be set to <code>TRUE</code>. Otherwise the controlling form wouldn't call the validate
   * method. This implementation currently doesn't support radio groups without values!
   *
   * @var boolean
   */
  public $required = true;

  /**
   * The fieldset's value.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new fieldset with input form elements of type radio.
   *
   * @param string $id
   *   The fieldset's global identifier.
   * @param string $legend
   *   The fieldset's legend content.
   * @param mixed $value
   *   The fieldset's initial value.
   * @param array $choices
   *   Associative array containing the value and label of each radio element, example:
   *   <code>[ "f" => $i18n->t("Female"), "m" => $i18n->t("Male") ]</code>
   * @param array $attributes [optional]
   *   Additional attributes for the fieldset. The <code>"id"</code> is always set to <var>$id</var>.
   * @param array $legendAttributes [optional]
   *   Additional attributes for the legend. This element has no default attributes.
   */
  public function __construct($id, $legend, $value, array $choices, array $attributes = null, array $legendAttributes = null) {
    $this->id = $id;
    $this->legend = $legend;
    $this->value = $value;
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
    if (isset($_POST[$this->id]) && isset($this->choices[$_POST[$this->id]])) {
      $this->choices[$_POST[$this->id]]["attributes"][] = "checked";
    }
    else {
      $this->choices[$value]["attributes"][] = "checked";
    }
    $this->attributes = $attributes;
    $this->attributes["id"] = $id;
    $this->attributes["aria-expanded"] = "true";
    $this->attributes["aria-required"] = "false"; // @todo Do we have support radiogroups without default values?
    $this->attributes["role"] = "radiogroup";
    $this->legendAttributes = $legendAttributes;
  }

  /**
   * Get string representation of this fieldset and it's form elements.
   *
   * @return string
   *   String representation of this fieldset and it's form elements.
   */
  public function __toString() {
    $choices = null;
    foreach ($this->choices as $value => $choice) {
      $choices .= "<label class='radio inline'><input{$this->expandTagAttributes($choice["attributes"])}>{$choice["label"]}</label>";
    }
    return "{$this->help}<fieldset{$this->expandTagAttributes($this->attributes)}><legend{$this->expandTagAttributes($this->legendAttributes)}>{$this->legend}</legend>{$choices}</fieldset>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Disable this form element.
   *
   * Indicates to the browser that this form element is disabled and not available for interaction. No <code>click</code>
   * events will be fired by this element and its value won't be submitted with the form. Therefor disabled elements are
   * automatically <b>not</b> validated.
   *
   * @return this
   */
  public function disable() {
    $this->attributes["aria-disabled"] = "true";
    $this->attributes[] = "disabled";
    $this->disabled = true;
    foreach ($this->choices as $value => $choice) {
      $this->choices[$value]["attributes"]["aria-disabled"] = "true";
      $this->choices[$value]["attributes"][] = "disabled";
    }
    return $this;
  }

  /**
   * Mark this form element as invalid.
   *
   * There is no attribute <code>invalid</code> in HTML, browsers apply the CSS pseudo-class <code>:invalid</code> on
   * elements that fail their own validations. We use the ARIA attribute <code>aria-invalid</code> and set it to
   * <code>true</code> to achieve the same goal. For easy CSS selection the class <code>invalid</code> is applied as
   * well.
   *
   * @return this;
   */
  public function invalid() {
    $this->addClass("invalid", $this->attributes);
    $this->attributes["aria-invalid"] = "true";
    foreach ($this->choices as $value => $choice) {
      $this->choices[$value]["attributes"]["aria-invalid"] = "true";
      $this->choices[$value]["attributes"]["class"] = "invalid";
    }
    return $this;
  }

  /**
   * Set input's help.
   *
   * Automatically instantiates <code>\MovLib\Presentation\Partial\Help</code> and sets the appropriate ARIA attribute
   * on this input element.
   *
   * @todo Is it a good idea to add the ARIA describeby attribute to each radio input?
   * @param string $content
   *   The translated help content.
   * @param boolean $popup [optional]
   *   Defines the rendering type, defaults to rendering as popup.
   * @return this
   */
  public function setHelp($content, $popup = true) {
    $this->attributes["aria-describedby"] = "{$this->id}-help";
    foreach ($this->choices as $value => $choice) {
      $this->choices[$value]["attributes"]["aria-describedby"] = "{$this->id}-help";
    }
    $this->help = new Help($content, $this->id, $popup);
    return $this;
  }

  /**
   * Validate the user submitted data.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    global $i18n;
    return $this;
  }

}
