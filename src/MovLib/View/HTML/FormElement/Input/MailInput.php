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
namespace MovLib\View\HTML\FormElement\Input;

use \MovLib\Exception\ValidatorException;
use \MovLib\View\HTML\FormElement\Input\TextInput;

/**
 * Create new email address input element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MailInput extends TextInput {

  /**
   * Instantiate new email address input form element.
   *
   * @todo Is the pattern to restrictive, or is our validate to nice?
   * @global \MovLib\Model\I18nModel $i18n
   * @param array $attributes
   *   Set additional or overwrite the defaults.
   * @param string $name [optional]
   *   The global identifier for this instance.
   * @param string $label [optional]
   *   The already translated human readable label.
   * @param string $defaultValue [optional]
   *   The default value of this form element. This will be used if the element is not required and no value was
   *   submitted by the user (e.g. GMT if the user should choose a timezone).
   */
  public function __construct($attributes = [], $name = "mail", $label = null, $defaultValue = "") {
    global $i18n;
    parent::__construct($name, $label ?: $i18n->t("email address"), array_merge([
      "maxlength" => $GLOBALS["conf"]["max_length_mail"],
//      "pattern"   => "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$",
//      "type"      => "email",
    ], $attributes), $defaultValue);
  }

  /**
   * Validate the user submitted email address.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    global $i18n;
    if (mb_strlen($_POST[$this->id]) > $GLOBALS["conf"]["max_length_mail"]) {
      throw new ValidatorException($i18n->t("The {0} is too long: it must be {1,number,integer} characters or less.", [ $this->label, $GLOBALS["conf"]["max_length_mail"] ]));
    }
    $filtered = filter_var($_POST[$this->id], FILTER_VALIDATE_EMAIL);
    if ($filtered === false || empty($filtered)) {
      throw new ValidatorException($i18n->t("The {0} does not appear to be valid.", [ $this->label ]));
    }
    if (strcmp($filtered, $_POST[$this->id]) !== 0) {
      throw new ValidatorException($i18n->t("The {0} contains illegal characters.", [ $this->label ]));
    }
    $this->value = $filtered;
    return $this;
  }

}
