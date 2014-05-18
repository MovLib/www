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

use \MovLib\Data\TemporaryStorage;
use \MovLib\Data\User\User;
use \MovLib\Exception\ClientException\NotFoundException;
use \MovLib\Exception\ClientException\UnauthorizedException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\Profile\ResetPasswordEmail;
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
   * The temporary storage instance.
   *
   * @var \MovLib\Data\TemporaryStorage
   */
  protected $temporaryStorage;

  /**
   * The user who's reseting the password.
   *
   * @var \MovLib\Data\User\User
   */
  protected $userId;


  // ------------------------------------------------------------------------------------------------------------------- Presentation


  /**
   * {@inheritdoc}
   */
  public function init() {
    if ($this->session->isAuthenticated === true) {
      throw new SeeOtherException($this->intl->r("/dashboard"));
    }
    $this->initPage($this->intl->t("Reset Password"));
    $this->initLanguageLinks("/profile/reset-password");
    $this->breadcrumb->addCrumb($this->intl->r("/users"), $this->intl->t("Users"));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // Note that we aren't checking against GET request method at this point, because we have to validate the token
    // before the user enters the new passwords and afterwards.
    if (($token = $this->request->filterInputString(INPUT_GET, "token"))) {
      $this->temporaryStorage = new TemporaryStorage($this->diContainerHTTP);
      $this->userId = $this->temporaryStorage->get($token);
      if ($this->userId === false || empty($this->userId)) {
        $this->invalidToken();
      }
      $form = (new Form($this->diContainerHTTP, [ "autocomplete" => "off", "class" => "s s6 o3" ]))
        ->addElement(new InputPassword($this->diContainerHTTP, "password_new", $this->intl->t("New Password"), $this->rawPasswordNew, [
          "autofocus"   => true,
          "placeholder" => $this->intl->t("Enter your new password"),
          "required"    => true,
        ]))
        ->addElement(new InputPassword($this->diContainerHTTP, "password_confirm", $this->intl->t("Confirm Password"), $this->rawPasswordConfirm, [
          "placeholder" => $this->intl->t("Enter your new password again"),
          "required"    => true,
        ]))
        ->init([ $this, "submitReset" ], [ $this, "validateReset" ])
      ;
    }
    else {
      $form = (new Form($this->diContainerHTTP, [ "class" => "s s6 o3" ]))
        ->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->email, [
          "#help-popup" => $this->intl->t(
            "Enter the email address associated with your {sitename} account. Password reset instructions will be " .
            "sent to you via email.",
            [ "sitename" => $this->config->sitename ]
          ),
          "autofocus" => true,
          "required"  => true,
        ]))
        ->init([ $this, "submitEmail" ])
      ;
    }
    $form->addAction($this->intl->t("Reset"), [ "class" => "btn btn-large btn-success" ]);
    $this->headingBefore = "<a class='btn btn-large btn-info fr' href='{$this->intl->r("/profile/sign-in")}'>{$this->intl->t("Sign In")}</a>";
    return "<div class='c'><div class='r'>{$form}</div></div>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set an alert message that explains that the token is invalid or expired and redirect the client to the initial
   * reset password form while removing the token from the URL.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   */
  protected function invalidToken() {
    $this->alertError(
      $this->intl->t("Token Invalid"),
      $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again.")
    );
    throw new SeeOtherException($this->request->path);
  }

  /**
   * Validate callback for the password reset form.
   */
  public function validateReset($errors) {
    if ($this->rawPasswordNew != $this->rawPasswordConfirm) {
      $errors["password_confirm"] = $this->intl->t("The confirmation password doesn’t match the new password, please try again.");
    }
    return $errors;
  }

  /**
   * Submit handler for valid reset password requests.
   */
  public function submitEmail() {
    (new Mailer())->send(new ResetPasswordEmail($this->email));
    http_response_code(202);
    $this->alertSuccess(
      $this->intl->t("Successfully requested password reset"),
      $this->intl->t("An email with further instructions has been sent to {email}.", [ "email" => $this->placeholder($this->email) ])
    );
  }

  /**
   * Submit handler for valid reset password.
   *
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   *   Thrown if no record exists for the previously stored user identifier.
   * @throws \MovLib\Exception\UnauthorizedException
   *   Always thrown after successful password update, the user has to sign in again.
   */
  public function submitReset() {
    try {
      $user = new User($this->diContainerHTTP, $this->userId, User::FROM_ID);
    }
    catch (NotFoundException $e) {
      $this->invalidToken();
    }
    $user->updatePassword($this->rawPasswordNew);
    $this->session->destroy(true);
    throw new UnauthorizedException(new Alert(
      $this->intl->t("Your password was successfully changed. Please use your new password to sign in from now on."),
      $this->intl->t("Password reset successful"),
      Alert::SEVERITY_SUCCESS
    ));
  }

}
