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

use \MovLib\Presentation\Validation\URL;

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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputURL extends \MovLib\Presentation\Partial\FormElement\AbstractInput {

  /**
   * @inheritdoc
   */
  public function __construct($id, $label, array $attributes = null, $help = null, $helpPopup = true) {
    global $i18n;
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
    $this->attributes["pattern"]     = URL::PATTERN;
    $this->attributes["placeholder"] = "http(s)://";
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
    global $i18n;
    $urlValidator                    = new URL($this->value);
    $urlValidator->allowExternal     = $this->attributes["data-allow-external"];
    $urlValidator->checkReachability = $this->attributes["data-check-reachability"];
    $this->value                     = $urlValidator->validate();
    return $this;
  }

}
