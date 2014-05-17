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

use \MovLib\Core\Memcached;
use \MovLib\Data\TemporaryStorage;
use \MovLib\Data\User\User;
use \MovLib\Exception\ClientException\UnauthorizedException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\Profile\EmailExists;
use \MovLib\Mail\Profile\JoinEmail;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputCheckbox;
use \MovLib\Partial\FormElement\InputEmail;
use \MovLib\Partial\FormElement\InputPassword;
use \MovLib\Partial\FormElement\InputText;

/**
 * User join presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Join extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this join attempt was accepted or not.
   *
   * @var boolean
   */
  protected $accepted = false;

  /**
   * The user that will join MovLib.
   *
   * @var \MovLib\Data\User\User
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Setup



  /**
   * {@inheritdoc}
   */
  public function init() {
    // If the user is signed in, no need for joining.
    if ($this->session->isAuthenticated) {
      throw new SeeOtherException($this->intl->r("/dashboard"));
    }

    $this
      ->initPage($this->intl->t("Join"))
      ->initBreadcrumb([[ $this->intl->r("/users"), $this->intl->t("Users") ]])
      ->initLanguageLinks("/profile/join")
    ;
    $this->breadcrumb->ignoreQuery = true;
    $this->user = new User($this->diContainerHTTP);

    if ($this->request->methodGET && isset($this->request->query["token"])) {
      $this->validateToken();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Layout


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingBefore = "<a class='btn btn-large btn-info fr' href='{$this->intl->r("/profile/sign-in")}'>{$this->intl->t("Sign In")}</a>";

    $terms = false; // We don't care about the value, the checkbox is required!
    $form  = (new Form($this->diContainerHTTP, [ "autocomplete" => "off", "class" => "s s6 o3" ]))
      ->addElement(new InputText($this->diContainerHTTP, "username", $this->intl->t("Username"), $this->user->name, [
        "autofocus"   => true,
        "maxlength"   => User::NAME_MAXIMUM_LENGTH,
        "pattern"     => "^(?!^[ ]+)(?![ ]+$)(?!^.*[ ]{2,}.*$)(?!^.*[" . preg_quote(User::NAME_ILLEGAL_CHARACTERS, "/") . "].*$).*$",
        "placeholder" => $this->intl->t("Enter your desired username"),
        "required"    => true,
        "title"       => $this->intl->t(
          "A username must be valid UTF-8, cannot contain spaces at the beginning and end or more than one space in a row, " .
          "it cannot contain any of the following characters {0} and it cannot be longer than {1,number,integer} characters.",
          [ User::NAME_ILLEGAL_CHARACTERS, User::NAME_MAXIMUM_LENGTH ]
        ),
      ]))
      ->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->user->email, [
        "placeholder" => $this->intl->t("Enter your email address"),
        "required"    => true,
      ]))
      ->addElement(new InputPassword($this->diContainerHTTP, "password", $this->intl->t("Password"), $this->user->passwordHash, [
        "placeholder" => $this->intl->t("Enter your desired password"),
        "required"    => true,
      ]))
      ->addElement(new InputCheckbox($this->diContainerHTTP, "terms", $this->intl->t(
        "I accept the {a1}privacy policy{a} and the {a2}terms of use{a}.",
        [ "a" => "</a>", "a1" => "<a href='{$this->intl->r("/privacy-policy")}'>", "a2" => "<a href='{$this->intl->r("/terms-of-use")}'>" ]
      ), $terms, [
        "required" => true,
      ]))
      ->addAction($this->intl->t("Sign Up"), [ "class" => "btn  btn-large btn-success" ])
      ->init([ $this, "submit" ], [ $this, "validate" ])
    ;

    if ($this->accepted === true) {
      return "<div class='c'><p>{$this->intl->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$this->request->uri}'>", "</a>" ]
      )}</small></p>";
    }

    return "<div class='c'><div class='r'>{$form}</div></div>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Form Validation


  /**
   * Continue form validation process after auto-validation.
   *
   * This hook is called after all form elements have been auto-validated. It allows concrete classes to extend the
   * validation process or alter error messages of specific form elements.
   *
   * @param null|array $errors
   *   Associative array containing all error messages, if any.
   * @return this
   */
  public function validate($errors) {
    // Only continue validation process if we have no errors for the username or if the errors we have are not because
    // it's a required field.
    if (empty($errors["username"]) || empty($errors["username"][InputText::ERROR_REQUIRED])) {
      // We want to validate the original data again
      $username = $this->request->filterInput(INPUT_POST, "username", FILTER_UNSAFE_RAW);

      if ($username{0} == " ") {
        $errors["username"][] = $this->intl->t("The username cannot begin with a space.");
      }

      if (substr($username, -1) == " ") {
        $errors["username"][] = $this->intl->t("The username cannot end with a space.");
      }

      if (strpos($username, "  ") !== false) {
        $errors["username"][] = $this->intl->t("The username cannot contain multiple spaces in a row.");
      }

      // Switch to sanitized data
      $username = $this->user->name;

      if (strpbrk($this->user->name, User::NAME_ILLEGAL_CHARACTERS) !== false) {
        $errors["username"][] = $this->intl->t(
          "The username cannot contain any of the following characters: {0}",
          [ "<code>{$this->htmlEncode(User::NAME_ILLEGAL_CHARACTERS)}</code>" ]
        );
      }

      if (mb_strlen($this->user->name) > User::NAME_MAXIMUM_LENGTH) {
        $errors["username"][] = $this->intl->t(
          "The username is too long: it must be {0,number,integer} characters or less.",
          [ User::NAME_MAXIMUM_LENGTH ]
        );
      }

      if (empty($errors["username"]) && $this->user->inUse("name", $this->user->name) === true) {
        $errors["username"][] = $this->intl->t("The username is already taken, please choose another one.");
      }

      if (!empty($errors["username"])) {
        $this->formElements["username"]->invalid();
        $errors["username"] = implode("<br>", $errors["username"]);
      }
    }

    // More descriptive error message if the user hasn't accepted the privacy policy and terms of use.
    if (isset($errors["terms"])) {
      $errors["terms"] = $this->intl->t("You have to accept the {privacy_policy} and {terms_of_use} to join {sitename}.", [
        "privacy_policy" => "<a href='{$this->intl->r("/privacy-policy")}'>{$this->intl->t("Privacy Policy")}</a>",
        "terms_of_use"   => "<a href='{$this->intl->r("/terms-of-use")}'>{$this->intl->t("Terms of Use")}</a>",
        "sitename"       => $this->config->sitename,
      ]);
    }

    if ((new Memcached($this->log))->isRemoteAddressFlooding($this->request->remoteAddress, "join") === true) {
      $errors[] = $this->intl->t("Too many joining attempts from this IP address. Please wait one hour before trying again.");
    }

    return $errors;
  }

  /**
   * Form's auto-validation succeeded.
   *
   * @return this
   */
  public function submit() {
    // Don't tell the user who's trying to join that we already have this email, otherwise it would be possible to
    // find out which emails we have in our system. Instead we send a message to the user this email belongs to.
    $mailer = new Mailer();
    if ($this->user->inUse("email", $this->user->email) === true) {
      $mailer->send($this->diContainerHTTP, new EmailExists($this->user->email));
    }
    // Send email with activation token if this emai isn't already in use.
    else {
      $mailer->send($this->diContainerHTTP, new JoinEmail($this->user));
    }

    // Setting this to true ensures that the user isn't going to see the form again. Check getContent()!
    $this->accepted = true;

    // Accepted but further action is required!
    http_response_code(202);
    $this->alertSuccess(
      $this->intl->t("Successfully Joined"),
      $this->intl->t("An email with further instructions has been sent to {0}.", $this->placeholder($this->user->email))
    );

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Token Validation


  /**
   * Validate the submitted authentication token, join, sign in and redirect to password settings.
   *
   * @return this
   */
  public function validateToken() {
    try {
      // The token is the base64 encoded email address of the user.
      $this->user->email = base64_decode($this->request->filterInputString(INPUT_GET, "token"));

      // Validate as email before attempting to load the user from the temporary table.
      if ($this->user->email === false || filter_var($this->user->email, FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR) === false) {
        throw new ValidationException($this->intl->t("The activation token is invalid, please go back to the mail we sent you and copy the whole link."));
      }

      // Email is valid, try to load the user from the temporary database.
      $tmp  = new TemporaryStorage($this->diContainerHTTP);
      $this->user = $tmp->get("jointoken{$this->user->email}");
      if (!($this->user instanceof User)) {
        throw new ValidationException(
          "<p>{$this->intl->t("We couldn’t find any activation data for your token.")}</p>" .
          "<ul>" .
          "<li>{$this->intl->t("The token might have expired, remember that you only have 24 hours to activate your account.")}</li>" .
          "<li>{$this->intl->t("The token might be invalid, check the email again we’ve sent you and be sure to copy the whole link.")}</li>" .
          "<li>{$this->intl->t("You can also just fill out the form again and we send you a new token.")}</li>" .
          "</ul>"
        );
      }

      // Check if the email is already activated.
      if ($this->user->inUse("email", $this->user->email) === true) {
        throw new UnauthorizedException(new Alert(
          $this->intl->t("Seems like you’ve already activated your account, please sign in."),
          $this->intl->t("Already Activated"),
          Alert::SEVERITY_INFO
        ));
      }

      // Check if the username was taken in the meantime.
      if ($this->user->inUse("name", $this->user->name) === true) {
        $this->username->attributes["value"] = $this->user->name;
        $this->email->attributes["value"]    = $this->user->email;
        $this->terms->attributes[]           = "checked"; // No problem, the user already accepted them!
        $this->username->invalid();
        throw new ValidationException($this->intl->t("Unfortunately in the meantime someone took your desired username, please choose another one."));
      }

      // Register the new account (this can't be done delayed because the user needs to validate directly after the
      // redirect) and stack the deletion of the temporary database entry.
      $this->user->join();
      $this->kernel->delayMethodCall([ $tmp, "delete" ], [ "jointoken{$this->user->email}" ]);

      // The user has to sign in, this makes sure that the person is really who she or he claims to be. The password is
      // entered by the user while joining and never displayed anywhere to anyone (plus we hash it right away in the
      // validate method of this class, so even we have no clue what it is). Even if somebody was able to activate an
      // account for another person (man in the middle; very unlikely) she or he couldn't access that new account
      // because that person would also need the secret password.
      throw new Unauthorized(
        $this->intl->t("Your account has been activated, please sign in with your email address and your secret password."),
        $this->intl->t("Hi {0}!", [ $this->user->name ]),
        Alert::SEVERITY_SUCCESS
      );
    }
    catch (ValidationException $e) {
      $this->alertError($this->intl->t("Validation Error"), $e->getMessage());
    }
    catch (Unauthorized $e) {
      if ($this->user->email) {
        $e->signInPresentation->email->attributes["value"] = $this->user->email;
      }
      throw $e;
    }

    return $this;
  }

}
