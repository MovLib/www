<?php

/* !
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
 * Description of UserResetPasswordView
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserResetPasswordView extends AbstractFormView {

  /**
   * {@inheritdoc}
   */
  public function __construct($presenter) {
    parent::__construct($presenter, __("Reset password"));
    $this->addStylesheet("/assets/css/modules/user.css");
    $this->attributes = [ "class" => "span span--0" ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedFormContent() {
    $emailLabel = __("Email address");
    $emailPlaceholder = __("Enter your email address");
    $emailTitle = __("Plase enter the email address with which you registered your account.");
    $emailValue = $this->presenter->getPostValue("email");

    $submit = __("Reset password");
    $submitTitle = __("Fill in your registered email address and we will generate a new secure password for you.");

    return
      "<div class='page-header--no-border'><h1>{$this->title}</h1></div>" .
      "<p><label for='email'>{$emailLabel}</label><input autofocus class='input input-text input--block-level' id='email' name='email' placeholder='{$emailPlaceholder}' required role='textbox' tabindex='{$this->getTabindex()}' title='{$emailTitle}' type='email' value='{$emailValue}'></p>" .
      "<p><button class='button button--success button--large input input-submit' name='{$_SERVER["REQUEST_URI"]}' tabindex='{$this->getTabindex()}' title='{$submitTitle}' type='submit'>{$submit}</button></p>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedView() {
    return $this->getRenderedViewWithoutFooter("row span--3");
  }

}
