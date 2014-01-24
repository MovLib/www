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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * HTML input type URL form element.
 *
 * Please note that this class will always validate it's value as URL, you can't override the validator class that it
 * uses. You can control the validation process via two data attributes:
 * <ul>
 *   <li><code>"data-allow-external"</code> takes a boolean value and has the same effect as the <var>$allowExternal</var>
 *   parameter of the URL validation class</li>
 *   <li><code>"data-check-reachability"</code> takes a boolean value and has the same effect as the
 *   <var>$checkReachability</var> parameter of the URL validation class</li>
 * </ul>
 * The printed input element's pattern is set to a regular expression that matches the internal validation process
 * pretty closely. Please see the URL validation class for more info on the regular expression used here.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @see \MovLib\Presentation\Validation\URL
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputURL extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * Instantiate new URL input element.
   *
   * @param string $id
   *   The input element's unique global identifier.
   * @param string $label
   *   The input element's translated label text.
   * @param array $attributes [optional]
   *   The input element's attributes array.
   */
  public function __construct($id, $label, array $attributes = null) {
    global $i18n;
    parent::__construct($id, $label, $attributes);
    $this->attributes["pattern"]     = "^https?://[a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*[a-z0-9_]\.[a-z]{2,6}(/.*)*$";
    $this->attributes["placeholder"] = isset($attributes["placeholder"]) ? $attributes["placeholder"] :"http(s)://";
    $this->attributes["title"]       = $i18n->t("The URL must start with either http:// or https:// and continue with a valid domain (username, password and port are not allowed)");
    $this->attributes["type"]        = "url";
    if (!isset($this->attributes["data-allow-external"])) {
      $this->attributes["data-allow-external"] = false;
    }
    if (!isset($this->attributes["data-check-reachability"])) {
      $this->attributes["data-check-reachability"] = false;
    }
  }

  /**
   * Validate the user submitted URL.
   *
   * <b>NOTE!</b> The URL input is always validated as URL, you cannot override this behaviour.
   *
   * @return this
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {
    global $i18n, $kernel;
    if (empty($this->value)) {
      if (array_key_exists("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” URL field is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Trim right before validating and not in the constructor, could be the property was set later.
    $this->value = trim($this->value);

    // Split the URL into separate parts for easy validation and proper escaping.
    $parts = false;
    if (empty($this->value) || ($parts = parse_url($this->value)) === false) {
      throw new ValidationException($i18n->t("The URL doesn’t seem to be valid."));
    }

    // Collect the following errors and throw a single exception.
    $errors = null;

    // A URL must have a scheme and host, otherwise we consider it to be invalid. No support for protocol relative
    // URLs. They often lead to problems for other applications and we're using SSL everywhere, which most other
    // websites aren't using. Therefor we simply don't allow and we have to make sure via the UI that the user is
    // always pasting absolute URLs (e.g. with the placeholder attribute as you can see above in the getPresentation()
    // method).
    if (empty($parts["scheme"]) || empty($parts["host"])) {
      $errors[] = $i18n->t("Scheme (protocol) and host are mandatory in a URL.");
    }
    // Only HTTP and HTTPS are considered valid schemes.
    elseif ($parts["scheme"] != "http" && $parts["scheme"] != "https") {
      $errors[] = $i18n->t("Scheme (protocol) must be of type HTTP or HTTPS.");
    }
    // Check for valid TLD.
    elseif (preg_match("/\.[a-z]{2,6}$/", $parts["host"]) == false) {
      $errors[] = $i18n->t("The URL must have a valid {0}TLD{1}.", [
        "<abbr title='{$i18n->t("Top-level domain")}'>",
        "</abbr>",
      ]);
    }

    // Stop here if scheme or host are missing or not valid.
    if ($errors) {
      throw new ValidationException(implode("<br>", $errors));
    }

    // Check if this is an external URL.
    if (strpos($parts["host"], $kernel->domainDefault) === false) {
      if ($this->attributes["data-allow-external"] === false) {
        throw new ValidationException($i18n->t("External URLs are forbidden in this context."));
      }
    }

    // If any of the following parts is present the complete URL is considered invalid. No reputable website is
    // using non-standard ports, they simply wouldn't be accessible for the majority of surfers.
    if (isset($parts["port"]) || isset($parts["user"]) || isset($parts["pass"])) {
      throw new ValidationException($i18n->t("The URL contains illegal parts. Port, usernames and passwords are not allowed!"));
    }

    // Start rebuilding the URL including all provided and allowed parts.
    $this->value = "{$parts["scheme"]}://{$parts["host"]}";

    // We have to encode unicode characters, otherwise not only the filter fails, but we are only interested in perfect
    // valid URLs and we cannot treat an unencoded unicode character in the path as something that is invalid. The
    // transformation should be transparent for normal human beings who are used to literal characters.
    if (isset($parts["path"])) {
      $path = explode("/", $parts["path"]);
      $c = count($path);
      for ($i = 0; $i < $c; ++$i) {
        $path[$i] = rawurlencode(rawurldecode($path[$i]));
      }
      $this->value .= implode("/", $path);

      // Don't forget the allowed optional parts of the URL including their prefix.
      if (($issetQuery = isset($parts["query"]))) {
        $this->value .= "?{$parts["query"]}";
      }
      if (($issetFragment = isset($parts["fragment"]))) {
        $this->value .= "#{$parts["fragment"]}";
      }

      // And last but not least validate it again including all the optional parts.
      if (($issetQuery === true || $issetFragment === true) && filter_var($this->value, FILTER_VALIDATE_URL) === false) {
        throw new ValidationException($i18n->t("The URL doesn’t seem to be valid."));
      }
    }
    // No path at all, add a slash or replace with slash if link to our home page.
    else {
      $this->value = "{$this->value}/";
    }

    // Additionally check if the URL exists if the appropriate data attribute is set.
    if ($this->attributes["data-check-reachability"] === true) {
      $ch = curl_init($this->url);
      curl_setopt_array($ch, [
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_ENCODING => "",
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_NOBODY => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 5,
        // It's very important to let other webmasters know who's probing their servers.
        CURLOPT_USERAGENT => "Mozilla/5.0 (compatible; MovLib-Validation/{$GLOBALS["movlib"]["version"]}; +https://{$GLOBALS["movlib"]["default_domain"]}/)",
      ]);
      curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if ($code !== 200) {
        throw new ValidationException($i18n->t("The URL doesn’t exists (more specifically isn’t reachable)."));
      }
    }

    return $this;
  }

}
