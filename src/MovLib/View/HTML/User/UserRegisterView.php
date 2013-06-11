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
 * User registration form.
 *
 * @link http://uxdesign.smashingmagazine.com/2011/11/08/extensive-guide-web-form-usability/
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserRegisterView extends AbstractFormView {

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
    parent::__construct($presenter, $i18n->t("Register"));
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
            "<label for='mail'>{$i18n->t("Email address")}</label>" .
            "<input autofocus class='input input-text input--block-level' id='mail' maxlength='" . UserModel::MAIL_MAX_LENGTH . "' name='mail' placeholder='{$i18n->t("Enter your email address")}' required role='textbox' tabindex='{$this->getTabindex()}' title='{$i18n->t("Plase enter your preferred email address.")}' type='email' value='{$this->presenter->getPostValue("mail")}'>" .
          "</p>" .
          "<p>" .
            "<label for='name'>{$i18n->t("Username")}</label>" .
            "<input class='input input-text input--block-level' id='name' maxlength='" . UserModel::NAME_MAX_LENGTH . "' name='name' placeholder='{$i18n->t("Enter your username")}' required role='textbox' tabindex='{$this->getTabindex()}' title='{$i18n->t("Please enter your desired username.")}' type='text' value='{$this->presenter->getPostValue("name")}'>" .
          "</p>" .
          "<p>" .
            "<button class='button button--success button--large input input-submit' name='submitted' tabindex='{$this->getTabindex()}' title='{$i18n->t("Click this button after you’ve filled out all fields to create a new account.")}' type='submit'>{$i18n->t("Sign up")}</button>" .
          "</p>" .
        "</div>" .
      "</div>"
    ;
  }

}
