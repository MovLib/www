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
use \MovLib\View\HTML\AbstractPageView;
use \MovLib\View\HTML\FormElement\Action\BaseAction;
use \MovLib\View\HTML\FormElement\Input\HiddenInput;

/**
 * The abstract form view contains utility methods for views with forms.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormView extends AbstractPageView {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The enctype string for <tt>octet/stream</tt> encoding (file uploads; in general differing MIME types).
   *
   * @var string
   */
  const ENCTYPE_BINARY = "multipart/form-data";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Associative array containing all action form elements.
   *
   * @var array
   */
  public $actionElements = [];

  /**
   * The attributes that will be applied to the <code><form></code>-element itself.
   *
   * @var array
   */
  public $formAttributes = [];

  /**
   * Associative array containing all hidden form elements.
   *
   * @var array
   */
  public $hiddenElements = [];

  /**
   * Associative array containing all visible form elements.
   *
   * @var array
   */
  public $formElements = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new form view.
   *
   * @global \MovLib\Model\UserModel $user
   * @param \MovLib\Presenter\AbstractPresenter $presenter
   *   The presenter controlling this view.
   * @param string $title
   *   The already translated title of this view.
   */
  public function __construct($presenter, $title) {
    global $user;
    parent::__construct($presenter, $title);
    if ($token = $user->csrfToken) {
      $this->hiddenElements["csrf"] = new HiddenInput("csrf", $token);
    }
    $this->formAttributes = [
      "accept-charset" => "UTF-8",
      "action"         => $_SERVER["REQUEST_URI"],
      "method"         => "post",
    ];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      $errors = null;
      foreach ($this->formElements as $id => $element) {
        try {
          $element->validate();
        } catch (ValidatorException $e) {
          $element->invalid();
          $errors .= "<p>{$e->getMessage()}</p>";
        }
      }
      if ($errors) {
        $this->setAlert($errors, self::ALERT_SEVERITY_ERROR);
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Attach a form element to this form.
   *
   * @param \MovLib\View\HTML\FormElement\AbstractFormElement $element
   *   <code>AbstractFormElement</code> instance.
   * @return this
   */
  public function attach($element) {
    if ($element instanceof BaseAction) {
      $this->actionElements[$element->id] = $element;
    }
    elseif ($element instanceof HiddenInput) {
      $this->hiddenElements[$element->id] = $element;
    }
    else {
      $this->formElements[$element->id] = $element;
    }
    return $this;
  }

  /**
   * Get the opening <code><form></code>-tag, including all hidden form elements.
   *
   * @param string $classes [optional]
   *   Additional CSS classes that should be applied to the <code><form></code>-tag.
   * @return string
   *   The opeining <code><form></code>-tag, including all hidden form elements.
   */
  public function formOpen($classes = null) {
    $hidden = "";
    foreach ($this->hiddenElements as $id => $element) {
      $hidden .= $element;
    }
    if ($classes) {
      $this->addClass($classes, $this->formAttributes);
    }
    return "<form{$this->expandTagAttributes($this->formAttributes)}>{$hidden}";
  }

  /**
   * Get the closing <code><form></code>-tag, including all form actions.
   *
   * @param boolean $wrapActions [optional]
   *   If set to <code>FALSE</code> the form action elements won't be wrapped with the <code><div class='form-actions'/>
   *   </code>-element, default is to wrap the form actions.
   * @return string
   *   The closing <code><form></code>-tag, including all form actions.
   */
  public function formClose($wrapActions = true) {
    $actions = "";
    foreach ($this->actionElements as $id => $element) {
      $actions .= $element;
    }
    if ($wrapActions === true) {
      $actions = "<div class='form-actions'>{$actions}</div>";
    }
    return "{$actions}</form>";
  }

//
//  // ------------------------------------------------------------------------------------------------------------------- OLD!!!! REFACTOR!!!!
//
//
//  /**
//   * Render an HTML input element.
//   *
//   * Always use this method to create your input elements of forms. This method ensures that all necessary ARIA
//   * attributes are applied to the element. Also all necessary attributes will be set correctly. The value of the
//   * element will be automatically filled with POST data—if available—and correctly sanitized.
//   *
//   * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html
//   * @link https://developer.mozilla.org/en-US/docs/Accessibility/ARIA/ARIA_Techniques
//   * @param string $name
//   *   The <em>name</em> of the input element. This value is used for the <em>id</em> and <em>name</em> attribute of
//   *   the input element.
//   * @param array $attributes
//   *   [Optional] Array containing attributes that should be applied to the element or to overwrite the defaults. Any
//   *   attribute that is valid for an input element can be passed. The following attributes can be overwritten:
//   *   <ul>
//   *     <li><em>type</em>: Default type is <em>text</em>.</li>
//   *     <li><em>value</em>: Default value is taken from POST and sanitized, if you overwrite this be sure to sanitize
//   *     it correctly by issuing the <code>filter_input()</code> function.</li>
//   *   </ul>
//   *   The following attributes are always applied and cannot be overwritten:
//   *   <ul>
//   *     <li><em>role</em>: The ARIA role.</li>
//   *     <li><em>id</em>: Is always set to the value of <var>$name</var>.</li>
//   *     <li><em>name</em>: Is always set to the value of <var>$name</var>.</li>
//   *     <li><em>tabindex</em>: Is always set to the next by calling <code>AbstractView::getTabindex()</code>.</li>
//   *   </ul>
//   * @param string $tag
//   *   [Optional] The elements tag, defaults to <em>input</em>.
//   * @param string $content
//   *   [Optional] If you create an element that can hold content (e.g. <em>button</em>, <em>select</em>,
//   *   <em>textarea</em>) pass it here.
//   * @return string
//   *   The input element ready for print.
//   */
//  protected function input($name, $attributes = [], $tag = "input", $content = "") {
//    $ariaAttributes = [ "hidden", "required", "readonly" ];
//    for ($i = 0; $i < 3; ++$i) {
//      if (isset($attributes[$ariaAttributes[$i]])) {
//        $attributes["aria-{$ariaAttributes[$i]}"] = "true";
//      }
//    }
//    if (isset($this->formInvalid[$name])) {
//      $attributes["aria-invalid"] = "true";
//    }
//    if (isset($this->formDisabled[$name])) {
//      $attributes["aria-disabled"] = "true";
//      $attributes[] = "disabled";
//    }
//    $attributes["id"] = $attributes["name"] = $name;
//    $attributes["tabindex"] = $this->getTabindex();
//    if (!isset($attributes["type"])) {
//      $attributes["type"] = "text";
//      $attributes["role"] = "textbox";
//    }
//    switch ($attributes["type"]) {
//      case "password":
//        $attributes["role"] = "textbox";
//        // Only the presenter or view is allowed to insert a value into a password field. Never ever use a password
//        // value that was submitted via the user.
//        if (isset($this->inputValues[$name])) {
//          $attributes["value"] = $this->inputValues[$name];
//        }
//        break;
//
//      case "radio":
//        unset($attributes["id"]);
//        if ((isset($_POST[$name]) && $_POST[$name] == $attributes["value"]) || (isset($this->inputValues[$name]) && $this->inputValues[$name] == $attributes["value"])) {
//          $attributes[] = "checked";
//        }
//        break;
//
//      case "checkbox":
//        if (isset($_POST[$name]) || isset($this->inputValues[$name]) && $this->inputValues[$name]) {
//          $attributes[] = "checked";
//        }
//        break;
//
//      default:
//        if (isset($_POST[$name])) {
//          $attributes["value"] = $_POST[$name];
//        }
//        elseif (isset($this->inputValues[$name])) {
//          $attributes["value"] = $this->inputValues[$name];
//        }
//    }
//    switch ($tag) {
//      case "button":
//      case "select":
//        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}</{$tag}>";
//
//      case "textarea":
//        $attributes["aria-multiline"] = "true";
//        unset($attributes["type"]);
//        unset($attributes["value"]);
//        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}</{$tag}>";
//
//      default:
//        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}";
//    }
//  }
//
//  public function selectGetSystemLanguages() {
//    global $i18n;
//    $systemLanguages[] = [ "disabled", "text" => $i18n->t("Select your preferred language"), "value" => "" ];
//    foreach ($i18n->getSystemLanguages() as $id => $language) {
//      $systemLanguages[] = [
//        "data-alternate-spellings" => $language["code"],
//        "text"  => $language["name"],
//        "value" => $id,
//      ];
//    }
//    return $systemLanguages;
//  }
//
//  public function selectGetLanguages() {
//    global $i18n;
//  }
//
//  /**
//   * Generate array containing the countries sorted by name for usage as options in a select element.
//   *
//   * @todo Prioritize certain countries
//   * @todo Include common typos
//   * @todo Extend alternate spellings
//   * @link http://uxdesign.smashingmagazine.com/2011/11/10/redesigning-the-country-selector/
//   * @see \MovLib\View\HTML\AbstractFormView::select()
//   * @global \MovLib\Model\I18nModel $i18n
//   *   Global i18n model instance.
//   * @return array
//   *   Numeric array for usage with <code>$this->select()</code>.
//   */
//  public function selectGetCountries() {
//    global $i18n;
//    $countries[] = [ "disabled", "text" => $i18n->t("Select your country"), "value" => "" ];
//    foreach ($i18n->getCountries(I18nModel::KEY_NAME) as $name => $country) {
//      $countries[] = [
//        "data-alternate-spellings" => $country["code"],
//        "text"  => $name,
//        "value" => $country["id"],
//      ];
//    }
//    return $countries;
//  }
//
//  /**
//   * Render an HTML select element.
//   *
//   * @see \MovLib\View\HTML\AbstractFormView::getInputElement()
//   * @param string $name
//   *   The <em>name</em> of the select element. This value is used for the <em>id</em> and <em>name</em> attribute of
//   *   the select element.
//   * @param array $options
//   *   Numerical array containing associative arrays for each option.
//   * @param array $attributes
//   *   [Optional] Array containing attributes that should be applied to the element or to overwrite the defaults.
//   * @return string
//   *   The select element ready for print.
//   */
//  protected function select($name, $options, $attributes = []) {
//    $selected = "";
//    if (isset($_POST[$name])) {
//      $selected = $_POST[$name];
//    }
//    elseif (isset($this->inputValues[$name])) {
//      $selected = $this->inputValues[$name];
//    }
//    $content = "";
//    $c = count($options);
//    for ($i = 0; $i < $c; ++$i) {
//      $attr = [];
//      if (is_array($options[$i])) {
//        $text = $options[$i]["text"];
//        unset($options[$i]["text"]);
//        $attr = $options[$i];
//      }
//      else {
//        $attr["value"] = $text = $options[$i];
//      }
//      if ($selected == $attr["value"]) {
//        $attr[] = "selected";
//      }
//      $content .= "<option{$this->expandTagAttributes($attr)}>{$text}</option>";
//    }
//    return $this->input($name, $attributes, "select", $content);
//  }
//
//  /**
//   * Get default submit button.
//   *
//   * @param string $text
//   *   The text that should be displayed within the button.
//   * @param string $title
//   *   The title that should be displayed in the tooltip.
//   * @return string
//   *   The submit button ready for print.
//   */
//  protected function submit($text, $title = "") {
//    return "<button class='button button--success button--large' tabindex='{$this->getTabindex()}' title='{$title}' type='submit'>{$text}</button>";
//  }
//
//  /**
//   * Get mark-up for help text on a default form element.
//   *
//   * @param string $text
//   *   The already translated help text.
//   * @return string
//   *   The text wrapped in the mark-up globally used for help elements.
//   */
//  protected function help($text) {
//    return "<span class='form-help popup-container'><i class='icon icon--help-circled'></i><small class='popup'>{$text}</small></span>";
//  }
//
//  /**
//   * Create a group of radio buttons.
//   *
//   * @param string $name
//   *   The <em>name</em> of the radio elements. This value is used for the <em>name</em> attribute of the radio elements.
//   * @param array $data
//   *   Associative array containing the data for the radio elements where the key is used as content of the value-
//   *   attribute and the value as label (already translated).
//   * @param boolean $inline
//   *   [Optional] Flag to determine if the radio group should be displayed inline or stacked. Defaults to
//   *   <code>TRUE</code>.
//   * @return string
//   *   The radio group ready for print.
//   */
//  protected function radioGroup($name, $data, $inline = true) {
//    $radios = "";
//    $inline = $inline ? " inline" : "";
//    foreach ($data as $value => $label) {
//      $radios .= "<label class='radio{$inline}'>{$this->input($name, [ "required", "type" => "radio", "value" => $value ])}{$label}</label>";
//    }
//    return $radios;
//  }

}
