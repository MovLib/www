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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * HTML input type password form element.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputPassword extends \MovLib\Presentation\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Minimum length for a password.
   *
   * @var int
   */
  public $minimumPasswordLength = 8;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type password.
   *
   * @param string $id [optional]
   *   The password's global unique identifier, defaults to <code>"password"</code>.
   * @param string $label [optional]
   *   The password's translated label text, defaults to <code>$i18n->t("Password")</code>.
   * @param array $attributes [optional]
   *   The password's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex (with <code>getTabindex()</code>)</li>
   *     <li><code>"required"</code> is set</li>
   *     <li><code>"pattern"</code> is set to a regular expression that matches our minimum password requirements</li>
   *     <li><code>"title"</code> explains the minimum password requirements</li>
   *     <li><code>"type"</code> is set to <code>"password"</code></li>
   *   </ul>
   *   You <b>should not</b> override any of the default attributes. The <code>"placeholder"</code> attribute is set to
   *   <code>$i18n->t("Enter your password")</code> if none is passed along.
   */
  public function __construct($id = "password", $label = null, array $attributes = null) {
    global $i18n;
    parent::__construct($id, $label ?: $i18n->t("Password"), $attributes);
    $this->attributes["pattern"]       = "^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{{$this->minimumPasswordLength},}$";
    $this->attributes["title"]         = $i18n->t(
      "A password must contain lowercase and uppercase letters, numbers, and must be at least {0,number,integer} characters long.",
      [ $this->minimumPasswordLength ]
    );
    $this->attributes["type"]          = "password";
    $this->attributes[]                = "required";
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your password");
    }
  }
  /**
   * @inheritdoc
   */
  public function __toString() {
    // Ensure value isn't prefilled in output!
    unset($this->value);
    return parent::__toString();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods



  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      throw new ValidationException($i18n->t("The highlighted password field is mandatory."));
    }

    $errors = null;
    if (mb_strlen($this->value) < $this->minimumPasswordLength) {
      $errors[] = $i18n->t("The password is too short: it must be {0,number,integer} characters or more.", [ $this->minimumPasswordLength ]);
    }
    if (preg_match("/{$this->attributes["pattern"]}/", $this->value) == false) {
      $errors[] = $i18n->t("The password is not complex enough: it must contain numbers plus lowercase and uppercase letters.");
    }
    if ($errors) {
      throw new ValidationException(implode("<br>", $errors));
    }

    return $this;
  }

}
