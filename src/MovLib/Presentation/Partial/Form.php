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
namespace MovLib\Presentation\Partial;

use \MovLib\Exception\ValidatorException;
use \MovLib\Presentation\Partial\FormElement\InputHidden;

/**
 * Auto-validating HTML form for POST requests.
 *
 * Please note that this form is only meant for POST requests only. If you need a form for GET requests simply create
 * the form within your HTML. A GET submitted form doesn't need a form ID, nor a CSRF token. Additionally auto-
 * validation doesn't make much sense for a GET form, as their parameters are often only used for pagination or similar
 * simple tasks which won't be saved nor affect cirtical parts of our system.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Form extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The encoding type for <code>"octet/stream"</code> encoding (file uploads; differing MIME types in general).
   *
   * @var string
   */
  const ENCTYPE_BINARY = "multipart/form-data";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Global identifier for this form.
   *
   * @var string
   */
  public $id;

  /**
   * This form's attributes.
   *
   * @var array
   */
  public $attributes = [];

  /**
   * Numeric array containing all action form elments.
   *
   * Action elements are the elements that will be included in the <code>Form::close()</code> method. Action elements
   * won't be validated.
   *
   * @var array
   */
  public $actionElements = [];

  /**
   * Numeric array containing all form elements that were passed to the constructor.
   *
   * All these elements are part of the forms body and will be auto-validated upon submission.
   *
   * @var type
   */
  private $elements = [];

  /**
   * Numeric array containing all hidden form elements.
   *
   * Hidden elements are the elements that will be included in the <code>Form::open()</code> method. Hidden elements
   * won't be validated.
   *
   * @var array
   */
  public $hiddenElements = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @param \MovLib\Presentation\Page $page
   *   The page instance this form is attached to.
   * @param array $elements
   *   Form elements that should be attached to this form and auto-validated.
   * @param string $id [optional]
   *   The global identifier of this form. If no value is supplied the identifier of the passed page is used.
   * @param string $validationCallback [optional]
   *   The name of the method that should be called if the form's auto-validation is completed an no errors were
   *   detected. Defaults to <code>"validate"</code>.
   * @throws \MovLib\Exception\ValidatorException
   */
  public function __construct($page, array $elements, $id = null, $validationCallback = "validate") {
    global $i18n, $session;
    $this->id = $id ?: $page->id;
    $this->hiddenElements[] = new InputHidden("form_id", $this->id);

    // Any form has to include the CSRF token if a session is active (including anon session where the login-flag would
    // be false).
    if ($token = $session->csrfToken) {
      $this->hiddenElements[] = new InputHidden("csrf", $token);
    }

    // Set default attributes, a dev can override them by accessing the properties directly.
    $this->attributes = [
      // @todo Can we trust on our supported browser to use the document encoding that we've sent via HTTP?
      //"accept-charset" => "UTF-8",
      "action" => $_SERVER["PATH_INFO"],
      "method" => "post",
    ];

    // Validate all attached form elements if we are receiving this form.
    if (filter_input(INPUT_POST, "form_id") == $this->id) {
      $errors = $mandatoryError = null;

      if ($session->validateCsrfToken() === false) {
        $page->checkErrors([$i18n->t("The form has become outdated. Copy any unsaved work in the form below and then {0}reload this page{1}.", [
          "<a href='{$_SERVER["REQUEST_URI"]}'>", "</a>"
        ])]);
        return;
      }

      $c = count($elements);
      for ($i = 0; $i < $c; ++$i) {
        // We don't want a copy of the element, we want the actual element the presentation class is keeping as a
        // property. Passing the array by reference in the declaration of the method would not allow us to do this,
        // because the array isn't kept as property in the presentation class.
        $this->elements[] = &$elements[$i];

        // A disabled element is not submitted by the browser, therefor we can't check it at all and we don't want to.
        if ($elements[$i]->disabled === true) {
          continue;
        }

        // No need to go through the complete validation process to check if the element is empty or not. Plus it's
        // tedious to re-implement this in each validation method. Directly take care of it here.
        if (empty($_POST[$elements[$i]->id]) && $elements[$i]->required === true) {
          $elements[$i]->invalid();
          $mandatoryError = true;
        }
        else {
          try {
            $elements[$i]->validate();
          } catch (ValidatorException $e) {
            $elements[$i]->invalid();
            $errors[] = $e->getMessage();
          }
        }
      }

      // No need to display several error messages about mandatory fields that weren't filled out. One message is more
      // than enough. Please note that the color is not a problem for accessability, because each mandatory field is
      // marked with the aria-required-attribute which ensures that clients for handicapped people tell them exactly
      // which elements are required and which aren't.
      if ($mandatoryError) {
        $errors[] = $i18n->t("One or more required fields are empty and are highlighted with a red color, please make sure to fill out all required fields.");
      }

      // Only call the validation callback if there were no errors at all.
      if ($page->checkErrors($errors) === false) {
        $page->{$validationCallback}();
      }
    }
    else {
      $c = count($elements);
      for ($i = 0; $i < $c; ++$i) {
        // The actual element, not a copy, see comment above.
        $this->elements[] = &$elements[$i];
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the opening <code><form></code>-tag, including all hidden input elements.
   *
   * @return string
   *   The opening <code><form></code>-tag, including all hidden input elements.
   */
  public function open() {
    $hidden = null;
    $c = count($this->hiddenElements);
    for ($i = 0; $i < $c; ++$i) {
      $hidden .= $this->hiddenElements[$i];
    }
    return "<form{$this->expandTagAttributes($this->attributes)}>{$hidden}";
  }

  /**
   * Get the closing <code><form></code>-tag, including all form elements from <code>Form::$actionElements</code>.
   *
   * @return string
   *   The closing <code><form></code>-tag, including all form elements from <code>Form::$actionElements</code>.
   */
  public function close() {
    $actions = null;
    $c = count($this->actionElements);
    for ($i = 0; $i < $c; ++$i) {
      $actions .= $this->actionElements[$i];
    }
    if ($actions) {
      $actions = "<p class='form-actions'>{$actions}</div>";
    }
    return "{$actions}</form>";
  }

  /**
   * Get string representation of this form element.
   *
   * @return string
   */
  public function __toString() {
    $inputs = null;
    $c = count($this->elements);
    for ($i = 0; $i < $c; ++$i) {
      $inputs .= $this->elements[$i];
    }
    return "{$this->open()}{$inputs}{$this->close()}";
  }

}
