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
namespace MovLib\Partial\FormElement;

/**
 * Input email form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class InputEmail extends \MovLib\Partial\FormElement\AbstractInput {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InputEmail";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for too long email address error message.
   *
   * @var integer
   */
  const ERROR_LENGTH = 1;

  /**
   * Error code for invalid email syntax error message.
   *
   * @var integer
   */
  const ERROR_SYNTAX = 2;

  /**
   * Error code for no DNS entry error message.
   *
   * @var integer
   */
  const ERROR_DNS = 3;

  /**
   * Maximum email address length.
   *
   * @link http://stackoverflow.com/a/574698/1251219
   * @var integer
   */
  const MAX_LENGTH = 254;

  /**
   * Regular expression pattern for client side email address validation.
   *
   * @link http://fightingforalostcause.net/content/misc/2006/compare-email-regex.php
   * @var string
   */
  const PATTERN = "^[a-zA-Z0-9!#$%&'*+-/=?^_`{|}~\.\"(),:;<>@[\]\\\\ ]+@[a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*[a-z0-9_]\.[a-z]{2,6}$";

  /**
   * The form element's type.
   *
   * @var string
   */
  const TYPE = "email";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the input email form element.
   *
   * @return string
   *   The input email form element.
   */
  public function __toString() {
    $this->attributes["maxlength"] = self::MAX_LENGTH;
    $this->attributes["pattern"]   = self::PATTERN;
    $this->attributes["title"]     = $this->intl->t("An email address in the format [local]@[host].[tld] with a maximum of {0,number,integer} characters", [ self::MAX_LENGTH ]);
    return parent::__toString();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted email address.
   *
   * @param string $email
   *   The user submitted email to validate.
   * @param null|array $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid email address.
   */
  protected function validateValue($email, &$errors) {
    // No need for multi-byte functions, utf-8 is not allowed in emails.
    if (strlen($email) > self::MAX_LENGTH) {
      $errors[self::ERROR_LENGTH] = $this->intl->t("The email address is too long: it must be {0,number,integer} or less.", [ self::MAX_LENGTH ]);
    }

    // Use PHP's built-in validation function.
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
      $errors[self::ERROR_SYNTAX] = $this->intl->t("The email address is invalid.");
    }

    // Only attempt to validate the reachability if the email address is valid.
    if (!$errors) {
      // If the syntax is valid, check if we actually can send emails to the given address. Appending a dot makes sure
      // that the checkdnsrr() calls are always accurate. Unit tests have shown that the function returns true for an A
      // check on the domain "foo.bar", which is definitely not correct. No test failed with the dot appended to the
      // host part.
      $host = substr($email, strrpos($email, "@") + 1) . ".";

      // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
      // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
      // IPv6 hosts and the MX record is for mail servers only.
      if (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false) {
        $errors[self::ERROR_DNS] = $this->intl->t("The email address is unreachable.");
      }
    }

    return $email;
  }

}
