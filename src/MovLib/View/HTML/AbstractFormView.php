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

  /**
   * The attributes that will be applied to the <code>&lt;form&gt;</code>-element.
   *
   * <b>IMPORTANT!</b> Default CSS classes for the element type will be included automatically!
   *
   * @var array
   */
  protected $attributes = [ "class" => "container" ];

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

  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return string
   */
  public function getContent() {
    global $user;
    $csrf = "";
    if (($token = $user->csrfToken)) {
      $csrf = "<input aria-hidden='true' hidden name='csrf_token' type='hidden' value='{$token}'>";
    }
    if (!isset($this->attributes["action"])) {
      $this->attributes["action"] = $_SERVER["REQUEST_URI"];
    }
    if (!isset($this->attributes["method"])) {
      $this->attributes["method"] = "post";
    }
    $this->addClass("form form-{$this->getShortName()}", $this->attributes);
    return "<form {$this->expandTagAttributes($this->attributes)}>{$csrf}{$this->getFormContent()}</form>";
  }

  /**
   * The HTML content of the <code>&lt;form&gt;</code>-element.
   *
   * <b>IMPORTANT!</b> Do not include opening and closing <code>form</code>-tags!
   *
   * @return string
   *   The HTML content of the <code>&lt;form&gt;</code>-element.
   */
  abstract public function getFormContent();

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
   *   </ul>
   * @param string $tag
   *   [Optional] The elements tag, defaults to <em>input</em>.
   * @param string $content
   *   [Optional] If you create an element that can hold content (e.g. <em>button</em>, <em>select</em>,
   *   <em>textarea</em>) pass it here.
   * @return string
   *   The input element ready for print.
   */
  protected function getInputElement($name, $attributes = [], $tag = "input", $content = "") {
    if (!isset($attributes["type"])) {
      $attributes["type"] = "text";
    }
    switch ($attributes["type"]) {
      case "email":
        $attributes["role"] = "textbox";
        if (empty($attributes["value"])) {
          $attributes["value"] = isset($this->inputValues[$name])
            ? $this->inputValues[$name]
            : filter_input(INPUT_POST, $name, FILTER_SANITIZE_EMAIL)
          ;
        }
        break;

      case "password":
        $attributes["role"] = "textbox";
        // Only the presenter or view is allowed to insert a value into a password field. Never ever use a password
        // value that was submitted via the user.
        if (empty($attributes["value"]) && isset($this->inputValues[$name])) {
          $attributes["value"] = $this->inputValues[$name];
        }
        break;

      case "text":
        $attributes["role"] = "textbox";
        if (empty($attributes["value"])) {
          $attributes["value"] = isset($this->inputValues[$name])
            ? $this->inputValues[$name]
            : filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_ENCODE_AMP)
          ;
        }
        break;
    }
    foreach ([ "hidden", "required", "readonly" ] as $attribute) {
      if (isset($attributes[$attribute])) {
        $attributes["aria-{$attribute}"] = "true";
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
    switch ($tag) {
      case "button":
      case "select":
        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}</{$tag}>";

      case "textarea":
        $attributes["aria-multiline"] = "true";
        unset($attributes["type"]);
        return "<{$tag}{$this->expandTagAttributes($attributes)}>{$content}</{$tag}>";

      default:
        return "<{$tag}{$this->expandTagAttributes($attributes)}>";
    }
  }

}
