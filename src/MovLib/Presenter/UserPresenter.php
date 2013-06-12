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
use \MovLib\View\HTML\User\UserSettingsView;
use \MovLib\View\HTML\User\UserRegisterView;

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
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructLogin() {
    global $user;
    if ($user->isLoggedIn === true) {
      return $this->setPresentation("Error\\Forbidden");
    }
    return $this->setPresentation("User\\UserLogin");
  }

  /**
   * Render the public profile page of the user identified by ID or name.
   *
   * @return $this
   */
  private function __constructProfile() {
    try {
      if (isset($_SERVER["USER_ID"])) {
        $this->profile = new UserModel("user_id", $_SERVER["USER_ID"]);
      }
      elseif (isset($_SERVER["USER_NAME"])) {
        $this->profile = new UserModel("name", $_SERVER["USER_NAME"]);
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
      return $this->setPresentation("Error\\Forbidden");
    }
    if (isset($_POST["submitted"])) {
      $view = new UserRegisterView($this);
      // Validate the submitted mail.
      $mail = $this->getPostValue("mail");
      if (($error = DelayedMailer::validateEmail($mail))) {
        $view->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      // Validate the submitted name.
      $name = $this->getPostValue("name");
      if (($error = UserModel::validateName($name))) {
        $view->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      elseif ($user->exists("name", $name)) {
        $view->setAlert($i18n->t("The name {0} is already taken.", [ String::placeholder($name) ]));
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
          $params = [ Crypt::getRandomHash(), $name, $mail ];
          DelayedMethodCalls::stack($user, "register", $params);
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
    elseif (isset($_SERVER["USER_HASH"])) {
      $view = new UserSettingsView($this);
      $view->tab = "Password";
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
    if ($user->isLoggedIn === true) {
      return $this->setPresentation("Error\\Forbidden");
    }
    $view = new UserResetPasswordView($this);
    if (isset($_POST["submitted"])) {
      $mail = $this->getPostValue("mail");
      if (($error = DelayedMailer::validateEmail($mail))) {
        $view->setAlert($error, AbstractView::ALERT_SEVERITY_ERROR, true);
      }
      if (!$error) {
        if ($user->exists("mail", $mail) === true) {
          $params = [ Crypt::getRandomHash(), $mail ];
          DelayedMethodCalls::stack($user, "resetPassword", $params);
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
    elseif (isset($_SERVER["USER_HASH"])) {
      // @todo Implement reset password checks and view.
    }
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
