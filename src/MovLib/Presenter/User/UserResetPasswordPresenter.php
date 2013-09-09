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
namespace MovLib\Presenter\User;

use \MovLib\Exception\UserException;
use \MovLib\Model\UserModel;
use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedMailer;
use \MovLib\Utility\DelayedMethodCalls;
use \MovLib\View\HTML\Alert;
use \MovLib\View\HTML\Input\MailInput;
use \MovLib\View\HTML\User\UserResetPasswordView;
use \MovLib\View\Mail\User\UserPasswordResetMail;

/**
 * Takes care of user's who want to reset their password.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserResetPasswordPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user reset password presenter.
   */
  public function __construct() {
    new UserResetPasswordView($this, [ (new MailInput())->required() ]);
  }

  /**
   * Continue validation after the basic form elements where validated by the view itself.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   * @param \MovLib\View\HTML\Form $form
   *   The auto-validated form.
   * @return this
   */
  public function validate($form) {
    global $i18n, $user;
    // Try to create a user model from the supplied email address.
    try {
      $userModel = new UserModel(UserModel::FROM_MAIL, $form->elements["mail"]->value);

      // Generate a unique token for this user, insert it into our temporary database and send a mail with instructions.
      $hash = Crypt::randomHash();
      DelayedMethodCalls::stack($userModel, "prepareResetPassword", [ $hash, $form->elements["mail"]->value ]);
      DelayedMailer::stack(new UserPasswordResetMail($hash, $form->elements["mail"]->value));
    }
    // Only tell a logged in user that the email is wrong!
    catch (UserException $e) {
      if ($user->isLoggedIn === true) {
        $this->view->addAlert(new Alert(
          $i18n->t("The email address you entered is not correct, please try again."),
          [ "severity" => Alert::SEVERITY_ERROR ]
        ));
      }
    }
    // Always display the alert view telling the user that we've sent the mail. No matter if we did or not.
    finally {
      new AlertView($this, $this->view->title, "<small>{$i18n->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
      )}</small>");
      $this->view->addAlert(
        $i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($form->elements["mail"]->value) ]),
        Alert::SEVERITY_SUCCESS
      );
    }
    return $this;
  }

}
