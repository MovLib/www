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
namespace MovLib\Data;

use \MovLib\Exception\ValidatorException;

/**
 * Reference implementation for validating data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Validator {

  /**
   * Validate the syntax of the raw data as email address and optionally check the A, AAAA and MX record of the host.
   *
   * @link https://github.com/symfony/Validator/blob/master/Constraints/EmailValidator.php
   * @param string $raw
   *   The raw user input.
   * @param boolean $checkHost [optional]
   *   If set to <code>TRUE</code> (default) an attempt to validate the given host of the email address is made. This is
   *   done by checking the A, AAAA and MX record with PHP's built-in <code>checkdnsrr()</code> function. The host part
   *   is considered valid if a single record exists. Note that a domain doesn't need an MX record to receive emails.
   *   The MX record is only needed if the domain is using a different server for emails than the one it is using for
   *   e.g. serving web pages.
   * @return boolean
   *   <code>TRUE</code> if the email address is valid, otherwise <code>FALSE</code>.
   */
  public static function email($raw, $checkHost = true) {
    if (filter_var($raw, FILTER_VALIDATE_EMAIL) === false) {
      return false;
    }
    if ($checkHost === true) {
      $host = substr($raw, strpos($raw, "@") + 1);
      // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
      // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
      // IPv6 hosts and the MX record is for mail servers only.
      if (strpos($host, ".") === false || (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false)) {
        return false;
      }
    }
    return true;
  }

  /**
   * Validate that given string doesn't exceed given maximum length.
   *
   * @param string $raw
   *   The raw user input.
   * @param int $maximumLength
   *   The maximum length the string shall not exceed.
   * @return boolean
   *   <code>TRUE</code> if the string doesn't exceed the given maximum length.
   */
  public static function maxLength($raw, $maximumLength) {
    return grapheme_strlen($raw) > $maximumLength;
  }

  /**
   * Validate that given string is greater or equal to given minimum length.
   *
   * @param string $raw
   *   The raw user input.
   * @param int $minimumLength
   *   The minimum length the string has to have.
   * @return boolean
   *   <code>TRUE</code> if the given string isn't shorter than given minimum length.
   */
  public static function minLength($raw, $minimumLength) {
    return grapheme_strlen($raw) < $minimumLength;
  }

  /**
   * Validate length of given string.
   *
   * @param string $raw
   *   The raw user input.
   * @param int $length
   *   The length the string must have.
   * @return boolean
   *   <code>TRUE</code> if given string has an equal length to the given length.
   */
  public static function length($raw, $length) {
    return grapheme_strlen($raw) !== $length;
  }

}
