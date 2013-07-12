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

use \MovLib\Utility\String;
use \MovLib\Utility\Validation;
use \MovLib\View\HTML\AbstractView;

/**
 * The abstract form view contains utility methods for views with forms.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormView extends AbstractView {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The enctype string for <tt>octet/stream</tt> encoding (file uploads; in general differing MIME types).
   *
   * @var string
   */
  const ENCTYPE_BINARY = "multipart/form-data";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes that will be applied to the <code>&lt;form&gt;</code>-element.
   *
   * @var array
   */
  protected $attributes = [
    "accept-charset" => "UTF-8",
    "class"          => "container",
    "method"         => "post",
  ];

  /**
   * Array that can be used by the presenter to set errors for certain input elements.
   *
   * The array should be in the form: <code>[ form-elements-name => true ]</code>
   *
   * @var array
   */
  public $formInvalid;

  /**
   * Array that can be used by the presenter to disable certain input elements.
   *
   * The array should be in the form: <code>[ form-elements-name => true ]</code>
   *
   * @var array
   */
  public $formDisabled;

  /**
   * Array that can be used by the presenter to set the value of any input element.
   *
   * The array should be in the form: <code>[ input-elements-name => value ]</code>
   *
   * @var array
   */
  public $inputValues;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Public Methods


  /**
   * The HTML content of the <code>&lt;form&gt;</code>-element.
   *
   * <b>IMPORTANT!</b> Do not include opening and closing <code>form</code>-tags!
   *
   * @return string
   *   The HTML content of the <code>&lt;form&gt;</code>-element.
   */
  abstract public function getFormContent();


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return string
   */
  public function getContent() {
    global $user;
    $csrf = "";
    if (($token = $user->csrfToken)) {
      $csrf = "<input aria-hidden='true' hidden name='csrf' type='hidden' value='{$token}'>";
    }
    if (!isset($this->attributes["action"])) {
      $this->attributes["action"] = $_SERVER["REQUEST_URI"];
    }
    return "<form{$this->expandTagAttributes($this->attributes)}>{$csrf}{$this->getFormContent()}</form>";
  }

  /**
   * Render an HTML input element.
   *
   * Always use this method to create your input elements of forms. This method ensures that all necessary ARIA
   * attributes are applied to the element. Also all necessary attributes will be set correctly. The value of the
   * element will be automatically filled with POST data—if available—and correctly sanitized.
   *
   * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html
   * @link https://developer.mozilla.org/en-US/docs/Accessibility/ARIA/ARIA_Techniques
   * @param string $name
   *   The <em>name</em> of the input element. This value is used for the <em>id</em> and <em>name</em> attribute of
   *   the input element.
   * @param array $attributes
   *   [Optional] Array containing attributes that should be applied to the element or to overwrite the defaults. Any
   *   attribute that is valid for an input element can be passed. The following attributes can be overwritten:
   *   <ul>
   *     <li><em>type</em>: Default type is <em>text</em>.</li>
   *     <li><em>value</em>: Default value is taken from POST and sanitized, if you overwrite this be sure to sanitize
   *     it correctly by issuing the <code>filter_input()</code> function.</li>
   *   </ul>
   *   The following attributes are always applied and cannot be overwritten:
   *   <ul>
   *     <li><em>role</em>: The ARIA role.</li>
   *     <li><em>id</em>: Is always set to the value of <var>$name</var>.</li>
   *     <li><em>name</em>: Is always set to the value of <var>$name</var>.</li>
   *     <li><em>tabindex</em>: Is always set to the next by calling <code>AbstractView::getTabindex()</code>.</li>
   *   </ul>
   * @param string $tag
   *   [Optional] The elements tag, defaults to <em>input</em>.
   * @param string $content
   *   [Optional] If you create an element that can hold content (e.g. <em>button</em>, <em>select</em>,
   *   <em>textarea</em>) pass it here.
   * @return string
   *   The input element ready for print.
   */
  protected function input($name, $attributes = [], $tag = "input", $content = "") {
    $ariaAttributes = [ "hidden", "required", "readonly" ];
    for ($i = 0; $i < 3; ++$i) {
      if (isset($attributes[$ariaAttributes[$i]])) {
        $attributes["aria-{$ariaAttributes[$i]}"] = "true";
      }
    }
    if (isset($this->formInvalid[$name])) {
      $attributes["aria-invalid"] = "true";
    }
    if (isset($this->formDisabled[$name])) {
      $attributes["aria-disabled"] = "true";
      $attributes[] = "disabled";
    }
    $attributes["id"] = $attributes["name"] = $name;
    $attributes["tabindex"] = $this->getTabindex();
    if (!isset($attributes["type"])) {
      $attributes["role"] = "textbox";
    }
    else {
      switch ($attributes["type"]) {
        case "email":
          $attributes["role"] = "textbox";
          if ((!isset($attributes["value"]) || empty($attributes["value"])) && isset($this->inputValues[$name]) && !empty($this->inputValues[$name])) {
            $attributes["value"] = $this->inputValues[$name];
          }
          elseif (($attributes["value"] = Validation::inputMail($name)) === false) {
            unset($attributes["value"]);
          }
          break;

        case "password":
          $attributes["role"] = "textbox";
          // Only the presenter or view is allowed to insert a value into a password field. Never ever use a password
          // value that was submitted via the user.
          if ((!isset($attributes["value"]) || empty($attributes["value"])) && isset($this->inputValues[$name]) && !empty($this->inputValues[$name])) {
            $attributes["value"] = $this->inputValues[$name];
          }
          break;

        case "radio":
          unset($attributes["id"]);
          if (isset($this->inputValues[$name]) && isset($attributes["value"]) && $attributes["value"] == $this->inputValues[$name]) {
            $attributes[] = "checked";
          }
          break;

        case "checkbox":
          if (isset($this->inputValues[$name]) && !empty($this->inputValues[$name])) {
            $attributes[] = "checked";
          }
          break;
      }
    }
    if (!isset($attributes["value"]) || empty($attributes["value"])) {
      if (isset($this->inputValues[$name]) && !empty($this->inputValues[$name])) {
        $attributes["value"] = $this->inputValues[$name];
      }
      elseif (($attributes["value"] = Validation::inputString($name)) === false) {
        unset($attributes["value"]);
      }
    }
    switch ($tag) {
      case "button":
      case "select":
        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}</{$tag}>";

      case "textarea":
        $attributes["aria-multiline"] = "true";
        unset($attributes["type"]);
        unset($attributes["value"]);
        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}</{$tag}>";

      default:
        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}";
    }
  }

  /**
   * Get an input element with a datalist.
   *
   * <b>Usage example:</b>
   * <pre>$this->inputDatalist([ name, attributes, tag, content ], [ id, options ]);</pre>
   *
   * @param array $input
   *   {@see \MovLib\View\HTML\AbstractFormView::input()}
   * @param array $datalist
   *   {@see \MovLib\View\HTML\AbstractFormView::datalist()}
   * @return string
   *   The input and datalist element ready for print.
   */
  protected function inputDatalist($input, $datalist) {
    if (!isset($input[1])) {
      $input[1] = [];
    }
    $input[1]["aria-autocomplete"] = "list";
    $input[1]["list"] = $datalist[0];
    $input = call_user_func_array([ $this, "input" ], $input);
    $datalist = call_user_func_array([ $this, "datalist" ], $datalist);
    return $input . $datalist;
  }

  /**
   * Generate datalist for autocomplete input element.
   *
   * @link https://github.com/thgreasi/datalist-polyfill
   * @param string $id
   *   The unique DOM ID of this datalist.
   * @param array
   *   Numeric array containing the options for this datalist.
   * @return string
   *   The datalist ready for print.
   */
  protected function datalist($id, $options) {
    $datalist = "<datalist id='{$id}'><select class='hidden'>";
    $c = count($options);
    for ($i = 0; $i < $c; ++$i) {
      $datalist .= "<option value='{$options[$i]}'>";
    }
    return "{$datalist}</select></datalist>";
  }

  /**
   * Generate datalist for countries.
   *
   * @todo Prioritize certain countries
   * @todo Include common typos
   * @link http://uxdesign.smashingmagazine.com/2011/11/10/redesigning-the-country-selector/
   * @global \MovLib\Model\I18nModel $i18n
   *   Global i18n model instance.
   * @param string $id
   *   The unique DOM ID of this datalist.
   * @return string
   *   The datalist ready for print.
   */
  public function getCountryDatalist($id) {
    global $i18n;
    $datalist = "<datalist id='{$id}'><select class='hidden'>";
    foreach ($i18n->getCountries() as $id => $country) {
      $datalist .=
        "<option value='{$country["name"]}'></option>" .
        "<option value='{$country["name"]}'>{$country["code"]}</option>"
      ;
    }
    return "$datalist</select></datalist>";
  }

  /**
   * Render an HTML select element.
   *
   * @see \MovLib\View\HTML\AbstractFormView::getInputElement()
   * @param string $name
   *   The <em>name</em> of the select element. This value is used for the <em>id</em> and <em>name</em> attribute of
   *   the select element.
   * @param array $options
   *   Numerical array containing associative arrays for each option.
   * @param array $attributes
   *   [Optional] Array containing attributes that should be applied to the element or to overwrite the defaults.
   * @return string
   *   The select element ready for print.
   */
  protected function select($name, $options, $attributes = []) {
    $content = "";
    $c = count($options);
    for ($i = 0; $i < $c; ++$i) {
      $text = $options[$i]["text"];
      unset($options[$i]["text"]);
      $content .= "<option{$this->expandTagAttributes($options[$i])}>{$text}</option>";
    }
    return $this->input($name, $attributes, "select", $content);
  }

  /**
   * Get default submit button.
   *
   * @param string $text
   *   The text that should be displayed within the button.
   * @param string $title
   *   The title that should be displayed in the tooltip.
   * @return string
   *   The submit button ready for print.
   */
  protected function submit($text, $title = "") {
    return "<button class='button button--success button--large' tabindex='{$this->getTabindex()}' title='{$title}' type='submit'>{$text}</button>";
  }

  /**
   * Get mark-up for help text on a default form element.
   *
   * @param string $text
   *   The already translated help text.
   * @return string
   *   The text wrapped in the mark-up globally used for help elements.
   */
  protected function help($text) {
    return "<span class='form-help'><i class='icon icon--help-circled'></i><small class='form-help-text'>{$text}</small></span>";
  }

  /**
   * Create a group of radio buttons.
   *
   * @param string $name
   *   The <em>name</em> of the radio elements. This value is used for the <em>name</em> attribute of the radio elements.
   * @param array $data
   *   Associative array containing the data for the radio elements where the key is used as content of the value-
   *   attribute and the value as label (already translated).
   * @param mixed $checked
   *   [Optional] The value of the radio element that should be checked by default. Defaults to <code>NULL</code>.
   * @param boolean $inline
   *   [Optional] Flag to determine if the radio group should be displayed inline or stacked. Defaults to
   *   <code>TRUE</code>.
   * @return string
   *   The radio group ready for print.
   */
  protected function radioGroup($name, $data, $checked = null, $inline = true) {
    $radios = "";
    $inline = $inline ? " inline" : "";
    foreach ($data as $value => $label) {
      $attr = [ "type" => "radio", "value" => $value, "required" ];
      if (!isset($this->inputValues[$name]) && $value === $checked) {
        $attr[] = "checked";
      }
      $radios .= "<label class='radio{$inline}'>{$this->input($name, [
        "type" => "radio",
        "value" => $value
      ])}{$label}</label>";
    }
    return $radios;
  }

}
