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
use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractView;

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
    /* @var $userResetPasswordView \MovLib\View\HTML\User\UserResetPasswordView */
    $userResetPasswordView = $this->getView("User\\UserResetPassword");
    if (isset($_POST["submitted"])) {
      if (isset($_POST["mail"]) && ($error = DelayedMailer::validateEmail($_POST["mail"]))) {
        $userResetPasswordView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (!$error) {
        DelayedMailer::sendPasswordReset($_POST["mail"]);
        $this->showSingleAlertAlertView(
          $userResetPasswordView->getTitle(),
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::checkPlain($_POST["mail"]) ])}</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS,
          true
        );
      }
    }
    return $this;
  }

  /**
   * Render a user's profile.
   *
   * @return $this
   */
  private function __constructShow() {
    try {
      if (isset($_SERVER["USER_ID"])) {
        $this->profile = new UserModel("user_id", $_SERVER["USER_ID"]);
      }
      elseif (isset($_SERVER["USER_NAME"])) {
        $this->profile = new UserModel("name", $_SERVER["USER_NAME"]);
      }
      // If this user's account is disabled, tell the client about it and exit (no need to redirect).
      if ($this->profile->deleted === true) {
        $this->setView("Error\\Gone");
        // @todo Display text to recreate account
        // @todo Check if account was deleted forever
        $this->view->setContent("Hello there!");
        return $this->setPresentation();
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
    /* @var $userRegisterView \MovLib\View\HTML\User\UserRegisterView */
    $userRegisterView = $this->getView("User\\UserRegister");
    if (isset($_POST["submitted"])) {
      // Validate email address and check if it is still available.
      if (isset($_POST["mail"])) {
        if (($error = DelayedMailer::validateEmail($_POST["mail"]))) {
          $userRegisterView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
        }
        elseif ($user->exists("mail", $_POST["mail"])) {
          $userRegisterView->setAlert(
            $i18n->t("The email address {0} is already registered.", [ "<em>{$_POST["mail"]}</em>" ]) .
            " " .
            $userRegisterView->a("/user/reset-password", "Have you forgotten your password?")
          );
        }
      }
      // Validate username and check if it is still available.
      if (isset($_POST["name"])) {
        if (($error = UserModel::validateName($_POST["name"]))) {
          $userRegisterView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
        }
        elseif ($user->exists("name", $_POST["name"])) {
          $userRegisterView->setAlert($i18n->t("The name {0} is already taken.", [ "<em>{$_POST["name"]}</em>" ]));
        }
      }
      // If we did not find any error, send the activation link via mail to the user and tell him to check his inbox.
      if (!isset($error)) {
        $activationHash = Crypt::getRandomHash();
        // Delay the insert, the mail will have a much longer roundtrip than our delayed insert.
        DelayedMethodCalls::stack($user, "register", [ $activationHash, $_POST["name"], $_POST["mail"] ]);
        // Delay the sending of the mail as well, this is a valid registration and we want to deliver the response asap.
        DelayedMailer::sendMail(
          $_POST["mail"],
          $i18n->t("Welcome to MovLib!"),
          $i18n->t(
"Hi {0}!

Thank you for registering at MovLib. You may now log in by clicking this link or copying and pasting it to your browser:

{1}

This link can only be used once to log in and will lead you to a page where you can set your password.

After setting your password, you will be able to log in at MovLib in the future using:

email address: {2}
password: Your password

— MovLib team",
            [ $_POST["name"], $i18n->r("/user/activate-{0}", [ $activationHash ]), $_POST["mail"] ]
          )
        );
        return $this->showSingleAlertAlertView(
          $userRegisterView->getTitle(),
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::checkPlain($_POST["mail"]) ])}</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS,
          true
        );
      }
    }
    return $this->setPresentation("User\\UserRegister");
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
    if (method_exists($this, __FUNCTION__ . $this->getAction())) {
      return $this->{__FUNCTION__ . $this->getAction()}();
    }
    if ($user->isLoggedIn === true) {
      return [[ $i18n->r("/user"), $i18n->t("Profile"), [ "title" => $i18n->t("Go to your user profile.") ]]];
    }
    return [[ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ]]];
  }

}
