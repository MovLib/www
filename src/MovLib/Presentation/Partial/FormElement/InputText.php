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
use \Normalizer;

/**
 * HTML input type text form element.
 *
 * In contrast to the default input element, this is specialized for plain text input. The user submitted string is
 * sanitized. No validation!
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/association-of-controls-and-forms.html#attr-fe-inputmode
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputText extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * @inheritdoc
   */
  public function __construct($id, $label, array $attributes = null) {
    parent::__construct($id, $label, $attributes);
    $this->attributes["type"] = "text";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” text field is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    if (preg_match("//u", $this->value) === false) {
      throw new ValidationException($i18n->t("The “{0}” text field contains invalid UTF-8 characters.", [ $this->label ]));
    }

    if (preg_match("/&.*;/i", $this->value) != false) {
      $this->value = html_entity_decode(html_entity_decode($this->value, ENT_QUOTES|ENT_HTML5), ENT_QUOTES|ENT_HTML5);
    }
    $this->value = htmlspecialchars($this->value, ENT_QUOTES|ENT_HTML5);

    if ($this->value != filter_var($this->value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_REQUIRE_SCALAR)) {
      throw new ValidationException($i18n->t("The “{0}” text field contains illegal low ASCII characters.", [ $this->label ]));
    }

    $this->value = $this->collapseWhitespace(Normalizer::normalize($this->value));
    return $this;
  }

}
