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
 * Create new button input element.
 *
 * Buttons are special input elements that can be used like submit input elements but additionally have a separate
 * <code>"value"</code>-attribute and they can contain HTML-mark-up.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ButtonInput extends TextInput {

  /**
   * Instantiate new button input element.
   *
   * @param string $id
   *   The global identifier of this input element.
   * @param array $attributes [optional]
   *   Additional attributes for this input element, the following attributes are set automatically:
   *   <ul>
   *     <li><code>"id"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"name"</code> is set to the value of <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex</li>
   *     <li><code>"type"</code> is set to <code>"button"</code> if no value is supplied</li>
   *   </ul>
   */
  public function __construct($id, array $attributes = null) {
    parent::__construct($id, $attributes, null);
    if (!isset($this->attributes["type"])) {
      $this->attributes["type"] = "button";
    }
  }

}
