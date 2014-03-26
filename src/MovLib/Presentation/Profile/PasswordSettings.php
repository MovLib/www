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

use \MovLib\Data\Temporary;
use \MovLib\Data\Shell;
use \MovLib\Presentation\Email\User\PasswordChange as PasswordChangeEmail;
use \MovLib\Presentation\Error\Unauthorized;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Allows a user to change her or his password.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PasswordSettings extends \MovLib\Presentation\Profile\Show {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The input password form element for the new password.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  protected $newPassword;

  /**
   * The input password form element for confirmation of the new password.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  protected $newPasswordConfirm;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user password settings presentation.
   *
   */
  public function __construct() {
    // Disallow caching of password settings.
    session_cache_limiter("nocache");

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session->checkAuthorization($this->intl->t("You need to sign in to change your password."));
    $session->checkAuthorizationTimestamp($this->intl->t("Please sign in again to verify the legitimacy of this request."));

    // Translate and set the page title.
    $this->init($this->intl->t("Password Settings"), "/profile/password-settings", [[ $this->intl->r("/profile"), $this->intl->t("Profile") ]]);

    // Validate the token if the page was requested via GET and a token is actually present.
    if ($kernel->requestMethod == "GET" && !empty($_GET["token"])) {
      $this->validateToken();
    }

    // First field to enter the new password.
    $this->newPassword = new InputPassword("new-password", $this->intl->t("New Password"), [
      "placeholder" => $this->intl->t("Enter your new password"),
    ]);

    // Second field to enter the new password for confirmation.
    $this->newPasswordConfirm = new InputPassword("new-password-confirm", $this->intl->t("Confirm Password"), [
      "placeholder" => $this->intl->t("Enter your new password again"),
    ]);

    // Initialize the actual form of this page.
    $this->form = new Form($this, [ $this->newPassword, $this->newPasswordConfirm ]);
    $this->form->attributes["autocomplete"] = "off";

    // The submit button.
    $this->form->actionElements[] = new InputSubmit($this->intl->t("Change"), [ "class" => "btn btn-large btn-success" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inhertidoc
   */
  protected function getBreadcrumbs() {
    return [[ $this->intl->r("/profile"), $this->intl->t("Profile") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    // Generate a KeePass like random password for the user.
    $randomPassword = `pwgen -cnBv 20 1`;
    if (empty($randomPassword)) {
      $passwordInfo = null;
      // @todo Log that pwgen isn't available.
    }
    else {
      $passwordInfo = new Alert(
        "<p>{$this->intl->t("Choose a strong password to secure your account.")}</p>" .
        "<p>{$this->intl->t(
          "A password must contain lowercase and uppercase letters, numbers, and must be at least " .
          "{0,number,integer} characters long.",
          [ $this->newPassword->minimumPasswordLength ]
        )}</p>" .
        "<p>{$this->intl->t(
          "As little help we generated the following password for you: {random_password}",
          [ "random_password" => "<code>{$randomPassword}</code>" ]
        )}</p>",
        $this->intl->t("Tip"),
        Alert::SEVERITY_INFO
      );
    }

    return "{$passwordInfo}{$this->form}";
  }

  /**
   * @inheritdoc
   */
  protected function valid() {
    $kernel->sendEmail(new PasswordChangeEmail($this->user, $this->newPassword->value));

    // The request has been accepted, but further action is required to complete it.
    http_response_code(202);

    // Explain to the user where to find this further action to complete the request.
    $this->alerts .= new Alert(
      $this->intl->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]),
      $this->intl->t("Successfully Requested Password Change"),
      Alert::SEVERITY_SUCCESS
    );

    // Also explain that this change is no immidiate action and that our system is still using the old password.
    $this->alerts .= new Alert(
      $this->intl->t("You have to sign in with your old password until you’ve successfully confirmed your password change via the link we’ve just sent you."),
      $this->intl->t("Important!"),
      Alert::SEVERITY_INFO
    );

    return $this;
  }

  /**
   * @inheritdoc
   * @todo OWASP and other sources recommend to store a password history for each user and check that the new password
   *       isn't one of the old passwords. This would increase the account's security a lot. Anyone willing to implement
   *       this is very welcome.
   * @param null|array $errors
   *   {@inheritdoc}
   * @return this
   */
  public function validate($errors) {
    // Both password's have to be equal.
    if ($this->newPassword->value != $this->newPasswordConfirm->value) {
      $this->newPassword->invalid();
      $this->newPasswordConfirm->invalid();
      $errors[] = $this->intl->t("The confirmation password doesn’t match the new password, please try again.");
    }

    // Instantiate full user object if we have no errors so far.
    if (!$errors) {
      // The new password shouldn't be the same as the old password.
      if ($this->user->verifyPassword($this->newPassword->value) === true) {
        $this->newPassword->invalid();
        $this->newPasswordConfirm->invalid();
        $errors[] = $this->intl->t("Your new password equals your existing password, please enter a new one.");
      }
    }

    // If we have no errors at this point send the email to the user's email address to confirm the password change.
    if ($this->checkErrors($errors) === false) {
      $this->valid();
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's password.
   *
   * @return this
   * @throws \MovLib\Presentation\Error\Unauthorized
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function validateToken() {
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["new_password"])) {
      $kernel->alerts .= new Alert(
        $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new SeeOtherRedirect($kernel->requestPath);
    }

    if ($data["user_id"] !== $session->userId) {
      $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);
      throw new Unauthorized(
        $this->intl->t("The confirmation token is invalid, please sign in again and request a new token."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR,
        true
      );
    }

    $kernel->delayMethodCall([ $this->user, "updatePassword" ], [ $data["new_password"] ]);
    $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);

    $this->alerts .= new Alert(
      $this->intl->t("Your password was successfully changed. Please use your new password to sign in from now on."),
      $this->intl->t("Password Changed Successfully"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
