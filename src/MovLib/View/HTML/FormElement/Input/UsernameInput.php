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
use \MovLib\Model\BaseModel;
use \MovLib\Utility\Validator;
use \MovLib\View\HTML\FormElement\Input\TextInput;

/**
 * Create new username input element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UsernameInput extends TextInput {

  /**
   * Instantiate new username input form element.
   *
   * @todo Include regular expression (pattern) to help browsers validate this field right away.
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
  public function __construct($attributes = [], $name = "name", $label = null, $defaultValue = "") {
    global $i18n;
    parent::__construct($name, $label ?: $i18n->t("username"), array_merge([
      "maxlength" => $GLOBALS["conf"]["max_length_username"],
      "type"      => "text",
    ], $attributes), $defaultValue);
  }

  /**
   * Validate the user submitted username.
   *
   * @todo We should check that the username is not a valid email address.
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    global $i18n;
    if (substr($_POST[$this->id], 0, 1) === " ") {
      throw new ValidatorException($i18n->t("The {0} cannot {1} with a space.", [ $this->label, $i18n->t("begin") ]));
    }
    if (substr($_POST[$this->id], -1) === " ") {
      throw new ValidatorException($i18n->t("The {0} cannot {1} with a space.", [ $this->label, $i18n->t("end") ]));
    }
    if (strpos($_POST[$this->id], "  ") !== false) {
      throw new ValidatorException($i18n->t("The {0} cannot contain multiple spaces in a row.", [ $this->label ]));
    }
    if (($filtered = Validator::string($_POST[$this->id])) === false) {
      throw new ValidatorException($i18n->t("The {0} contains one or more illegal character.", [ $this->label ]));
    }
    if (mb_strlen($filtered) > $GLOBALS["conf"]["max_length_username"]) {
      throw new ValidatorException($i18n->t("The {0} is too long: it must be {1,number,integer} characters or less.", [ $this->label, $GLOBALS["conf"]["max_length_username"] ]));
    }
    // @todo The blacklist's content must be translated along with the routes.
    $blacklist = json_decode(file_get_contents("{$_SERVER["HOME"]}/conf/username-blacklist.json"));
    $c = count($blacklist);
    for ($i = 0; $i < $c; ++$i) {
      if (strcmp($blacklist[$i], mb_strtolower($filtered)) === 0) {
        throw new ValidatorException("The username is a system reserved word, please choose another one.");
      }
    }
    if (!empty((new BaseModel())->select("SELECT `user_id` FROM `users` WHERE `name` = ? LIMIT 1", "s", [ $filtered ]))) {
      throw new ValidatorException("The {0} {1} is already taken, please choose another one.", [ $this->label, String::placeholder($filtered) ]);
    }
    $this->value = $filtered;
    return $this;
  }

}
