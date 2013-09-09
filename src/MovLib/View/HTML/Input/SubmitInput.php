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

use \MovLib\View\HTML\Input\AbstractInput;

/**
 * The submit input element is part of any form and displayed at the end of forms.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class SubmitInput extends AbstractInput {

  /**
   * Instantiate new submit input element.
   *
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to this input element. Please note that the following attributes
   *   are always applied by default and can only be overwritten after instantiating the object:
   *   <ul>
   *     <li><code>"tabindex"</code> is always set to the next global tabindex</li>
   *     <li><code>"type"</code> is always set to <code>"submit"</code></li>
   *     <li><code>"class"</code> is always extended with <code>"button"</code></li>
   *   </ul>
   */
  public function __construct(array $attributes = null) {
    $this->id = "submit";
    $this->attributes = $attributes;
    $this->attributes["tabindex"] = $this->getTabindex();
    $this->attributes["type"] = "submit";
    $this->addClass("button", $this->attributes);
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    if (empty($this->attributes["value"])) {
      $this->attributes["value"] = $i18n->t("Submit");
    }
    return "<input{$this->expandTagAttributes($this->attributes)}>";
  }

}
