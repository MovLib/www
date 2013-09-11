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
namespace MovLib\Presentation\FormElement;

use \MovLib\Data\User;
use \MovLib\Exception\ValidatorException;

/**
 * HTML input type email form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmail extends \MovLib\Presentation\FormElement\Input {

  /**
   * Instantiate new HTML input form element of type email.
   *
   * @param array $attributes [optional]
   *   Additional attributes that should be set on this form element, defaults to no additional attributes.
   * @param string $id [optional]
   *   The global unique identifier of this form element.
   * @param string $defaultValue [optional]
   *   The default value of this form element, defaults to empty string.
   */
  public function __construct(array $attributes = null, $id = "email", $defaultValue = "") {
    parent::__construct($id, $attributes, $defaultValue);
    // @todo Right now all email related inputs are directly related to users, therefor we can set this within this
    //       class. This might change in the future if other email input fields are required. Be sure to update the
    //       validation method, because it checks for the length that is set here!
    $this->attributes["max-length"] = User::MAX_LENGTH_EMAIL;
    $this->attributes["type"] = "email";
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    if (!$this->label) {
      $this->label = $i18n->t("Email Address");
    }
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your email address");
    }
    if (!isset($this->attributes["title"])) {
      $this->attributes["title"] = $i18n->t("Please enter your email address in this field.");
    }
    return parent::__toString();
  }

  /**
   * Validates the user submitted email address.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\ValidatorException
   */
  public function validate() {
    global $i18n;
    $errors = null;

    // Use PHP's built-in validation function.
    if (filter_var($_POST[$this->id], FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
      $errors[] = $i18n->t("The email address {0} doesn’t appear to be valid.", [ $this->placeholder($_POST[$this->id]) ]);
    }
    // If the syntax is valid, check if we actually can send emails to the given address.
    else {
      $host = substr($_POST[$this->id], strpos($_POST[$this->id], "@") + 1);
      // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
      // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
      // IPv6 hosts and the MX record is for mail servers only.
      if (strpos($host, ".") === false || (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false)) {
        $errors[] = $i18n->t("The email address {0} doesn’t appear to be valid.", [ $this->placeholder($_POST[$this->id]) ]);
      }
    }

    // No need for multi-byte functions, utf-8 is not allowed in emails.
    if (strlen($_POST[$this->id]) > $this->attributes["max-length"]) {
      $errors[] = $i18n->t("The email address {0} is too long: it must be {1,number,integer} or less.", [ $this->placeholder($_POST[$this->id]), $this->attributes["max-length"] ]);
    }

    // Always throw aggregated error messages, to ensure that the user knows about all problems regarding this field.
    if ($errors) {
      throw new ValidatorException(implode("<br>", $errors));
    }

    $this->value = $_POST[$this->id];
    return $this;
  }

}
