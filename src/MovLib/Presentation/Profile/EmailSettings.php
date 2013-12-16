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
use \MovLib\Presentation\Email\User\EmailChange;
use \MovLib\Presentation\Error\Unauthorized;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Allows a user to change her or his email address.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class EmailSettings extends \MovLib\Presentation\Profile\Show {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The input email form element.
   *
   * A confirmation field is {@link http://ux.stackexchange.com/a/4769 senseless}.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  private $email;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user email settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // Disallow caching of email settings.
    session_cache_limiter("nocache");

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session->checkAuthorization($i18n->t("You need to sign in to change your email address."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    // Translate and set the page title.
    $this->init($i18n->t("Email Settings"), "/profile/email-settings", [[ $i18n->r("/profile"), $i18n->t("Profile") ]]);

    // Validate the token if the page was requested via GET and a token is actually present.
    if ($kernel->requestMethod == "GET" && !empty($_GET["token"])) {
      $this->validateToken();
    }

    // Field to enter the new email address.
    $this->email = new InputEmail();
    $this->email->setHelp($i18n->t(
        "MovLib takes your privacy seriously. That’s why your email address will never show up in public. In fact, it " .
        "stays top secret like your password. If you’d like to manage when to receive messages from MovLib go to your " .
        "{0}notification settings{1}.", [ "<a href='{$i18n->r("/profile/notification-settings")}'>", "</a>" ]
    ));

        // Initialize the actual form of this page.
    $this->form  = new Form($this, [ $this->email ]);

    // The submit button.
    $this->form->actionElements[] = new InputSubmit($i18n->t("Request Email Change"), [
      "class" => "btn btn-large btn-success",
      "title" => $i18n->t("Click here to request the change of your email address after you filled out all fields."),
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inhertidoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->r("/profile"), $i18n->t("Profile") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $currentEmail           = new Alert($i18n->t("Your current email address is {0}.", [ $this->placeholder($this->user->email) ]));
    $currentEmail->severity = Alert::SEVERITY_INFO;
    return "{$currentEmail}{$this->form}";
  }

  /**
   * Validation callback after auto-validation of form has succeeded.
   *
   * The redirect exception is thrown if the supplied data is valid. The user will be redirected to her or his personal
   * dashboard.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return this
   */
  public function validate(array $errors = null) {
    global $i18n, $kernel;

    // Check if the user re-entered the email she or he is already using, if not check if this email address is taken
    // by another user. If a user enters a email address that is already in use, this might imply that the user has
    // multiple accounts. Although it's never good having user's with multiple accounts, there isn't much we can do
    // against it and punishing somebody (at this point for example) would be senseless. Just tell the user that this
    // address is taken and carry on.
    if ($this->user->email == $this->email->value) {
      $errors[] = $i18n->t("You are already using this email address.");
    }
    // Check if this email address is already in use by another user. Entering an already used email address might imply
    // that the user has multiple accounts. Although it's never good having user's with multiple accounts, there isn't
    // much we can do against it. Especially becuase it's impossible to delete any account, some users might simply
    // create a new account because they don't want to be associated with their old account. But still, we can't let
    // anyone use any email we already have in our system. A user experiencing this kind of problem should contact us
    // directly.
    elseif ($this->user->checkEmail($this->email->value) === true) {
      $errors[] = $i18n->t("This email address is already is use, please choose another one.");
      $errors[] = $i18n->t("If you feel that receiving this error is a mistake please {0}contact us{1}.", [
        "<a href='{$i18n->r("/contact")}'>", "</a>"
      ]);
    }

    if ($this->checkErrors($errors) === false) {
      $kernel->sendEmail(new EmailChange($this->user, $this->email->value));

      // The request has been accepted, but further action is required to complete it.
      http_response_code(202);

      // Explain to the user where to find this further action to complete the request.
      $this->alerts .= new Alert(
        $i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->email->value) ]),
        $i18n->t("Successfully Requested Email Change"),
        Alert::SEVERITY_SUCCESS
      );

      // Also explain that this change is no immidiate action and that our system is still using the old email address.
      $this->alerts .= new Alert(
        $i18n->t("You have to sign in with your old email address until you’ve successfully confirmed your email change via the link that we’ve just sent you."),
        $i18n->t("Important!"),
        Alert::SEVERITY_INFO
      );
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token and update the user's email address.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return this
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  protected function validateToken() {
    global $i18n, $kernel;
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["new_email"])) {
      $kernel->alerts .= new Alert(
        $i18n->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $i18n->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new SeeOtherRedirect($kernel->requestPath);
    }

    if ($data["user_id"] !== $this->user->id) {
      $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);
      throw new Unauthorized(
        $i18n->t("The confirmation token is invalid, please sign in again and request a new token."),
        $i18n->t("Token Invalid"),
        Alert::SEVERITY_ERROR,
        true
      );
    }

    $this->user->updateEmail($data["new_email"]);
    $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);

    $this->alerts .= new Alert(
      $i18n->t("Your email address was successfully changed. Please use your new email address to sign in from now on."),
      $i18n->t("Email Changed Successfully"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
