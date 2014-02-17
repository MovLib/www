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

/**
 * Input text form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputText extends \MovLib\Presentation\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for invalid UTF-8 error message.
   *
   * @var integer
   */
  const ERROR_UNICODE = 1;

  /**
   * Error code for low ASCII characters error message.
   *
   * @var integer
   */
  const ERROR_LOW_ASCII = 2;

  /**
   * The form element's type.
   *
   * @var string
   */
  const TYPE = "text";


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted text.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $text
   *   The user submitted text to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid and sanitized text.
   */
  protected function validateValue($text, &$errors) {
    global $i18n;

    // Validate that the input string is valid UTF-8.
    if (preg_match("//u", $text) === false) {
      $errors[self::ERROR_UNICODE] = $i18n->t("The “{0}” field contains invalid UTF-8 characters.", [ $this->label ]);
    }

    // Let PHP validate the string again and strip any low special ASCII characters (e.g. NULL byte).
    if ($text != filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)) {
      $errors[self::ERROR_LOW_ASCII] = $i18n->t("The “{0}” field contains illegal low ASCII characters.", [ $this->label ]);
    }

    // Only attempt to sanitize the string further if we have no errors so far.
    if (!$errors) {
      // Double decode ANY encoded HTML entity.
      if (preg_match("/&.+;/", $text)) {
        $text = $this->htmlDecodeEntities($text);
        $text = $this->htmlDecodeEntities($text);
      }

      // Only encode characters with a special purpose in HTML (secure by default).
      $text = $this->htmlEncode($text);

      // Normalize the submitted text to Unicode's NFC form (as recommended by W3C).
      $text = \Normalizer::normalize($text);

      // Collapse all whitespace characters to single space.
      $text = $this->collapseWhitespace($text);
    }

    return $text;
  }

}
