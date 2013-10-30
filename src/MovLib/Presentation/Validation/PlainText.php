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
namespace MovLib\Presentation\Validation;

use \MovLib\Exception\ValidationException;
use \Normalizer;

/**
 * Validate a string as plain text and UTF-8.
 *
 * @see \MovLib\Presentation\Validation\InterfaceValidation
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PlainText extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Exception code for invalid UTF-8.
   *
   * @var int
   */
  const E_INVALID_UTF8 = 1;

  /**
   * Exception code for illegal UTF-8 characters (NFC form).
   *
   * @var int
   */
  const E_ILLEGAL_UTF8 = 2;

  /**
   * Exception code for encoded HTML tags.
   *
   * @var int
   */
  const E_DOUBLE_ENCODED = 3;

  /**
   * Exception code for low ASCII characters.
   *
   * @var int
   */
  const E_LOW_ASCII = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The plain text string to validate.
   *
   * @var string
   */
  public $plainText;

  /**
   * Flag indicating whether to preserve line feeds for not.
   *
   * @var boolean
   */
  public $preserveLineFeeds = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new plain text validator.
   *
   * @param string $plainText [optional]
   *   The plain text string to validate.
   * @param boolean $preserveLineFeeds [optional]
   *   Flag controlling line feed handling.
   */
  public function __construct($plainText = null, $preserveLineFeeds = false) {
    $this->plainText         = $plainText;
    $this->preserveLineFeeds = $preserveLineFeeds;
  }

  /**
   * Get the plain text string.
   *
   * @return string
   *   The plain text string.
   */
  public function getPresentation() {
    return $this->plainText;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the plain text to validate.
   *
   * @param string $plainText
   *   The plain text to validate.
   */
  public function set($plainText) {
    $this->plainText = $plainText;
  }

  /**
   * Validate the plain text string.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The valid plain text string.
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n;

    // Validate UTF-8 encoding.
    if (preg_match("//u", $this->plainText) === false) {
      throw new ValidationException($i18n->t("The text contains invalid UTF-8 characters.", self::E_INVALID_UTF8));
    }

    // Validate NFC form.
    if ($this->plainText != Normalizer::normalize($this->plainText)) {
      throw new ValidationException($i18n->t("The text contains illegal UTF-8 characters (NFC form).", self::E_ILLEGAL_UTF8));
    }

    // Validate HTML injection.
    if ($this->plainText != html_entity_decode(html_entity_decode($this->plainText, ENT_QUOTES|ENT_HTML5), ENT_QUOTES|ENT_HTML5)) {
      throw new ValidationException($i18n->t("The text contains (double) encoded HTML tags.", self::E_DOUBLE_ENCODED));
    }

    if ($this->preserveLineFeeds === true) {
      $this->plainText = str_replace("\n", "<movLibLineFeed>", $this->normalizeLineFeeds($this->plainText));
    }

    // Validate low ASCII characters.
    if ($this->plainText != filter_var($this->plainText, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_REQUIRE_SCALAR)) {
      throw new ValidationException($i18n->t("The text contains illegal low ASCII characters.", self::E_LOW_ASCII));
    }

    if ($this->preserveLineFeeds === true) {
      $this->plainText = str_replace("<movLibLineFeed>", "\n", $this->plainText);
    }

    // Collapse all white space characters and trim the string at beginning and end (no error for this).
    return trim($this->collapseWhitespace($this->plainText));
  }

}
