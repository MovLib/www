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

use \MovLib\Entity\User;
use \MovLib\Exception\UserException;
use \MovLib\Presenter\AbstractPresenter;
use \MovLib\Utility\AsyncMail;
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
   * User object of the profile that we are currently displaying.
   *
   * @var \MovLib\Entity\User
   */
  private $profile;


  // ------------------------------------------------------------------------------------------------------------------- Breadcrumb methods


  /**
   * {@inheritdoc}
   */
  public function getBreadcrumb() {
    if (method_exists($this, __FUNCTION__ . $this->getAction())) {
      return $this->{__FUNCTION__ . $this->getAction()}();
    }
    if ($this->user->loggedIn() === true) {
      return [[ "href" => route("user"), "text" => __("Profile"), "title" => __("Your user profile.") ]];
    }
    return [[ "href" => route("users"), "text" => __("Users"), "title" => __("Have a look at our user statistics.") ]];
  }


  // ------------------------------------------------------------------------------------------------------------------- Public getters


  /**
   * Get the user object for the user profile that we are currently displaying.
   *
   * @return \MovLib\Entity\User
   */
  public function getProfile() {
    return $this->profile;
  }

  /**
   * Get the username of the user object that we are currently displaying.
   *
   * @return string
   */
  public function getUsername() {
    if ($this->profile instanceof User) {
      return $this->profile->getName();
    }
    return $this->user->getName();
  }


  // ------------------------------------------------------------------------------------------------------------------- Init (rendering) methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->{__FUNCTION__ . $this->getAction()}()
      ->setOutput()
    ;
  }

  /**
   * Render the reset password page.
   *
   * @return $this
   */
  private function initResetPassword() {
    if ($this->user->loggedIn() === true) {
      return $this->setOutput("Error\\Forbidden");
    }
    /* @var $userResetPasswordView \MovLib\View\HTML\User\UserResetPasswordView */
    $userResetPasswordView = $this->getView("User\\UserResetPassword");
    if (isset($_POST["submitted"])) {
      if (isset($_POST["email"]) && ($error = AsyncMail::validateEmail($_POST["email"]))) {
        $userResetPasswordView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (!$error) {
        AsyncMail::resetPassword($_POST["email"]);
        $this->showSingleAlertAlertView(
          $userResetPasswordView->getTitle(),
          "<p>" . __("An email with further instructions has been sent to %emailAddress.", [ "%emailAddress" => $_POST["email"] ]) . "</p>",
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
  private function initShow() {
    try {
      // Try to load the user's data from the database.
      if (isset($_SERVER["USER_ID"])) {
        $this->profile = (new User())->constructFromId($_SERVER["USER_ID"]);
      }
      elseif (isset($_SERVER["USER_NAME"])) {
        $this->profile = (new User())->constructFromName($_SERVER["USER_NAME"]);
      }
      // If this user's account is disabled, tell the client about it and exit (no need to redirect).
      if ($this->profile->getStatus() === false) {
        return $this->setOutput("Error\\Gone");
      }
      // Check if the requested URI is a perfect match to what we want to have.
      $profileRoute = $this->profile->getProfileRoute();
      if ($_SERVER["REQUEST_URI"] !== "/{$profileRoute}") {
        HTTP::redirect($profileRoute);
      }
      // Everything looks good, render the profile.
      return $this->setOutput("User\\UserShow");
    } catch (UserException $e) {
      return $this->setOutput("Error\\NotFound");
    }
  }

  /**
   * Render the sign in form.
   *
   * @return $this
   */
  private function initSignIn() {
    if ($this->user->loggedIn() === true) {
      return $this->setOutput("Error\\Forbidden");
    }
    return $this->setOutput("User\\UserSignIn");
  }

  /**
   * Render the sign up form.
   *
   * @return $this
   */
  private function initSignUp() {
    if ($this->user->loggedIn() === true) {
      return $this->setOutput("Error\\Forbidden");
    }
    /* @var $userSignUpView \MovLib\View\HTML\User\UserSignUpView */
    $userSignUpView = $this->getView("User\\UserSignUp");
    if (isset($_POST["submitted"])) {
      if (isset($_POST["email"]) && ($error = AsyncMail::validateEmail($_POST["email"]))) {
        $userSignUpView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (isset($_POST["name"]) && ($error = User::validateName($_POST["name"]))) {
        $userSignUpView->setAlert($error, null, AbstractView::ALERT_SEVERITY_ERROR);
      }
      if (!$error) {
        $this->showSingleAlertAlertView(
          $userSignUpView->getTitle(),
          "<p>" . __("An email with further instructions has been sent to %emailAddress.", [ "%emailAddress" => $_POST["email"] ]) . "</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS,
          true
        );
      }
    }
    return $this->setOutput("User\\UserSignUp");
  }

}
