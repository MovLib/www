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

use \MovLib\Exception\ValidationException;

/**
 * HTML input type email form element.
 *
 * @link http://www.rfc-editor.org/errata_search.php?rfc=3696&eid=1690
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmail extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * Instantiate new input form element of type email.
   *
   * @param string $id [optional]
   *   The email's global unique identifier, defaults to <code>"email"</code>.
   * @param string $label [optional]
   *   The email's translated label text, defaults to <code>$i18n->t("Email Address")</code>.
   * @param array $attributes [optional]
   *   The email's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"tabindex"</code> is set to the next global tabindex (with <code>getTabindex()</code>)</li>
   *     <li><code>"required"</code> is set</li>
   *     <li><code>"maxlength"</code> is set to <code>254</code></li>
   *     <li><code>"pattern"</code> is set to a regular expression that validates common email addresses</li>
   *     <li><code>"title"</code> explains the requirements for a valid email address</li>
   *     <li><code>"type"</code> is set to <code>"email"</code></li>
   *   </ul>
   *   You <b>should not</b> override any of the default attributes. The <code>"placeholder"</code> attribute is set to
   *   <code>$i18n->t("Enter your email address")</code> if none is passed along.
   */
  public function __construct($id = "email", $label = null, array $attributes = null) {
    global $i18n;
    parent::__construct($id, $label ?: $i18n->t("Email Address"), $attributes);
    $this->attributes["maxlength"]     = 254;
    $this->attributes["pattern"]       = "^[a-zA-Z0-9!#$%&'*+-/=?^_`{|}~\.\"(),:;<>@[\]\\\\ ]+@[a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*[a-z0-9_]\.[a-z]{2,6}$";
    $this->attributes["title"]         = $i18n->t(
      "An email address in the format [local]@[host].[tld] with a maximum of {0,number,integer} characters",
      [ $this->attributes["maxlength"] ]
    );
    $this->attributes["type"]          = "email";
    $this->attributes[]                = "required";
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter your email address");
    }
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      throw new ValidationException($i18n->t("The email address is mandatory."), self::E_MANDATORY);
    }

    // No need for multi-byte functions, utf-8 is not allowed in emails.
    if (strlen($this->value) > $this->attributes["maxlength"]) {
      throw new ValidationException($i18n->t(
        "The email address is too long: it must be {0,number,integer} or less.", [ $this->attributes["maxlength"] ]
      ));
    }

    // Use PHP's built-in validation function.
    if (filter_var($this->value, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR|FILTER_FLAG_HOST_REQUIRED) === false) {
      throw new ValidationException($i18n->t("The email address is invalid."));
    }

    // If the syntax is valid, check if we actually can send emails to the given address. Appending a dot makes sure
    // that the checkdnsrr() calls are always accurate. Unit tests have shown that the function returns true for an A
    // check on the domain "foo.bar", which is definitely not correct. No test failed with the dot appended to the
    // host part.
    $host = substr($this->value, strrpos($this->value, "@") + 1) . ".";

    // The host part needs at least a single point in it, otherwise localhost would be valid. We check the DNS entries
    // in the order of their weight for the current operation. An A record is for IPv4 hosts, an AAAA record is for
    // IPv6 hosts and the MX record is for mail servers only.
    if (checkdnsrr($host, "A") === false && checkdnsrr($host, "AAAA") === false && checkdnsrr($host, "MX") === false) {
      throw new ValidationException($i18n->t("The email address is unreachable."));
    }

    return $this;
  }

}
