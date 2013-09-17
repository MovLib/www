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

use \MovLib\Exception\ValidatorException;

/**
 * HTML input type URL form element.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputUrl extends \MovLib\Presentation\Partial\FormElement\InputText {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type url.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $id
   *   The form element's global identifier.
   * @param string $label
   *   The form element's label content.
   * @param string $value [optional]
   *   The form element's default value.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id, $label, $value = null, array $attributes = null, array $labelAttributes = null) {
    global $i18n;
    parent::__construct($id, $label, $value, $attributes, $labelAttributes);
    $this->attributes["type"]    = "url";
    $this->attributes["pattern"] = "^https?://[a-z0-9\-\.]+\.[a-z]{2,5}(/.*)*$";
    $this->attributes["title"]   = $i18n->t("The URL must start with either http:// or https:// and continue with a valid domain (username, password and port are not allowed)");
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = "http(s)://";
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    // Split the URL into separate parts for easy validation and proper encoding.
    if (($parts = parse_url($this->value)) === false) {
      throw new ValidatorException($i18n->t("The URL {0} doesn’t seem to be valid.", [ $this->placeholder($_POST[$this->id]) ]));
    }

    $errors = null;
    // A URL must have a scheme and host, otherwise we consider it to be invalid. No support for protocol relative
    // URLs. They often lead to problems for other applications and we're using SSL everywhere, which most other
    // websites aren't using. Therefor we simply don't allow and we have to make sure via the UI that the user is
    // always pasting absolute URLs (e.g. with the placeholder attribute as you can see above in the __toString()
    // method).
    if (empty($parts["scheme"]) || empty($parts["host"])) {
      $errors[] = $i18n->t("Scheme (protocol) and host are mandatory in a URL.");
    }
    // Only HTTP and HTTPS are considered valid schemes.
    elseif ($parts["scheme"] != "http" && $parts["scheme"] != "https") {
      $errors[] = $i18n->t("Scheme (protocol) must be of type HTTP or HTTPS.");
    }
    // Check for valid TLD.
    elseif (preg_match("/\.[a-z]{2,5}$/", $parts["host"]) == false) {
      $errors[] = $i18n->t("The URL {0} must have a valid top level domain (TLD).", [ $this->placeholder($_POST[$this->id]) ]);
    }

    // If any of the following parts is present the complete URL is considered invalid. No reputable website is
    // using non-standard ports, they simply wouldn't be accessible for the majority of surfers.
    if (!empty($parts["port"]) || !empty($parts["user"]) || !empty($parts["pass"])) {
      $errors[] = $i18n->t("The URL {0} contains illegal parts. Port, usernames and passwords are not allowed!", [ $this->placeholder($_POST[$this->id]) ]);
    }

    // We can't continue if offset scheme or host is missing.
    if ($errors) {
      throw new ValidatorException(implode("<br>", $errors));
    }

    // Rebuild the URL including all provided and allowed parts.
    $this->value = "{$parts["scheme"]}://{$parts["host"]}";

    // We have to encode unicode characters, otherwise not only the filter fails, but we are only interested in perfect
    // valid URLs and we cannot treat an unencoded unicode character in the path as something that is invalid. The
    // transformation should be transparent for normal human beings who are used to literal characters.
    if (!empty($parts["path"])) {
      $path = explode("/", $parts["path"]);
      $c = count($path);
      for ($i = 0; $i < $c; ++$i) {
        $path[$i] = rawurlencode(rawurldecode($path[$i]));
      }
      $this->value .= implode("/", $path);
    }

    // Don't forget the allowed optional parts of the URL including their prefix.
    foreach ([ "query" => "?", "fragment" => "#" ] as $optionalPart => $prefix) {
      if (!empty($parts[$optionalPart])) {
        $this->value .= "{$prefix}{$parts[$optionalPart]}";
      }
    }

    // And last but not least validate it again (the flag might be a bit useless at this point).
    if (filter_var($this->value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === false) {
      throw new ValidatorException($i18n->t("The URL {0} doesn’t seem to be valid.", [ $this->placeholder($_POST[$this->id]) ]));
    }

    // Additionally check if the URL exists if the appropriate data attribute is set.
    if (isset($this->attributes["data-url-exists"]) && $this->attributes["data-url-exists"] == true) {
      $ch = curl_init($this->value);
      curl_setopt($ch, CURLOPT_NOBODY, true);
      curl_exec($ch);
      $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE) / 100;
      curl_close($ch);
      if ($code !== 2 || $code !== 3) {
        throw new ValidatorException($i18n->t("The URL {0} doesn’t exists more specifically isn’t reachable.", [ $this->placeholder($_POST[$this->id]) ]));
      }
    }

    $this->value = $this->value;
    return $this;
  }

}
