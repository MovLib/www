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
 * Input URL form element.
 *
 * You can control the validation process via two <code>"data"</code>-attributes:
 * <table border="1" cellspacing="0">
 *   <tr><th>Attribute</th><th>Description</th></tr>
 *   <tr><td><code>"data-allow-external"</code></td><td>Set this to the string <code>"true"</code> to allow external
 *   URLs to validate.</td></tr>
 *   <tr><td><code>"data-check-reachability"</code></td>Set this to the string <code>"true"</code> to check if the URL
 *   is really reachable via the WWW (done with cURL).</td></tr>
 * </table>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputURL extends \MovLib\Partial\FormElement\AbstractInput {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "InputURL";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for malformed URL error message.
   *
   * @var integer
   */
  const ERROR_MALFORMED = 1;

  /**
   * Error code for missing scheme or host error message.
   *
   * @var integer
   */
  const ERROR_SCHEME_OR_HOST = 2;

  /**
   * Error code for disallowed scheme error message.
   *
   * @var integer
   */
  const ERROR_SCHEME = 3;

  /**
   * Error code for missing TLD error message.
   *
   * @var integer
   */
  const ERROR_TLD = 4;

  /**
   * Error code for disallowed external URL error message.
   *
   * @var integer
   */
  const ERROR_EXTERNAL = 5;

  /**
   * Error code for disallowed parts error message.
   *
   * @var integer
   */
  const ERROR_PARTS = 6;

  /**
   * Error code for unreachable URL error message.
   *
   * @var integer
   */
  const ERROR_REACHABILITY = 7;

  /**
   * Regular expression pattern for client side URL validation.
   *
   * @var string
   */
  const PATTERN = "^https?://[a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*[a-z0-9_]\.[a-z]{2,6}(/.*)*$";

  /**
   * The form element's type.
   *
   * @var string
   */
  const TYPE = "url";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the input URL form element.
   *
   * @return string
   *   The input URL form element.
   */
  public function __toString() {
    $this->attributes["pattern"] = self::PATTERN;
    $this->attributes["title"]   = $this->intl->t(
      "The URL must start with either http:// or https:// and continue with a valid domain (username, password and port are not allowed)"
    );
    if (empty($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = "http(s)://";
    }
    return parent::__toString();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted URL.
   *
   * @param string $url
   *   The user submitted url to validate.
   * @param null|array $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid URL.
   */
  protected function validateValue($url, &$errors) {
    // Split the URL into separate parts for easy validation and proper escaping. parse_url() returns FALSE if it fails,
    // but it's more or less close to impossible to reach that goal. Still, if it happens directly abort.
    if (($parts = parse_url($url)) === false) {
      $errors[self::ERROR_MALFORMED] = $this->intl->t("The URL is invalid.");
      return $url;
    }

    // A URL must have a scheme and host, otherwise we consider it to be invalid. No support for protocol relative
    // URLs. They often lead to problems for other applications and we're using SSL everywhere, which most other
    // websites aren't using. Therefor we simply don't allow and we have to make sure via the UI that the user is
    // always pasting absolute URLs (e.g. with the placeholder attribute as you can see above in the getPresentation()
    // method).
    if (empty($parts["scheme"]) || empty($parts["host"])) {
      $errors[self::ERROR_SCHEME_OR_HOST] = $this->intl->t("Scheme (protocol) and host are mandatory in a URL.");
    }
    // Only HTTP and HTTPS are considered valid schemes.
    elseif ($parts["scheme"] != "http" && $parts["scheme"] != "https") {
      $errors[self::ERROR_SCHEME] = $this->intl->t("Scheme (protocol) must be of type HTTP or HTTPS.");
    }
    // Check for valid TLD.
    elseif (preg_match("/\.[a-z]{2,6}$/", $parts["host"]) == false) {
      $errors[self::ERROR_TLD] = $this->intl->t("The URL must have a valid {0}TLD{1}.", [
        "<abbr title='{$this->intl->t("Top-level domain")}'>",
        "</abbr>",
      ]);
    }

    // Stop here if scheme, host, or TLD are missing or not valid.
    if ($errors) {
      return $url;
    }

    // Check if this is an external URL.
    if (strpos($parts["host"], $this->config->hostname) === false && isset($this->attributes["data-allow-external"]) && $this->attributes["data-allow-external"] != true) {
      $errors[self::ERROR_EXTERNAL] = $this->intl->t("External URLs are not allowed.");
    }

    // If any of the following parts is present the complete URL is considered invalid. No reputable website is
    // using non-standard ports, they simply wouldn't be accessible for the majority of surfers.
    if (isset($parts["port"]) || isset($parts["user"]) || isset($parts["pass"])) {
      $errors[self::ERROR_PARTS] = $this->intl->t("The URL contains illegal parts. Port, usernames and passwords are not allowed!");
    }

    // Don't bother rebuilding the URL if we the URL isn't valid.
    if ($errors) {
      return $url;
    }

    // Start rebuilding the URL including all provided and allowed parts.
    $url = "{$parts["scheme"]}://{$parts["host"]}";

    // We have to encode unicode characters, otherwise not only the filter fails, but we are only interested in perfect
    // valid URLs and we cannot treat an unencoded unicode character in the path as something that is invalid. The
    // transformation should be transparent for normal human beings who are used to literal characters.
    if (isset($parts["path"])) {
      $path = explode("/", $parts["path"]);
      $c = count($path);
      for ($i = 0; $i < $c; ++$i) {
        $path[$i] = rawurlencode(rawurldecode($path[$i]));
      }
      $url .= implode("/", $path);

      // Don't forget the allowed optional parts of the URL including their prefix.
      if (($issetQuery = isset($parts["query"]))) {
        $url .= "?{$parts["query"]}";
      }
      if (($issetFragment = isset($parts["fragment"]))) {
        $url .= "#{$parts["fragment"]}";
      }

      // And last but not least validate it again including all the optional parts.
      if (($issetQuery === true || $issetFragment === true) && filter_var($url, FILTER_VALIDATE_URL) === false) {
        $errors[self::ERROR_MALFORMED] = $this->intl->t("The URL is invalid.");
      }
    }
    // No path at all, add a slash to ensure that the link points to the homepage.
    else {
      $url = "{$url}/";
    }

    // Additionally check if the URL exists if the appropriate data attribute is set.
    if (isset($this->attributes["data-check-reachability"]) && $this->attributes["data-check-reachability"] == true) {
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
        CURLOPT_USERAGENT      => "Mozilla/5.0 (compatible; MovLib-Validation/{$this->config->version}; +https://{$this->config->hostname}/)",
      ]);
      curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if ($code !== 200) {
        $errors[self::ERROR_REACHABILITY] = $this->intl->t("The URL doesn’t exists (more specifically isn’t reachable).");
      }
    }

    return $url;
  }

}
