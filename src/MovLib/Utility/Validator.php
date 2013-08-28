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

use \IntlException;
use \MovLib\Exception\ValidatorException;
use \MovLib\Model\UserModel;
use \MovLib\Utility\String;
use \NumberFormatter;

/**
 * Utility class to ease validation of user input.
 *
 * You should always use the utility methods provided in this class to validate any user input or data you don't trust.
 * Each method will return the content of the variable with the correct data type set. This ensures that you can always
 * use type save comparison operators (e.g. <code>===</code>). Correct data validation is a tedious task, do not try
 * to validate anything with a custom method.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Validator {


  // ------------------------------------------------------------------------------------------------------------------- Helper


  /**
   * Call validation method from <code>Validator::input*</code> methods.
   *
   * @param string $fn
   *   The name of the function to call.
   * @param string $name
   *   The name of the offset within the <var>INPUT_*</var> array.
   * @param array $options
   *   Array with user supplied options.
   * @return mixed
   *   Depends on which validator method is called.
   */
  private static function input($fn, $name, $options) {
    return self::{$fn}(filter_input(isset($options["#type"]) ? $options["#type"] : INPUT_POST, $name, FILTER_UNSAFE_RAW), $options);
  }

  /**
   * Remove single plus sign from the beginning of a string.
   *
   * By default any string starting with a single plus sign will not validate as number. Whereas variables with correct
   * type (e.g. <code>(float) +0.0</code>) would validate just fine. Fun fact, a dash at the beginning (which is
   * interpreted as minus, although the correct minus character is a different one) is always interpreted correctly. We
   * overcome this inconsistency by simply removing a single plus sign from the beginning of any string that we try to
   * validate. We don't see any harm in doing so.
   *
   * There is no need to use multi-byte functions at this point. We are only interested in the very first character and
   * the plus sign, which is standard ASCII. Any encoded variation of the plus sign (e.g. octet) shall fail!
   *
   * @param mixed $data
   *   The variable to process.
   * @return mixed
   *   <var>$data</var> with first plus sign removed (if any).
   */
  public static function removePlusSign(&$data) {
    if (is_string($data) && strlen($data) > 0 && $data[0] === "+") {
      $data = substr($data, 1);
    }
    return $data;
  }


  // ------------------------------------------------------------------------------------------------------------------- Boolean


  /**
   * Validate given boolean.
   *
   * @see FILTER_VALIDATE_BOOLEAN
   * @param mixed $raw
   *   The boolean to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   * @return null|boolean
   *   <code>NULL</code> if the boolean is not valid, otherwise the boolean itself is returned.
   */
  public static function boolean($raw, $options = []) {
    return filter_var($raw, FILTER_VALIDATE_BOOLEAN, array_merge([ "flags" => FILTER_NULL_ON_FAILURE ], $options));
  }

  /**
   * Validate boolean from user input.
   *
   * @see FILTER_VALIDATE_BOOLEAN
   * @see \MovLib\Utility\Validator::boolean()
   * @param string $name
   *   The variable name within the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   * @return null|string
   *   <code>NULL</code> if the boolean is not valid, otherwise the boolean itself is returned.
   */
  public static function inputBoolean($name, $options = []) {
    // The input method would return false in this case, which is not correct. Catch this problem.
    if (filter_has_var(isset($options["#type"]) ? $options["#type"] : INPUT_POST, $name) === false) {
      return null;
    }
    return self::input("boolean", $name, $options);
  }


  // ------------------------------------------------------------------------------------------------------------------- Number


  /**
   * Validate given number.
   *
   * @link http://docs.oracle.com/cd/E19957-01/806-3568/ncg_goldberg.html
   *   What Every Computer Scientist Should Know About Floating-Point Arithmetic
   * @see FILTER_VALIDATE_FLOAT
   * @param mixed $raw
   *   The number to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   * @return false|float
   *   <code>FALSE</code> if the number is not valid, otherwise the number itself.
   */
  public static function number($raw, $options = []) {
    return filter_var(self::removePlusSign($raw), FILTER_VALIDATE_FLOAT, $options);
  }

  /**
   * Validate float from user input.
   *
   * @link http://docs.oracle.com/cd/E19957-01/806-3568/ncg_goldberg.html
   *   What Every Computer Scientist Should Know About Floating-Point Arithmetic
   * @see FILTER_VALIDATE_FLOAT
   * @see \MovLib\Utility\Validator::number()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   * @return false|float
   *   <code>FALSE</code> if the number is not valid, otherwise the number itself.
   */
  public static function inputNumber($name, $options = []) {
    return self::input("number", $name, $options);
  }

  /**
   * Validate given formatted string as float.
   *
   * @link http://docs.oracle.com/cd/E19957-01/806-3568/ncg_goldberg.html
   *   What Every Computer Scientist Should Know About Floating-Point Arithmetic
   * @see FILTER_VALIDATE_FLOAT
   * @see \MovLib\Utility\Validator::number()
   * @global \MovLib\Model\I18nModel $i18n
   * @param mixed $raw
   *   The formatted string to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#locale"</code>: Specify the locale for the formatter, default is the current display locale</li>
   *     <li><code>"#formatted"</code>: If set to <code>TRUE</code> the formatted number will be returned, by default
   *     a computer readable number is returned. Numbers should always be stored in a computer readable form. Otherwise
   *     it's impossible to determine the language of the number.</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   * @return boolean|float|string
   *   <code>FALSE</code> if the number is not valid, otherwise the float value of the formatted string. If the
   *   <code>"#formatted"</code> option is set to <code>TRUE</code> the formatted string will be returned.
   */
  public static function numberFormatted($raw, $options = []) {
    global $i18n;
    $options = array_merge([ "#locale" => $i18n->locale, "#formatted" => false ], $options);
    self::removePlusSign($raw);
    try {
      $numberFormatter = new NumberFormatter($options["#locale"], NumberFormatter::DECIMAL);
      $filtered = self::number($numberFormatter->parse($raw));
      $formatted = $numberFormatter->format($filtered);
      // Check if the filter failed but also check if the formatted number equals the user input. The reason for this
      // are different formatting between languages. E.g. 1.000,01 is a valid German formatted number and would be
      // 1,000.01 in English formatting. If we wouldn't compare the input strings the aformentioned German formatted
      // number would validate as 1.0!
      if ($filtered === false || strcmp($formatted, $raw) !== 0) {
        return false;
      }
      if ($options["#formatted"] === true) {
        return $formatted;
      }
      return $filtered;
    } catch (IntlException $e) {
      return false;
    }
  }

  /**
   * Validate formatted string from user input as float.
   *
   * @link http://docs.oracle.com/cd/E19957-01/806-3568/ncg_goldberg.html
   *   What Every Computer Scientist Should Know About Floating-Point Arithmetic
   * @see FILTER_VALIDATE_FLOAT
   * @see \MovLib\Utility\Validator::numberFormatted()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *     <li><code>"#locale"</code>: Specify the locale for the formatter, default is the current display locale</li>
   *     <li><code>"#formatted"</code>: If set to <code>TRUE</code> the formatted number will be returned, by default
   *     a computer readable number is returned. Numbers should always be stored in a computer readable form. Otherwise
   *     it's impossible to determine the language of the number.</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   * @return boolean|float|string
   *   <code>FALSE</code> if the number is not valid, otherwise the float value of the formatted string. If the
   *   <code>"#formatted"</code> option is set to <code>TRUE</code> the formatted string will be returned.
   */
  public static function inputNumberFormatted($name, $options = []) {
    return self::input("numberFormatted", $name, $options);
  }


  // ------------------------------------------------------------------------------------------------------------------- IP Address


  /**
   * Validate given IPv4 or IPv6 address.
   *
   * @see FILTER_VALIDATE_IP
   * @param mixed $raw
   *   The IP address to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   * @return boolean|string
   *   <code>FALSE</code> if the IP address is not valid or empty, otherwise the IP address is returned.
   */
  public static function ipAddress($raw, $options = []) {
    $filtered = filter_var($raw, FILTER_VALIDATE_IP, $options);
    if ($filtered === false || empty($filtered)) {
      return false;
    }
    return $filtered;
  }

  /**
   * Validate IPv4 or IPv6 address from user input.
   *
   * @see FILTER_VALIDATE_IP
   * @see \MovLib\Utility\Validator::ipAddress()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   * @return boolean|string
   *   <code>FALSE</code> if the IP address is not valid or empty, otherwise the IP address is returned.
   */
  public static function inputIpAddress($name, $options = []) {
    return self::input("ipAddress", $name, $options);
  }


  // ------------------------------------------------------------------------------------------------------------------- Mail


  /**
   * Validate given email address.
   *
   * @see FILTER_VALIDATE_EMAIL
   * @param mixed $raw
   *   The email address to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   * @return boolean|string
   *   <code>FALSE</code> if the email address is not valid or empty, otherwise the email address is returned. Note that
   *   this method will return a lowercased representation of the valid email address.
   */
  public static function mail($raw, $options = []) {
    $filtered = filter_var($raw, FILTER_VALIDATE_EMAIL, $options);
    if ($filtered === false || empty($filtered)) {
      return false;
    }
    return mb_strtolower($filtered);
  }

  /**
   * Validate email address from user input.
   *
   * @see FILTER_VALIDATE_EMAIL
   * @see \MovLib\Utility\Validator::mail()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   * @return boolean|string
   *   <code>FALSE</code> if the email address is not valid or empty, otherwise the email address is returned. Note that
   *   this method will return a lowercased representation of the valid email address.
   */
  public static function inputMail($name, $options = []) {
    return self::input("mail", $name, $options);
  }


  // ------------------------------------------------------------------------------------------------------------------- Integer


  /**
   * Validate given integer.
   *
   * @see FILTER_VALIDATE_INT
   * @param mixed $raw
   *   The integer to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, please refer to the
   *   {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to find out more about
   *   available options.
   *
   *   The following default values are used for integer validation:
   *   <ul>
   *     <li><code>"min_range"</code>: Is set to <code>0</code></li>
   *     <li><code>"max_range"</code>: Is set to <var>PHP_INT_MAX</var></li>
   *   </ul>
   * @return false|int
   *   <code>FALSE</code> if the integer is not valid, otherwise the integer itself.
   */
  public static function integer($raw, $options = []) {
    return filter_var($raw, FILTER_VALIDATE_INT, array_merge(["options" => [
      "min_range" => 0,
      "max_range" => PHP_INT_MAX,
    ]], $options));
  }

  /**
   * Validate integer from user input.
   *
   * @see FILTER_VALIDATE_INT
   * @see \MovLib\Utility\Validator::integer()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   *
   *   The following default values are used for integer validation:
   *   <ul>
   *     <li><code>"min_range"</code>: Is set to <code>0</code></li>
   *     <li><code>"max_range"</code>: Is set to <var>PHP_INT_MAX</var></li>
   *   </ul>
   * @return false|int
   *   <code>FALSE</code> if the integer is not valid, otherwise the integer itself.
   */
  public static function inputInteger($name, $options = []) {
    return self::input("integer", $name, $options);
  }

  /**
   * Validate given formatted string as integer.
   *
   * @see FILTER_VALIDATE_INT
   * @see \MovLib\Utility\Validator::integer()
   * @global \MovLib\Model\I18nModel $i18n
   * @param mixed $raw
   *   The formatted string to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#locale"</code>: Specify the locale for the formatter, default is the current display locale</li>
   *     <li><code>"#formatted"</code>: If set to <code>TRUE</code> the formatted number will be returned, by default
   *     a computer readable number is returned. Numbers should always be stored in a computer readable form. Otherwise
   *     it's impossible to determine the language of the number.</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   *
   *   The following default values are used for integer validation:
   *   <ul>
   *     <li><code>"min_range"</code>: Is set to <code>0</code></li>
   *     <li><code>"max_range"</code>: Is set to <var>PHP_INT_MAX</var></li>
   *   </ul>
   * @return boolean|int|string
   *   <code>FALSE</code> if the integer is not valid, otherwise the integer value of the formatted string. If the
   *   <code>"#formatted"</code> option is set to <code>TRUE</code> the formatted string will be returned.
   */
  public static function integerFormatted($raw, $options = []) {
    global $i18n;
    $options = array_merge([ "#locale" => $i18n->locale, "#formatted" => false ], $options);
    try {
      $integerFormatter = new NumberFormatter($options["#locale"], NumberFormatter::DECIMAL);
      $filtered = self::integer($integerFormatter->parse($raw));
      $formatted = $integerFormatter->format($filtered);
      // Check if the filter failed but also check if the formatted number equals the user input. The reason for this
      // are different formatting between languages. E.g. 1.000,01 is a valid German formatted number and would be
      // 1,000.01 in English formatting. If we wouldn't compare the input strings the aformentioned German formatted
      // number would validate as 1.0!
      if ($filtered === false || strcmp($formatted, $raw) !== 0) {
        return false;
      }
      if ($options["#formatted"] === true) {
        return $formatted;
      }
      return $filtered;
    } catch (IntlException $e) {
      return false;
    }
  }

  /**
   * Validate formatted string from user input as integer.
   *
   * @see FILTER_VALIDATE_INT
   * @see \MovLib\Utility\Validator::integerFormatted()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *     <li><code>"#locale"</code>: Specify the locale for the formatter, default is the current display locale</li>
   *     <li><code>"#formatted"</code>: If set to <code>TRUE</code> the formatted number will be returned, by default
   *     a computer readable number is returned. Numbers should always be stored in a computer readable form. Otherwise
   *     it's impossible to determine the language of the number.</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   *
   *   The following default values are used for integer validation:
   *   <ul>
   *     <li><code>"min_range"</code>: Is set to <code>0</code></li>
   *     <li><code>"max_range"</code>: Is set to <var>PHP_INT_MAX</var></li>
   *   </ul>
   * @return boolean|int|string
   *   <code>FALSE</code> if the integer is not valid, otherwise the integer value of the formatted string. If the
   *   <code>"#formatted"</code> option is set to <code>TRUE</code> the formatted string will be returned.
   */
  public static function inputIntegerFormatted($name, $options = []) {
    return self::input("integerFormatted", $name, $options);
  }


  // ------------------------------------------------------------------------------------------------------------------- String


  /**
   * Validate given string.
   *
   * This validation method should help us to avoid storage of senseless stuff. It is by no means a real XSS protection,
   * always remember to use <code>String::checkPlain()</code> if you output user entered plain text strings. Our check
   * at this point is as restrictive as possible without being problematic to users who enter sane texts.
   *
   * @param mixed $raw
   *   The string to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#allow_empty"</code>: If set to <code>TRUE</code> empty strings are considered valid.</li>
   *     <li><code>"#allow_lf"</code>: If set to <code>TRUE</code> line feeds in the string will be preserved and
   *     normalized to the LF character (<code>U+000A</code>).</li>
   *   </ul>
   * @return false|string
   *   <code>FALSE</code> if the string is not valid or empty, otherwise the string itself.
   */
  public static function string($raw, $options = []) {
    if (isset($options["#allow_empty"]) && $options["#allow_empty"] === true && empty($raw)) {
      return "";
    }
    // This is much more secure than using mb_check_encoding() which seems to fail on any invalid byte sequence.
    if (preg_match("//u", $raw) == false) {
      return false;
    }
    // Decode twice to ensure double escaped sequences are converted as well.
    $filtered = String::decodeEntities(String::decodeEntities($raw));
    // Strip control and non-printable characters, by default this includes any line feed characters.
    // @link http://stackoverflow.com/a/8171868/1251219
    if (isset($options["#allow_lf"]) && $options["#allow_lf"] === true) {
      // Accept all kinds of line feeds as valid, it's not the user's fault if the used client is not using the LFs
      // we work with.
      $raw = String::normalizeLineFeeds($raw);
      $filtered = preg_replace("/[\x{0}-\x{9}\x{B}-\x{1F}\x{80}-\x{9F}]/u", "", $filtered);
    }
    else {
      $filtered = preg_replace("/[\x{0}-\x{1F}\x{80}-\x{9F}]/u", "", $filtered);
    }
    // After decoding and stripping remove all possible tags that might come up now. But allow user to write a <>
    // character combination as this does no harm at all.
    $filtered = strip_tags($filtered, "<>");
    if (empty($filtered) || strcmp($filtered, $raw) !== 0) {
      return false;
    }
    return trim($filtered);
  }

  /**
   * Validate string from user input.
   *
   * @see \MovLib\Utility\Validator::string()
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *     <li><code>"#allow_empty"</code>: If set to <code>TRUE</code> empty strings are considered valid.</li>
   *     <li><code>"#allow_lf"</code>: If set to <code>TRUE</code> line feeds in the string will be preserved and
   *     normalized to the LF character (<code>U+000A</code>).</li>
   *   </ul>
   * @return false|string
   *   <code>FALSE</code> if the string is not valid, otherwise the string itself.
   */
  public static function inputString($name, $options = []) {
    return self::input("string", $name, $options);
  }


  // ------------------------------------------------------------------------------------------------------------------- URL


  /**
   * Validate given URL syntax.
   *
   * This validation process does not include a check if the address is reachable at all. It will only check the syntax
   * and ensure that this link is save to use. Please note that we only support <code>"http(s)"</code> links, no other
   * protocol is allowed. If the URL contains a port, user, or pass it won't validate. International URLs will be
   * automatically encoded to the correct representation.
   *
   * @param mixed $raw
   *   The string to validate.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#allow_empty"</code>: If set to <code>TRUE</code> empty strings are considered valid.</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   * @return false|string
   *   <code>FALSE</code> if the URL is not valid or empty, otherwise the URL itself.
   */
  public static function url($raw, $options = []) {
    if (isset($options["#allow_empty"]) && $options["#allow_empty"] === true && empty($raw)) {
      return "";
    }
    // Make sure it is a valid plain text string, every URL is.
    $filtered = self::string($raw);
    if ($filtered === false) {
      return false;
    }
    // Split the URL into it's components for easy encoding of the path elements.
    $parts = parse_url($filtered);
    if ($parts === false) {
      return false;
    }
    // A URL must have a scheme and host, otherwise we consider it to be invalid. Yes, no support for protocol relative
    // URLs. They often lead to problems for other applications (think of the API). Simply don't allow it and make sure
    // that the UI always helps users to include it (e.g. with the placeholder attribute and placing the http:// in
    // front, plus maybe some JavaScript that adds the protocol if the user omits it).
    foreach ([ "scheme", "host" ] as $mandatoryPart) {
      if (empty($parts[$mandatoryPart])) {
        return false;
      }
    }
    // As mentioned in the description of this method, only HTTP and HTTPS are valid protocols within our software.
    if ($parts["scheme"] !== "http" && $parts["scheme"] !== "https") {
      return false;
    }
    // If any of these is present the complete URL is invalid. Why ports? It's very unusual that a website that is
    // accessible for the public is using a non standard port. Therefor we don't allow this.
    foreach ([ "port", "user", "pass" ] as $invalidPart) {
      if (!empty($parts[$invalidPart])) {
        return false;
      }
    }
    // We have to encode unicode characters, otherwise not only the filter fails, but we are only interested in perfect
    // valid URLs and we cannot treat an unencoded unicode character in the path as something that is invalid. The
    // transformation should be transparent for normal human beings who are used to literal characters.
    if (!empty($parts["path"])) {
      $pathParts = explode("/", $parts["path"]);
      $c = count($pathParts);
      for ($i = 0; $i < $c; ++$i) {
        $pathParts[$i] = rawurlencode(rawurldecode($pathParts[$i]));
      }
      $parts["path"] = implode("/", $pathParts);
    }
    // Rebuild the URL including all provided parts, but leaving out those parts we are not interested in.
    $filtered = "{$parts["scheme"]}://{$parts["host"]}";
    // Don't forget the allowed optional parts of the URL including their prefix.
    foreach ([ "path" => "", "query" => "?", "fragment" => "#" ] as $optionalPart => $prefix) {
      if (!empty($parts[$optionalPart])) {
        $filtered .= $prefix . $parts[$optionalPart];
      }
    }
    // Last but not least let PHP do it's magic.
    return filter_var($filtered, FILTER_VALIDATE_URL, $options);
  }

  /**
   * Validate URL syntax from user input.
   *
   * This validation process does not include a check if the address is reachable at all. It will only check the syntax
   * and ensure that this link is save to use. Please note that we only support <code>"http(s)"</code> links, no other
   * protocol is allowed. If the URL contains a port, user, or pass it won't validate. International URLs will be
   * automatically encoded to the correct representation.
   *
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *     <li><code>"#allow_empty"</code>: If set to <code>TRUE</code> empty strings are considered valid.</li>
   *   </ul>
   *   Please refer to the {@link http://www.php.net/manual/en/filter.filters.validate.php official documentation} to
   *   find out more about available options.
   * @return false|string
   *   <code>FALSE</code> if the URL is not valid or empty, otherwise the URL itself.
   */
  public static function inputUrl($name, $type = INPUT_POST) {
    return self::url(filter_input($type, $name, FILTER_UNSAFE_RAW));
  }


  // ------------------------------------------------------------------------------------------------------------------- Username


  /**
   * Validate given username.
   *
   * Please note that this method will also check if the username is already taken by another user.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param string $name
   *   The username to validate.
   * @return string
   *   <var>$name</var> if the username is valid and not already in use.
   * @throws \MovLib\Exception\ValidatorException
   */
  public static function username($name) {
    global $i18n;
    if (empty($name)) {
      throw new ValidatorException($i18n->t("You must enter a username."));
    }
    if (substr($name, 0, 1) === " ") {
      throw new ValidatorException($i18n->t("The username cannot begin with a space."));
    }
    if (substr($name, -1) === " ") {
      throw new ValidatorException($i18n->t("The username cannot end with a space."));
    }
    if (strpos($name, "  ") !== false) {
      throw new ValidatorException($i18n->t("The username cannot contain multiple spaces in a row."));
    }
    if (($name = self::string($name)) === false) {
      throw new ValidatorException($i18n->t("The username contains one or more illegal character."));
    }
    if (mb_strlen($name) > UserModel::NAME_MAX_LENGTH) {
      throw new ValidatorException($i18n->t(
        "The username {0} is too long: it must be {1,number,integer} characters or less.",
        [ $name, UserModel::NAME_MAX_LENGTH ]
      ));
    }
    // @todo The blacklist content must be translated along with the routes.
    $blacklist = json_decode(file_get_contents(__DIR__ . "/username-blacklist.json"));
    $c = count($blacklist);
    for ($i = 0; $i < $c; ++$i) {
      if (strcmp($blacklist[$i], mb_strtolower($name)) === 0) {
        throw new ValidatorException($i18n->t("The username is a system reserved word, please choose another one."));
      }
    }
    if ((new UserModel())->existsName($name) === true) {
      throw new ValidatorException($i18n->t(
        "The {0} {1} is already in use.",
        [ $i18n->t("username"), String::placeholder($name) ]
      ));
    }
    return $name;
  }

  /**
   * Validate username from user input.
   *
   * Please note that this method will also check if the username is already taken by another user.
   *
   * @param string $name
   *   The name of the offset in the <var>INPUT_*</var> array.
   * @param array $options [optional]
   *   Associative array containing additional options for the validation process, the following options are available:
   *   <ul>
   *     <li><code>"#type"</code>: Specify the <var>INPUT_*</var> array from where the value to validate should be read,
   *     by default <var>INPUT_POST</var> is used</li>
   *   </ul>
   * @return string
   *   <var>$name</var> if the username is valid and not already in use.
   * @throws \MovLib\Exception\ValidatorException
   */
  public static function inputUsername($name, $options = []) {
    return self::input("username", $name, $options);
  }

}
