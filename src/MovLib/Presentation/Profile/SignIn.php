<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Profile;

use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * User sign in presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SignIn extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\Users\TraitUsers;


  // ------------------------------------------------------------------------------------------------------------------- Properties
  // The properties are public to allow classes that throw an UnauthorizedException to manipulate them.


  /**
   * The input email form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  public $email;

  /**
   * The input password form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  public $password;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new sign in presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Exception\Client\RedirectSeeOtherException
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // If the user is logged in, but didn't request to be signed out, redirect her or him to the personal dashboard.
    if ($session->isAuthenticated === true) {
      throw new RedirectSeeOtherException($i18n->r("/my"));
    }

    // Start rendering the page.
    $this->init($i18n->t("Sign In"));

    // We need to know the translated version of the sign in route for comparison.
    $routeSignIn = $action = $i18n->r("/profile/sign-in");

    // Snatch the current requested URI if a redirect was requested and no redirect is already active. We have to build
    // the complete target URI to ensure that this presenter will receive the submitted form, but at the same time we
    // want to enable ourself to redirect the user after successful sign in to the page she or he requested.
    if ($kernel->requestURI != $routeSignIn && empty($_GET["redirect_to"])) {
      $_GET["redirect_to"] = $kernel->requestURI;
    }

    if (isset($_GET["redirect_to"])) {
      $action .= "?redirect_to={$_GET["redirect_to"]}";
    }

    // Ensure all views are using the correct path info to render themselves.
    $kernel->requestURI = $routeSignIn;

    $this->email                      = new InputEmail();
    $this->email->setHelp("<a href='{$i18n->r("/profile/reset-password")}'>{$i18n->t("Forgot your password?")}</a>", false);
    $this->password                   = new InputPassword();
    $this->form                       = new Form($this, [ $this->email, $this->password ]);
    $this->form->attributes["action"] = $action;
    $this->form->attributes["class"]  = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit($i18n->t("Sign In"), [
      "class" => "button button--large button--success",
      "title" => $i18n->t("Click here to sign in after you filled out all fields"),
    ]);
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    return "<div class='container'><div class='row'>{$this->form}</div></div>";
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * The redirect exception is thrown if the supplied data is valid. The user will be redirected to her or his personal
   * dashboard. The session exception is thrown if our system isn't able to start a new session at all.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Exception\Client\RedirectSeeOther
   */
  public function validate(array $errors = null) {
    global $i18n, $kernel, $session;
    if ($this->checkErrors($errors) === false) {
      try {
        $session->authenticate($this->email->value, $this->password->value);
      }
      catch (\Exception $e) {
        $this->checkErrors($i18n->t("We either don’t know the email address, or the password was wrong."));
        return $this;
      }

      $kernel->alerts .= new Alert(
        $i18n->t("Successfully Signed In!"),
        $i18n->t("Welcome back {0}!", [ $this->placeholder($session->userName) ]),
        Alert::SEVERITY_SUCCESS
      );

      throw new RedirectSeeOtherException(!empty($_GET["redirect_to"]) ? $_GET["redirect_to"] : $i18n->r("/my"));
    }
    return $this;
  }

}
