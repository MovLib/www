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

use \MovLib\Data\Delayed\Mailer;
use \MovLib\Data\User\Full as User;
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
class PasswordSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\Profile\TraitProfile;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's form.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  private $form;

  /**
   * The input password form element for the new password.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  private $newPassword;

  /**
   * The input password form element for confirmation of the new password.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputPassword
   */
  private $newPasswordConfirm;


  // ------------------------------------------------------------------------------------------------------------------- Methods



  /**
   * Instantiate new user password settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   */
  public function __construct() {
    global $i18n, $session;

    if (!isset($_GET["token"])) {
      $session
        ->checkAuthorization($i18n->t("You need to sign in to change your password."))
        ->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."))
      ;
    }

    $this->init($i18n->t("Password Settings"));
    $this->newPassword        = new InputPassword("new-password");
    $this->newPasswordConfirm = new InputPassword("new-password-confirm");
    $this->form               = new Form($this, [ $this->newPassword, $this->newPasswordConfirm ]);

    if (isset($_GET["token"])) {
      $this->validateToken();
    }

    // @see \MovLib\Presentation\User\Registration::validateToken()
    if (isset($_SESSION["password"])) {
      $success = new Alert($i18n->t("Welcome to {0} {1}, we hope that you like it here!", [ "MovLib", $this->placeholder($session->userName) ]));
      $success->title = $i18n->t("Signed Up Successfully");
      $success->severity = Alert::SEVERITY_SUCCESS;

      $info = new Alert("<p>{$i18n->t(
        "We have generated the following password for you, you can use this password in the future to sign in at {0}. " .
        "If you like to have a different password you can change it with the form below.",
        [ "MovLib" ]
      )}</p><p><b>{$i18n->t("Your Secret Password")}:</b> <code>{$_SESSION["password"]}</code></p>");
      $info->title = $i18n->t("Your Secret Password");
      $info->severity = Alert::SEVERITY_INFO;

      $this->alerts .= "{$success}{$info}";
      unset($_SESSION["password"]);
    }
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $info = null;
    if (!isset($_SESSION["password"])) {
      $randomPassword = User::getRandomPassword();
      $info = new Alert(
        "<p>{$i18n->t("Choose a strong password to secure your account. To help you, we generated a password for you:")}</p>" .
        "<p><code>{$randomPassword}</code></p>"
      );
      $info->severity = Alert::SEVERITY_INFO;
    }
    $this->newPassword->attributes[]                     = "autofocus";
    $this->newPassword->attributes["placeholder"]        = $i18n->t("Enter your new password");
    $this->newPassword->label                            = $i18n->t("New Password");
    $this->newPassword->setHelp("<a href='{$i18n->r("/user/reset-password")}'>{$i18n->t("Forgot your password?")}</a>", false);
    $this->newPasswordConfirm->attributes["placeholder"] = $i18n->t("Enter your new password again");
    $this->newPasswordConfirm->label                     = $i18n->t("Confirm Password");
    $this->form->actionElements[]                        = new InputSubmit([
      "class" => "button--large button--success",
      "title" => $i18n->t("Click here to request the password change after you filled out all fields."),
      "value" => $i18n->t("Request Password Change"),
    ]);
    $this->form->attributes["action"]                    = $i18n->r("/user/password-settings");
    $this->form->attributes["autocomplete"]              = "off";
    return "{$info}{$this->form}";
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   */
  public function validate() {
    global $i18n, $session;
    if ($this->newPassword->value != $this->newPasswordConfirm->value) {
      $this->newPassword->invalid();
      $this->newPasswordConfirm->invalid();
      $this->checkErrors([ $i18n->t("The confirmation password doesn’t match the new password, please try again.") ]);
    }
    else {
      $this->user = new User(User::FROM_ID, $session->userId);
      Mailer::stack(new PasswordChangeEmail($this->user, $this->newPassword->value));

      // The request has been accepted, but further action is required to complete it.
      http_response_code(202);

      // Explain to the user where to find this further action to complete the request.
      $success = new Alert($i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]));
      $success->title = $i18n->t("Successfully Requested Password Change");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $this->alerts .= $success;

      // Also explain that this change is no immidiate action and that our system is still using the old password.
      $info = new Alert($i18n->t("You have to sign in with your old password until you’ve successfully confirmed your password change via the link we’ve just sent you."));
      $info->title = $i18n->t("Important!");
      $info->severity = Alert::SEVERITY_INFO;
      $this->alerts .= $info;
    }
    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's password.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   */
  public function validateToken() {
    global $i18n, $session;
    $this->user = new User();
    $data = $this->user->validateAuthenticationToken($errors, $this->id);
    if ($data && $data["id"]) {
      if ($session->isAuthenticated === true && $data["id"] !== $session->id) {
        throw new UnauthorizedException($i18n->t("The authentication token is invalid, please sign in again and request a new token to change your password."));
      }
      $this->user = new User(User::FROM_ID, $data["id"]);
    }
    if (empty($data["password"])) {
      $data["password"] = User::getRandomPassword();
      $success = new Alert($i18n->t("Your new secret password is {0}.", [ "<code>{$data["password"]}</code>" ]));
      $success->title = $i18n->t("Password Reset Successfully");
    }
    else {
      $success = new Alert($i18n->t("Your password was successfully changed to {0}.", [ "<code>{$data["password"]}</code>" ]));
      $success->title = $i18n->t("Password Changed Successfully");
    }
    if ($this->checkErrors($errors) === false) {
      $this->user->updatePassword($data["password"]);
      $session->authenticate($this->user->email, $data["password"]);
      $success->severity = Alert::SEVERITY_SUCCESS;
      $this->alerts .= $success;
    }
    elseif ($session->isAuthenticated === false) {
      throw new UnauthorizedException("Your authentication token has expired, please fill out the form again.");
    }
    return $this;
  }

}
