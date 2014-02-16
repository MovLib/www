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
namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\AbstractInputFile;

/**
 * Add form to presentation.
 *
 * <h2>Methods inherited from {@see \MovLib\Presentation\AbstractBase}</h2>
 * @method string a($route, $text, array $attributes = null, $ignoreQuery = true)
 * @method this addClass($class, array &$attributes = null)
 * @method string collapseWhitespace($string)
 * @method string expandTagAttributes(array $attributes)
 * @method string getImage($style, $route = true, array $attributes = null, array $anchorAttributes = null)
 * @method string htmlDecode($text)
 * @method string htmlDecodeEntities($text)
 * @method string htmlEncode($text)
 * @method string lang($lang)
 * @method string normalizeLineFeeds($text)
 * @method string placeholder($text)
 *
 * <h2>Methods and properties inherited from {@see \MovLib\Presentation\Page}</h2>
 * @property string $alerts
 * @property string $bodyClasses
 * @property \MovLib\Presentation\Partial\Navigation $breadcrumb
 * @property string $breadcrumbTitle
 * @property string $contentAfter
 * @property string $contentBefore
 * @property string $headingBefore
 * @property string $headingAfter
 * @property string $headingSchemaProperty
 * @property-read string $id
 * @property-read array $languageLinks
 * @property-read array $namespace
 * @property-read string $pageTitle
 * @property-read string $schemaType
 * @property-read string $title
 * @method string getContent()
 * @method string getFooter()
 * @method string getHeader()
 * @method string getHeadTitle()
 * @method string getPresentation()
 * @method string getMainContent()
 * @method this initBreadcrumb()
 * @method this initLanguageLinks($route, array $args = null, $plural = false, $query = null)
 * @method this initPage($title)
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitForm {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form's action elements (e.g. submit input element).
   *
   * @var string
   */
  private $formActionElements;

  /**
   * The form's attributes array.
   *
   * @var array
   */
  private $formAttributes;

  /**
   * The form's auto-validate elements.
   *
   * @internal
   *   Keep visibility at protected and allow implementing class to access the various form elements.
   * @var array
   */
  protected $formElements;

  /**
   * The form's hidden elements (e.g. CSRF).
   *
   * @var string
   */
  private $formHiddenElements;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * The submitted form has no auto-validation errors, continue normal program flow.
   *
   * @return this
   */
  abstract protected function formValid();


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
  final protected function formAddAction($text, $attributes = []) {
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
    $attributes["value"] = $this->htmlEncode($text);

    // Put it all together and add it to this form's action elements.
    $this->formActionElements .= "<input{$this->expandTagAttributes($attributes)}>";

    return $this;
  }

  /**
   * Add form element to this page's form.
   *
   * @param \MovLib\Presentation\Partial\FormElement\AbstractFormElement $formElement
   *   The form element to add.
   * @return this
   */
  final protected function formAddElement($formElement) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($formElement instanceof \MovLib\Presentation\Partial\FormElement\AbstractFormElement)) {
      throw new \InvalidArgumentException("Any form element must inherit from \\MovLib\\Presentation\\Partial\\FormElement\\AbstractFormElement");
    }
    if (isset($this->formElements[$formElement->id])) {
      throw new \LogicException("This form already contains an element with the identifier '{$formElement->id}'.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Auto change to multipart form if we have an input file form element present.
    if ($formElement instanceof AbstractInputFile) {
      $this->formAttributes["enctype"] = "multipart/form-data";
    }

    // Add the form element to class scope.
    $this->formElements[$formElement->id] = $formElement;

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
  final protected function formAddHidden($name, $value) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($name)) {
      throw new \InvalidArgumentException("The \$name of a hidden form element cannot be empty.");
    }
    if (strpos($this->formHiddenElements, $name) !== false) {
      throw new \LogicException("The \$name of a hidden form element has to be unique, '{$name}' is already present in this form.");
    }
    if (empty($value)) {
      throw new \InvalidArgumentException("The \$value of a hidden form element cannot be empty.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->formHiddenElements .= "<input name='{$name}' type='hidden' value='{$this->htmlEncode($value)}'>";
    return $this;
  }

  /**
   * Get the form's action elements and closing tag.
   *
   * @return string
   *   The form's action elements and closing tag.
   */
  protected function formClose() {
    if (($actions = $this->formActionElements)) {
      $actions = "<p class='actions'>{$actions}</p>";
    }
    return "{$actions}</form>";
  }

  /**
   * Initialize the form.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @param array $attributes [optional]
   *   The form's additional attributes, the following attributes are always set:
   *   <ul>
   *     <li><code>"accept-charset"</code> is always set to <code>"utf-8"</code></li>
   *     <li><code>"action"</code> is set to <var>$kernel->requestURI</var> if not set</li>
   *     <li><code>"method"</code> is always set to <code>"post"</code></li>
   *   </ul>
   * @return this
   */
  final protected function formInit(array $attributes = null) {
    global $i18n, $kernel, $session;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!method_exists($this, "initPage")) {
      throw new \LogicException("You can only use the form trait within a presenting page class");
    }
    foreach ([ "accept-charset", "method" ] as $attribute) {
      if (isset($attributes[$attribute])) {
        throw new \LogicException("You must not set the '{$attribute}' attribute of a form");
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Export attribute to class scope and add default attributes.
    $this->formAttributes                   = $attributes;
    $this->formAttributes["accept-charset"] = "utf-8";
    $this->formAttributes["method"]         = "post";
    isset($this->formAttributes["action"]) || ($this->formAttributes["action"] = $kernel->requestURI);

    // Validate the form if we're receiving it.
    if (isset($_POST["form_id"]) && $_POST["form_id"] == $this->id) {
      // Validate the form's token if we have an active session.
      if ($session->active === true) {
        // Assume that we don't have a form token stored in the user's session.
        $formToken = false;

        // If we have create local copy of it and remove it from the user's session, we want to ensure that this token
        // cannot be re-used for this form.
        if (isset($session["form_tokens"][$this->id])) {
          $formToken = $session["form_tokens"][$this->id];
          unset($session["form_tokens"][$this->id]);
        }

        // Check if we have a token for this form in session and submitted via HTTP plus compare both tokens.
        if ($formToken === false || empty($_POST["form_token"]) || $_POST["form_token"] != $formToken) {
          // Give the user the chance to re-submit this form.
          $this->alerts .= new Alert(
            "<p>{$i18n->t("The form has become outdated. Copy any unsaved work in the form below and then {0}reload this page{1}.", [
              "<a href='{$kernel->requestURI}'>", "</a>",
            ])}</p>",
            $i18n->t("Form Outdated"),
            Alert::SEVERITY_ERROR
          );
          return $this;
        }
      }

      // Used to collect error messages of all form elements.
      $errors = null;

      // Iterate through all form elements and validate them.
      /* @var $formElement \MovLib\Presentation\Partial\FormElement\AbstractFormElement */
      foreach ($this->formElements as $formElement) {
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

      // Allow concrete classes to extend the validation process or alter certain error messages.
      $this->hookFormValidation($errors);

      // If we have errors at this point export them and abort.
      if ($errors) {
        // Join all error messages of a specific form element with a break.
        foreach ($errors as $id => $error) {
          $errors[$id] = implode("<br>", $error);
        }

        // Join all error messages with paragraphs.
        $errors = implode("</p><p>", $errors);

        // Finally export all error messages combined in a single alert message.
        $this->alerts .= new Alert("<p>{$errors}</p>", $i18n->t("Validation Error"), Alert::SEVERITY_ERROR);
      }
      // If no errors were found continue processing.
      else {
        $this->formValid();
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
  protected function formOpen() {
    global $session;

    // Add the globally unique page's identifier as form identifier to the presentation.
    $this->formHiddenElements .= "<input name='form_id' type='hidden' value='{$this->id}'>";

    // Generate one-time CSRF token for this form and add it to the user's session, the user can only have a single
    // token per unique form.
    if ($session->active === true) {
      $session["form_tokens"][$this->id] = hash("sha512", openssl_random_pseudo_bytes(1024));
      $this->formHiddenElements .= "<input name='form_token' type='hidden' value='{$session["form_tokens"][$this->id]}'>";
    }

    return "<form{$this->expandTagAttributes($this->formAttributes)}>{$this->formHiddenElements}";
  }

  /**
   * Get the form including all elements.
   *
   * @return string
   *   The form including all elements.
   */
  protected function formRender() {
    $elements = implode($this->formElements);
    return "{$this->formOpen()}{$elements}{$this->formClose()}";
  }

  /**
   * Continue form validation process after auto-validation.
   *
   * This hook is called after all form elements have been auto-validated. It allows concrete classes to extend the
   * validation process or alter error messages of specific form elements.
   *
   * @param null|array $errors
   *   <code>NULL</code> if no errors were found so far. Otherwise it is an associative array where each key is they
   *   unique identifier of the form element that contains errors. Each form elements offset contains a numeric array
   *   where the key is defined via the classe's <var>ERROR_*</var> constants and the value is the default error
   *   message as defined in the form element's validation method. Use the <var>ERROR_*</var> to check for certain
   *   errors.
   * @return this
   */
  protected function hookFormValidation(&$errors) {
    return $this;
  }

}
