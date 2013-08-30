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

use \MovLib\View\HTML\AbstractPageView;

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
class UserLoginView extends AbstractPageView {

  /**
   * The login form.
   *
   * @var \MovLib\View\HTML\FormView
   */
  public $form;

  /**
   * Instantiate new user login view.
   *
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The user presenter controlling this view.
   * @param \MovLib\View\HTML\FormView $form
   *   The login form.
   */
  public function __construct($userPresenter) {
    global $i18n;
    parent::__construct($userPresenter, $i18n->t("Login"));
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->addClass("input--block-level", $this->form->elements["mail"]->attributes);
    $this->addClass("input--block-level", $this->form->elements["pass"]->attributes);
    $this->addClass("button--large", $this->form->actions["submit"]->attributes);
    return
      "<div class='container'>" .
        "<div class='row'>" .
          $this->form->open("span span--6 offset--3") .
            "<p>{$this->form->elements["mail"]}</p>" .
            "<p>{$this->form->elements["pass"]}</p>" .
          $this->form->close(false) .
        "</div>" .
      "</div>"
    ;
  }

}
