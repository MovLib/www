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
use \MovLib\Utility\Validator;
use \MovLib\View\HTML\Alert;
use \MovLib\View\HTML\AlertView;
use \MovLib\View\HTML\Input\MailInput;
use \MovLib\View\HTML\Input\TextInput;
use \MovLib\View\HTML\Redirect;
use \MovLib\View\HTML\User\UserRegisterView;
use \MovLib\View\Mail\User\UserRegisterMail;

/**
 * Handles user registrations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserRegisterPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user register form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   */
  public function __construct() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      $this->view = new Redirect($i18n->r("/my"), 302);
      return;
    }
    new UserRegisterView(
      $this,
      (new MailInput())->required(),
      (new TextInput("name", [ "maxlength" => $GLOBALS["movlib"]["max_length_username"] ]))->required()
    );
  }

  /**
   * Continue validation after the basic form elements where validated by the view itself.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\View\HTML\Form $form
   *   The already validated user registration form.
   * @return this
   */
  public function validate($form) {
    global $i18n;
    $errors = null;
    // Validate the username.
    if (substr($_POST[$form->elements["name"]->id], 0, 1) == " ") {
      $errors[] = $i18n->t("The username cannot begin with a space.");
    }
    if (substr($_POST[$form->elements["name"]->id], -1) == " ") {
      $errors[] = $i18n->t("The username cannot end with a space.");
    }
    if (strpos($_POST[$form->elements["name"]->id], "  ") !== false) {
      $errors[] = $i18n->t("The username cannot contain multiple spaces in a row.");
    }
    if (($nameFiltered = Validator::string($_POST[$form->elements["name"]->id])) === false) {
      $errors[] = $i18n->t("The username contains one or more illegal character.");
    }
    if (mb_strlen($nameFiltered) > $GLOBALS["movlib"]["max_length_username"]) {
      $errors[] = $i18n->t("The username is too long: it must be {1,number,integer} characters or less.", [ $GLOBALS["movlib"]["max_length_username"] ]);
    }
    // @todo The blacklist's content must be translated along with the routes.
    $blacklist = json_decode(file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/conf/username-blacklist.json"));
    $c = count($blacklist);
    for ($i = 0; $i < $c; ++$i) {
      if ($blacklist[$i] == mb_strtolower($nameFiltered)) {
        $errors[] = $i18n->t("The username {0} is a system reserved word, please choose another one.", [ String::placeholder($nameFiltered) ]);
        break;
      }
    }
    if ($errors) {
      $errors = implode("<br>", $errors);
      $this->view->addAlert(new Alert("<p>{$errors}</p>", [ "severity" => Alert::SEVERITY_ERROR ]));
      return;
    }
    // We have to query the database for some checks, create empty user model instance.
    $userModel = new UserModel();
    if (!empty($userModel->select("SELECT `user_id` FROM `users` WHERE `name` = ? LIMIT 1", "s", [ $nameFiltered ]))) {
      $this->view->addAlert(new Alert(
        "<p>{$i18n->t("The username {0} is already taken, please choose another one.", [ String::placeholder($nameFiltered) ])}</p>",
        [ "severity" => Alert::SEVERITY_ERROR ]
      ));
      return;
    }
    // Don't tell the user that we already have this mail, otherwise it would be possible for an attacker to find
    // out which mails we have in our system. Instead we send a message to the user this mail belongs to.
    if (!empty($userModel->select("SELECT `user_id` FROM `users` WHERE `mail` = ? OR `init` = ? LIMIT 1", "ss", [ $form->elements["mail"]->value, $form->elements["mail"]->value ]))) {
      DelayedMailer::stack(new UserRegisterExistingMail($form->elements["mail"]->value));
    }
    // If this is a valid new registration generate a unique activation link and insert the user's data into our
    // temporary database table (which will be deleted after 24 hours). Also send the user a mail explaining what
    // to do to activate the account.
    else {
      $hash = Crypt::randomHash();
      DelayedMethodCalls::stack($userModel, "prepareRegistration", [ $hash, $form->elements["name"]->value, $form->elements["mail"]->value ]);
      DelayedMailer::stack(new UserRegisterMail($hash, $form->elements["name"]->value, $form->elements["mail"]->value));
    }
    // Tell the user that we've sent a mail with instructions but use a view without distractions.
    (new AlertView($this, $this->view->title, "<div class='container'><small>{$i18n->t(
      "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
      [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
    )}</small></div>"))->addAlert(new Alert(
      "<p>{$i18n->t("A mail with further instructions has been sent to {0}.", [ String::placeholder($form->elements["mail"]->value) ])}</p>",
      [ "severity" => Alert::SEVERITY_INFO ]
    ));
    return $this;
  }

}
