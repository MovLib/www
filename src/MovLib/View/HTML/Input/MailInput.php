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
namespace MovLib\View\HTML\Input;

use \MovLib\Exception\ValidatorException;
use \MovLib\View\HTML\Input\TextInput;

/**
 * Create new email address input element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MailInput extends TextInput {

  /**
   * Instantiate new email address input form element.
   *
   * @todo Is the pattern to restrictive, or is our validate to nice?
   * @param array $attributes [optional]
   *   Additional attributes for this input element, the following attributes are set automatically:
   *   <ul>
   *     <li><code>"id"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"name"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex</li>
   *     <li><code>"maxlength"</code> is set to the global configuration <var>$GLOBALS["movlib"]["max_length_mail"]</var></li>
   *     <li><code>"pattern"</code> is set to a regular expression that helps browsers to validate the email address</li>
   *     <li><code>"type"</code> is set to <code>"email"</code></li>
   *   </ul>
   *   The <code>"value"</code> will be overwritten by any user submitted <var>$_POST[$id]</var>.
   * @param string $id [optional]
   *   The global identifier of the input element.
   * @param string $defaultValue [optional]
   *   The default value of this form element. This will be used if the element is not required and no value was
   *   submitted by the user (e.g. GMT if the user should choose a timezone).
   */
  public function __construct(array $attributes = null, $id = "mail", $defaultValue = "") {
    parent::__construct($id, $attributes, $defaultValue);
    $this->attributes["maxlength"] = $GLOBALS["movlib"]["max_length_mail"];
    $this->attributes["pattern"] = "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$";
    $this->attributes["type"] = "email";
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    if (!$this->label) {
      $this->label = $i18n->t("Email Address");
    }
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your email address");
    }
    if (!isset($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your email address in this field.");
    }
    return parent::__toString();
  }

  /**
   * Validate the user submitted email address.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    global $i18n;
    if (mb_strlen($_POST[$this->id]) > $GLOBALS["movlib"]["max_length_mail"]) {
      throw new ValidatorException($i18n->t("The {0} is too long: it must be {1,number,integer} characters or less.", [ $this->label, $GLOBALS["movlib"]["max_length_mail"] ]));
    }
    $filtered = filter_var($_POST[$this->id], FILTER_VALIDATE_EMAIL);
    if ($filtered === false || empty($filtered)) {
      throw new ValidatorException($i18n->t("The {0} does not appear to be valid.", [ $this->label ]));
    }
    if ($filtered != $_POST[$this->id]) {
      throw new ValidatorException($i18n->t("The {0} contains illegal characters.", [ $this->label ]));
    }
    $this->value = $filtered;
    return $this;
  }

}
