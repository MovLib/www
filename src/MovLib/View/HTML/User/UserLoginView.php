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
namespace MovLib\View\HTML\User;

use \MovLib\View\HTML\AbstractFormView;

/**
 * User login form.
 *
 * @link http://uxdesign.smashingmagazine.com/2011/11/08/extensive-guide-web-form-usability/
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserLoginView extends AbstractFormView {

  /**
   * Instantiate new user login view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The user presenter controlling this view.
   * @param array $elements
   *   Numeric array of form elements that should be attached to this view.
   */
  public function __construct($userPresenter, $elements) {
    global $i18n;
    parent::__construct($userPresenter, $i18n->t("Login"), $elements);
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='container'><div class='row'>{$this->formOpen("span span--6 offset--3")}<p>{$this->formElements["mail"]}</p><p>{$this->formElements["pass"]}</p>{$this->formClose(false)}</div></div>";
  }

}
