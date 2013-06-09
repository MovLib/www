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
use \MovLib\Utility\DelayedMailer;
use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractView;

/**
 *
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
      if (isset($_POST["email"]) && ($error = DelayedMailer::validateEmail($_POST["email"]))) {
        $userResetPasswordView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (!$error) {
        DelayedMailer::sendPasswordReset($_POST["email"]);
        $this->showSingleAlertAlertView(
          $userResetPasswordView->getTitle(),
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::checkPlain($_POST["email"]) ])}</p>",
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
      // Try to load the user's data from the database.
      if (isset($_SERVER["USER_ID"])) {
        $this->profile = (new UserModel())->__constructFromId($_SERVER["USER_ID"]);
      }
      elseif (isset($_SERVER["USER_NAME"])) {
        $this->profile = (new UserModel())->__constructFromName($_SERVER["USER_NAME"]);
      }
      // If this user's account is disabled, tell the client about it and exit (no need to redirect).
      if ($this->profile->deleted === true) {
        return $this->setPresentation("Error\\Gone");
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
  private function __constructSignIn() {
    global $user;
    if ($user->isLoggedIn === true) {
      return $this->setPresentation("Error\\Forbidden");
    }
    return $this->setPresentation("User\\UserSignIn");
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
  private function __constructSignUp() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      return $this->setPresentation("Error\\Forbidden");
    }
    /* @var $userSignUpView \MovLib\View\HTML\User\UserSignUpView */
    $userSignUpView = $this->getView("User\\UserSignUp");
    if (isset($_POST["submitted"])) {
      if (isset($_POST["email"]) && ($error = DelayedMailer::validateEmail($_POST["email"]))) {
        $userSignUpView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (isset($_POST["username"]) && ($error = User::validateName($_POST["username"]))) {
        $userSignUpView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (!$error) {
        $this->showSingleAlertAlertView(
          $userSignUpView->getTitle(),
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::checkPlain($_POST["email"]) ])}</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS,
          true
        );
      }
    }
    return $this->setPresentation("User\\UserSignUp");
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
      return [[
        "href" => $i18n->r("/user"),
        "text" => $i18n->t("Profile"),
        "title" => $i18n->t("Go to your user profile.")
      ]];
    }
    return [[
      "href" => $i18n->r("/users"),
      "text" => $i18n->t("Users"),
      "title" => $i18n->t("Have a look at our user statistics.")
    ]];
  }

}
