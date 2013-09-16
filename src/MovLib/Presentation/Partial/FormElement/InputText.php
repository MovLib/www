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
use \MovLib\Exception\ValidatorException;

/**
 * HTML input type text form element.
 *
 * In contrast to the default input element, this is specialized for plain text input. The user submitted string is
 * sanitized. No validation!
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputText extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {
  use \MovLib\Presentation\Partial\FormElement\TraitReadonly;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form element's value.
   *
   * @var null|string
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type text.
   *
   * @param string $id
   *   The form element's global identifier.
   * @param string $label
   *   The form element's label content.
   * @param string $value [optional]
   *   The form element's default value.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id, $label, $value = null, array $attributes = null, array $labelAttributes = null) {
    parent::__construct($id, $attributes, $label, $labelAttributes);
    $this->attributes["type"] = "text";
    $this->attributes["role"] = "textbox";
    $this->value = isset($_POST[$this->id]) ? $_POST[$this->id] : $value;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    $this->attributes["value"] = $this->value;
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    // Validate UTF-8 and normalize to NFC.
    if (preg_match("//u", $this->value) === false || !($this->value = Normalizer::normalize($this->value))) {
      throw new ValidatorException($i18n->t("The highlighted element contains illegal characters."));
    }

    // Decode possible entities twice.
    $this->value = html_entity_decode(html_entity_decode($this->value, ENT_QUOTES), ENT_QUOTES|ENT_HTML5);

    // Strip all possible HTML tags, but allow <> as it's harmless and no problem.
    $this->value = strip_tags($this->value, "<>");

    // Collapse all kinds of whitespace characters to a single whitespace.
    if (!($this->value = preg_replace("/\s+/m", " ", $this->value))) {
      throw new ValidatorException($i18n->t("The highlighted element contains illegal characters."));
    }

    // Strip low ASCII characters (including line feeds).
    if (($this->value = filter_var($this->value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_REQUIRE_SCALAR)) === false) {
      throw new ValidatorException($i18n->t("The highlighted element contains illegal characters."));
    }

    // Finally remove whitespaces at beginning and end and we're done.
    $this->value = trim($this->value);
    return $this;
  }

}
