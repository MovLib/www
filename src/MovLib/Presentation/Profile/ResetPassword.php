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
use \MovLib\Data\User\FullUser;
use \MovLib\Presentation\Email\User\ResetPassword as ResetPasswordEmail;
use \MovLib\Presentation\Error\Unauthorized;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Redirect\SeeOther;

/**
 * User reset password presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ResetPassword extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**#@+
   * Form element identifiers.
   *
   * @var string
   */
  const FORM_EMAIL            = "email";
  const FORM_PASSWORD_NEW     = "password_new";
  const FORM_PASSWORD_CONFIRM = "password_confirm";
  const FORM_PASSWORD_TOKEN   = "password_token";
  /**#@-*/


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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user reset password presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;

    $this->initPage($i18n->t("Reset Password"));
    $this->initBreadcrumb([[ $i18n->rp("/users"), $i18n->t("Users") ]]);
    $this->breadcrumb->ignoreQuery = true;
    $this->initLanguageLinks("/profile/reset-password");

    $this->headingBefore = "<a class='btn btn-large btn-primary fr' href='{$i18n->r("/profile/sign-in")}'>{$i18n->t("Sign In")}</a>";

    if (!empty($_GET["token"]) && $this->validateToken() === true) {
      // First field to enter the new password.
      $this->formAddElement(new InputPassword(self::FORM_PASSWORD_NEW, $i18n->t("New Password"), $this->rawPasswordNew, [
        "autofocus"   => true,
        "placeholder" => $i18n->t("Enter your new password"),
        "required"    => true,
      ]));

      // Second field to enter the new password for confirmation.
      $this->formAddElement(new InputPassword(self::FORM_PASSWORD_CONFIRM, $i18n->t("Confirm Password"), $this->rawPasswordConfirm, [
        "placeholder" => $i18n->t("Enter your new password again"),
        "required"    => true,
      ]));

      $this->formAddAction($i18n->r("Reset Password"), [ "class" => "btn btn-large btn-success" ]);
      $this->formInit([ "autocomplete" => "off", "class" => "s s6 o3" ]);
    }
    else {
      $this->formAddElement(new InputEmail(self::FORM_EMAIL, $i18n->t("Email Address"), $this->email, [
        "#help-popup" => $i18n->t("Enter the email address associated with your {sitename} account. Password reset instructions will be sent via email.", [ "sitename" => $kernel->siteName ]),
        "autofocus"   => true,
        "placeholder" => $i18n->t("Enter your email address"),
        "required"    => true,
      ]));
      $this->formAddAction($i18n->t("Request Reset"), [ "class" => "btn btn-large btn-success" ]);
      $this->formInit([ "class" => "s s6 o3" ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function formValid() {
    global $i18n, $kernel;
    if (!empty($this->email)) {
      $kernel->sendEmail(new ResetPasswordEmail($this->email));
      http_response_code(202);
      $this->alerts .= new Alert(
        $i18n->t("An email with further instructions has been sent to {email}.", [ "email" => $this->placeholder($this->email) ]),
        $i18n->t("Successfully Requested Password Reset"),
        Alert::SEVERITY_SUCCESS
      );
    }
    elseif (!empty($this->rawPasswordNew)) {
      // Update this user's password.
      $this->user->updatePassword($this->user->hashPassword($this->rawPasswordNew));

      // Delete the token from the temporary database.
      $kernel->delayMethodCall([ new Temporary(), "delete" ], [ $_GET["token"] ]);

      // Make sure that the user isn't redirected to the password reset page after successful login.
      $kernel->requestURI = $kernel->requestPath = "/";

      // Display sign in presentation to the user and let the user know that the password was updated.
      throw new Unauthorized(
        $i18n->t("Your password was successfully changed. Please use your new password to sign in from now on."),
        $i18n->t("Password Changed Successfully"),
        Alert::SEVERITY_SUCCESS,
        true // This will delete any left over session of this user!
      );
    }
    return $this;
  }

  /**
   * Implements form validation hook.
   * @param type $errors
   * @return \MovLib\Presentation\Profile\ResetPassword
   */
  protected function hookFormValidation(&$errors) {
    global $i18n;
    if (isset($_GET["token"]) && !empty($this->rawPasswordNew) && $this->rawPasswordNew != $this->rawPasswordConfirm) {
      $this->formElements[self::FORM_PASSWORD_NEW]->invalid();
      $this->formElements[self::FORM_PASSWORD_CONFIRM]->invalid();
      $errors[self::FORM_PASSWORD_CONFIRM][] = $i18n->t("The confirmation password doesn’t match the new password, please try again.");
    }
    return $this;
  }

  /**
   * Get the page's content.
   *
   * @return string
   *   The page's content.
   */
  public function getContent() {
    return "<div class='c'><div class='r'>{$this->formRender()}</div></div>";
  }

  /**
   * Validate the submitted authentication token and reset the user's password.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return boolean
   *   <code>FALSE</code> if the token is invalid, otherwise <code>TRUE</code>
   * @throws \MovLib\Presentation\Error\Unauthorized
   * @throws \MovLib\Presentation\Redirect\SeeOther
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
      throw new SeeOther($kernel->requestPath);
    }

    if ($session->isAuthenticated === true && $session->userId !== $data["user_id"]) {
      $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);
      throw new Unauthorized(
        $i18n->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $i18n->t("Token Invalid"),
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
