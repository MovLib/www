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

use \MovLib\Model\UserModel;
use \MovLib\View\HTML\AbstractFormView;

/**
 * Sign in (login) form.
 *
 * @link http://uxdesign.smashingmagazine.com/2011/11/08/extensive-guide-web-form-usability/
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserSignInView extends AbstractFormView {

  /**
   * The user presenter controlling this view.
   *
   * @var \MovLib\Presenter\UserPresenter
   */
  protected $presenter;

  /**
   * {@inheritdoc}
   */
  public function __construct($presenter) {
    global $i18n;
    parent::__construct($presenter, $i18n->t("Sign in"));
    $this->addStylesheet("/assets/css/modules/user.css");
  }

  /**
   * {@inheritdoc}
   */
  public function getFormContent() {
    global $i18n;
    return
      "<div class='row'>" .
        "<div class='span span--6 offset--3'>" .
          "<p>" .
            "<label for='email'>{$i18n->t("Email address")}</label>" .
            "<input autofocus class='input input-text input--block-level' id='email' maxlength='" . UserModel::MAIL_MAX_LENGTH . "' name='email' placeholder='{$i18n->t("Enter your email address")}' required role='textbox' tabindex='{$this->getTabindex()}' title='{$i18n->t("Plase enter the email address you used to register.")}' type='email' value='{$this->presenter->getPostValue("email")}'>" .
          "</p>" .
          "<p>" .
            "<small>{$this->a("/user/reset-password", "Reset your password", [ "class" => "pull-right", "title" => $i18n->t("Click this link if you forgot your password."), ])}</small>" .
            "<label for='password'>{$i18n->t("Password")}</label>" .
            "<input class='input input-text input--block-level' id='password' name='password' placeholder='{$i18n->t("Enter your password")}' role='password' tabindex='{$this->getTabindex()}' title='{$i18n->t("Please enter your secret password in this field.")}' type='password'>" .
          "</p>" .
          "<p>" .
            "<label class='checkbox' for='remember' title='{$i18n->t("Check this box to stay signed in for the next 30 days.")}'>" .
            "<input class='input input-checkbox' id='remember' name='remember' tabindex='{$this->getTabindex()}' type='checkbox' value='remember'>" .
            " {$i18n->t("Keep me signed in (for up to 30 days)")}</label>" .
          "</p>" .
          "<p>" .
            "<button class='button button--success button--large input input-submit' name='submitted' tabindex='{$this->getTabindex()}' title='{$i18n->t("Click here after you have filled out all fields.")}' type='submit'>{$i18n->t("Sign in")}</button>" .
          "</p>" .
        "</div>" .
      "</div>"
    ;
  }

}
