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
namespace MovLib\Presentation\User;

use \MovLib\Exception\RedirectException;
use \MovLib\Presentation\Form;
use \MovLib\Presentation\FormElement\Input;
use \MovLib\Presentation\FormElement\InputEmail;
use \MovLib\Presentation\FormElement\InputSubmit;

/**
 * User registration presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Registration extends \MovLib\Presentation\User\AbstractUserPage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The input email form element.
   *
   * @var \MovLib\Presentation\FormElement\InputEmail
   */
  private $email;

  /**
   * The page's form.
   *
   * @var \MovLib\Presentation\Form
   */
  private $form;

  /**
   * The input text form element for the username.
   *
   * @var \MovLib\Presentation\FormElement\Input
   */
  private $username;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new user registration presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   */
  public function __construct() {
    global $i18n, $session;

    // If the user is logged in, no need for registration.
    if ($session->isLoggedIn === true) {
      throw new RedirectException($i18n->r("/my"), 302);
    }

    // Start rendering the page.
    $this->init($i18n->t("Registration"));

    // @todo max-length
    $this->email = new InputEmail([ "autofocus", "class" => "input--block-level" ]);
    $this->email->required();

    // @todo max-length
    $this->username = new Input("username", [ "class" => "input--block-level" ]);
    $this->username->required();

    $this->form = new Form($this, [ $this->email, $this->username ]);
    $this->form->attributes["class"] = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--success button--large",
      "title" => $i18n->t("Click here to sign up after you filled out all fields"),
      "value" => $i18n->t("Sign Up"),
    ]);
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    global $i18n;
    return
      "<div class='container'><div class='row'>{$this->form->open()}" .
        "<small class='form-help'><a href='{$i18n->r("/user/login")}'>{$i18n->t("Already have an account?")}</a></small>" .
        "<p>{$this->email}</p>" .
        "<p>{$this->username}</p>" .
      "{$this->form->close(false)}</div></div>"
    ;
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * The redirect exception is thrown if the supplied data is valid. The user will be redirected to her or his personal
   * dashboard.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   * @throws \MovLib\Exception\RedirectException
   */
  public function validate() {
    global $i18n;
    $errors = null;
    // Validate the username.
    if (substr($this->username->value, 0, 1) == " ") {
      $errors[] = $i18n->t("The username cannot begin with a space.");
    }
    if (substr($this->username->value, -1) == " ") {
      $errors[] = $i18n->t("The username cannot end with a space.");
    }
    if (strpos($this->username->value, "  ") !== false) {
      $errors[] = $i18n->t("The username cannot contain multiple spaces in a row.");
    }
    // @todo we need to have a new method to validate strings
//    if (($nameFiltered = Validator::string($_POST[$form->elements["name"]->id])) === false) {
//      $errors[] = $i18n->t("The username contains one or more illegal character.");
//    }
    // @todo max-length must go into the user class, this is specific to our persistent data
//    if (mb_strlen($nameFiltered) > $GLOBALS["movlib"]["max_length_username"]) {
//      $errors[] = $i18n->t("The username is too long: it must be {1,number,integer} characters or less.", [ $GLOBALS["movlib"]["max_length_username"] ]);
//    }
    // @todo The blacklist's content must be translated along with the routes.
    $blacklist = json_decode(file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/conf/username-blacklist.json"));
    $c = count($blacklist);
    for ($i = 0; $i < $c; ++$i) {
      if ($blacklist[$i] == mb_strtolower($this->username->value)) {
        $errors[] = $i18n->t("The username {0} is a system reserved word, please choose another one.", [ String::placeholder($nameFiltered) ]);
        break;
      }
    }
    if ($errors) {
      $errors = implode("<br>", $errors);
      $alert = new Alert("<p>{$errors}</p>");
      $alert->severity = Alert::SEVERITY_ERROR;
      $this->alerts .= $alert;
      return $this;
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
