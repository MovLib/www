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
namespace MovLib\Presenter;

use \MovLib\Exception\UserException;
use \MovLib\Model\UserModel;
use \MovLib\Presenter\AbstractPresenter;
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedMailer;
use \MovLib\Utility\DelayedMethodCalls;
use \MovLib\Utility\HTTP;
use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractView;
use \MovLib\View\HTML\AlertView;
use \MovLib\View\HTML\User\UserLoginView;
use \MovLib\View\HTML\User\UserRegisterView;
use \MovLib\View\HTML\User\UserResetPasswordView;
use \MovLib\View\HTML\User\UserSettingsView;

/**
 * @todo Description.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserPresenter extends AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * User model of the profile that we are currently displaying.
   *
   * @var \MovLib\Model\UserModel
   */
  public $profile;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct() {
    return $this
      ->{__FUNCTION__ . $this->getAction()}()
      ->setPresentation()
    ;
  }

  /**
   * Render the sign in form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructLogin() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      HTTP::redirect("/", 302);
    }
    // Ensure we are using the correct route (this method is called from other constructors in this presenter as well).
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/login");
    $view = new UserLoginView($this);
    if (isset($_POST["submitted"])) {
      $mail = filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL);
      if (($error = DelayedMailer::validateEmail($mail))) {
        $view->setAlert($error, AbstractView::ALERT_SEVERITY_ERROR);
        $view->formInvalid["mail"] = true;
      }
      elseif (!($pass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)) || empty($pass)) {
        $view->setAlert($i18n->t("The password field is mandatory."), AbstractView::ALERT_SEVERITY_ERROR);
        $view->formInvalid["pass"] = true;
      }
      else {
        try {
          $user->login($mail, $pass);
        } catch (UserException $e) {
          $view->setAlert([
            "title" => $i18n->t("Login Error"),
            "message" =>
              "<p>{$i18n->t("We either don’t know this email address or the password you entered contains errors.")}</p>" .
              "<p>{$i18n->t("Please try again, or visit the {0} to create a new account.", [
                $view->a($i18n->r("/user/register"), $i18n->t("registration page"))
              ])}</p>"
          ], AbstractView::ALERT_SEVERITY_ERROR, true);
          $view->formInvalid["mail"] = $view->formInvalid["pass"] = true;
        }
      }
    }
    $this->view = $view;
    return $this;
  }

  /**
   * Render the public profile page of the user identified by ID or name.
   *
   * @return $this
   */
  private function __constructProfile() {
    try {
      if (isset($_SERVER["USER_ID"])) {
        $this->profile = new UserModel("user_id", filter_input(INPUT_SERVER, "USER_ID", FILTER_SANITIZE_NUMBER_INT));
      }
      elseif (isset($_SERVER["USER_NAME"])) {
        $this->profile = new UserModel("name", filter_input(INPUT_SERVER, "USER_NAME", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW));
      }
      // If this user's account is disabled, tell the client about it and exit (no need to redirect).
      if ($this->profile->deleted === true) {
        $this->view = new GoneView($this);
        // @todo Display text to recreate account
        // @todo Check if account was deleted forever
        $this->view->content = "";
        return $this;
      }
      // Check if the requested URI is a perfect match to what we want to have.
      if (($profileRoute = $this->profile->getProfileRoute()) && $_SERVER["REQUEST_URI"] !== $profileRoute) {
        HTTP::redirect($profileRoute);
      }
      // Everything looks good, render the profile.
      return $this->setPresentation("User\\UserShow");
    } catch (UserException $e) {
      return $this->setPresentation("Error\\NotFound");
    }
  }

  /**
   * Render the sign up form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructRegister() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      HTTP::redirect("/", 302);
    }
    if (isset($_POST["submitted"])) {
      $view = new UserRegisterView($this);
      // Validate the submitted mail.
      $mail = filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL);
      if (($error = DelayedMailer::validateEmail($mail))) {
        $view->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
        $view->formInvalid["mail"] = true;
      }
      // Validate the submitted name.
      $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
      if (($error = UserModel::validateName($name))) {
        $view->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
        $view->formInvalid["name"] = true;
      }
      elseif ($user->exists("name", $name)) {
        $view->setAlert($i18n->t("The name {0} is already taken.", [ String::placeholder($name) ]));
        $view->formInvalid["name"] = true;
      }
      // If we did not find any error, send the activation link via mail to the user and tell him to check his inbox.
      if (!isset($error)) {
        // Do not tell the user that we already have this mail, otherwise it would be possible for an attacker to find
        // out which mails we have in our system. Instead we send a message to the user this mail belongs to.
        if ($user->exists("mail", $mail)) {
          DelayedMailer::stackMethod("setActivationMailExists", [ $mail ]);
        }
        // If this is a valid new registration generate a unique activation link and insert the user's data into our
        // temporary database table (which will be deleted after 24 hours). Also send the user a mail explaining what
        // he has to do.
        else {
          $params = [ Crypt::encrypt("[{$name},{$mail}]"), $name, $mail ];
          DelayedMethodCalls::stack($user, "insertRegistrationData", $params);
          DelayedMailer::stackMethod("setActivationMail", $params);
        }
        $view = new AlertView($this, $view->title);
        $view->setAlert(
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($mail) ])}</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS
        );
        $view->content =
          "<div class='container'><p class='text-center text-small'>{$i18n->t(
            "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
            [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
          )}</p></div>";
      }
    }
    elseif (isset($_SERVER["TOKEN"])) {
      $view = $this->activateOrResetPassword($i18n->r("/user/register"), $i18n->t("registration page"));
    }
    else {
      $view = new UserRegisterView($this);
    }
    $this->view = $view;
    return $this;
  }

  /**
   * Render the reset password page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructResetPassword() {
    global $i18n, $user;
    $view = new UserResetPasswordView($this);
    if (isset($_POST["submitted"])) {
      $mail = filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL);
      if (($error = DelayedMailer::validateEmail($mail))) {
        $view->setAlert($error, AbstractView::ALERT_SEVERITY_ERROR, true);
      }
      if (!$error) {
        if ($user->exists("mail", $mail) === true) {
          $params = [ Crypt::randomHash(), $mail ];
          DelayedMethodCalls::stack($user, "insertResetPasswordData", $params);
          DelayedMailer::stackMethod("setPasswordReset", $params);
        }
        $view = new AlertView($this, $view->title);
        $view->setAlert(
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($mail) ])}</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS
        );
        $view->content =
          "<div class='container'><p class='text-center text-small'>{$i18n->t(
            "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
            [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
          )}</p></div>";
      }
    }
    elseif (isset($_SERVER["TOKEN"])) {
      $view = $this->activateOrResetPassword($i18n->r("/user/reset-password"), $i18n->t("reset password page"));
    }
    $this->view = $view;
    return $this;
  }

  /**
   * Render the settings page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructSettings() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      $this->__constructLogin();
      $this->view->setAlert([
        "title" => $i18n->t("Authentication Required"),
        "message" => $i18n->t("You have to log in before you can access your settings page.")
      ], AbstractView::ALERT_SEVERITY_INFO);
      return $this;
    }
    $view = new UserSettingsView($this, $_SERVER["TAB"]);
    $this->view = $view;
    return $this;
  }

  /**
   * Render the currently logged in user's profile.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructShow() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      // @todo Should we display an error site? Or maybe ask the user if he'd like to register a new account?
      HTTP::redirect($i18n->r("/users"), 302);
    }
    return $this;
  }

  /**
   * Helper method to render the user settings page where the user can set his password after registration or a reset
   * password request.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @param string $expiredTokenRoute
   *   The route to the page where the user should go if the token has expired.
   * @param string $expiredTokenRouteText
   *   The text that should be linked.
   * @return mixed
   *   The view to render.
   */
  private function activateOrResetPassword($expiredTokenRoute, $expiredTokenRouteText) {
    global $i18n, $user;
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/settings/password");
    $view = new UserSettingsView($this, "Password");
    if (empty($_SERVER["TOKEN"])) {
      $view = new AlertView($this, $view->title);
      $view->setAlert(
        "<p>{$i18n->t("Your link is missing the authentication token, please go back to the mail we’ve sent you and copy the whole link.")}</p>",
        AbstractView::ALERT_SEVERITY_ERROR,
        true
      );
    }
    elseif (
      ($data = $user->selectTmpData(filter_input(INPUT_SERVER, "TOKEN", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)))
      && (time() - $data["time"]) <= 86400
    ) {
      // Generate a new user friendly password for the user.
      $password = Crypt::randomUserPassword();
      if (empty($data["name"]) && $user->isLoggedIn === false) {
        $user = new UserModel("mail", $data["mail"]);
        $user->sessionStart();
      }
      else {
        if ($user->isLoggedIn === true) {
          return $this->setPresentation("Error\\Forbidden");
        }
        try {
//          $user = $user->createAccount($data["name"], $data["mail"], $password);
          $user = new UserModel("mail", "richard@fussenegger.info");
          $user->sessionStart();
        } catch (UserException $e) {
          $view = new AlertView($this, $view->title);
          $view->setAlert(
            "<p>{$e->getMessage()}</p>" .
            "<p>{$i18n->t("Seems like somebody was faster than you and has registered an account with your desired data.")}<br>" .
            "{$i18n->t("Please go to the {0} and create a new account.", [ $view->a($expiredTokenRoute, $expiredTokenRouteText) ])}</p>",
            AbstractView::ALERT_SEVERITY_ERROR
          );
          return $view;
        }
      }
      $view->setAlert(
        [ "title" => $i18n->t("Create a password for your account"), "message" =>
          "<p>{$i18n->t("You can now create a password for your account via the following form. Please choose a strong password that is hard to guess.")}</p>" .
          "<p>{$i18n->t("We created the following random password for you:")} <code>{$password}</code></p>" .
          "<p>{$i18n->t("If you like it, write it down and leave this page.")}</p>"
        ],
        AbstractView::ALERT_SEVERITY_INFO,
        true
      );
      $view->formDisabled["current-password"] = true;
      $view->inputValues["current-password"] = $password;
    }
    else {
      $view = new AlertView($this, $view->title);
      $view->setAlert(
        "<p>{$i18n->t("The authentication token has expired.")}</p>" .
        "<p>{$i18n->t("Please go back to the {0} to get a new valid token.", [ $view->a($expiredTokenRoute, $expiredTokenRouteText) ])}</p>",
        AbstractView::ALERT_SEVERITY_WARNING,
        true
      );
    }
    return $view;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Associative array containing the breadcrumb trail for this presenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return array
   */
  public function getBreadcrumb() {
    global $i18n, $user;
    $breadcrumb = [ $user->isLoggedIn === true
      ? [ $i18n->r("/user"), $i18n->t("Profile"), [ "title" => $i18n->t("Go to your user profile.") ] ]
      : [ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ] ]
    ];
    $additionalBreadcrumbsMethod = __FUNCTION__ . $this->getAction();
    if (method_exists($this, $additionalBreadcrumbsMethod)) {
      return $this->{$additionalBreadcrumbsMethod}($breadcrumb);
    }
    return $breadcrumb;
  }

}
