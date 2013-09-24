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

use \MovLib\Presentation\Validation\EmailAddress;

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
    $this->attributes["maxlength"] = EmailAddress::MAX_LENGTH;
    $this->attributes["pattern"]   = EmailAddress::PATTERN;
    $this->attributes["title"]     =
      $i18n->t("An email address in the format [local]@[host].[tld] with a maximum of {0} characters", [
        EmailAddress::MAX_LENGTH
      ])
    ;
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
    $this->value = (new EmailAddress($this->value))->validate();
    return $this;
  }

}
