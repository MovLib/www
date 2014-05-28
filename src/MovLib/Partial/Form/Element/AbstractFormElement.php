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
namespace MovLib\Partial\Form\Element;

use \MovLib\Partial\Alert;
use \MovLib\Partial\HTMLAttributes;

/**
 * Defines the base class for form elements.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormElement extends \MovLib\Core\Presentation\Base {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractFormElement";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether a client has access to this form element or not, defaults to <code>TRUE</code> for all form elements.
   *
   * @var boolean
   */
  public $access = true;

  /**
   * The form element's attributes.
   *
   * @var \MovLib\Partial\HTMLAttributes
   */
  public $attributes;

  /**
   * The form element's child form elements.
   *
   * @var array
   */
  public $children;

  /**
   * The form element's description.
   *
   * @var string
   */
  public $description;

  /**
   * The form element's translated label.
   *
   * @var string
   */
  public $label;

  /**
   * The form element's global unique identifier.
   *
   * @var string
   */
  public $name;

  /**
   * The form element's parent form elements.
   *
   * @var array
   */
  public $parents = [];

  /**
   * Text or markup to include before the form element.
   *
   * @see AbstractFormElement::$suffix
   * @var string
   */
  public $prefix;

  /**
   * The form element's parent form elements within the controlling form output.
   *
   * @var array
   */
  public $renderParents;

  /**
   * Whether this form element is required or not.
   *
   * @var boolean
   */
  public $required = false;

  /**
   * Text or markup to include after the form element.
   *
   * @see AbstractFormElement::$prefix
   * @var string
   */
  public $suffix;

  /**
   * Whether this form element acts as collection for other form elements or not.
   *
   * @var boolean
   */
  public $tree = false;

  /**
   * The form element's validator.
   *
   * @var \MovLib\Validation\AbstractValidator
   */
  public $validator;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form element object.
   *
   * @param string $name
   *   The form element's global unique identifier. The name should be written in <b>snake_case</b> as per convention.
   * @param string $label
   *   The form element's translated label.
   * @param mixed $value
   *   The form element's atomic value. The value will be used as initial value of the form element when it is rendered
   *   and the class property value will contain the client submitted value after the form was submitted to the server.
   *
   *   Please read the comments on {@see AbstractFromElement::$value}, {@see AbstractFormElement::$valueDefault}, and
   *   {@see AbstractFormElement::$valueValid}.
   * @param array $settings
   *   The form element's settings.
   * @param array $defaults
   *   The form element's default settings. Note that the default settings will be auto-merged with the settings.
   */
  public function __construct($name, $label, &$value, array $settings, array $defaults) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($name), "A form element's name cannot be empty.");
    assert(preg_match("/[^a-z0-9_]+/") !== 1, "A form element's name has to be all lowercase as written in snake notation.");
    assert(!empty($label), "A form elements' label cannot be empty.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Export parameters to class scope.
    $this->name         = $name;
    $this->label        = $label;
    $this->valueDefault = $value;
    $this->valueValid   =& $value;

    // Merge settings with defaults and export to class scope.
    foreach ($settings + $defaults as $name => $value) {
      $this->$name = $value;
    }

    // Transform the attributes array to an HTML attributes instance. Note that a form element always has the current
    // request's language code.
    $this->attributes = new HTMLAttributes($this->attributes);
  }

  /**
   * Get the form element's string representation.
   *
   * @return string
   *   The form element's string representation.
   */
  public function __toString() {
    try {
      return (string) $this->render();
    }
    catch (\Exception $e) {
      return new Alert("<pre>{$e}</pre>", "Error Rendering Form Element", Alert::SEVERITY_ERROR);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the form element's string representation.
   *
   * @internal
   *   The rendering is done in the <code>render</code> method to avoid the repeated task of enclosing a
   *   <code>__toString()</code> methods body in a try-catch-block. Although it's a 100% performance overhead we create
   *   here.
   * @todo
   *   Rewrite all <code>render</code> methods to <code>__toString()</code> methods and remove the
   *   <code>__toString()</code> from this class. Thus removing the overhead from the additional method call. A form
   *   element should have been properly tested before deployment.
   * @return string
   *   The form element's string representation.
   */
  abstract protected function render();

}
