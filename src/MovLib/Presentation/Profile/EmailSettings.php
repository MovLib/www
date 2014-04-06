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

use \MovLib\Data\User\User;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\User\EmailChange;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputEmail;

/**
 * Defines the profile email settings presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class EmailSettings extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\Profile\ProfileTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The new email address.
   *
   * A confirmation field is {@link http://ux.stackexchange.com/a/4769 senseless}.
   *
   * @var string
   */
  protected $email;

  /**
   * The user currently signed in.
   *
   * @var \MovLib\Data\User\User
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    session_cache_limiter("nocache");
    $this->response->cacheable = false;
    $this->initProfilePresentation(
      $this->intl->t("You must be signed in to change your email settings."),
      $this->intl->t("Email Settings"),
      "/profile/email-settings"
    );
    $this->session->checkAuthorizationTime($this->intl->t("Please sign in again to verify the legitimacy of this request."));
    $this->user  = new User($this->diContainerHTTP, $this->session->userId, User::FROM_ID);
    $this->email = $this->user->email;
    if ($this->request->methodGET && isset($this->request->query["token"])) {
      $this->validateToken($this->request->query["token"]);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $currentEmail = new Alert(
      "<p>{$this->intl->t(
        "{sitename} takes your privacy seriously. That’s why your email address will never show up in public. In fact, " .
        "it stays top secret like your password.",
        [ "sitename" => $this->config->sitename ]
      )}</p>",
      null,
      Alert::SEVERITY_INFO
    );

    $form = (new Form($this->diContainerHTTP))
      ->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->email, [ "autofocus" => true, "required" => true ]))
      ->addAction($this->intl->t("Change Email Settings"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ], null, [ $this, "validate" ])
    ;

    return "{$currentEmail}{$form}";
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    // The request has been accepted, but further action is required to complete it.
    http_response_code(202);
    (new Mailer())->send($this->diContainerHTTP, new EmailChange($this->user, $this->email));

    // Explain to the user where to find this further action to complete the request.
    $this->alerts .= new Alert(
      $this->intl->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->email) ]),
      $this->intl->t("Successfully Requested Email Change"),
      Alert::SEVERITY_SUCCESS
    );

    // Also explain that this change is no immidiate action and that our system is still using the old email address.
    $this->alerts .= new Alert(
      $this->intl->t("You have to sign in with your old email address until you’ve successfully confirmed your email change via the link that we’ve just sent you."),
      $this->intl->t("Important!"),
      Alert::SEVERITY_INFO
    );

    return $this;
  }

  /**
   * Continue form validation.
   *
   * @param array|null $errors
   *   Errors found by the auto-validation, or <code>NULL</code>.
   * @return array|null
   *   The possibly found errors.
   */
  public function validate(&$errors) {
    // Check if the user re-entered the email she or he is already using, if not check if this email address is taken
    // by another user. If a user enters a email address that is already in use, this might imply that the user has
    // multiple accounts. Although it's never good having user's with multiple accounts, there isn't much we can do
    // against it and punishing somebody (at this point for example) would be senseless. Just tell the user that this
    // address is taken and carry on.
    if ($this->user->email == $this->email) {
      $errors[] = $this->intl->t("You are already using this email address.");
    }
    // Check if this email address is already in use by another user. Entering an already used email address might imply
    // that the user has multiple accounts. Although it's never good having user's with multiple accounts, there isn't
    // much we can do against it. Especially becuase it's impossible to delete any account, some users might simply
    // create a new account because they don't want to be associated with their old account. But still, we can't let
    // anyone use any email we already have in our system. A user experiencing this kind of problem should contact us
    // directly.
    elseif ($this->user->inUse("email", $this->email) === true) {
      $errors[] = $this->intl->t("This email address is already in use, please choose another one.");
    }
    return $errors;
  }

  /**
   * {@inheritdoc}
   */
  public function validateToken() {
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["new_email"])) {
      $kernel->alerts .= new Alert(
        $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new SeeOtherRedirect($kernel->requestPath);
    }

    if ($data["user_id"] !== $this->user->id) {
      $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);
      throw new Unauthorized(
        $this->intl->t("The confirmation token is invalid, please sign in again and request a new token."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR,
        true
      );
    }

    $this->user->updateEmail($data["new_email"]);
    $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);

    $this->alerts .= new Alert(
      $this->intl->t("Your email address was successfully changed. Please use your new email address to sign in from now on."),
      $this->intl->t("Email Changed Successfully"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
