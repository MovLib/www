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
   * Maximum length an email address can have.
   *
   * This length must be the same as it is defined in the database table. We redefine this here in order to validate the
   * length of the email address before attempting to insert it into our database. Be sure to count the strings length
   * with <code>mb_strlen()</code> because the length is defined per character and not per byte.
   *
   * We limit the length of an email address because we don't want to use BLOB fields in our database to store them. Any
   * “normal” email address should fit into this length.
   *
   * @var int
   */
  const MAIL_MAX_LENGTH = 254;

  /**
   * Instantiate new email address input form element.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param array $attributes
   *   Set additional or overwrite the defaults.
   * @param string $name [optional]
   *   The global identifier for this instance.
   * @param string $label [optional]
   *   The already translated human readable label.
   */
  public function __construct($attributes = null, $name = "mail", $label = null) {
    global $i18n;
    parent::__construct($name, $label ?: $i18n->t("email address"), $attributes);
    $this->attributes["maxlength"] = self::MAIL_MAX_LENGTH;
    $this->attributes["pattern"] = "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$";
    $this->attributes["type"] = "email";
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
    if ($this->required === true && empty($_POST[$this->id])) {
      throw new ValidatorException($i18n->t("The {0} cannot be empty.", [ $this->label ]));
    }
    if (mb_strlen($_POST[$this->id]) > self::MAIL_MAX_LENGTH) {
      throw new ValidatorException($i18n->t("The {0} is too long: it must be {1,number,integer} characters or less.", [ $this->label ]));
    }
    $filtered = filter_var($_POST[$this->id], FILTER_VALIDATE_EMAIL);
    if ($filtered === false || empty($filtered) || strcmp($filtered, $_POST[$this->id]) !== 0) {
      throw new ValidatorException($i18n->t("The {0} contains illegal characters.", [ $this->label ]));
    }
    $this->value = $filtered;
    return $this;
  }

}
