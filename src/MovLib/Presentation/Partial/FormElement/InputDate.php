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

use \DateTime;
use \DateTimeZone;
use \IntlDateFormatter;
use \MovLib\Exception\ValidationException;

/**
 * HTML input type date form element.
 *
 * The input date form element has it's very own validation method and doesn't utilize a validation class. The
 * validation process can't be changed. The rules regarding the format of the various attributes are fixed by the W3C
 * and this form element will only accept exaclty that.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputDate extends \MovLib\Presentation\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The maximum date as timestamp.
   *
   * @var int
   */
  protected $max;

  /**
   * The minimum date as timestamp.
   *
   * @var int
   */
  protected $min;

  /**
   * Contains <code>$this->value</code> as timestamp if it's a valid date.
   *
   * You shouldn't use this timestamp before a call to <code>$this->validate()</code> if you received the form via POST!
   *
   * @var boolean|int
   */
  public $timestamp = false;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type date.
   *
   * @param string $id
   *   The date's global unique identifier.
   * @param string $label
   *   The date's translated label text.
   * @param array $attributes [optional]
   *   The date's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"type"</code> is set to <code>"date"</code></li>
   *   </ul>
   *   You <b>should not</b> override any of the default attributes.
   */
  public function __construct($id, $label, array $attributes = null) {
    parent::__construct($id, $label, $attributes);
    $this->attributes["data-format"] = "Y-m-d";
    $this->attributes["type"]        = "date";
    if (isset($this->attributes["max"])) {
      $this->max = $this->attributes["max"];
      $this->attributes["max"] = date($this->attributes["data-format"], $this->attributes["max"]);
    }
    if (isset($this->attributes["min"])) {
      $this->min = $this->attributes["min"];
      $this->attributes["min"] = date($this->attributes["data-format"], $this->attributes["min"]);
    }
    if (isset($this->value)) {
      $this->timestamp = strtotime($this->value);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Normalize the date from the given <var>$timeZoneId</var> to the default time zone from PHP's global INI file.
   *
   * @param string $timeZoneId
   *   A valid PHP time zone ID.
   * @return this
   */
  public function normalizeDate($timeZoneId) {
    $defaultTimeZoneId = ini_get("date.timezone");
    if ($timeZoneId != $defaultTimeZoneId) {
      $this->timestamp += (new DateTimeZone($defaultTimeZoneId))
        ->getOffset(DateTime::createFromFormat("!Y-m-d", $this->value, new DateTimeZone($timeZoneId)));
      $this->value = date($this->attributes["data-format"], $this->timestamp);
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n, $session;

    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” date is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    $dateInfo = date_parse_from_format("Y-m-d", $this->value);
    if ($dateInfo["warning_count"] !== 0 || $dateInfo["error_count"] !== 0) {
      throw new ValidationException($i18n->t("The “{0}” date is invalid.", [ $this->label ]));
    }

    if ($this->max || $this->min) {
      if ($this->max && $this->timestamp > $this->max) {
        throw new ValidationException($i18n->t("The date {0} must not be greater than {1}.", [
          $i18n->formatDate($this->timestamp, $session->userTimeZoneId, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE),
          $i18n->formatDate($this->max, $session->userTimeZoneId, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE)
        ]));
      }

      if ($this->min && $this->timestamp < $this->min) {
        throw new ValidationException($i18n->t("The date {0} must not be less than {1}.", [
          $i18n->formatDate($this->timestamp, $session->userTimeZoneId, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE),
          $i18n->formatDate($this->min, $session->userTimeZoneId, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE)
        ]));
      }
    }

    return $this;
  }

}
