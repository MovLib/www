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
use \MovLib\View\HTML\Form;

/**
 * User register form.
 *
 * @link http://uxdesign.smashingmagazine.com/2011/11/08/extensive-guide-web-form-usability/
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserRegisterView extends AbstractPageView {

  /**
   * The user register form.
   *
   * @var \MovLib\View\HTML\Form
   */
  private $form;

  /**
   * Instantiate user register view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\UserPresenter $presenter
   *   The presenting presenter.
   * @param \MovLib\View\HTML\Input\MailInput $mail
   *   The mail input.
   * @param \MovLib\View\HTML\Input\TextInput $name
   *   The username input.
   */
  public function __construct($presenter, $mail, $name) {
    global $i18n;
    $this->init($presenter, $i18n->t("Register"));
    $this->stylesheets[] = "modules/user.css";
    $mail->attributes[] = "autofocus";
    $mail->attributes["class"] = $name->attributes["class"] = "input--block-level";
    $name->attributes["placeholder"] = $i18n->t("Enter your desired username");
    $name->attributes["title"] = $i18n->t("Please enter your desired username in this field.");
    $name->label = $i18n->t("Username");
    $this->form = new Form(
      "register",
      $this->presenter,
      [ $mail, $name ],
      [ "class" => "span span--6 offset--3" ],
      [ "class" => "button--success button--large", "value" => $i18n->t("Sign Up") ]
    );
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    global $i18n;
    return
      "<div class='container'><div class='row'>{$this->form->open()}" .
        "<small class='form-help'><a href='{$i18n->r("/user/login")}'>{$i18n->t("Already have an account?")}</a></small>" .
        "<p>{$this->form->elements["mail"]}</p>" .
        "<p>{$this->form->elements["name"]}</p>" .
      "{$this->form->close(false)}</div></div>"
    ;
  }

}
