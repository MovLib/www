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
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The user presenter controlling this view.
   */
  public function __construct($userPresenter) {
    global $i18n;
    parent::__construct($userPresenter, $i18n->t("Login"));
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * {@inheritdoc}
   */
  public function getFormContent() {
    global $i18n;
    return
      "<div class='row'>" .
        "<div class='span span--6 offset--3'>" .
          "<p><label for='mail'>{$i18n->t("Email address")}</label>{$this->input("mail", [
            "autofocus",
            "class"       => "input--block-level",
            "maxlength"   => UserModel::MAIL_MAX_LENGTH,
            "placeholder" => $i18n->t("Enter your email address"),
            "required",
            "title"       => $i18n->t("Plase enter the email address you used to register."),
            "type"        => "email",
          ])}</p>" .
          "<p><small>{$this->a("/user/reset-password", "Reset your password", [
            "class" => "pull-right",
            "title" => $i18n->t("Click this link if you forgot your password."),
          ])}</small><label for='pass'>{$i18n->t("Password")}</label>{$this->input("pass", [
            "class"       => "input--block-level",
            "placeholder" => $i18n->t("Enter your password"),
            "required",
            "title"       => $i18n->t("Please enter your secret password in this field."),
            "type"        => "password",
          ])}</p>" .
          "<p>{$this->submit($i18n->t("Sign in"), $i18n->t("Click here after you’ve filled out all fields."))}</p>" .
        "</div>" .
      "</div>"
    ;
  }

}
