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
class InputDate extends \MovLib\Presentation\Partial\FormElement\InputText {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Exception code for invalid format.
   *
   * @var int
   */
  const E_FORMAT = 1;

  /**
   * Exception code for invalid date.
   *
   * @var int
   */
  const E_INVALID = 2;

  /**
   * Exception code for maximum.
   *
   * @var int
   */
  const E_MAX = 3;

  /**
   * Exception code for minimum.
   *
   * @var int
   */
  const E_MIN = 4;

  /**
   * Full date formatting according to RFC3339.
   *
   * This constant can be used to format the default value, or the min and max attribute. The resulting formatted string
   * will be in the format <code>"yyyy-mm-dd"</code> (e.g. <code>date(InputDate::RFC3339, time())</code>
   * becomse <code>2013-06-27</code>).
   *
   * @link http://tools.ietf.org/html/rfc3339#section-5.6
   * @var string
   */
  const RFC3339 = "Y-m-d";


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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type date.
   *
   * @param string $id
   *   The form element's global identifier.
   * @param string $label
   *   The form element's label content.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param string $value [optional]
   *   The form element's default value.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id, $label, array $attributes = null, $value = null, array $labelAttributes = null) {
    parent::__construct($id, $label, $value, $attributes, $labelAttributes);
    $this->attributes["type"] = "date";
    if (!empty($this->attributes["max"])) {
      $this->max = $this->attributes["max"];
      $this->attributes["max"] = date(self::RFC3339, $this->max);
    }
    if (!empty($this->attributes["min"])) {
      $this->min = $this->attributes["min"];
      $this->attributes["max"] = date(self::RFC3339, $this->min);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $this->value) === false) {
      throw new ValidationException($i18n->t("The submitted date {0} has an invalid format, the format must be {1}yyyy-mm-dd{2}.", [
        $this->placeholder($this->value), "<code>", "</code>"
      ]), self::E_FORMAT);
    }

    list($year, $month, $day) = explode("-", $this->value);
    if (checkdate($month, $day, $year) === false) {
      throw new ValidationException($i18n->t("The submitted date {0} is not valid.", [ $this->placeholder($this->value) ]), self::E_INVALID);
    }

    if ($this->max || $this->min) {
      $date = strtotime($this->value);

      if ($this->max && $date > $this->max) {
        throw new ValidationException($i18n->t("The submitted date {0} must not be greater than {1}.", [
          $this->placeholder($this->value),
          $this->attributes["max"]
        ]), self::E_MAX);
      }

      if ($this->min && $date < $this->min) {
        throw new ValidationException($i18n->t("The submitted date {0} must not be less than {1}.", [
          $this->placeholder($this->value),
          $this->attributes["min"]
        ]), self::E_MIN);
      }
    }

    return $this;
  }

}
