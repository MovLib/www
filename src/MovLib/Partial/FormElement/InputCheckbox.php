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
namespace MovLib\Partial\FormElement;

/**
 * Input checkbox form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class InputCheckbox extends \MovLib\Partial\FormElement\AbstractFormElement {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InputCheckbox";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input checkbox form element.
   *
   * Please note that a checkbox's value is always <code>TRUE</code> or <code>FALSE</code>. You cannot change this
   * behavior. A single checkbox is meant for boolean rows in the database or other decisions that are solely based on
   * a yes or not question.
   *
   * @param \MovLib\Core\HTTP\Container $container
   *   HTTP dependency injection container.
   * @param string $id
   *   The checkbox's unique global identifier.
   * @param string $label
   *   The checkbox's translated label text.
   * @param boolean $value
   *   The checkbox's atomic value.
   * @param array $attributes [optional]
   *   The checkbox's attributes array, defaults to <code>NULL</code> (no additional attributes).
   */
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, &$value, array $attributes = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($attributes["checked"])) {
      throw new \LogicException("You must not set the 'checked' attribute on a checkbox");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct($container, $id, $label, $value, $attributes);
    $this->attributes["checked"] =& $this->value;
  }

  /**
   * Get the input text form element.
   *
   * @return string
   *   The input text form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $string = "{$this->required}{$this->helpPopup}<p class='checkbox'><label><input{$this->presenter->expandTagAttributes($this->attributes)} name='{$this->id}' type='checkbox'>{$this->label}</label></p>";
      if (isset($this->attributes["#prefix"])) {
        $string = "{$this->attributes["#prefix"]}{$string}";
      }
      if (isset($this->attributes["#suffix"])) {
        $string .= $this->attributes["#suffix"];
      }
      return $string;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the form element's submitted value.
   *
   * @param array $errors
   *   Array to collect error messages.
   * @return this
   */
  public function validate(&$errors) {
    // A single checkbox only has two states: checked and not checked
    $this->value = !empty($_POST[$this->id]);

    // If it's not checked but required log the error. We can't use the label within this error message, because they
    // are usually much longer than labels of other input elements. Presentations should override this and display a
    // meaningful error message.
    if ($this->value === false && $this->required) {
      $errors[self::ERROR_REQUIRED] = $this->intl->t("The checkbox is required.");
    }

    return $this;
  }

  /**
   * Nothing to validate!
   */
  protected function validateValue($value, &$errors) {}

}
