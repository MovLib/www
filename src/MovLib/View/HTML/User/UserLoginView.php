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
use \MovLib\View\HTML\Input\SubmitInput;

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
   * The user login form.
   *
   * @param \MovLib\View\HTML\Form
   */
  private $form;

  /**
   * Instantiate new user login view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\User\UserLoginPresenter $presenter
   *   The presenting presenter.
   * @param \MovLib\View\HTML\Form $form
   *   The user login form.
   */
  public function __construct($presenter, $form) {
    global $i18n;
    $this->form = $form;
    $this->init($presenter, $i18n->t("Login"));
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    global $i18n;

    $this->addClass("span span--6 offset--3", $this->form->attributes);
    $this->addClass("input--block-level", $this->form->elements["mail"]->attributes);
    $this->addAttributes([ "autofocus" ], $this->form->elements["mail"]->attributes);
    $this->addClass("input--block-level", $this->form->elements["pass"]->attributes);
    $this->addClass("button--success button--large", $this->form->actionElements["submit"]->attributes);
    $this->addAttributes([ "value" => $i18n->t("Sign In") ], $this->form->actionElements["submit"]->attributes);

    return
      "<div class='container'><div class='row'>{$this->form->open()}" .
        "<small class='form-help'><a href='{$i18n->r("/user/reset-password")}'>{$i18n->t("Forgot your password?")}</a></small>" .
        "<p>{$this->form->elements["mail"]}</p>" .
        "<p>{$this->form->elements["pass"]}</p>" .
      "{$this->form->close(false)}</div></div>"
    ;
  }

}
