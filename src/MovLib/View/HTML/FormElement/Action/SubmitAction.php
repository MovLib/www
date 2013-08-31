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
namespace MovLib\View\HTML\FormElement\Action;

use \MovLib\View\HTML\FormElement\Action\BaseAction;

/**
 * Submit action element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class SubmitAction extends BaseAction {

  /**
   * Instantiate new submit action form element.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to this input element. Please note that the CSS classes <code>
   *   "button button--success"</code> are always applied.
   */
  public function __construct($attributes = null) {
    global $i18n;
    parent::__construct("submit", $attributes);
    $this->addClass("button button--success", $this->attributes);
    if (empty($this->attributes["value"])) {
      $this->attributes["value"] = $i18n->t("Submit");
    }
  }

}
