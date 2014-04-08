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
namespace MovLib\Partial;

use \MovLib\Partial\Alert;
use \MovLib\Partial\FormElement\AbstractInputFile;

/**
 * Defines basic POST form.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Form extends \MovLib\Core\Presentation\DependencyInjectionBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form's action elements (e.g. submit input element).
   *
   * @var string
   */
  protected $actionElements;

  /**
   * The form's attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The form's auto-validate elements.
   *
   * @internal
   *   Keep visibility at protected and allow implementing class to access the various form elements.
   * @var array
   */
  protected $elements;

  /**
   * The form's hidden elements (e.g. CSRF).
   *
   * @var string
   */
  protected $hiddenElements;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   HTTP dependency injection container.
   * @param array $attributes [optional]
   *   The form's additional attributes, the following attributes are always set:
   *   <ul>
   *     <li><code>"accept-charset"</code> is always set to <code>"utf-8"</code></li>
   *     <li><code>"action"</code> is set to <var>$kernel->requestURI</var> if not set</li>
   *     <li><code>"method"</code> is always set to <code>"post"</code></li>
   *   </ul>
   * @param string $id [optional]
   *   Set the form's global unique identifier, defaults to the presenting presenter's identifier.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, array $attributes = [], $id = null) {
    parent::__construct($diContainerHTTP);
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "accept-charset", "method" ] as $attribute) {
      assert(!isset($attributes[$attribute]), "You must not set the '{$attribute}' attribute of a form!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->presenter  = $diContainerHTTP->presenter;
    $this->attributes = $attributes;
    $this->id         = $id ? $id : $this->presenter->id;
  }

  /**
   * Get the form including all elements.
   *
   * @return string
   *   The form including all elements.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $elements = $this->elements ? implode($this->elements) : null;
      return "{$this->open()}{$elements}{$this->close()}";
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Form", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add action form element to the page's form.
   *
   * @internal
   *   We actually want the submit buttons to cover the native <code>submit()</code> function in JavaScript because
   *   executing the <code>submit()</code> function doesn't fire the onsubmit event, while a click on a submit button
   *   does and we always want the browser's validation to be executed before any form is submitted to the server.
   * @param string $text
   *   The action form element's translated text.
   * @param array $attributes [optional]
   *   The action form element's attributes array, the following attributes are always added:
   *   <ul>
   *     <li><code>"name"</code> is set to <code>"submit"</code> if not present</li>
   *     <li><code>"type"</code> is set to <code>"submit"</code> if not present</li>
   *     <li><code>"value"</code> is set to <var>$text</var> and HTML encoded</li>
   *   </ul>
   * @return this
   */
  public function addAction($text, array $attributes = []) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($text)) {
      throw new \InvalidArgumentException("The \$text of an action form element cannot be empty.");
    }
    if ($attributes && !is_array($attributes)) {
      throw new \InvalidArgumentException("The \$attributes of an action form element has to be an array.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Set default values for name and type if not present.
    isset($attributes["name"]) || ($attributes["name"] = "submit");
    isset($attributes["type"]) || ($attributes["type"] = "submit");

    // Always encode characters with special meaning in HTML.
    $attributes["value"] = $this->presenter->htmlEncode($text);

    // Put it all together and add it to this form's action elements.
    $this->actionElements .= "<input{$this->presenter->expandTagAttributes($attributes)}>";

    return $this;
  }

  /**
   * Add form element to this page's form.
   *
   * @param \MovLib\Partial\FormElement\AbstractFormElement $formElement
   *   The form element to add.
   * @return this
   */
  public function addElement(\MovLib\Partial\FormElement\AbstractFormElement $formElement) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($this->elements[$formElement->id])) {
      throw new \LogicException("This form already contains an element with the identifier '{$formElement->id}'.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Auto change to multipart form if we have an input file form element present.
    if ($formElement instanceof AbstractInputFile) {
      $this->attributes["enctype"] = "multipart/form-data";
    }

    // Add the form element to class scope.
    $this->elements[$formElement->id] = $formElement;

    return $this;
  }

  /**
   * Add hidden form element to this page's form.
   *
   * @param string $name
   *   The hidden form element's global unique identifier.
   * @param string $value
   *   The hidden form element's value.
   * @return this
   */
  public function addHiddenElement($name, $value) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($name)) {
      throw new \InvalidArgumentException("The \$name of a hidden form element cannot be empty.");
    }
    if (strpos($this->hiddenElements, "name='{$name}'") !== false) {
      throw new \LogicException("The \$name of a hidden form element has to be unique, '{$name}' is already present in this form.");
    }
    if (empty($value)) {
      throw new \InvalidArgumentException("The \$value of a hidden form element cannot be empty.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->hiddenElements .= "<input name='{$name}' type='hidden' value='{$this->presenter->htmlEncode($value)}'>";
    return $this;
  }

  /**
   * Get the form's action elements and closing tag.
   *
   * @return string
   *   The form's action elements and closing tag.
   */
  public function close() {
    if (($actions = $this->actionElements)) {
      $actions = "<p class='actions'>{$actions}</p>";
    }
    return "{$actions}</form>";
  }

  /**
   * Initialize the form.
   *
   * @param callable $validCallback [optional]
   *   Callable to call if the form is valid. The callable will be invoked without any arguments.
   * @param callable $validationCallback [optional]
   *   Callable to call to continue form validation in the presenter. The callable will get the errors as first
   *   parameter and you have to return the same array.
   * @return this
   */
  public function init(callable $validCallback = null, callable $validationCallback = null) {
    // Export attribute to class scope and add default attributes.
    $this->attributes["accept-charset"] = "utf-8";
    $this->attributes["method"]         = "post";
    if (empty($this->attributes["action"])) {
      $this->attributes["action"] = $this->request->uri;
    }

    // Validate the form if we're receiving it.
    if (isset($this->request->post["form_id"]) && $this->request->post["form_id"] == $this->id) {
      // Validate the form's token if we have an active session.
      if ($this->session->active === true) {
        // Assume that we don't have a form token stored in the user's session.
        $formToken = $this->session->storageGet("form_{$this->id}", false, true);

        // Check if we have a token for this form in session and submitted via HTTP plus compare both tokens.
        if ($formToken === false || empty($this->request->post["form_token"]) || $this->request->post["form_token"] != $formToken) {
          // Give the user the chance to re-submit this form.
          $this->presenter->alerts .= new Alert(
            "<p>{$this->intl->t("The form has become outdated. Copy any unsaved work in the form below and then {0}reload this page{1}.", [
              "<a href='{$this->request->uri}'>", "</a>",
            ])}</p>",
            $this->intl->t("Form Outdated"),
            Alert::SEVERITY_ERROR
          );
          return $this;
        }
      }

      // Used to collect error messages of all form elements.
      $errors = null;

      if ($this->elements) {
        // Iterate through all form elements and validate them.
        /* @var $formElement \MovLib\Presentation\Partial\FormElement\AbstractFormElement */
        foreach ($this->elements as $formElement) {
          // Used to collect the error messages of this specific form element.
          $error = null;

          // Let the form element validate itself.
          $formElement->validate($error);

          // If we have one or more errors for this form element collect them under its unique identifier for easy access
          // later on within the concrete class. This allows a concrete class to alter certain error messages or react
          // on certain errors.
          if ($error) {
            $errors[$formElement->id] = $error;
          }
        }
      }

      // Allow concrete classes to extend the validation process or alter certain error messages.
      if ($validationCallback) {
        $errors = $validationCallback($errors);
      }

      // If we have errors at this point export them and abort.
      if ($errors) {
        // Join all error messages of a specific form element with a break.
        foreach ($errors as $id => $error) {
          $errors[$id] = implode("<br>", (array) $error);
          if (isset($this->elements[$id])) {
            $this->elements[$id]->invalid();
          }
        }

        // Join all error messages with paragraphs.
        $errors = implode("</p><p>", $errors);

        // Finally export all error messages combined in a single alert message.
        $this->presenter->alerts .= new Alert("<p>{$errors}</p>", $this->intl->t("Validation Error"), Alert::SEVERITY_ERROR);
      }
      // If no errors were found continue processing.
      elseif ($validCallback) {
        $validCallback();
      }
    }

    return $this;
  }

  /**
   * Get the form's hidden elements and opening tag.
   *
   * @return string
   *   The form's hidden elements and opening tag.
   */
  public function open() {
    // Add the globally unique page's identifier as form identifier to the presentation.
    $this->hiddenElements .= "<input name='form_id' type='hidden' value='{$this->id}'>";

    // Generate one-time CSRF token for this form and add it to the user's session, the user can only have a single
    // token per unique form.
    if ($this->session->active === true) {
      $this->hiddenElements .= "<input name='form_token' type='hidden' value='{$this->session->storageSave(
        "form_{$this->id}",
        hash("sha512", openssl_random_pseudo_bytes(1024))
      )}'>";
    }

    return "<form{$this->presenter->expandTagAttributes($this->attributes)}>{$this->hiddenElements}";
  }

}
