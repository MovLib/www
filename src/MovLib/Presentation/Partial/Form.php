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

/**
 * Auto-validating HTML form for POST requests.
 *
 * Please note that this form is only meant for POST requests only. If you need a form for GET requests simply create
 * the form within your HTML. A GET submitted form doesn't need a form ID, nor a CSRF token. Additionally auto-
 * validation doesn't make much sense for a GET form, as their parameters are often only used for pagination or similar
 * simple tasks which won't be saved nor affect cirtical parts of our system.
 *
 * @link https://github.com/MovLib/www/wiki/How-to-create-a-multipart-form
 * @link http://www.w3.org/TR/2013/WD-aria-in-html-20130214/#recommendations-table
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Form extends \MovLib\Presentation\AbstractBase {


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
  public $attributes;

  /**
   * Numeric array containing all action elements.
   *
   * @var array
   */
  public $actionElements = [];

  /**
   * Numeric array containing all form elements that were passed to the constructor.
   *
   * All these elements are part of the forms body and will be auto-validated upon submission.
   *
   * @var array
   */
  private $elements;

  /**
   * String containing all hidden elements.
   *
   * @var string
   */
  public $hiddenElements = "";


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
   */
  public function __construct($page, array $elements, $id = null, $validationCallback = "validate") {
    global $i18n, $session;
    $this->elements        = $elements;
    $this->id              = $id ? : $page->id;
    $this->hiddenElements .= "<input name='form_id' type='hidden' value='{$this->id}'>";

    // Any form has to include the CSRF token if a session is active (including anon sessions).
    if (isset($session->csrfToken)) {
      $this->hiddenElements .= "<input name='csrf' type='hidden' value='{$session->csrfToken}'>";
    }

    // Set default attributes, a dev can override them by accessing the properties directly.
    $this->attributes = [ "action" => $_SERVER["PATH_INFO"], "method" => "post" ];

    if (isset($_SERVER["MULTIPART"])) {
      $this->attributes["enctype"] = "multipart/form-data";
      if ($_SERVER["MULTIPART"] == UPLOAD_ERR_INI_SIZE) {
        $page->checkErrors([ $i18n->t("The image is too large: it must be {0,number} {1} or less.", $this->formatBytes(ini_get("upload_max_filesize"))) ]);
      }
    }

    // Validate the form if we're receiving it.
    if (isset($_POST["form_id"]) && $_POST["form_id"] == $this->id) {
      // Validate the CSRF token and only continue if it is valid.
      if ($session->validateCsrfToken() === false) {
        $page->checkErrors([$i18n->t("The form has become outdated. Copy any unsaved work in the form below and then {0}reload this page{1}.", [
          "<a href='{$_SERVER["REQUEST_URI"]}'>", "</a>"
        ])]);
        return;
      }

      // Validate all attached form elements.
      $errors = $mandatoryError = null;
      $c = count($this->elements);
      for ($i = 0; $i < $c; ++$i) {
        if (empty($_POST[$this->elements[$i]->id]) && (empty($_FILES[$this->elements[$i]->id]) || $_FILES[$this->elements[$i]->id]["error"] === UPLOAD_ERR_NO_FILE)) {
          if (isset($this->elements[$i]->attributes["aria-required"])) {
            $this->elements[$i]->invalid();
            $mandatoryError = true;
          }
        }
        else {
          try {
            $this->elements[$i]->validate();
          }
          catch (\MovLib\Exception\ValidationException $e) {
            $this->elements[$i]->invalid();
            $errors[] = $e->getMessage();
          }
        }
      }

      // No need to display several error messages about mandatory fields that weren't filled out. One message is more
      // than enough. Please note that the color is not a problem for accessability, because each mandatory field is
      // marked with the aria-required-attribute which ensures that clients for handicapped people tell them exactly
      // which elements are required and which aren't.
      if ($mandatoryError === true) {
        $errors[] = $i18n->t("One or more required fields are empty and are highlighted with a red color, please make sure to fill out all required fields.");
      }

      if ($page->checkErrors($errors) === false) {
        $page->{$validationCallback}();
      }
    }
  }

  /**
   * Get string representation of this form element.
   *
   * @return string
   */
  public function __toString() {
    $inputs = null;
    $c      = count($this->elements);
    for ($i = 0; $i < $c; ++$i) {
      $inputs .= $this->elements[$i];
    }
    return "{$this->open()}{$inputs}{$this->close()}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the closing <code><form></code>-tag, including all form elements from <code>Form::$actionElements</code>.
   *
   * @return string
   *   The closing <code><form></code>-tag, including all form elements from <code>Form::$actionElements</code>.
   */
  public function close() {
    $actions = null;
    $c       = count($this->actionElements);
    for ($i = 0; $i < $c; ++$i) {
      $actions .= $this->actionElements[$i];
    }
    return "<p class='form-actions'>{$actions}</p></form>";
  }

  /**
   * Get the opening <code><form></code>-tag, including all hidden input elements.
   *
   * @return string
   *   The opening <code><form></code>-tag, including all hidden input elements.
   */
  public function open() {
    return "<form{$this->expandTagAttributes($this->attributes)}>{$this->hiddenElements}";
  }

}
