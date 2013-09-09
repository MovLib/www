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

namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Exception\ValidatorException;
use \MovLib\View\HTML\Input\HiddenInput;

/**
 * Description of Form
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
   * Array used to create a map between PHP's <var>INPUT_*</var> constants to their string representation.
   *
   * @var array
   */
  public $input = [
    INPUT_POST => "POST",
    INPUT_GET  => "GET",
  ];

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
   * Action elements are the elements that will be included in the <code>Form::close()</code>-method. Action elements
   * won't be validated.
   *
   * @var array
   */
  public $actionElements = [];

  /**
   * Numeric array containing all hidden form elements.
   *
   * Hidden elements are all elements that are an instance of <code>\MovLib\View\HTML\Input\HiddenInput</code>. Hidden
   * elements won't be validated, hidden elements usually include special values like the <code>"form_id"</code> and/or
   * the <code>"csrf"</code>-token. They are usually validated at other points within the application.
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
   * @param int $inputType [optional]
   *   One of PHP's built-in <var>INPUT_*</var> constants, defaults to <var>INPUT_POST</var>.
   * @throws \MovLib\Exception\ValidatorException
   */
  public function __construct($page, array $elements, $id = null, $validationCallback = "validate", $inputType = INPUT_POST) {
    global $i18n, $session;
    $this->id = $id ?: $page->id;
    $this->hiddenElements[] = new HiddenInput("form_id", $this->id);

    // Any form has to include the CSRF token if a session is active (including anon session where the login-flag would
    // be false).
    if ($token = $session->csrfToken) {
      $this->hiddenElements[] = new HiddenInput("csrf", $token);
    }

    // Set default attributes, a dev can override them by accessing the properties directly.
    $this->attributes = [
      // @todo Can we trust on our supported browser to use the document encoding that we've sent via HTTP?
      //"accept-charset" => "UTF-8",
      "action" => "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}{$_SERVER["PATH_INFO"]}",
      "method" => $this->input[$inputType],
    ];

    // Validate all attached form elements if we are receiving this form.
    if ($_SERVER["REQUEST_METHOD"] == $this->input[$inputType] && filter_input($inputType, "form_id") == $this->id) {
      $errors = $mandatoryError = null;

      $c = count($elements);
      for ($i = 0; $i < $c; ++$i) {
        // A disabled element is not submitted by the browser, therefor we can't check it at all and we don't want to.
        if ($elements[$i]->disabled === true) {
          continue;
        }

        // No need to go through the complete validation process to check if the element is empty or not. Plus it's
        // tedious to re-implement this in each validation method. Directly take care of it here.
        if (!filter_input($inputType, $elements[$i]->id, FILTER_DEFAULT, FILTER_REQUIRE_SCALAR)) {
          if ($elements[$i]->required() === true) {
            $elements[$i]->invalid();
            $mandatoryError = true;
          }
          else {
            $elements[$i]->value = $elements[$i]->defaultValue;
          }
        }
        else {
          try {
            $elements[$i]->validate($inputType);
          } catch (ValidatorException $e) {
            $elements[$i]->invalid();
            $errors .= "<p>{$e->getMessage()}</p>";
          }
        }
      }

      // No need to display several error messages about mandatory fields that weren't filled out. One message is more
      // than enough. Please note that the color is not a problem for accessability, because each mandatory field is
      // marked with the aria-required-attribute which ensures that clients for handicapped people tell them exactly
      // which elements are required and which aren't.
      if ($mandatoryError) {
        $errors .= "<p>{$i18n->t("One or more required fields are empty and are highlighted with a red color, please make sure to fill out all required fields.")}</p>";
      }

      // Only call the presenter's validate method if there was no error so far.
      if ($errors) {
        $alert = new Alert($errors);
        $alert->severity = Alert::SEVERITY_ERROR;
        $page->alerts .= $alert;
      }
      else {
        $page->{$validationCallback}();
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
    $hidden = "";
    $c = count($this->hiddenElements);
    for ($i = 0; $i < $c; ++$i) {
      $hidden .= $this->hiddenElements[$i];
    }
    return "<form{$this->expandTagAttributes($this->attributes)}>{$hidden}";
  }

  /**
   * Get the closing <code><form></code>-tag, including the submit input element.
   *
   * If no submit element is present within the current <var>$this->actionElements</var> one will be created with the
   * default values.
   *
   * @param boolean $wrap [optional]
   *   If set to <code>FALSE</code> the submit input element won't be wrapped with the <code><div class='form-actions'/>
   *   </code>-mark-up, default is <code>TRUE</code> and therefor to wrap it.
   * @return string
   *   The closing <code><form></code>-tag, including the submit input element.
   */
  public function close($wrap = true) {
    $actions = "";
    $c = count($this->actionElements);
    for ($i = 0; $i < $c; ++$i) {
      $actions .= $this->actionElements[$i];
    }
    if ($wrap === true) {
      $actions = "<div class='form-actions'>{$actions}</div>";
    }
    return "{$actions}</form>";
  }

}
