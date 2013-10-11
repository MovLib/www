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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
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
   * @var boolean|int
   */
  public $timestamp = false;

  /**
   * The time zone ID, defaults to the time zone ID from the session.
   *
   * @var string
   */
  public $timeZoneId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type date.
   *
   * @global \MovLib\Data\Session $session
   * @param string $id
   *   The date's global unique identifier.
   * @param string $label
   *   The date's translated label text.
   * @param array $attributes [optional]
   *   The date's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex (with <code>getTabindex()</code>)</li>
   *     <li><code>"type"</code> is set to <code>"date"</code></li>
   *   </ul>
   *   You <b>should not</b> override any of the default attributes.
   * @param string $timeZoneId [optional]
   *   The time zone ID of this date input, defaults to the session's user time zone ID.
   */
  public function __construct($id, $label, array $attributes = null, $timeZoneId = null) {
    global $session;
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
    $this->timeZoneId = $timeZoneId ?: $session->userTimeZoneId;
    if (isset($this->value)) {
      $date = DateTime::createFromFormat("!Y-m-d", $this->value, new DateTimeZone($this->timeZoneId));
      if ($date !== false) {
        $defaultTimeZoneId = ini_get("date.timezone");
        if ($this->timeZoneId != $defaultTimeZoneId) {
          $date->setTimezone(new DateTimeZone($defaultTimeZoneId));
        }
        $this->timestamp = $date->getTimestamp();
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n, $session;

    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” date is mandatory.", [ $this->label ]), self::E_MANDATORY);
      }
      return $this;
    }

    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $this->value) == false) {
      throw new ValidationException($i18n->t("The “{0}” date has an invalid format, the format must be {1}yyyy-mm-dd{2}.", [
        $this->label, "<code>", "</code>"
      ]));
    }

    list($year, $month, $day) = explode("-", $this->value);
    if (checkdate($month, $day, $year) === false) {
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
