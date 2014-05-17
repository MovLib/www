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
 * Select single option form element.
 *
 * Use a select form element if your options <b>exceed 9 choices</b> or if the choices grow in the future depending on
 * data in our database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Select extends \MovLib\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for invalid choice error message.
   *
   * @var integer
   */
  const ERROR_CHOICE = 1;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The select's options.
   *
   * @var array
   */
  protected $options;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new select single option form element.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param string $id
   *   The select's global identifier.
   * @param string $label
   *   The select's label text.
   * @param array $options
   *   The select's available options as associative array where the key is the content of the option's value-attribute
   *   and the array's value the option's display text.
   * @param mixed $value [optional]
   *   The selected option's value, defaults to <code>NULL</code> (no option is selected).
   * @param array $attributes [optional]
   *   Additional attributes for the textarea, defaults to <code>NULL</code> (no additional attributes).
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $id, $label, array $options, &$value = null, array $attributes = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($options)) {
      throw new \LogicException("The options array of a select element cannot be empty.");
    }
    if (isset($value) && !isset($options[$value])) {
      throw new \LogicException("The value ({$value}) passed to a select form element must be present in the available options array.");
    }
    // @devEnd
    // @codeCoverageIgnoreEnd
    parent::__construct($diContainerHTTP, $id, $label, $value, $attributes);
    $this->options = $options;
  }

  /**
   * Get the select form element.
   *
   * @return string
   *   The select form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $options = null;
      //  The first child option element of a select element with a required attribute and without a multiple attribute,
      //  and whose size is 1, must have either an empty value attribute, or must have no text content.
      if ($this->required) {
        $options = "<option disabled value=''>{$this->intl->t("Please Select…")}</option>";
      }
      else {
        $selected = empty($this->value) ? " selected" : null;
        $options = "<option{$selected} value=''>{$this->intl->t("None")}</option>";
      }
      foreach ($this->options as $value => $option) {
        $attributes = [];
        if ($option === (array) $option) {
          $attributes = $option[1];
          $option     = $option[0];
        }
        $attributes["value"] = $value;
        if ($this->value == $value) {
          $attributes["selected"] = true;
        }
        $options .= "<option{$this->expandTagAttributes($attributes)}>{$option}</option>";
      }
      return
        "{$this->required}{$this->helpPopup}{$this->helpText}<p>" .
          "<label for='{$this->id}'>{$this->label}</label>" .
          "<select id='{$this->id}' name='{$this->id}'{$this->expandTagAttributes($this->attributes)}>{$options}</select>" .
        "</p>"
      ;
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
   * Validate the user submitted choice.
   *
   * @param string $choice
   *   The user submitted choice.
   * @param null|array $errors
   *   Variable used to collect error messages.
   * @return mixed
   *   The user submitted choice (with the correct type).
   */
  protected function validateValue($choice, &$errors) {
    // Cast the submitted choice to the correct type.
    settype($choice, gettype(key($this->options)));

    // Make sure that the choice is really available and valid.
    if (!isset($this->options[$choice])) {
      $errors[self::ERROR_CHOICE] = $this->intl->t("{0} isn’t a valid choice.", [ $this->placeholder($choice) ]);
    }

    return $choice;
  }

}
