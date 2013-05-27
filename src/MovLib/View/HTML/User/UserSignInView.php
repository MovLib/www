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
    parent::__construct($presenter, __("Sign in"));
    $this->addStylesheet("/assets/css/modules/user.css");
    $this->attributes = [ "class" => "span span--0" ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedFormContent() {
    $emailLabel = __("Email address");
    $emailPlaceholder = __("Enter your email address");
    $emailTitle = __("Plase enter the email address you used to register.");
    $emailValue = $this->presenter->getPostValue("email");

    $passwordLabel = __("Password");
    $passwordPlaceholder = __("Enter your password");
    $passwordTitle = __("Please enter your secret !sitename password in this field.", [ "!sitename" => SITENAME ]);

    $remember = __("Keep me signed in (for up to 30 days)");
    $rememberTitle = __("Check this box to stay signed in for the next 30 days.");

    $submit = __("Sign in");
    $submitTitle = __("Click here after you have filled out all fields.");

    $resetPasswordLink = $this->a(
      route("user/reset-password"),
      __("Reset your password"),
      [ "class" => "pull-right", "title" => __("Click this linke if you forgot your password.") ]
    );

    return
      "<div class='page-header--no-border'><h1>{$this->title}</h1></div>" .
      "<p><label for='email'>{$emailLabel}</label><input autofocus class='input input-text input--block-level' id='email' name='email' placeholder='{$emailPlaceholder}' required role='textbox' tabindex='{$this->getTabindex()}' title='{$emailTitle}' type='email' value='{$emailValue}'></p>" .
      "<p><small>{$resetPasswordLink}</small><label for='password'>{$passwordLabel}</label><input class='input input-text input--block-level' id='password' name='password' placeholder='{$passwordPlaceholder}' role='password' tabindex='{$this->getTabindex()}' title='{$passwordTitle}' type='password'></p>" .
      "<p><label class='checkbox' for='remember' title='{$rememberTitle}'><input class='input input-checkbox' id='remember' name='remember' tabindex='{$this->getTabindex()}' type='checkbox' value='remember'> {$remember}</label></p>" .
      "<p><button class='button button--success button--large input input-submit' name='submitted' tabindex='{$this->getTabindex()}' title='{$submitTitle}' type='submit'>{$submit}</button></p>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderedView() {
    return $this->getRenderedViewWithoutFooter("row span--3");
  }

}
