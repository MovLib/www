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
namespace MovLib\Presentation\Partial;

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Exception\ValidationException;

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
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
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
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @param \MovLib\Presentation\Page $page
   *   The page instance this form is attached to.
   * @param array $elements [optional]
   *   Form elements that should be attached to this form and auto-validated.
   * @param string $id [optional]
   *   The global identifier of this form. If no value is supplied the identifier of the passed page is used.
   * @param string $validationCallback [optional]
   *   The name of the method that should be called if the form's auto-validation is completed an no errors were
   *   detected. Defaults to <code>"validate"</code>.
   */
  public function __construct($page, array $elements = null, $id = null, $validationCallback = "validate") {
    global $i18n, $kernel, $session;
    $this->elements        = $elements;
    $this->id              = $id ?: $page->id;
    $this->hiddenElements .= "<input name='form_id' type='hidden' value='{$this->id}'>";

    // Set default attributes, a dev can override them by accessing the properties directly.
    $this->attributes = [ "action" => $kernel->requestURI, "method" => "post" ];

    // Validate the form if we're receiving it.
    if (isset($_POST["form_id"]) && $_POST["form_id"] == $this->id) {
      $errors = null;

      // Validate the CSRF token and only continue validation if this is valid.
      if ($session->active && (empty($_POST["csrf"]) || empty($session["csrf"]) || $_POST["csrf"] != $session["csrf"])) {
        $errors["csrf"] = $i18n->t("The form has become outdated. Copy any unsaved work in the form below and then {0}reload this page{1}.", [
          "<a href='{$kernel->requestURI}'>", "</a>"
        ]);
      }
      else {
        // Validate all attached form elements.
        $c = count($this->elements);
        for ($i = 0; $i < $c; ++$i) {
          try {
            $this->elements[$i]->validate();
          }
          catch (ValidationException $e) {
            // Mark this form element as invalid.
            $this->elements[$i]->invalid();
            $errors[$this->elements[$i]->id] = $e->getMessage();
          }
        }
      }

      // Call the validation callback method and let it handle any errors or continue with the validation process.
      $page->{$validationCallback}($errors);
    }

    // Generate new CSRF token for this form if we have an active session.
    if ($session->active) {
      $csrf                  = hash("sha512", openssl_random_pseudo_bytes(1024));
      $session["csrf"]       = $csrf;
      $this->hiddenElements .= "<input type='hidden' name='csrf' value='{$csrf}'>";
    }
  }

  /**
   * Get string representation of this form element.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   */
  public function __toString() {
    global $i18n;
    try {
      $inputs = null;
      $c      = count($this->elements);
      for ($i = 0; $i < $c; ++$i) {
        $inputs .= $this->elements[$i];
      }
      return "{$this->open()}{$inputs}{$this->close()}";
    }
    catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", $i18n->t("Unexpected Error"), Alert::SEVERITY_ERROR);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the closing <code><form></code> tag.
   *
   * The closing form tag contains all action and hidden elements.
   *
   * @return string
   *   The closing <code><form></code> tag.
   */
  public function close() {
    $actions = null;
    $c       = count($this->actionElements);
    for ($i = 0; $i < $c; ++$i) {
      $actions .= $this->actionElements[$i];
    }
    if ($actions) {
      $actions = "<p class='form-actions'>{$actions}</p>";
    }
    return "{$actions}{$this->hiddenElements}</form>";
  }

  /**
   * Make this form a multipart form.
   *
   * @return this
   */
  public function multipart() {
    $this->attributes["enctype"] = "multipart/form-data";
    return $this;
  }

  /**
   * Get the opening <code><form></code> tag.
   *
   * @return string
   *   The opening <code><form></code> tag.
   */
  public function open() {
    return "<form{$this->expandTagAttributes($this->attributes)}>";
  }

}
