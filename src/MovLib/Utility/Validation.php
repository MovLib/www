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
namespace MovLib\Utility;

use \MovLib\Model\UserModel;

/**
 * Utility class to ease validation of user input.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Validation {

  /**
   * Validate given IP address.
   *
   * @param string $ipAddress
   *   The IP address to validate.
   * @return boolean|string
   *   <code>FALSE</code> if the IP address is not valid or empty, otherwise the IP address is returned.
   */
  public static function ipAddress($ipAddress) {
    if (($ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP)) === false || empty($ipAddress)) {
      return false;
    }
    return $ipAddress;
  }

  /**
   * Get IP address from user input.
   *
   * @param string $name
   *   The variable name within the <var>$type</var>-array.
   * @param int $type
   *   [Optional] One of the PHP <var>INPUT_*</var> constants, defaults to <var>INPUT_POST</var>.
   * @return boolean|string
   *   <code>FALSE</code> if the IP address is not valid or empty, otherwise the IP address is returned.
   */
  public static function inputIpAddress($name, $type = INPUT_POST) {
    if (($ipAddress = filter_input($type, $name, FILTER_VALIDATE_IP)) === false || empty($ipAddress)) {
      return false;
    }
    return $ipAddress;
  }

  /**
   * Sanitize given string.
   *
   * @param string $string
   *   The string to sanitize.
   * @return boolean|string
   *   <code>FALSE</code> if the filter failed or the string was empty after the filter was applied, otherwise the
   *   string is returned.
   */
  public static function string($string) {
    if (($string = filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)) === false || empty($string)) {
      return false;
    }
    return $string;
  }

  /**
   * Get string from user input.
   *
   * Always use this method to get a string that was submitted from a user via any form of submission. Basic
   * sanitization is performed in the form that low ASCII characters are automaticall stripped from the input. This
   * means that this method will remove any newline characters (<code>\n</code>) from the input. Empty strings are
   * treated as errors.
   *
   * @param string $name
   *   The variable name within the <code>$type</code>-array.
   * @param int $type
   *   [Optional] One of the PHP <var>INPUT_*</var> constants, defaults to <var>INPUT_POST</var>.
   * @return boolean|string
   *   <code>FALSE</code> if the filter failed or the string was empty after the filter was applied, otherwise the
   *   string is returned.
   */
  public static function inputString($name, $type = INPUT_POST) {
    if (($string = filter_input($type, $name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)) === false || empty($string)) {
      return false;
    }
    return $string;
  }

  /**
   * Validate given mail.
   *
   * @param string $mail
   *   The mail to validate.
   * @return boolean|string
   *   <code>FALSE</code> if the mail is not valid or empty, otherwise the mail is returned.
   */
  public static function mail($mail) {
    if (($mail = filter_var($mail, FILTER_VALIDATE_EMAIL)) === false || empty($mail)) {
      return false;
    }
    return $mail;
  }

  /**
   * Get mail from user input.
   *
   * @param string $name
   *   The variable name within the <var>$type</var>-array.
   * @param int $type
   *   [Optional] One of the PHP <var>INPUT_*</var> constants, defaults to <var>INPUT_POST</var>.
   * @return boolean|string
   *   <code>FALSE</code> if the mail is not valid or empty, otherwise the mail is returned.
   */
  public static function inputMail($name, $type = INPUT_POST) {
    if (($mail = filter_input($type, $name, FILTER_VALIDATE_EMAIL)) === false || empty($mail)) {
      return false;
    }
    return $mail;
  }

  /**
   * Validate the given URL.
   *
   * This method will check if the URL is really reachable via the web.
   *
   * @param string $url
   *   The URL to validate.
   * @return boolean|string
   *   <code>FALSE</code> if the URL is not valid or empty, otherwise the URL is returned.
   */
  public static function url($url) {
    if (empty($url)) {
      return false;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_HEADER         => 1, // Get the header
      CURLOPT_NOBODY         => 1, // Only get the header
      CURLOPT_RETURNTRANSFER => 1, // Get the response as string (no echo)
      CURLOPT_FRESH_CONNECT  => 1, // Do not use cached version
    ]);
    if (!curl_exec($ch)) {
      return false;
    }
    return $url;
  }

  /**
   * Get URL from user input.
   *
   * @param string $name
   *   The variable name within the <var>$type</var>-array.
   * @param int $type
   *   [Optional] One of the PHP <var>INPUT_*</var> constants, defaults to <var>INPUT_POST</var>.
   * @return boolean|string
   *   <code>FALSE</code> if the URL is not valid or empty, otherwise the URL is returned.
   */
  public static function inputUrl($name, $type = INPUT_POST) {
    if (($url = filter_input($type, $name, FILTER_VALIDATE_URL)) === false) {
      return false;
    }
    return Validation::url($url);
  }

  /**
   * Validate the given username.
   *
   * <b>Usage example:</b>
   * <pre>if ($error = UserModel::validateName($name)) {
   *   throw new Exception($error);
   * }</pre>
   *
   * @link http://api.drupal.org/api/drupal/core!modules!user!user.module/function/user_validate_name/8
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @param string $name
   *   The username to validate.
   * @return null|string
   *   A translated string containing the error message if something is wrong with the given name, otherwise <code>NULL</code>.
   */
  public static function username($name) {
    global $i18n;
    if (empty($name)) {
      return $i18n->t("You must enter a username.");
    }
    if (substr($name, 0, 1) === " ") {
      return $i18n->t("The username cannot begin with a space.");
    }
    if (substr($name, -1) === " ") {
      return $i18n->t("The username cannot end with a space.");
    }
    if (strpos($name, "  ") !== false) {
      return $i18n->t("The username cannot contain multiple spaces in a row.");
    }
    if (preg_match("/[^\x{80}-\x{F7} a-z0-9@_.\'-]/i", $name) === 1) {
      return $i18n->t("The username contains an illegal character.");
    }
    if (preg_match(
      "/[\x{80}-\x{A0}" .   // Non-printable ISO-8859-1 + NBSP
      "\x{AD}" .            // Soft-hyphen
      "\x{2000}-\x{200F}" . // Various space characters
      "\x{2028}-\x{202F}" . // Bidirectional text overrides
      "\x{205F}-\x{206F}" . // Various text hinting characters
      "\x{FEFF}" .          // Byte order mark
      "\x{FF01}-\x{FF60}" . // Full-width latin
      "\x{FFF9}-\x{FFFD}" . // Replacement characters
      "\x{0}-\x{1F}]/u",    // NULL byte and control characters
      $name
    ) === 1) {
      return $i18n->t("The username contains an illegal character.");
    }
    if (mb_strlen($name) > UserModel::NAME_MAX_LENGTH) {
      return $i18n->t("The username {0} is too long: it must be {1,number,integer} characters or less.", [ $name, UserModel::NAME_MAX_LENGTH ]);
    }
    // @todo The blacklist content must be translated along with the routes.
    $blacklist = json_decode(file_get_contents(__DIR__ . "/username-blacklist.json"));
    $c = count($blacklist);
    for ($i = 0; $i < $c; ++$i) {
      if ($name === $blacklist[$i]) {
        return $i18n->t("The username contains a system reserved word, please choose another one.");
      }
    }
  }

}
