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
 * URL validation class.
 *
 * @see \MovLib\Presentation\Validation\InterfaceValidation
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class URL extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Error code for malformed URLs.
   *
   * @var int
   */
  const E_MALFORMED = 1;

  /**
   * Error code for malformed scheme or host parts.
   *
   * @var int
   */
  const E_SCHEME_OR_HOST_MALFORMED = 2;

  /**
   * Error code for external links.
   *
   * @var int
   */
  const E_NO_EXTERNAL = 3;

  /**
   * Error code for URLs that include illegal parts.
   *
   * @var int
   */
  const E_ILLEGAL_PARTS = 4;

  /**
   * Error code for unreachable URLs.
   *
   * @var int
   */
  const E_UNREACHABLE = 5;

  /**
   * URL regular expression pattern for validation.
   *
   * Regular expression that can be used in the pattern attribute or in JavaScript to validate URLs before sending them
   * to the server. Please note that this is only meant as a fast validation process and the server is still going
   * to validate all input. But it's easier if the user has direct feedback and our servers aren't validating stuff all
   * day.
   *
   * Whitle the regular expression matches our internal validation process pretty closely, some major differences are
   * found regarding path, query, and fragment. All these elements will simply validate, no matter their content (see
   * the <code>"(/.*)*"</code> part in the regular expression), while unicode queries or fragments will fail in this
   * class. The reason for this behaviour is PHP's built in validation function we call, it would only accept those
   * unicode characters if they are properly encoded, but we only encode the path itself. The future has to tell if this
   * is enough or not. Most websites should use standard ASCII in their queries and fragments.
   *
   * @var int
   */
  const PATTERN = "^https?://[a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*[a-z0-9_]\.[a-z]{2,6}(/.*)*$";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whetever to consider external URLs as valid or not.
   *
   * @var boolean
   */
  public $allowExternal = false;

  /**
   * Flag indicating if external URL.
   *
   * @var boolean
   */
  public $external = false;

  /**
   * Whetever to check if the URL is reachable or not.
   *
   * @var boolean
   */
  public $checkReachability = false;

  /**
   * The various parts of the URL as returned by PHP's built-in <code>parse_url()</code> function.
   *
   * Note that the default value is <code>FALSE</code>.
   *
   * @var boolean|array
   */
  public $parts = false;

  /**
   * The URL to validate.
   *
   * Note that this property is only set it the URL is valid!
   *
   * @var string
   */
  public $url;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new URL and validate it.
   *
   * @param string $url [optional]
   *   The URL to validate, defaults to none.
   */
  public function __construct($url = null) {
    $this->url = $url;
  }

  /**
   * Get the URL.
   *
   * @return string
   *   The URL.
   */
  public function __toString() {
    return $this->url;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the URL to validate.
   *
   * @param string $url
   *   The URL to validate.
   */
  public function set($url) {
    $this->url = $url;
  }

  /**
   * Validate the URL with the current options.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The validated URL.
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n;

    // Trim right before validating and not in the constructor, could be the property was set later.
    $this->url = trim($this->url);

    // Split the URL into separate parts for easy validation and proper escaping.
    if (empty($this->url) || ($this->parts = parse_url($this->url)) === false) {
      throw new ValidationException($i18n->t("The URL doesn’t seem to be valid."), self::E_MALFORMED);
    }

    // Collect the following errors and throw a single exception.
    $errors = null;

    // A URL must have a scheme and host, otherwise we consider it to be invalid. No support for protocol relative
    // URLs. They often lead to problems for other applications and we're using SSL everywhere, which most other
    // websites aren't using. Therefor we simply don't allow and we have to make sure via the UI that the user is
    // always pasting absolute URLs (e.g. with the placeholder attribute as you can see above in the __toString()
    // method).
    if (empty($this->parts["scheme"]) || empty($this->parts["host"])) {
      $errors[] = $i18n->t("Scheme (protocol) and host are mandatory in a URL.");
    }
    // Only HTTP and HTTPS are considered valid schemes.
    elseif ($this->parts["scheme"] != "http" && $this->parts["scheme"] != "https") {
      $errors[] = $i18n->t("Scheme (protocol) must be of type HTTP or HTTPS.");
    }
    // Check for valid TLD.
    elseif (preg_match("/\.[a-z]{2,6}$/", $this->parts["host"]) == false) {
      $errors[] = $i18n->t("The URL must have a valid {0}TLD{1}.", [
        "<abbr title='{$i18n->t("Top-level domain")}'>",
        "</abbr>",
      ]);
    }

    // Stop here if scheme or host are missing or not valid.
    if ($errors) {
      throw new ValidationException(implode("<br>", $errors), self::E_SCHEME_OR_HOST_MALFORMED);
    }

    // Check if this is an external URL.
    if (strpos($this->parts["host"], $GLOBALS["movlib"]["default_domain"]) === false) {
      if ($this->allowExternal === false) {
        throw new ValidationException($i18n->t("External URLs are forbidden in this context."), self::E_NO_EXTERNAL);
      }
      $this->external = true;
    }

    // If any of the following parts is present the complete URL is considered invalid. No reputable website is
    // using non-standard ports, they simply wouldn't be accessible for the majority of surfers.
    if (isset($this->parts["port"]) || isset($this->parts["user"]) || isset($this->parts["pass"])) {
      throw new ValidationException($i18n->t("The URL contains illegal parts. Port, usernames and passwords are not allowed!"), self::E_ILLEGAL_PARTS);
    }

    // Start rebuilding the URL including all provided and allowed parts.
    $this->url = "{$this->parts["scheme"]}://{$this->parts["host"]}";

    // We have to encode unicode characters, otherwise not only the filter fails, but we are only interested in perfect
    // valid URLs and we cannot treat an unencoded unicode character in the path as something that is invalid. The
    // transformation should be transparent for normal human beings who are used to literal characters.
    if (isset($this->parts["path"])) {
      $path = explode("/", $this->parts["path"]);
      $c = count($path);
      for ($i = 0; $i < $c; ++$i) {
        $path[$i] = rawurlencode(rawurldecode($path[$i]));
      }
      $this->url .= implode("/", $path);

      // Don't forget the allowed optional parts of the URL including their prefix.
      if (($issetQuery = isset($this->parts["query"]))) {
        $this->url .= "?{$this->parts["query"]}";
      }
      if (($issetFragment = isset($this->parts["fragment"]))) {
        $this->url .= "#{$this->parts["fragment"]}";
      }

      // And last but not least validate it again including all the optional parts.
      if (($issetQuery === true || $issetFragment === true) && filter_var($this->url, FILTER_VALIDATE_URL) === false) {
        throw new ValidationException($i18n->t("The URL doesn’t seem to be valid."), self::E_MALFORMED);
      }
    }
    // No path at all, add a slash or replace with slash if link to our home page.
    else {
      $this->url = "{$this->url}/";
    }

    // Additionally check if the URL exists if the appropriate data attribute is set.
    if ($this->checkReachability === true) {
      $this->checkReachability();
    }

    return $this->url;
  }

  /**
   * Check if the URL is reachable.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Exception\ValidationException
   */
  public function checkReachability() {
    global $i18n;
    $ch = curl_init($this->url);
    curl_setopt_array($ch, [
      CURLOPT_AUTOREFERER    => true,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_ENCODING       => "",
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS      => 1,
      CURLOPT_NOBODY         => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_TIMEOUT        => 5,
      // It's very important to let other webmasters know who's probing their servers.
      CURLOPT_USERAGENT      => "Mozilla/5.0 (compatible; MovLib-Validation/{$GLOBALS["movlib"]["version"]}; +https://{$GLOBALS["movlib"]["default_domain"]}/)",
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) {
      throw new ValidationException($i18n->t("The URL doesn’t exists (more specifically isn’t reachable)."), self::E_UNREACHABLE);
    }
    return $this;
  }

}
