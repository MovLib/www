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

use \Normalizer;

/**
 * HTML input type text form element.
 *
 * In contrast to the default input element, this is specialized for plain text input. The user submitted string is
 * sanitized. No validation!
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputText extends \MovLib\Presentation\Partial\FormElement\Input {

  /**
   * Instantiate new HTML input form element of type text.
   *
   * @param string $id
   *   The global unique identifier of this form element.
   * @param array $attributes [optional]
   *   Additional attributes that should be set on this form element, defaults to no additional attributes.
   * @param string $defaultValue [optional]
   *   The default value of this form element, defaults to empty string.
   */
  public function __construct($id, array $attributes = null, $defaultValue = "") {
    parent::__construct($id, $attributes, $defaultValue);
    $this->attributes["type"] = "text";
  }

  /**
   * Sanitize the input string.
   *
   * @return this
   */
  public function validate() {
    // Normalize utf-8 to NFC.
    $normalized = Normalizer::normalize($_POST[$this->id]);
    // Decode possible entities twice.
    $decoded = html_entity_decode(html_entity_decode($normalized, ENT_QUOTES|ENT_HTML5), ENT_QUOTES|ENT_HTML5);
    // Strip all possible HTML tags, but allow <> as it's harmless and no problem.
    $stripped = strip_tags($decoded, "<>");
    // Collapse all kinds of whitespace characters to a single whitespace.
    $collapsed = preg_replace("/\s+/m", " ", $stripped);
    // Strip low ASCII characters (including line feeds).
    $filtered = filter_var($collapsed, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    // Finally remove whitespaces at beginning and end and we're done.
    return trim($filtered);
  }

}
