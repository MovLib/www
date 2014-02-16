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
 * Base class for any form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/tag/Forms
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormElement extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for required error messages.
   *
   * @var integer
   */
  const ERROR_REQUIRED = 0;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form element's attributes.
   *
   * @var array
   */
  protected $attributes;

  /**
   * Attribute used to collect error messages during validation.
   *
   * @var mixed
   */
  protected $errors;

  /**
   * The form element's help.
   *
   * @see AbstractFormElement::setHelp
   * @var null|\MovLib\Presentation\Partial\Help
   */
  protected $help;

  /**
   * The form element's global identifier.
   *
   * The ID is used for the ID and name attributes of the element.
   *
   * @var string
   */
  public $id;

  /**
   * The form element's label content.
   *
   * @var string
   */
  protected $label;

  /**
   * Contains a popup that describes that this form element is required.
   *
   * @var string
   */
  protected $required;

  /**
   * The form element's atomic value.
   *
   * @var mixed
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form element.
   *
   * @param string $id
   *   The form element's unique global identifier.
   * @param string $label
   *   The form element's translated label text.
   * @param array $attributes
   *   The form element's attributes array.
   * @param mixed $value
   *   The form element's atomic value.
   * @param string $help
   *   The form element's translated help text.
   * @param boolean $helpPopup
   *   Whether the form element's help should be displayed as popup or not.
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct($id, $label, array $attributes = null, &$value = null, $help = null, $helpPopup = true) {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($id)) {
      throw new \InvalidArgumentException("A form element's \$id cannot be empty");
    }
    if (empty($label)) {
      throw new \InvalidArgumentException("A form element's \$label cannot be empty");
    }
    if (isset($this->attributes["required"]) && !is_bool($this->attributes["required"])) {
      throw new \InvalidArgumentException("A form element's required attribute has to be of type boolean");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Export parameters to class scope.
    $this->attributes = $attributes;
    $this->id         = $id;
    $this->label      = $label;
    $this->value      =& $value;

    // Add note to form element if it's required.
    if (isset($this->attributes["required"])) {
      $this->attributes["aria-describedby"] = "{$this->id}-required";
      $this->required = "<div class='fr ico ico-alert popup' id='{$this->id}-required' role='note'><small class='content'>{$i18n->t("This field is required.")}</small></div>";
    }

    // Add help text to form element if one is present.
    if (isset($help)) {
      if ($helpPopup === true) {
        if (isset($this->attributes["aria-describedby"])) {
          $this->attributes["aria-describedby"] .= " {$this->id}-help";
        }
        else {
          $this->attributes["aria-describedby"] = "{$this->id}-help";
        }
        $this->help = "<div class='fr ico ico-help popup' id='{$this->id}-help' role='note'><small class='content'>{$help}</small></div>";
      }
      else {
        $this->help = "<small class='fr' id='{$this->id}-help' role='note'>{$help}</small>";
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Validate the form element's submitted value.
   *
   * @param mixed $value
   *   The user submitted value to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return mixed
   *   The valid atomic value.
   */
  abstract protected function validateValue($value, &$errors);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Mark form element as invalid.
   *
   * @return this
   */
  public function invalid() {
    $this->addClass("invalid", $this->attributes);
    $this->attributes["aria-invalid"] = "true";
    return $this;
  }

  /**
   * Validate the form element's submitted value.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param array $errors
   *   Array to collect error messages.
   * @return this
   */
  public function validate(&$errors) {
    global $i18n;

    // Check if a value was submitted for this form element.
    if (empty($_POST[$this->id])) {
      // Make sure that the value is really NULL and not an empty string or similar (important for storing).
      $this->value = null;

      // If this is a required field, empty values are not permited.
      $this->required && ($errors[self::ERROR_REQUIRED] = $i18n->t("The “{0}” field is required.", [ $this->label ]));
    }
    // Let the concrete class validate the value if we have one.
    else {
      $this->value = $this->validateValue($_POST[$this->id], $errors);
    }

    // Mark this form element as invalid if we have any error at this point.
    $errors && $this->invalid();

    return $this;
  }

}
