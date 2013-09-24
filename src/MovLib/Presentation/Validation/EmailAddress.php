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
namespace MovLib\Presentation\Validation;

use \MovLib\Exception\ValidationException;

/**
 * Validate an email address.
 *
 * @see \MovLib\Presentation\Validation\InterfaceValidation
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class EmailAddress extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Exception code for too long emails.
   *
   * @var int
   */
  const E_LENGTH = 1;

  /**
   * Exception code for invalid syntax.
   *
   * @var int
   */
  const E_SYNTAX = 2;

  /**
   * Exception code for no DNS entry.
   *
   * @var int
   */
  const E_DNS = 3;

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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email address to validate.
   *
   * @var string
   */
  public $email;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new email address validator.
   *
   * @param string $email [optional]
   *   The email address to validate.
   */
  public function __construct($email = null) {
    $this->email = $email;
  }

  /**
   * Get the email address.
   *
   * @return string
   *   The email address.
   */
  public function __toString() {
    return $this->email;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the email address to validate.
   *
   * @param string $email
   *   The email address to validate.
   */
  public function set($email) {
    $this->email = $email;
  }

  /**
   * Validate the email address.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The valid email address.
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n;

    // No need for multi-byte functions, utf-8 is not allowed in emails.
    if (strlen($this->email) > self::MAX_LENGTH) {
      throw new ValidationException($i18n->t("The email address {0} is too long: it must be {1,number,integer} or less.", [
        $this->placeholder($this->email),
        self::MAX_LENGTH,
      ]), self::E_LENGTH);
    }

    // Use PHP's built-in validation function.
    if (filter_var($this->email, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
      throw new ValidationException($i18n->t("The email address {0} doesn’t appear to be valid.", [
        $this->placeholder($this->email),
      ]), self::E_SYNTAX);
    }

    // If the syntax is valid, check if we actually can send emails to the given address.
    $host = substr($this->email, strrpos($this->email, "@") + 1);

    // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
    // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
    // IPv6 hosts and the MX record is for mail servers only.
    if (strpos($host, ".") === false || (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false)) {
      throw new ValidationException($i18n->t("The email address {0} doesn’t appear to be valid.", [
        $this->placeholder($this->email),
      ]), self::E_DNS);
    }

    return $this->email;
  }

}
