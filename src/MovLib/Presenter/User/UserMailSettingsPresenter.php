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

use \MovLib\Model\UserModel;
use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedMailer;
use \MovLib\Utility\DelayedMethodCalls;
use \MovLib\Utility\String;
use \MovLib\View\HTML\Alert;
use \MovLib\View\HTML\Input\MailInput;
use \MovLib\View\HTML\Input\PasswordInput;
use \MovLib\View\HTML\User\UserMailSettingsView;
use \MovLib\View\Mail\User\UserMailChangeMail;

/**
 * Takes care of user mail settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserMailSettingsPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user mail settings presenter.
   *
   * @global \MovLib\Model\SessionModel $user
   */
  public function __construct() {
    global $user;
    $this->checkAuthorization();
    $this->profile = new UserModel(UserModel::FROM_ID, $user->id);
    new UserMailSettingsView($this, [
      new MailInput(),
      new PasswordInput(),
    ]);
    if (isset($_GET["token"])) {
      $this->validateMailChange();
    }
  }

  /**
   * Continue validation after the basic form elements where validated by the view itself.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   */
  public function validate() {
    global $i18n;
    // Convenience
    $newMail = $this->view->formElements["mail"]->value;
    if ($newMail == $this->profile->mail) {
      $this->view->addAlert(new Alert($i18n->t("You are already using this email address.")));
      return $this;
    }
    if (!empty($this->profile->select("SELECT `user_id` FROM `users` WHERE `user_id` != ? AND (`mail` = ? OR `init` = ?) LIMIT 1", "dss", [ $this->profile->userId, $newMail, $newMail ]))) {
      $this->view->addAlert(new Alert($i18n->t("The entered email address is already registered at MovLib, please choose another one."), [ "severity" => Alert::SEVERITY_ERROR ]));
      return $this;
    }
    if (password_verify($this->view->formElements["pass"]->value, $this->profile->pass) === false) {
      $this->view->addAlert(new Alert($i18n->t("The entered password is not valid, please try again."), [ "severity" => Alert::SEVERITY_ERROR ]));
      return $this;
    }
    $hash = Crypt::randomHash();
    DelayedMethodCalls::stack($this->profile, "prepareMailChange", [ $hash, $newMail ]);
    DelayedMailer::stack(new UserMailChangeMail($hash, $this->profile->name, $newMail));
    $this->view
      ->addAlert(new Alert($i18n->t("A mail with further instructions has been sent to {0}.", [ String::placeholder($newMail) ]), [
        "block"    => true,
        "title"    => $i18n->t("Successfully requested mail change"),
        "severity" => Alert::SEVERITY_SUCCESS,
      ]))
      ->addAlert(new Alert($i18n->t("You have to sign in with your old email address until you’ve successfully confirmed your mail change via the link that we’ve just sent you."), [
        "block"    => true,
        "title"    => $i18n->t("Important Notice"),
        "severity" => Alert::SEVERITY_INFO
      ]))
    ;
    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's mail.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   * @return this
   */
  private function validateMailChange() {
    global $i18n, $user;
    if (empty($_GET["token"]) || strlen($_GET["token"]) !== Crypt::HASH_LENGTH_SHA512) {
      $this->view->addAlert(new Alert("<p>{$i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.")}</p>", [
        "block"    => true,
        "title"    => $i18n->t("Invalid Authentication Token"),
        "severity" => Alert::SEVERITY_ERROR,
      ]));
      return $this;
    }
    $this->profile = new UserModel(UserModel::FROM_ID, $user->id);
    if (!($data = $this->profile->getTemporaryMailChangeData($_GET["token"]))) {
      $this->view->addAlert(new Alert(
        "<p>{$i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.")}</p>" .
        "<p>{$i18n->t("Your authentication token has expired if it is older than 24 hours. If so, please request a new mail change below.")}</p>",
        [
          "block"    => true,
          "title"    => $i18n->t("Invalid Authentication Token"),
          "severity" => Alert::SEVERITY_ERROR,
        ]
      ));
      return $this;
    }
    if ($data["id"] !== $user->id) {
      $user->destroySession();
      http_response_code(401);
      // http://stackoverflow.com/a/1088127/1251219
      header("WWW-Authenticate: MovLib location=\"{$i18n->r("/user/login")}\"");
      new AlertView($this, $this->view->title);
      $this->view->addAlert(
        $i18n->t("Something is odd, therefor we logged you out. Please double check the authentication token you used and sign in with your old email address before trying again."),
        [
          "block"    => true,
          "title"    => $i18n->t("Wrong Authentication Token"),
          "severity" => Alert::SEVERITY_ERROR,
        ]
      );
      return $this;
    }
    // Do not catch the possible database exception at this point. The user exists and therefor changing the mail should
    // not fail. If it fails, let the exception bubble up and display an internal server error message.
    $this->profile->updateMail($data["mail"]);
    $this->view->addAlert(new Alert(
      "<p>{$i18n->t("Your email address was successfully updated to {0}.", [ String::placeholder($data["mail"]) ])}</p>",
      [
        "block"    => true,
        "severity" => Alert::SEVERITY_SUCCESS,
        "title"    => $i18n->t("Successfully Changed Email Address"),
      ]
    ));
    return $this;
  }

}
