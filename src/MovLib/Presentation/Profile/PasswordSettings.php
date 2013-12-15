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
use \MovLib\Data\User\Full as UserFull;
use \MovLib\Data\UnixShell as sh;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Presentation\Email\User\PasswordChange as PasswordChangeEmail;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

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
  use \MovLib\Presentation\TraitFormPage;


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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // Disallow caching of password settings.
    session_cache_limiter("nocache");

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session->checkAuthorization($i18n->t("You need to sign in to change your password."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    // Validate the token if the page was requested via GET and a token is actually present.
    if ($kernel->requestMethod == "GET" && !empty($_GET["token"])) {
      $this->validateToken();
    }

    // Translate and set the page title.
    $this->init($i18n->t("Password Settings"));

    // First field to enter the new password.
    $this->newPassword = new InputPassword("new-password", $i18n->t("New Password"), [
      "placeholder" => $i18n->t("Enter your new password"),
    ]);

    // Second field to enter the new password for confirmation.
    $this->newPasswordConfirm = new InputPassword("new-password-confirm", $i18n->t("Confirm Password"), [
      "placeholder" => $i18n->t("Enter your new password again"),
    ]);

    // Initialize the actual form of this page.
    $this->form = new Form($this, [ $this->newPassword, $this->newPasswordConfirm ]);
    $this->form->attributes["autocomplete"] = "off";

    // The submit button.
    $this->form->actionElements[] = new InputSubmit($i18n->t("Request Password Change"), [
      "class" => "btn btn-large btn-success",
      "title" => $i18n->t("Continue here to request the password change after you filled out all fields."),
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;

    // Generate a KeePass like random password for the user.
    sh::execute("pwgen -cnBv 20 1", $randomPassword);
    $randomPassword = trim($randomPassword[0]);

    $info = new Alert(
      "<p>{$i18n->t("Choose a strong password to secure your account.")} {$i18n->t("A password must contain lowercase and uppercase letters, numbers, and must be at least {0,number,integer} characters long.", [
        $this->newPassword->minimumPasswordLength
      ])} {$i18n->t("As little help we generated the following password for you:")} <code>{$randomPassword}</code></p>",
      $i18n->t("Tip"),
      Alert::SEVERITY_INFO
    );

    return "{$info}{$this->form}";
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * @todo OWASP and other sources recommend to store a password history for each user and check that the new password
   *       isn't one of the old passwords. This would increase the account's security a lot. Anyone willing to implement
   *       this is very welcome.
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return this
   */
  public function validate(array $errors = null) {
    global $kernel, $i18n, $session;

    // Both password's have to be equal.
    if ($this->newPassword->value != $this->newPasswordConfirm->value) {
      $this->newPassword->invalid();
      $this->newPasswordConfirm->invalid();
      $errors[] = $i18n->t("The confirmation password doesn’t match the new password, please try again.");
    }

    // Instantiate full user object if we have no errors so far.
    if (!$errors) {
      $this->user = new UserFull(UserFull::FROM_ID, $session->userId);

      // The new password shouldn't be the same as the old password.
      if ($this->user->verifyPassword($this->newPassword->value) === true) {
        $this->newPassword->invalid();
        $this->newPasswordConfirm->invalid();
        $errors[] = $i18n->t("Your new password equals your existing password, please enter a new one.");
      }
    }

    // If we have no errors at this point send the email to the user's email address to confirm the password change.
    if ($this->checkErrors($errors) === false) {
      $kernel->sendEmail(new PasswordChangeEmail($this->user, $this->newPassword->value));

      // The request has been accepted, but further action is required to complete it.
      http_response_code(202);

      // Explain to the user where to find this further action to complete the request.
      $this->alerts .= new Alert(
        $i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]),
        $i18n->t("Successfully Requested Password Change"),
        Alert::SEVERITY_SUCCESS
      );

      // Also explain that this change is no immidiate action and that our system is still using the old password.
      $this->alerts .= new Alert(
        $i18n->t("You have to sign in with your old password until you’ve successfully confirmed your password change via the link we’ve just sent you."),
        $i18n->t("Important!"),
        Alert::SEVERITY_INFO
      );
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's password.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\Session $session
   * @return this
   */
  protected function validateToken() {
    global $i18n, $kernel, $session;
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["new_password"])) {
      $kernel->alerts .= new Alert(
        $i18n->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $i18n->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new RedirectSeeOtherException($kernel->requestPath);
    }

    if ($data["user_id"] !== $session->userId) {
      throw new UnauthorizedException($i18n->t("The confirmation token is invalid, please sign in again and request a new token."));
    }

    $kernel->delayMethodCall([ new UserFull(UserFull::FROM_ID, $data["user_id"]), "updatePassword" ], [ $data["new_password"] ]);
    $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);

    $this->alerts .= new Alert(
      $i18n->t("Your password was successfully changed. Please use your new password to sign in from now on."),
      $i18n->t("Password Changed Successfully"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
