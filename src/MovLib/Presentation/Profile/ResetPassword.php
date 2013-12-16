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
use \MovLib\Exception\Client\ErrorUnauthorizedException;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Presentation\Email\User\ResetPassword as ResetPasswordEmail;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * User reset password presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ResetPassword extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\Users\TraitUsers;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The email input form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  protected $email;

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

  /**
   * The user who's reseting her or his password.
   *
   * @var \MovLib\Data\User\Full
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new user reset password presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->initPage($i18n->t("Reset Password"));
    $this->initBreadcrumb();
    $this->initLanguageLinks("/profile/reset-password");

    if (!empty($_GET["token"]) && $this->validateToken() === true) {
      // First field to enter the new password.
      $this->newPassword = new InputPassword("new-password", $i18n->t("New Password"), [
        "placeholder" => $i18n->t("Enter your new password"),
      ]);

      // Second field to enter the new password for confirmation.
      $this->newPasswordConfirm = new InputPassword("new-password-confirm", $i18n->t("Confirm Password"), [
        "placeholder" => $i18n->t("Enter your new password again"),
      ]);

      // Initialize the actual form of this page.
      $this->form = new Form($this, [ $this->newPassword, $this->newPasswordConfirm ], "{$this->id}-password", "validatePassword");
      $this->form->attributes["autocomplete"] = "off";

      // The submit button.
      $this->form->actionElements[] = new InputSubmit($i18n->t("Reset Password"), [
        "class" => "btn btn-large btn-success",
        "title" => $i18n->t("Continue here to reset your password after you filled out all fields."),
      ]);
    }
    else {
      $this->email = new InputEmail();
      $this->email->attributes["placeholder"] = $i18n->t("Enter your email address");

      $this->form = new Form($this, [ $this->email ], "{$this->id}-email", "validateEmail");

      $this->form->actionElements[] = new InputSubmit($i18n->t("Request Password Reset"), [
        "class" => "btn btn-large btn-success",
        "title" => $i18n->t("Continue here to request a password reset for the entered email address"),
      ]);
    }

    $this->form->attributes["class"] = "s s6 o3";
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    return "<div class='c'><div class='r'>{$this->form}</div></div>";
  }

  /**
   * Validation callback after auto-validation of reset password form has succeeded.
   *
   * The redirect exception is thrown if the supplied data is valid.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return this
   */
  public function validateEmail(array $errors = null) {
    global $kernel, $i18n;

    if ($this->checkErrors($errors) === false) {
      $kernel->sendEmail(new ResetPasswordEmail($this->email->value));

      http_response_code(202);

      $this->alerts .= new Alert(
        $i18n->t("An email with further instructions has been sent to {email}.", [ "email" => $this->placeholder($this->email->value) ]),
        $i18n->t("Successfully Requested Password Reset"),
        Alert::SEVERITY_SUCCESS
      );
    }

    return $this;
  }

  /**
   * Validation callback after auto-validation of change password form has succeeded.
   *
   * @todo OWASP and other sources recommend to store a password history for each user and check that the new password
   *       isn't one of the old passwords. This would increase the account's security a lot. Anyone willing to implement
   *       this is very welcome.
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return this
   */
  public function validatePassword(array $errors = null) {
    global $i18n, $kernel;

    if ($this->newPassword->value != $this->newPasswordConfirm->value) {
      $this->newPassword->invalid();
      $this->newPasswordConfirm->invalid();
      $errors[] = $i18n->t("The confirmation password doesn’t match the new password, please try again.");
    }

    if ($this->checkErrors($errors) === false) {
      $this->user->updatePassword($this->user->hashPassword($this->newPassword->value));
      $kernel->delayMethodCall([ new Temporary(), "delete" ], [ $_GET["token"] ]);
      $kernel->alerts .= new Alert(
        $i18n->t("Your password was successfully changed. Please use your new password to sign in from now on."),
        $i18n->t("Password Changed Successfully"),
        Alert::SEVERITY_SUCCESS
      );

      throw new RedirectSeeOtherException($i18n->r("/profile/sign-in"));
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token and reset the user's password.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\Session $session
   * @return boolean
   *   <code>FALSE</code> if the token is invalid, otherwise <code>TRUE</code>
   * @throws \MovLib\Exception\Client\ErrorUnauthorizedException
   */
  protected function validateToken() {
    global $i18n, $kernel, $session;
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["reset_password"])) {
      $kernel->alerts .= new Alert(
        $i18n->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $i18n->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new RedirectSeeOtherException($kernel->requestPath);
    }

    if ($session->userId !== $data["user_id"]) {
      $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);
      throw new ErrorUnauthorizedException(
        $i18n->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $i18n->t("Token Invalid"),
        Alert::SEVERITY_ERROR,
        true
      );
    }

    if ($kernel->requestMethod == "POST") {
      $this->user = new UserFull(UserFull::FROM_ID, $data["user_id"]);
    }

    return true;
  }

}
