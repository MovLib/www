<?php

/* !
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
use \MovLib\Exception\UnauthorizedException;
use \MovLib\Exception\UserException;
use \MovLib\Presentation\Email\User\EmailChange;
use \MovLib\Presentation\Form;
use \MovLib\Presentation\FormElement\InputEmail;
use \MovLib\Presentation\FormElement\InputPassword;
use \MovLib\Presentation\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\Alert;

/**
 * Allows a user to change her or his email address.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class EmailSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
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
   * The input password form element.
   *
   * @var \MovLib\Presentation\FormElement\InputPassword
   */
  private $password;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new user email settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $session;
    if ($session->isLoggedIn === false) {
      throw new UnauthorizedException($i18n->t("You must be signed in to change your email address."));
    }
    $this->init($i18n->t("Email Settings"));
    $this->user = new User(User::FROM_ID, $session->id);

    $this->email = new InputEmail([
      "autofocus",
      "class"       => "input--block-level",
      "placeholder" => $i18n->t("Enter your new email address"),
      "title"       => $i18n->t("Please enter your desired new email address in this field."),
    ]);
    $this->email->label = $i18n->t("New Email Address");
    $this->email->required();

    $this->password = new InputPassword([
      "autocomplete" => "off",
      "class"        => "input--block-level",
      "title"        => $i18n->t("Please enter your secret password in this field to verify this action."),
    ]);

    $this->form = new Form($this, [ $this->email, $this->password ]);

    $this->form->actionElements[] = new InputSubmit([
      "class" => "button--large button--success",
      "title" => $i18n->t("Click here to request the change of your email address after you filled out all fields."),
      "value" => $i18n->t("Request Email Change"),
    ]);

    if (isset($_GET["token"])) {
      $this->validateEmailChange();
    }
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $currentMail = new Alert($i18n->t("Your current email address is {0}.", [ $this->placeholder($this->user->email) ]));
    $currentMail->severity = Alert::SEVERITY_INFO;
    return
      "<p>{$i18n->t(
        "MovLib takes your privacy seriously. That’s why your email address will never show up in public. In fact, it " .
        "stays top secret like your password. If you’d like to manage when to receive messages from MovLib go to the " .
        "{0}notification settings{1}.",
        [ "<a href='{$i18n->r("/user/notification-settings")}'>", "</a>" ]
      )}</p>{$currentMail}{$this->form->open()}<p>{$this->email}</p><p>{$this->password}</p>{$this->form->close()}"
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
   */
  public function validate() {
    global $i18n;
    $errors = null;

    // Check if the user re-entered the email she or he is already using, if not check if this email address is taken
    // by another user. If a user enters a email address that is already in use, this might imply that the user has
    // multiple accounts. Although it's never good having user's with multiple accounts, there isn't much we can do
    // against it and punishing somebody (at this point for example) would be senseless. Just tell the user that this
    // address is taken and carry on.
    if ($this->user->email == $this->email->value) {
      $errors[] = $i18n->t("You are already using this email address.");
    }
    else {
      try {
        new User(User::FROM_EMAIL, $this->email->value);
        $errors[] = $i18n->t("The entered email address is already in use, please choose another one.");
      } catch (UserException $e) {
        // No problem if we couldn't find a user with this email address.
      }
    }

    // A user is only allowed to change the email address if she or he knows the current password.
    if ($this->user->verifyPassword($this->password->value) === false) {
      $errors[] = $i18n->t("The entered password is not valid, please try again.");
    }

    if ($this->checkErrors($errors) === false) {
      $this->user->setAuthenticationToken();
      DelayedMethodCalls::stack($this->user, "prepareEmailChange", [ $this->email->value ]);
      Mailer::stack(new EmailChange($this->user, $this->email->value));

      // The request has been accepted, but further action is required to complete it.
      http_response_code(202);

      // Explain to the user where to find this further action to complete the request.
      $success = new Alert($i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->email->value) ]));
      $success->block = true;
      $success->title = $i18n->t("Successfully requested email change");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $this->alerts .= $success;

      // Also explain that this change is no immidiate action and that our system is still using the old email address.
      $info = new Alert($i18n->t("You have to sign in with your old email address until you’ve successfully confirmed your email change via the link that we’ve just sent you."));
      $info->block = true;
      $info->title = $i18n->t("Important!");
      $info->severity = Alert::SEVERITY_INFO;
      $this->alerts .= $info;
    }
    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's email address.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   */
  private function validateEmailChange() {
    global $i18n, $session;
    $errors = null;

    // Validate the token before attempting to load associated data from the database. We don't use the Validator method
    // length at this point, because the string only consists of ASCII characters and we don't want a try-catch-block.
    if (empty($_GET["token"]) || strlen($_GET["token"]) !== User::AUTHENTICATION_TOKEN_LENGTH) {
      $errors[] = $i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.");
    }
    // The token seems valid, try to load associated data from the temporary table.
    elseif (!($data = $this->user->getTemporaryEmailChangeData($_GET["token"]))) {
      $errors[] = $i18n->t("The authentication token is invalid, please go back to the mail we sent you and copy the whole link.");
      $errors[] = $i18n->t("Your authentication token has expired if it is older than 24 hours. If so, please request a new mail change below.");
    }
    // Data was loaded successfully, compare the stored ID with the current user's ID. This is a really odd situation
    // we could encounter here. How was this user able to snatch the token from the other user? What should we do?
    // Might this be a hash collision (which is highly unlikely)?
    elseif ($data["id"] !== $session->id) {
      throw new UnauthorizedException($i18n->t("Something is odd, therefor we logged you out. Please double check the authentication token you used and sign in with your old email address before trying again."));
    }

    if ($this->checkErrors($errors) === false) {
      $this->user->updateEmail($data["email"]);
      $success = new Alert($i18n->t("Your email address was successfully changed to {0}.", [ $this->placeholder($this->user->email) ]));
      $success->block = true;
      $success->title = $i18n->t("Email address was successfully changed");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $this->alerts .= $success;
    }
    return $this;
  }

}
