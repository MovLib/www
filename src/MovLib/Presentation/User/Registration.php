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

use \MovLib\Data\Delayed\Mailer;
use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Data\User;
use \MovLib\Exception\RedirectException;
use \MovLib\Presentation\Form;
use \MovLib\Presentation\FormElement\Input;
use \MovLib\Presentation\FormElement\InputEmail;
use \MovLib\Presentation\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\Alert;
use \Normalizer;

/**
 * User registration presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Registration extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\User\UserTrait;


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

  /**
   * Flag indicating if this registration attempt was accepted or not.
   *
   * @var boolean
   */
  private $accepted = false;


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

    $this->email = new InputEmail([ "autofocus", "class" => "input--block-level" ]);
    $this->email->required();

    // We do not use the specialized input text form element, as it would sanitize the string too much. We want to let
    // the user know about anything that's not okay with the entered string.
    $this->username = new Input("username", [
      "class"       => "input--block-level",
      "max-length"  => User::MAX_LENGTH_NAME,
      "placeholder" => $i18n->t("Enter your desired username"),
      "title"       => $i18n->t("Please enter your desired username in this field."),
    ]);
    $this->username->required();
    $this->username->label = $i18n->t("Username");

    $this->form = new Form($this, [ $this->email, $this->username ]);
    $this->form->attributes["class"] = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--large button--success",
      "title" => $i18n->t("Click here to sign up after you filled out all fields"),
      "value" => $i18n->t("Sign Up"),
    ]);
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    global $i18n;
    if ($this->accepted === true) {
      return "<div class='container'><small>{$i18n->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$_SERVER["PATH_INFO"]}'>", "</a>" ]
      )}</small></div>";
    }
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

    // Prepare a user for this registration.
    $user = new User();

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
    if (mb_strlen($this->username->value) > $this->username->attributes["max-length"]) {
      $errors[] = $i18n->t("The username is too long: it must be {1,number,integer} characters or less.", [ $this->username->attributes["max-length"] ]);
    }
    elseif (
      // Validate UTF-8
      preg_match("//u", $this->username->value) == false
      // Check if UTF-8 is in NFC form
      || $this->username->value != Normalizer::normalize($this->username->value)
      // Check for low ASCII characters (control characters)
      || $this->username->value != filter_var($this->username->value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW)
    ) {
      $errors[] = $i18n->t("The username contains illegal characters.");
    }
    elseif ($user->checkName($this->username->value) === false) {
      $errors[] = $i18n->t("The username {0} is already taken, please choose another one.", [ $this->placeholder($this->username->value) ]);
    }

    // @todo The blacklist's content must be translated along with the routes.
    $blacklist = json_decode(file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/conf/username-blacklist.json"));
    $lowercased = mb_strtolower($this->username->value);
    $c = count($blacklist);
    for ($i = 0; $i < $c; ++$i) {
      if ($blacklist[$i] == $lowercased) {
        $errors[] = $i18n->t("The username {0} is a system reserved word, please choose another one.", [ $this->placeholder($this->username->value) ]);
        break;
      }
    }

    if ($this->checkErrors($errors) === false) {
      // Don't tell the user who's trying to register that we already have this email, otherwise it would be possible
      // to find out which emails we have in our system. Instead we send a message to the user this email belongs to.
      if ($user->checkEmail($this->email->value) === true) {
        Mailer::stack(new RegistrationEmailExists($this->email->value));
      }
      // If this is a vliad new registration generate the authentication token and insert the submitted data into our
      // temporary database, and of course send out the email with the token.
      else {
        $user->name = $this->username->value;
        $user->email = $this->email->value;
        $user->setAuthenticationToken();
        DelayedMethodCalls::stack($user, "prepareRegistration");
        Mailer::stack(new RegistrationEmail($user));
      }

      $this->accepted = true;
      $success = new Alert($i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($user->email)] ));
      $success->block = true;
      $success->title = $i18n->t("Registration Successful");
      $success->severity = Alert::SEVERITY_INFO;
      $this->alerts .= $success;
    }

    return $this;
  }

}
