<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Users;

use \MovLib\Data\User\Full as UserFull;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Exception\UserException;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Exception\ValidationException;
use \MovLib\Presentation\Email\Users\Registration as RegistrationEmail;
use \MovLib\Presentation\Email\Users\RegistrationEmailExists;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * User registration presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Registration extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\Users\TraitUsers;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Flag indicating if this registration attempt was accepted or not.
   *
   * @var boolean
   */
  protected $accepted = false;

  /**
   * The input email form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  protected $email;

  /**
   * The input password form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  protected $password;

  /**
   * The input checkbox for accepting the terms of service and privacy policy.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputCheckbox
   */
  protected $terms;

  /**
   * The input text form element for the username.
   *
   * @var \MovLib\Presentation\Partial\FormElement\AbstractInput
   */
  protected $username;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user registration presentation.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Exception\Client\RedirectSeeOtherException
   */
  public function __construct() {
    global $kernel, $i18n, $session;

    // If the user is logged in, no need for registration.
    if ($session->isAuthenticated === true) {
      throw new RedirectSeeOtherException($i18n->r("/my"));
    }

    // Start rendering the page.
    $this->init($i18n->t("Registration"));

    $this->username = new InputText("username", $i18n->t("Username"), [
      "maxlength"   => UserFull::NAME_MAXIMUM_LENGTH,
      "pattern"     => "^(?!^[ ]+)(?![ ]+$)(?!^.*[ ]{2,}.*$)(?!^.*[" . preg_quote(UserFull::NAME_ILLEGAL_CHARACTERS, "/") . "].*$).*$",
      "placeholder" => $i18n->t("Enter your desired username"),
      "required",
      "title"       => $i18n->t(
        "A username must be valid UTF-8, cannot contain spaces at the beginning and end or more than one space in a row, " .
        "it cannot contain any of the following characters {0} and it cannot be longer than {1,number,integer} characters.",
        [ UserFull::NAME_ILLEGAL_CHARACTERS, UserFull::NAME_MAXIMUM_LENGTH ]
      ),
    ]);
    $this->username->setHelp("<a href='{$i18n->r("/users/login")}'>{$i18n->t("Already have an account?")}</a>", false);

    $this->email    = new InputEmail();
    $this->password = new InputPassword();

    $this->terms = new InputCheckbox("terms", $i18n->t(
      "I accept the {0}Privacy Policy{2} and {1}Terms of Use{2}.",
      [ "<a href='{$i18n->t("/privacy-policy")}'>", "<a href='{$i18n->r("/terms-of-use")}'>", "</a>" ]
    ), [ "required" ]);

    $this->form                             = new Form($this, [ $this->username, $this->email, $this->password, $this->terms ]);
    $this->form->attributes["action"]       = $kernel->requestURI;
    $this->form->attributes["autocomplete"] = "off";
    $this->form->attributes["class"]        = "span span--6 offset--3";

    $this->form->actionElements[] = new InputSubmit([
      "title" => $i18n->t("Click here to sign up after you filled out all fields"),
      "value" => $i18n->t("Sign Up"),
    ]);

    if (isset($_GET["token"])) {
      $this->validateToken();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getContent() {
    global $i18n, $kernel;
    if ($this->accepted === true) {
      return "<div class='container'><small>{$i18n->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$kernel->requestURI}'>", "</a>" ]
      )}</small></div>";
    }
    return "<div class='container'><div class='row'>{$this->form}</div></div>"
    ;
  }

  /**
   * {@inheritdoc}
   *
   * The redirect exception is thrown if the supplied data is valid. The user will be redirected to her or his personal
   * dashboard.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return this
   */
  public function validate(array $errors = null) {
    global $kernel, $i18n;
    $user           = new UserFull();
    $user->name     = $_POST[$this->username->id]; // We want to validate the original data again
    $usernameErrors = null;

    if ($user->name[0] == " ") {
      $usernameErrors[] = $i18n->t("The username cannot begin with a space.");
    }

    if (substr($user->name, -1) == " ") {
      $usernameErrors[] = $i18n->t("The username cannot end with a space.");
    }

    if (strpos($user->name, "  ") !== false) {
      $usernameErrors[] = $i18n->t("The username cannot contain multiple spaces in a row.");
    }

    // Switch to sanitized data
    $user->name = $this->username->value;

    if (strpbrk($user->name, UserFull::NAME_ILLEGAL_CHARACTERS) !== false) {
      $usernameErrors[] = $i18n->t(
        "The username cannot contain any of the following characters: {0}",
        [ "<code>{$this->checkPlain(UserFull::NAME_ILLEGAL_CHARACTERS)}</code>" ]
      );
    }

    if (mb_strlen($user->name) > UserFull::NAME_MAXIMUM_LENGTH) {
      $usernameErrors[] = $i18n->t(
        "The username is too long: it must be {0,number,integer} characters or less.",
        [ UserFull::NAME_MAXIMUM_LENGTH ]
      );
    }

    if (!$usernameErrors && $user->checkName($user->name) === true) {
      $usernameErrors[] = $i18n->t("The username is already taken, please choose another one.");
    }

    if ($usernameErrors) {
      $this->username->invalid();
      $errors[$this->id] = implode("<br>", $usernameErrors);
    }

    if (isset($errors[$this->terms->id])) {
      $errors[$this->terms->id] = $i18n->t(
        "You have to accept the {0}Privacy Policy{2} and {1}Terms of Use{2} to sign up.",
        [ "<a href='{$i18n->t("/privacy-policy")}'><a href='{$i18n->r("/terms-of-use")}'></a>" ]
      );
    }

    if ($this->checkErrors($errors) === false) {
      try {
        $user->email = $this->email->value;

        // Don't tell the user who's trying to register that we already have this email, otherwise it would be possible
        // to find out which emails we have in our system. Instead we send a message to the user this email belongs to.
        if ($user->checkEmail($user->email) === true) {
          $kernel->sendEmail(new RegistrationEmailExists($user->email));
        }
        // If this is a vliad new registration generate the authentication token and insert the submitted data into our
        // temporary database, and of course send out the email with the token.
        else {
          $user->prepareRegistration($this->password->value);
          $kernel->sendEmail(new RegistrationEmail($user->name, $user->email));
        }

        // Settings this to true ensures that the user isn't going to see the form again. Check getContent()!
        $this->accepted = true;

        // Accepted but further action is required!
        http_response_code(202);
        $this->alerts .= new Alert(
          $i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->email->value) ]),
          $i18n->t("Registration Successful"),
          Alert::SEVERITY_SUCCESS
        );
      }
      catch (UserException $e) {
        $this->alerts .= new Alert(
          $i18n->t("Too many registration attempts with this email address. Please wait 24 hours before trying again."),
          $i18n->t("Validation Error"),
          Alert::SEVERITY_ERROR
        );
      }
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token, register, sign in and redirect to password settings.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @return this
   */
  public function validateToken() {
    global $i18n;
    $user = new UserFull();
    try {
      if (empty($_GET["token"])) {
        throw new ValidationException($i18n->t("The activation token is missing, please go back to the mail we sent you and copy the whole link."));
      }

      $user->email = base64_decode($_GET["token"]);

      if (filter_var($user->email, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
        throw new ValidationException($i18n->t("The activation token is invalid, please go back to the mail we sent you and copy the whole link."));
      }

      if ($user->checkEmail($user->email) === true) {
        throw new UnauthorizedException(
          $i18n->t("Seems like you’ve already activated your account, please sign in."),
          $i18n->t("Already Activated"),
          Alert::SEVERITY_INFO
        );
      }

      $data = $user->getRegistrationData();
      if ($user->checkName($user->name) === true) {
        $this->username->attributes["value"] = $user->name;
        $this->email->attributes["value"]    = $user->email;
        $this->terms->attributes[]           = "checked"; // No problem, the user already accepted them!
        $this->username->invalid();
        throw new ValidationException($i18n->t("Unfortunately in the meantime someone took your desired username, please choose another one."));
      }

      $user->register($data["password"]);
      throw new UnauthorizedException(
        $i18n->t("Your account has been activated, please sign in with your email address and your secret password."),
        $i18n->t("Hi there {0}!", [ $user->name ]),
        Alert::SEVERITY_SUCCESS
      );
    }
    catch (ValidationException $e) {
      $alert = new Alert($e->getMessage());
    }
    catch (UnauthorizedException $e) {
      unset($e->loginPresentation->email->attributes[array_search("autofocus", $e->loginPresentation->email->attributes)]);
      $e->loginPresentation->password->attributes[] = "autofocus";
      throw $e;
    }
    catch (UserException $e) {
      $alert = new Alert(
        "<p>{$i18n->t("We couldn’t find any registration data for your token.")}</p>" .
        "<ul>" .
          "<li>{$i18n->t("The token might have expired, remember that you only have 24 hours to activate your account.")}</li>" .
          "<li>{$i18n->t("The token might be invalid, check the email again we’ve sent you and be sure to copy the whole link.")}</li>" .
          "<li>{$i18n->t("You can also just fill out the form again and we send you a new token.")}</li>" .
        "</ul>"
      );
    }

    $alert->title    = $i18n->t("Validation Error");
    $alert->severity = Alert::SEVERITY_ERROR;
    $this->alerts   .= $alert;
    return $this;
  }

}
