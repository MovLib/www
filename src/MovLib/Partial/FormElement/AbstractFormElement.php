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
 * Base class for any form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/tag/Forms
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormElement extends \MovLib\Core\Presentation\DependencyInjectionBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for required error message.
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
   * The form element's help popup (if any).
   *
   * @var null|string
   */
  protected $helpPopup;

  /**
   * The form element's help text (if any).
   *
   * @var null|string
   */
  protected $helpText;

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
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

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
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param string $id
   *   The form element's unique global identifier.
   * @param string $label
   *   The form element's translated label text.
   * @param mixed $value
   *   The form element's atomic value.
   * @param array $attributes [optional]
   *   The form element's attributes array, defaults to <code>NULL</code> (no additional attributes).
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $id, $label, &$value, array $attributes = null) {
    parent::__construct($diContainerHTTP);
    $this->presenter = $diContainerHTTP->presenter;

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
    $ariaDescribedby = null;
    if (isset($this->attributes["required"])) {
      $ariaDescribedby[] = "required";
      $this->required    = "<div class='fr ico ico-alert popup' id='{$this->id}-required' role='note'><small class='content'>{$this->intl->t("This field is required.")}</small></div>";
    }

    // Add help popup to form element.
    if (isset($this->attributes["#help-popup"])) {
      $ariaDescribedby[] = "help-popup";
      $this->helpPopup   = "<div class='fr ico ico-help popup' id='{$this->id}-help-popup' role='note'><small class='content'>{$this->attributes["#help-popup"]}</small></div>";
      unset($this->attributes["#help-popup"]);
    }

    // Add help text to form element.
    if (isset($this->attributes["#help-text"])) {
      $ariaDescribedby[] = "help-text";
      $this->helpText    = "<small class='fr' id='{$this->id}-help-text' role='note'>{$this->attributes["#help-text"]}</small>";
      unset($this->attributes["#help-text"]);
    }

    if ($ariaDescribedby) {
      $this->attributes["aria-describedby"] = " {$this->id}-" . implode(" {$this->id}-", $ariaDescribedby);
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
   * @param array $errors
   *   Array to collect error messages.
   * @return this
   */
  public function validate(&$errors) {
    // Check if a value was submitted for this form element.
    if (empty($_POST[$this->id]) && strlen($_POST[$this->id]) === 0) {
      // Make sure that the value is really NULL and not an empty string or similar (important for storing).
      $this->value = null;

      // If this is a required field, empty values are not permited.
      $this->required && ($errors[self::ERROR_REQUIRED] = $this->intl->t("The “{0}” field is required.", [ $this->label ]));
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
