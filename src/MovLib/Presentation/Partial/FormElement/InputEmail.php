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
 * HTML input type email form element.
 *
 * The usage of a magic constant at this point is absolutely okay, because 254 is the official upper limit for any valid
 * email address. This restriction is layed down in RFC 2821 on the length for MAIL and RCPT commands.
 *
 * @link http://www.rfc-editor.org/errata_search.php?rfc=3696&eid=1690
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmail extends \MovLib\Presentation\Partial\FormElement\InputText {

  /**
   * Instantiate new input form element of type text.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $id [optional]
   *   The form element's global identifier, defaults to <code>"email"</code>.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param string $label [optional]
   *   The form element's label content.
   * @param string $value [optional]
   *   The form element's default value.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($id = "email", array $attributes = null, $label = null, $value = null, array $labelAttributes = null) {
    global $i18n;
    parent::__construct($id, $label, $value, $attributes, $labelAttributes);
    $this->attributes["type"]      = "email";
    $this->attributes["maxlength"] = 254;
    $this->attributes["pattern"]   = "^.+@.+\..+$"; // @todo Extend regular expression to be more precise!
    $this->attributes["title"]     = $i18n->t("An email address in the format [local]@[host].[tld]");
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your email address");
    }
    if (!$this->label) {
      $this->label = $i18n->t("Email Address");
    }
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    // No need for multi-byte functions, utf-8 is not allowed in emails.
    if (strlen($this->value) > $this->attributes["maxlength"]) {
      throw new ValidatorException($i18n->t("The email address {0} is too long: it must be {1,number,integer} or less.", [
        $this->placeholder($this->value),
        $this->attributes["maxlength"],
      ]));
    }

    // Use PHP's built-in validation function.
    if (filter_var($this->value, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
      throw new ValidatorException($i18n->t("The email address {0} doesn’t appear to be valid.", [
        $this->placeholder($this->value),
      ]));
    }

    // If the syntax is valid, check if we actually can send emails to the given address.
    $host = substr($this->value, strrpos($this->value, "@") + 1);
    // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
    // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
    // IPv6 hosts and the MX record is for mail servers only.
    if (strpos($host, ".") === false || (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false)) {
      throw new ValidatorException($i18n->t("The email address {0} doesn’t appear to be valid.", [
        $this->placeholder($this->value),
      ]));
    }

    return $this;
  }

}
