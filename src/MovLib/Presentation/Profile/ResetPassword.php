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

use \MovLib\Exception\UnauthorizedException;
use \MovLib\Mail\Mailer;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputEmail;
use \MovLib\Partial\FormElement\InputPassword;

/**
 * User reset password presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ResetPassword extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's email address.
   *
   * @var string
   */
  protected $email;

  /**
   * The user's new raw password.
   *
   * @var string
   */
  protected $rawPasswordNew;

  /**
   * The user's new raw confirmation password.
   *
   * @var string
   */
  protected $rawPasswordConfirm;

  /**
   * The user who's reseting the password.
   *
   * @var \MovLib\Data\User\FullUser
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Presentation


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return "<div class='c'><div class='r'>{$this->form}</div></div>";
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->intl->t("Reset Password"));
    $this->initBreadcrumb([[ $this->intl->rp("/users"), $this->intl->t("Users") ]]);
    $this->breadcrumb->ignoreQuery = true;
    $this->initLanguageLinks("/profile/reset-password");

    $this->headingBefore = "<a class='btn btn-large btn-primary fr' href='{$this->intl->r("/profile/sign-in")}'>{$this->intl->t("Sign In")}</a>";

    $this->form = new Form($this->diContainerHTTP);
    if (isset($this->request->query["token"]) && $this->validateToken()) {
      // First field to enter the new password.
      $this->form->addElement(new InputPassword($this->diContainerHTTP, "password_new", $this->intl->t("New Password"), $this->rawPasswordNew, [
        "autofocus"   => true,
        "placeholder" => $this->intl->t("Enter your new password"),
        "required"    => true,
      ]));

      // Second field to enter the new password for confirmation.
      $this->form->addElement(new InputPassword($this->diContainerHTTP, "password_confirm", $this->intl->t("Confirm Password"), $this->rawPasswordConfirm, [
        "placeholder" => $this->intl->t("Enter your new password again"),
        "required"    => true,
      ]));

      $this->formAddAction($this->intl->r("Reset Password"), [ "class" => "btn btn-large btn-success" ]);
      $this->formInit([ $this, "validReset" ], [ "autocomplete" => "off", "class" => "s s6 o3" ]);
    }
    else {
      $this->form->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->email, [
        "#help-popup" => $this->intl->t("Enter the email address associated with your {sitename} account. Password reset instructions will be sent via email.", [ "sitename" => $this->config->sitename ]),
        "autofocus"   => true,
        "placeholder" => $this->intl->t("Enter your email address"),
        "required"    => true,
      ]));
      $this->form->addAction($this->intl->t("Request Reset"), [ "class" => "btn btn-large btn-success" ]);
      $this->form->init([ $this, "validEmail" ], [ "class" => "s s6 o3" ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Valid


  /**
   * The submitted email address is valid, try to load a user with it and send the reset password email.
   */
  public function validEmail() {
    (new Mailer())->send(new ResetPasswordEmail($this->email));
    http_response_code(202);
    $this->alerts .= new Alert(
      $this->intl->t("An email with further instructions has been sent to {email}.", [ "email" => $this->placeholder($this->email) ]),
      $this->intl->t("Successfully Requested Password Reset"),
      Alert::SEVERITY_SUCCESS
    );
  }

  /**
   * The submitted passwords are valid, update the account with the new password.
   *
   * @throws \MovLib\Exception\UnauthorizedException
   *   Always thrown after successful password update, the user has to sign in again.
   */
  public function validReset() {
    // Update this user's password.
    $this->user->updatePassword($this->rawPasswordNew);

    // Delete the token from the temporary database.
    $this->kernel->delayMethodCall([ new Temporary(), "delete" ], [ $_GET["token"] ]);

    // Make sure that the user isn't redirected to the password reset page after successful login.
    $this->request->uri = $this->request->path = "/";

    // Delete any left over sessions of this user.
    $this->session->destroy(true);

    // Display sign in presentation to the user and let the user know that the password was updated.
    throw new UnauthorizedException(
      $this->intl->t("Your password was successfully changed. Please use your new password to sign in from now on."),
      $this->intl->t("Password Changed Successfully"),
      Alert::SEVERITY_SUCCESS
    );
  }


  // ------------------------------------------------------------------------------------------------------------------- Validation


  /**
   * Implements form validation hook.
   * @param type $errors
   * @return \MovLib\Presentation\Profile\ResetPassword
   */
  public function invalid(&$errors) {
    if (isset($_GET["token"]) && !empty($this->rawPasswordNew) && $this->rawPasswordNew != $this->rawPasswordConfirm) {
      $this->formElements[self::FORM_PASSWORD_NEW]->invalid();
      $this->formElements[self::FORM_PASSWORD_CONFIRM]->invalid();
      $errors[self::FORM_PASSWORD_CONFIRM][] = $this->intl->t("The confirmation password doesn’t match the new password, please try again.");
    }
    return $this;
  }

  /**
   * Validate the submitted authentication token and reset the user's password.
   *
   * @return boolean
   *   <code>FALSE</code> if the token is invalid, otherwise <code>TRUE</code>
   * @throws \MovLib\Presentation\Error\Unauthorized
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function validateToken() {
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["reset_password"])) {
      $kernel->alerts .= new Alert(
        $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new SeeOther($this->request->path);
    }

    if ($session->isAuthenticated === true && $session->userId !== $data["user_id"]) {
      $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);
      throw new Unauthorized(
        $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR,
        true
      );
    }

    if ($kernel->requestMethod == "POST") {
      $this->user = new FullUser(FullUser::FROM_ID, $data["user_id"]);
    }

    return true;
  }

}
