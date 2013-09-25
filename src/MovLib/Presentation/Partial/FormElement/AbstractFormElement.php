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

use \MovLib\Presentation\Partial\Help;

/**
 * Base class for any form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/tag/Forms
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormElement extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form element's attributes.
   *
   * The following attributes are always set:
   * <ul>
   *   <li><code>"id"</code> is set to <var>AbstractFormElement::$id</var></li>
   *   <li><code>"name"</code> is set to <var>AbstractFormElement::$id</var></li>
   *   <li><code>"tabindex"</code> is set to the next global tabindex</li>
   * </ul>
   *
   * @var array
   */
  public $attributes;

  /**
   * Flag indicating if this element is disabled or not.
   *
   * @var boolean
   */
  public $disabled = false;

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
  public $label;

  /**
   * The form element's label attributes.
   *
   * The following attributes are always set:
   * <ul>
   *   <li><code>"for"</code> is set to <var>AbstractFormElement::$id</var></li>
   * </ul>
   *
   * @var array
   */
  public $labelAttributes;

  /**
   * Flag indicating if this element is required or not.
   *
   * @var boolean
   */
  public $required = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form element.
   *
   * @param string $id
   *   The form element's global identifier.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param string $label [optional]
   *   The form element's label content.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id, array $attributes = null, $label = null, array $labelAttributes = null) {
    $this->id                     = $id;
    $this->attributes             = $attributes;
    $this->attributes["id"]       = $id;
    $this->attributes["name"]     = $id;
    $this->attributes["tabindex"] = $this->getTabindex();
    $this->label                  = $label;
    $this->labelAttributes        = $labelAttributes;
    $this->labelAttributes["for"] = $id;
  }

  /**
   * Get string representation of this form element.
   *
   * @return string
   *   String representation of this form element.
   */
  abstract public function __toString();


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
    return $this;
  }

  /**
   * Mark this form element as required.
   *
   * A form element that is marked as required will also fail during validation if the submitted value is empty.
   * Browsers supporting this feature will prevent form submission if this element is empty. The attribute is ignored
   * on input form elements of type <code>hidden</code>, <code>image</code>, <code>submit</code>, <code>reset</code>
   * and <code>button</code>.
   *
   * @return this
   */
  public function required() {
    $this->attributes["aria-required"] = "true";
    $this->attributes[] = "required";
    $this->required = true;
    return $this;
  }

  /**
   * Set input's help.
   *
   * Automatically instantiates <code>\MovLib\Presentation\Partial\Help</code> and sets the appropriate ARIA attribute
   * on this input element.
   *
   * @param string $content
   *   The translated help content.
   * @param boolean $popup [optional]
   *   Defines the rendering type, defaults to rendering as popup.
   * @return this
   */
  public function setHelp($content, $popup = true) {
    $this->attributes["aria-describedby"] = "{$this->id}-help";
    $this->help = new Help($content, $this->id, $popup);
    return $this;
  }

  /**
   * Validate the user submitted data.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\ValidationException
   */
  abstract public function validate();

}
