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

use \MovLib\View\HTML\Input\TextInput;

/**
 * Create new password input element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PasswordInput extends TextInput {

  /**
   * Instantiate new password input element.
   *
   * @param string $id [optional]
   *   The global identifier of the input element.
   * @param array $attributes [optional]
   *   Additional attributes for this input element, the following attributes are set automatically:
   *   <ul>
   *     <li><code>"id"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"name"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex</li>
   *     <li><code>"type"</code> is set to <code>"password"</code></li>
   *   </ul>
   */
  public function __construct($id = "pass", array $attributes = null) {
    parent::__construct($id, $attributes, "");
    $this->attributes["type"] = "password";
    // Only allow a presenter to set the value of a password element!
    if (!empty($_POST[$this->id])) {
      unset($this->attributes["value"]);
    }
    $this->required();
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    if (!$this->label) {
      $this->label = $i18n->t("Password");
    }
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your password");
    }
    if (!isset($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your password in this field.");
    }
    return parent::__toString();
  }

}
