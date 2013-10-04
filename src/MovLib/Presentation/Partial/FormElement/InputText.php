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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputText extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * @inheritdoc
   */
  protected $attributes = [
    "type" => "text",
  ];

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    // Validate UTF-8 encoding.
    if (preg_match("//u", $this->value) === false) {
      throw new ValidationException($i18n->t("The text contains invalid UTF-8 characters."));
    }

    // Validate NFC form.
    if ($this->value != Normalizer::normalize($this->value)) {
      throw new ValidationException($i18n->t("The text contains illegal UTF-8 characters (NFC form)."));
    }

    // Validate low ASCII characters.
    if ($this->value != filter_var($this->value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_REQUIRE_SCALAR)) {
      throw new ValidationException($i18n->t("The text contains illegal low ASCII characters."));
    }

    // Collapse all white space characters and trim the string at beginning and end (no error for this).
    $this->value = trim($this->collapseWhitespace($this->value));

    return $this;
  }

}
