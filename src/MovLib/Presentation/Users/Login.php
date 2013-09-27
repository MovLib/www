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
namespace MovLib\Presentation\Users;

use \MovLib\Exception\RedirectException;
use \MovLib\Exception\SessionException;
use \MovLib\Exception\UserException;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * User login presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Login extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\Users\TraitUsers;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The input email form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  private $email;

  /**
   * The page's form.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  private $form;

  /**
   * The input password form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  private $password;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new user login presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\RedirectException
   */
  public function __construct() {
    global $i18n, $session;

    // Translate the sign out route, so we can check if the current page is the sign out page.
    $routeLogout = $i18n->r("/profile/sign-out");

    // If the user is logged in, but didn't request to be signed out, redirect her or him to the personal dashboard.
    if ($session->isAuthenticated === true && $_SERVER["PATH_INFO"] != $routeLogout) {
      throw new RedirectException($i18n->r("/my"), 302);
    }

    // Start rendering the page.
    $this->init($i18n->t("Login"));

    // Now we also need to know the translated version of the login route.
    $routeLogin = $action = $i18n->r("/users/login");

    // Snatch the current requested URI if a redirect was requested and no redirect is already active. We have to build
    // the complete target URI to ensure that this presenter will receive the submitted form, but at the same time we
    // want to enable ourself to redirect the user after successful sign in to the page she or he requested.
    if ($_SERVER["PATH_INFO"] != $routeLogin && $_SERVER["PATH_INFO"] != $routeLogout) {
      if (empty($_GET["redirect_to"])) {
        $_GET["redirect_to"] = $_SERVER["REQUEST_URI"];
      }
      $_GET["redirect_to"] = rawurlencode($_GET["redirect_to"]);
      $action .= "?redirect_to={$_GET["redirect_to"]}";
    }

    // @todo max-length
    $this->email = new InputEmail("email", [ "autofocus" ]);
    $this->email->required();
    $this->email->setHelp("<a href='{$i18n->r("/users/reset-password")}'>{$i18n->t("Forgot your password?")}</a>", false);

    $this->password = new InputPassword("password");

    $this->form = new Form($this, [ $this->email, $this->password ]);
    $this->form->attributes["action"] = $action;
    $this->form->attributes["class"] = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--large button--success",
      "title" => $i18n->t("Click here to sign in after you filled out all fields."),
      "value" => $i18n->t("Sign In"),
    ]);

    // If the user requested to be signed out, do so.
    if ($session->isAuthenticated === true && $_SERVER["PATH_INFO"] == $routeLogout) {
      $session->destroy();
      $alert = new Alert($i18n->t("We hope to see you again soon."));
      $alert->severity = Alert::SEVERITY_SUCCESS;
      $alert->title = $i18n->t("You’ve been signed out successfully.");
      $this->alerts .= $alert;
    }

    // Ensure all views are using the correct path info to render themselves.
    $_SERVER["PATH_INFO"] = $routeLogin;
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
   * @global \MovLib\Data\Session $session
   * @return this
   * @throws \MovLib\Exception\RedirectException
   * @throws \MovLib\Exception\SessionException
   */
  public function validate() {
    global $i18n, $session;
    try {
      $session->authenticate($this->email->value, $_POST[$this->password->id]);

      // Ensure that the user know's that the log in succeded.
      $success = new Alert($i18n->t("Login was successful."));
      $success->title = $i18n->t("Welcome back {0}!", [ $this->placeholder($session->userName) ]);
      $success->severity = Alert::SEVERITY_SUCCESS;
      $session->alerts .= $success;

      // Redirect the user to the requested redirect destination and if none was set to the personalized dashboard.
      throw new RedirectException(!empty($_GET["redirect_to"]) ? $_GET["redirect_to"] : $i18n->r("/my"), 302);
    }
    // Never tell the person who's trying to sing in which value was wrong. Both attributes are considered a secret and
    // should never be exposed by our application itself.
    catch (SessionException $e) {
      $error = new Alert($i18n->t("We either don’t know the email address, or the password was wrong."));
      $error->severity = Alert::SEVERITY_ERROR;
      $this->alerts .= $error;
    }
    // Account has been deactivated!
    catch (UserException $e) {
      throw new RedirectException($i18n->r("/profile/deactivated"), 302, $e);
    }
    return $this;
  }

}
