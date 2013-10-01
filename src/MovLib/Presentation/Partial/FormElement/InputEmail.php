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
 * HTML input type email form element.
 *
 * @link http://www.rfc-editor.org/errata_search.php?rfc=3696&eid=1690
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmail extends \MovLib\Presentation\Partial\FormElement\AbstractInput {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The usage of a magic constant at this point is absolutely okay, because 254 is the official upper limit for any
   * valid email address. This restriction is layed down in RFC 2821 on the length for MAIL and RCPT commands.
   *
   * @var int
   */
  const MAX_LENGTH = 254;

  /**
   * Email address regular expression pattern for validation.
   *
   * The regular expression isn't as sophisticated as PHP's email validator, it's only a fast check for a valid email
   * address. Any email addresss will be validated from the server and fancy stuff shall simply go throught he regular
   * expression and PHP shall take care of it.
   *
   * @var string
   */
  const PATTERN = "^[a-zA-Z0-9!#$%&'*+-/=?^_`{|}~\.\"(),:;<>@[\]\\\\ ]+@[a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*[a-z0-9_]\.[a-z]{2,6}$";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    $this->attributes["aria-required"] = "true";
    $this->attributes["id"]            = $this->id;
    $this->attributes["maxlength"]     = self::MAX_LENGTH;
    $this->attributes["name"]          = $this->id;
    $this->attributes["pattern"]       = self::PATTERN;
    $this->attributes["title"]         = $i18n->t("An email address in the format [local]@[host].[tld] with a maximum of {0} characters", [ self::MAX_LENGTH ]);
    $this->attributes["type"]          = "email";
    $this->attributes[]                = "required";
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your email address");
    }
    if (!$this->label) {
      $this->label = $i18n->t("Email Address");
    }
    return parent::__toString();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      throw new ValidationException($i18n->t("The highlighted email field is mandatory."));
    }

    // No need for multi-byte functions, utf-8 is not allowed in emails.
    if (strlen($this->value) > self::MAX_LENGTH) {
      throw new ValidationException($i18n->t("The email address {0} is too long: it must be {1,number,integer} or less.", [ $this->placeholder($this->value), self::MAX_LENGTH ]));
    }

    // Use PHP's built-in validation function.
    if (filter_var($this->value, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
      throw new ValidationException($i18n->t("The email address {0} doesn’t appear to be valid.", [ $this->placeholder($this->value) ]));
    }

    // If the syntax is valid, check if we actually can send emails to the given address.
    $host = substr($this->value, strrpos($this->value, "@") + 1);

    // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
    // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
    // IPv6 hosts and the MX record is for mail servers only.
    if (strpos($host, ".") === false || (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false)) {
      throw new ValidationException($i18n->t("The email address {0} doesn’t appear to be valid.", [ $this->placeholder($this->value) ]));
    }

    return $this;
  }

}
