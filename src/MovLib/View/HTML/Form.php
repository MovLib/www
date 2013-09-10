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
namespace MovLib\View\HTML;

use \MovLib\Exception\ValidatorException;
use \MovLib\View\HTML\AbstractBaseView;
use \MovLib\View\HTML\Input\HiddenInput;
use \MovLib\View\HTML\Input\SubmitInput;

/**
 * Represents a HTML form element which can be used inline or as part of a form view.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Form extends AbstractBaseView {


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
   * Associative array containing all action input form elments.
   *
   * Action elements are the elements that will be included in the <code>Form::close()</code>-method. Action elements
   * won't be validated, as they only consist of submit input elements and/or links.
   *
   * @var array
   */
  public $actionElements = [];

  /**
   * Associative array containing all hidden input form elements.
   *
   * Hidden elements are all elements that are an instance of <code>\MovLib\View\HTML\Input\HiddenInput</code>. Hidden
   * elements won't be validated, hidden elements usually include special values like the <code>"form_id"</code> and/or
   * the <code>"csrf"</code>-token. They are usualy validated at other points within the application.
   *
   * @var array
   */
  public $hiddenElements = [];

  /**
   * Associative array containing all visible input form elements that should be auto-validated.
   *
   * @var array
   */
  public $elements = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   * @param string $id
   *   The global identifier of this form.
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenting presenter.
   * @param array $elements
   *   Input elements that should be attached and auto-validated.
   * @param array $attributes [optional]
   *   Set or overwrite additional attributes on the form element itself.
   * @throws \MovLib\Exception\ValidatorException
   */
  public function __construct($id, $presenter, array $elements, array $attributes = null, array $submitAttributes = null) {
    global $i18n, $user;
    $this->id = $id;
    $this->hiddenElements["form_id"] = new HiddenInput("form_id", $id);

    // Any form has to include the CSRF token if a session is active (including anon session where the login-flag would
    // be false).
    if ($token = $user->csrfToken) {
      $this->hiddenElements["csrf"] = new HiddenInput("csrf", $token);
    }

    // Every form has to have a submit input element!
    $this->actionElements["submit"] = new SubmitInput($submitAttributes);

    // Export dev supplied attributes to class scope.
    $this->attributes = $attributes;
    // @todo Can we trust on our supported browser to use the document encoding that we've sent via HTTP?
    //if (!isset($this->attributes["accept-charset"])) {
    //  $this->attributes["accept-charset"] = "UTF-8";
    //}
    if (!isset($this->attributes["action"])) {
      $this->attributes["action"] = "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}{$_SERVER["PATH_INFO"]}";
    }
    if (!isset($this->attributes["method"])) {
      $this->attributes["method"] = "post";
    }

    // Export all form elements to class scope. We create an associative array for easy access of all elements later on.
    $c = count($elements);
    for ($i = 0; $i < $c; ++$i) {
      $this->elements[$elements[$i]->id] = $elements[$i];
    }

    // Validate all attached form elements if we are receiving this form.
    //
    // @todo Every form has a form ID attached as hidden input, do we really need to check the existence of the offset?
    //       If we don't check and access the non existing offset an exception should be thrown automatically and a
    //       error page would be displayed, isn't that the desired behaviour in this case?
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form_id"]) && $_POST["form_id"] == $this->id) {
      $errors = $mandatoryError = null;

      foreach ($this->elements as $id => $element) {
        // A disabled element is not submitted by the browser, therefor we can't check it at all and we don't want to.
        if ($element->disabled === true) {
          continue;
        }

        // No need to go through the complete validation process to check if the element is empty or not. Plus it's
        // tedious to re-implement this in each validation method. Directly take care of it here.
        if (empty($_POST[$element->id])) {
          if ($element->required() === true) {
            $element->invalid();
            $mandatoryError = true;
          }
          else {
            $element->value = $element->defaultValue;
          }
        }
        else {
          try {
            $element->validate();
          } catch (ValidatorException $e) {
            $element->invalid();
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
        $presenter->view->addAlert(new Alert($errors, [ "severity" => Alert::SEVERITY_ERROR ]));
      }
      else {
        $presenter->validate($this);
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
    foreach ($this->hiddenElements as $id => $hiddenElement) {
      $hidden .= $hiddenElement;
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
    foreach ($this->actionElements as $id => $actionElement) {
      $actions .= $actionElement;
    }
    if ($wrap === true) {
      $actions = "<div class='form-actions'>{$actions}</div>";
    }
    return "{$actions}</form>";
  }

}
