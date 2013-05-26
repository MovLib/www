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

use \MovLib\Presenter\AbstractPresenter;
use \MovLib\Utility\AsyncMailer;

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

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this
      ->{__FUNCTION__ . $this->getAction()}()
      ->setOutput()
    ;
  }

  private function initResetPassword() {
    if ($this->user->loggedIn() === true) {
      return $this->setOutput("Error\\Forbidden");
    }
    /* @var $userResetPasswordView \MovLib\View\HTML\User\UserResetPasswordView */
    $userResetPasswordView = $this->getView("User\\UserResetPassword");
    if (filter_has_var(INPUT_POST, $_SERVER["REQUEST_URI"]) === true) {
      $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
      if ($email === null || $email === false) {
        $userResetPasswordView->setAlert(__("The supplied email address is not valid."), null, "error");
        return $this;
      }
      AsyncMailer::resetPassword($email);
      $this->showSingleAlertAlertView(
        $userResetPasswordView->getTitle(),
        "<p>" . __("A password reset email has been sent to <b>{$email}</b>.") . "</p>",
        "success",
        true
      );
    }
    return $this;
  }

  private function initShow() {
    return $this->setOutput("User\\UserShow");
  }

  private function initSignIn() {
    // @todo Active session? NO ACCESS!
    return $this->setOutput("User\\UserSignIn");
  }

  private function initSignUp() {
    // @todo Active session? NO ACCESS!
    return $this->setOutput("User\\UserSignUp");
  }

  /**
   * Display success alert view to the user.
   *
   * @param string $title
   *   The title of the operation that succeeded.
   * @param string $message
   *   The message describing what succeeded.
   * @return $this
   */
  private function showSuccessView($title, $message) {
    return $this->showSingleAlertAlertView($title, $message, "success", true);
  }

}
