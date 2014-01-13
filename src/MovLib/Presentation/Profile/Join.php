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
namespace MovLib\Presentation\Profile;

use \MovLib\Data\Memcached;
use \MovLib\Data\Temporary;
use \MovLib\Data\User\Full as FullUser;
use \MovLib\Exception\ValidationException;
use \MovLib\Presentation\Email\Users\EmailExists;
use \MovLib\Presentation\Email\Users\Join as JoinEmail;
use \MovLib\Presentation\Error\Unauthorized;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputCheckbox;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * User join presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Join extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this join attempt was accepted or not.
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
   * The full user object we create during a valid join attempt.
   *
   * @var \MovLib\Data\User\Full
   */
  protected $user;

  /**
   * The input text form element for the username.
   *
   * @var \MovLib\Presentation\Partial\FormElement\AbstractInput
   */
  protected $username;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user join presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // If the user is signed in, no need for joining.
    if ($session->isAuthenticated === true) {
      throw new SeeOtherRedirect($i18n->r("/my"));
    }

    // Start rendering the page.
    $this->initPage($i18n->t("Join"));
    $this->initBreadcrumb([[ $i18n->rp("/users"), $i18n->t("Users") ]]);
    $this->initLanguageLinks("/profile/join");

    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$i18n->r("/profile/sign-in")}'>{$i18n->t("Sign In")}</a>";

    $this->username = new InputText("username", $i18n->t("Username"), [
      "maxlength"   => FullUser::NAME_MAXIMUM_LENGTH,
      "pattern"     => "^(?!^[ ]+)(?![ ]+$)(?!^.*[ ]{2,}.*$)(?!^.*[" . preg_quote(FullUser::NAME_ILLEGAL_CHARACTERS, "/") . "].*$).*$",
      "placeholder" => $i18n->t("Enter your desired username"),
      "required",
      "title"       => $i18n->t(
        "A username must be valid UTF-8, cannot contain spaces at the beginning and end or more than one space in a row, " .
        "it cannot contain any of the following characters {0} and it cannot be longer than {1,number,integer} characters.",
        [ FullUser::NAME_ILLEGAL_CHARACTERS, FullUser::NAME_MAXIMUM_LENGTH ]
      ),
    ]);

    $this->email    = new InputEmail();
    $this->password = new InputPassword();

    $this->terms = new InputCheckbox("terms", $i18n->t(
      "I accept the {0}Privacy Policy{2} and {1}Terms of Use{2}.",
      [ "<a href='{$i18n->t("/privacy-policy")}'>", "<a href='{$i18n->r("/terms-of-use")}'>", "</a>" ]
    ), [ "required" ]);

    $this->form                             = new Form($this, [ $this->username, $this->email, $this->password, $this->terms ]);
    $this->form->attributes["action"]       = $kernel->requestURI;
    $this->form->attributes["autocomplete"] = "off";
    $this->form->attributes["class"]        = "s s6 o3";

    $this->form->actionElements[] = new InputSubmit($i18n->t("Sign Up"), [
      "class" => "btn btn-large btn-success",
      "title" => $i18n->t("Click here to sign up after you filled out all fields"),
    ]);

    if ($kernel->requestMethod == "GET" && !empty($_GET["token"])) {
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
      return "<div class='c'><small>{$i18n->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$kernel->requestURI}'>", "</a>" ]
      )}</small></div>";
    }

    return "<div class='c'><div class='r'>{$this->form}</div></div>";
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function valid() {
    global $i18n, $kernel;

    $this->user->email    = $this->email->value;
    $this->user->password = $this->user->hashPassword($this->password->value);
    if ((new Memcached())->isRemoteAddressFlooding("join") === true) {
      $this->checkErrors($i18n->t("Too many joining attempts from this IP address. Please wait one hour before trying again."));
    }

    // Don't tell the user who's trying to join that we already have this email, otherwise it would be possible to
    // find out which emails we have in our system. Instead we send a message to the user this email belongs to.
    if ($this->user->checkEmail($this->user->email) === true) {
      $kernel->sendEmail(new EmailExists($this->user->email));
    }
    // Send email with activation token if this emai isn't already in use.
    else {
      $kernel->sendEmail(new JoinEmail($this->user));
    }

    // Settings this to true ensures that the user isn't going to see the form again. Check getContent()!
    $this->accepted = true;

    // Accepted but further action is required!
    http_response_code(202);
    $this->alerts .= new Alert(
      $i18n->t("An email with further instructions has been sent to {email}.", [ "email" => $this->placeholder($this->email->value) ]),
      $i18n->t("Successfully Joined"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * The redirect exception is thrown if the supplied data is valid. The user will be redirected to her or his personal
   * dashboard.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param array $errors
   *   {@inheritdoc}
   * @return this
   */
  public function validate($errors) {
    global $i18n, $kernel;

    $this->user       = new FullUser();
    $this->user->name = $_POST[$this->username->id]; // We want to validate the original data again
    $usernameErrors   = null;

    if ($this->user->name[0] == " ") {
      $usernameErrors[] = $i18n->t("The username cannot begin with a space.");
    }

    if (substr($this->user->name, -1) == " ") {
      $usernameErrors[] = $i18n->t("The username cannot end with a space.");
    }

    if (strpos($this->user->name, "  ") !== false) {
      $usernameErrors[] = $i18n->t("The username cannot contain multiple spaces in a row.");
    }

    // Switch to sanitized data
    $this->user->name = $this->username->value;

    if (strpbrk($this->user->name, FullUser::NAME_ILLEGAL_CHARACTERS) !== false) {
      $usernameErrors[] = $i18n->t(
        "The username cannot contain any of the following characters: {0}",
        [ "<code>{$kernel->htmlEncode(FullUser::NAME_ILLEGAL_CHARACTERS)}</code>" ]
      );
    }

    if (mb_strlen($this->user->name) > FullUser::NAME_MAXIMUM_LENGTH) {
      $usernameErrors[] = $i18n->t(
        "The username is too long: it must be {0,number,integer} characters or less.",
        [ FullUser::NAME_MAXIMUM_LENGTH ]
      );
    }

    if (!$usernameErrors && $this->user->checkName($this->user->name) === true) {
      $usernameErrors[] = $i18n->t("The username is already taken, please choose another one.");
    }

    if ($usernameErrors) {
      $this->username->invalid();
      $errors[$this->id] = implode("<br>", $usernameErrors);
    }

    if (isset($errors[$this->terms->id])) {
      $errors[$this->terms->id] = $i18n->t(
        "You have to accept the {0}Privacy Policy{2} and {1}Terms of Use{2} to join {sitename}.",
        [ "<a href='{$i18n->t("/privacy-policy")}'><a href='{$i18n->r("/terms-of-use")}'></a>", "sitename" => $kernel->siteName ]
      );
    }

    if ($this->checkErrors($errors) === false) {
      $this->valid();
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token, join, sign in and redirect to password settings.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  public function validateToken() {
    global $i18n, $kernel;
    try {
      // The token is the base64 encoded email address of the user, decode and validate as email before attempting to
      // load the user from the temporary table.
      if (($email = base64_decode($_GET["token"])) === false || filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
        throw new ValidationException($i18n->t("The activation token is invalid, please go back to the mail we sent you and copy the whole link."));
      }

      // Email is valid, try to load the user from the temporary database.
      $tmp  = new Temporary();
      $user = $tmp->get("jointoken{$email}");
      if (!($user instanceof FullUser)) {
        throw new ValidationException(
          "<p>{$i18n->t("We couldn’t find any activation data for your token.")}</p>" .
          "<ul>" .
            "<li>{$i18n->t("The token might have expired, remember that you only have 24 hours to activate your account.")}</li>" .
            "<li>{$i18n->t("The token might be invalid, check the email again we’ve sent you and be sure to copy the whole link.")}</li>" .
            "<li>{$i18n->t("You can also just fill out the form again and we send you a new token.")}</li>" .
          "</ul>"
        );
      }

      // Check if the email is already activated.
      if ($user->checkEmail($user->email) === true) {
        throw new Unauthorized(
          $i18n->t("Seems like you’ve already activated your account, please sign in."),
          $i18n->t("Already Activated"),
          Alert::SEVERITY_INFO
        );
      }

      // Check if the username was taken in the meantime.
      if ($user->checkName($user->name) === true) {
        $this->username->attributes["value"] = $user->name;
        $this->email->attributes["value"]    = $user->email;
        $this->terms->attributes[]           = "checked"; // No problem, the user already accepted them!
        $this->username->invalid();
        throw new ValidationException($i18n->t("Unfortunately in the meantime someone took your desired username, please choose another one."));
      }

      // Register the new account (this can't be done delayed because the user needs to validate directly after the
      // redirect) and stack the deletion of the temporary database entry.
      $user->join();
      $kernel->delayMethodCall([ $tmp, "delete" ], [ "jointoken{$user->email}" ]);

      // The user has to sign in, this makes sure that the person is really who she or he claims to be. The password is
      // entered by the user while joining and never displayed anywhere to anyone (plus we hash it right away in the
      // validate method of this class, so even we have no clue what it is). Even if somebody was able to activate an
      // account for another person (man in the middle; very unlikely) she or he couldn't access that new account
      // because that person would also need the secret password.
      throw new Unauthorized(
        $i18n->t("Your account has been activated, please sign in with your email address and your secret password."),
        $i18n->t("Hi there {0}!", [ $user->name ]),
        Alert::SEVERITY_SUCCESS
      );
    }
    catch (ValidationException $e) {
      $this->checkErrors($e->getMessage());
    }
    catch (Unauthorized $e) {
      if (isset($user) && isset($user->email)) {
        $e->signInPresentation->email->attributes["value"] = $user->email;
      }
      throw $e;
    }

    return $this;
  }

}
