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
use \MovLib\Exception\ClientException\UnauthorizedException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\Profile\PasswordChangeEmail;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputPassword;

/**
 * Defines the profile password settings presenter.
 *
 * @routeCache false
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PasswordSettings extends \MovLib\Presentation\Profile\AbstractProfilePresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's desired new password.
   *
   * @var string
   */
  protected $newPassword;

  /**
   * The user's confirmation password.
   *
   * @var string
   */
  protected $confirmPassword;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initProfilePresentation(
      $this->intl->t("You must be signed in to change your password settings."),
      $this->intl->t("Password Settings"),
      "/profile/password-settings",
      true,
      $this->intl->t("Please sign in again to verify the legitimacy of this request.")
    );

    if ($this->request->methodGET && ($token = $this->request->filterInputString(INPUT_GET, "token"))) {
      $this->validateToken($token);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $passwordInfo =
      "<p>{$this->intl->t("Choose a strong password to secure your account.")}</p>" .
      "<p>{$this->intl->t(
        "A password must contain lowercase and uppercase letters, numbers, and must be at least " .
        "{0,number,integer} characters long.",
        InputPassword::MIN_LENGTH
      )}</p>"
    ;

    try {
      // Try to generate a KeePass like random password for the user.
      $randomPassword = `pwgen -cnBv 20 1`;
    }
    catch (\Exception $e) {/* Ignore! */}

    if (empty($randomPassword)) {
      $this->log->error("Couldn't execute pwgen command, please ensure that pwgen is installed on the server.");
    }
    else {
      $passwordInfo .= "<p>{$this->intl->t(
        "As little help we generated the following password for you: {random_password}",
        [ "random_password" => "<code>{$randomPassword}</code>" ]
      )}</p>";
    }

    // First field to enter the new password.
    $form = (new Form($this->container, [ "autocomplete" => "off" ]))
      ->addElement(new InputPassword($this->container, "password_new", $this->intl->t("New Password"), $this->newPassword, [
        "autofocus"   => true,
        "placeholder" => $this->intl->t("Enter your new password"),
        "required"    => true,
      ]))
      ->addElement(new InputPassword($this->container, "password_confirm", $this->intl->t("Confirm Password"), $this->confirmPassword, [
        "placeholder" => $this->intl->t("Enter your new password again"),
        "required"    => true,
      ]))
      ->addAction($this->intl->t("Change"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "submit" ], [ $this, "validate" ])
    ;

    return "{$this->calloutInfo($passwordInfo, $this->intl->t("Tip"))}{$form}";
  }

  /**
   * The submitted passwords are valid, continue.
   *
   * @return this
   */
  public function submit() {
    // The request has been accepted, but further action is required to complete it.
    http_response_code(202);
    (new Mailer())->send(new PasswordChangeEmail($this->user, $this->newPassword));

    // Explain to the user where to find this further action to complete the request.
    $this->alertSuccess(
      $this->intl->t("Password change request successful"),
      $this->intl->t("An email with further instructions has been sent to {email}.", [ "email" => $this->placeholder($this->user->email) ])
    );

    // Also explain that this change is no immidiate action and that our system is still using the old password.
    $this->alertInfo(
      $this->intl->t("Important!"),
      $this->intl->t("You have to sign in with your old password until you’ve successfully confirmed your password change via the link we’ve just sent you.")
    );

    return $this;
  }

  /**
   * Continue form validation.
   *
   * @todo OWASP and other sources recommend to store a password history for each user and check that the new password
   *       isn't one of the old passwords. This would increase the account's security a lot. Anyone willing to implement
   *       this is very welcome.
   * @param array $errors
   *   Possibly found errors.
   * @return array
   *   Possibly found errors.
   */
  public function validate($errors) {
    // Make sure the password differs from the current password.
    if (password_verify($this->newPassword, $this->user->passwordHash)) {
      $errors["password_new"] = $this->intl->t("Your new password equals your existing password, please enter a new one.");
    }
    // Both password's have to be equal.
    if ($this->newPassword != $this->confirmPassword) {
      $errors["password_confirm"] = $this->intl->t("The confirmation password doesn’t match the new password, please try again.");
    }
    return $errors;
  }

  /**
   * Validate the submitted token.
   *
   * @param string $token
   *   The submitted token.
   * @return this
   */
  protected function validateToken($token) {
    $tmp = new TemporaryStorage($this->container);
    /* @var $data \MovLib\Stub\Mail\Profile\PasswordChange */
    $data = $tmp->get($token);

    if ($data === false || empty($data->userId) || empty($data->newPasswordHash)) {
      $this->alertError(
        $this->intl->t("Token Invalid"),
        $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again.")
      );
      throw new SeeOtherException($this->request->path);
    }

    $this->kernel->delayMethodCall("delete.token.password", $tmp, "delete", [ $token ]);

    if ($data->userId !== $this->user->id) {
      throw new UnauthorizedException(new Alert(
        $this->intl->t("The confirmation token is invalid, please sign in again and request a new token."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      ));
    }

    $this->user->updatePassword($data->newPasswordHash);
    $this->alertSuccess(
      $this->intl->t("Password change successful"),
      $this->intl->t("Your password was successfully changed. Please use your new password to sign in from now on.")
    );

    return $this;
  }

}
