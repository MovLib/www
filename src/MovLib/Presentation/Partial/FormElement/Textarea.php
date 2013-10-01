<?php

/* !
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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Presentation\Validation\HTML;

/**
 * HTML textarea form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/textarea
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Textarea extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * @inheritdoc
   */
  public function __toString() {
    $this->attributes["aria-multiline"] = "true";
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->value}</textarea></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    $this->value = (new HTML($this->value, $this->attributes["data-format"], $this->attributes["data-allow-external"]))->validate();
    return $this;
  }

}
