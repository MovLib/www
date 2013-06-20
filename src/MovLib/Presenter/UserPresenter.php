<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presenter;

use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\UserException;
use \MovLib\Model\UserModel;
use \MovLib\Presenter\AbstractPresenter;
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedMailer;
use \MovLib\Utility\DelayedMethodCalls;
use \MovLib\Utility\HTTP;
use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractView;
use \MovLib\View\HTML\AlertView;
use \MovLib\View\HTML\User\UserLoginView;
use \MovLib\View\HTML\User\UserRegisterView;
use \MovLib\View\HTML\User\UserResetPasswordView;
use \MovLib\View\HTML\User\UserSettingsView;

/**
 * @todo Description.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserPresenter extends AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * User model of the profile that we are currently displaying.
   *
   * @var \MovLib\Model\UserModel
   */
  public $profile;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct() {
    // Automaticall call the correct constructor for this action.
    $errors = $this->{__FUNCTION__ . $this->getAction()}();
    // If we have any errors, set them.
    if ($errors) {
      foreach ($errors as $key => $msg) {
        $this->view->setAlert($msg, AbstractView::ALERT_SEVERITY_ERROR);
        $this->view->formInvalid[$key] = true;
      }
    }
    // Make sure the rendered view is exported to class scope.
    $this->setPresentation();
  }

  /**
   * Render the sign in form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return null|array
   *   Associative array containing error messages, if there are any. Otherwise <tt>NULL</tt>.
   */
  private function __constructLogin() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      HTTP::redirect($i18n->r("/user"), 302);
    }
    $this->view = new UserLoginView($this);
    // Ensure we are using the correct route (this method is called from other constructors in this presenter as well).
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/login");
    // If the user requested the simple form, just render it.
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      return;
    }
    // Sanitize and validate the submitted email address.
    if (($mail = DelayedMailer::validateInput()) === false) {
      $errors["mail"] = $i18n->t("The submitted email address is not valid.");
    }
    // Try to create a user from the given mail.
    try {
      $user = new UserModel(UserModel::FROM_MAIL, $mail);
    } catch (UserException $e) {
      $errors["mail"] = $i18n->t("We either don’t know the submitted email address, or the password was wrong.");
    }
    // Validate the password and log the user in if it is valid.
    if ($user->validatePassword() === false) {
      $errors["pass"] = $i18n->t("We either don’t know the submitted email address, or the password was wrong.");
    }
    // We're done if there are any errors.
    if (isset($errors)) {
      return $errors;
    }
    // Things look good, hello there!
    $_SESSION["ALERTS"][] = [
      $i18n->t("Log in was successful, welcome back {0}!", [ "<b>{$user->name}</b>" ]),
      AbstractView::ALERT_SEVERITY_SUCCESS
    ];
    HTTP::redirect($i18n->r("/user"), 302);
  }

  /**
   * Log the user out from the current session.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   */
  private function __constructLogout() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      $user->sessionDestroy();
      $this->__constructLogin();
      $this->view->setAlert([
        "title" => $i18n->t("You’ve been logged out successfully."),
        "message" => $i18n->t("We hope to see you again soon.")
      ], AbstractView::ALERT_SEVERITY_SUCCESS, true);
      return;
    }
    HTTP::redirect($i18n->r("/user/login"), 302);
  }

  /**
   * Render the public profile page of the user identified by ID or name.
   */
  private function __constructProfile() {
    global $i18n;
    // Let's pretend we didn't find the user if the input is invalid.
    if (($name = String::filterInput("USER_NAME", INPUT_SERVER)) === false) {
      $this->setPresentation("Error\\NotFound");
      $this->view->setAlert("Input is invalid!", AbstractView::ALERT_SEVERITY_ERROR);
      return;
    }
    // Try to find the user in our database.
    try {
      $this->profile = new UserModel(UserModel::FROM_NAME, $name);
    } catch (UserException $e) {
      $this->setPresentation("Error\\NotFound");
      $this->view->setAlert($e->getMessage(), AbstractView::ALERT_SEVERITY_ERROR);
      $this->view->setAlert("<code>{$_SERVER["USER_NAME"]}</code>");
      return;
    }
    // If this user's account is disabled, tell the client about it and exit (no need to redirect).
    if ($this->profile->deleted === true) {
      $this->view = new GoneView($this);
      // @todo Display text to recreate account
      // @todo Check if account was deleted forever
      $this->view->content = "";
      return;
    }
    // Check if the requested URI is a perfect match to what we want to have.
    $profileRoute = $i18n->r("/user/{0}", [ String::convertToRoute($this->profile->name) ]);
    if ($profileRoute !== $_SERVER["REQUEST_URI"]) {
      HTTP::redirect($profileRoute);
    }
    $this->setPresentation("User\\UserProfile");
  }

  /**
   * Render the sign up form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructRegister() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      HTTP::redirect("/", 302);
    }
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      isset($_SERVER["TOKEN"])
        ? $this->activateOrResetPassword($i18n->r("/user/register"), $i18n->t("registration page"))
        : $this->setPresentation("User\\UserRegister")
      ;
      return;
    }
    $this->view = new UserRegisterView($this);
    if (($mail = DelayedMailer::validateInput()) === false) {
      $errors["mail"] = $i18n->t("The submitted email address is not valid.");
    }
    if (($name = String::filterInput("name")) === false) {
      $errors["name"] = $i18n->t("The submitted name is not valid.");
    }
    if (($error = UserModel::validateName($name))) {
      $errors["name"] = $error;
    }
    elseif ($user->exists(UserModel::FROM_NAME, $name) === true) {
      $errors["name"] = $i18n->t("The {0} {1} is already in use.", [ $i18n->t("username"), String::placeholder($name) ]);
    }
    if (isset($errors)) {
      return $errors;
    }
    // Do not tell the user that we already have this mail, otherwise it would be possible for an attacker to find out
    // which mails we have in our system. Instead we send a message to the user this mail belongs to.
    if ($user->exists("mail", $mail) === true) {
      DelayedMailer::stackMethod("stackActivationMailExists", [ $mail ]);
    }
    // If this is a valid new registration generate a unique activation link and insert the user's data into our
    // temporary database table (which will be deleted after 24 hours). Also send the user a mail explaining what to do.
    else {
      $params = [ Crypt::randomHash(), $name, $mail ];
      DelayedMethodCalls::stack($user, "preRegister", $params);
      DelayedMailer::stackMethod("stackActivationMail", $params);
    }
    $this->view = new AlertView($this, $this->view->title);
    $this->view->setAlert(
      "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($mail) ])}</p>",
      AbstractView::ALERT_SEVERITY_SUCCESS
    );
    $this->view->content =
      "<div class='container'><p class='text-center text-small'>{$i18n->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
      )}</p></div>"
    ;
  }

  /**
   * Render the reset password page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructResetPassword() {
    global $i18n, $user;
    $view = new UserResetPasswordView($this);
    if (isset($_POST["submitted"])) {
      $mail = filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL);
      if (($error = DelayedMailer::validate($mail))) {
        $view->setAlert($error, AbstractView::ALERT_SEVERITY_ERROR, true);
      }
      if (!$error) {
        if ($user->exists("mail", $mail) === true) {
          $params = [ Crypt::randomHash(), $mail ];
          DelayedMethodCalls::stack($user, "preResetPassword", $params);
          DelayedMailer::stackMethod("stackPasswordReset", $params);
        }
        $view = new AlertView($this, $view->title);
        $view->setAlert(
          "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($mail) ])}</p>",
          AbstractView::ALERT_SEVERITY_SUCCESS
        );
        $view->content =
          "<div class='container'><p class='text-center text-small'>{$i18n->t(
            "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
            [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
          )}</p></div>";
      }
    }
    elseif (isset($_SERVER["TOKEN"])) {
      $view = $this->activateOrResetPassword($i18n->r("/user/reset-password"), $i18n->t("reset password page"));
    }
    $this->view = $view;
  }

  /**
   * Render the settings page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructSettings() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      http_response_code(401);
      $this->__constructLogin();
      $this->view->setAlert([
        "title" => $i18n->t("Authentication Required"),
        "message" => $i18n->t("You have to log in before you can access {0}.", [ $i18n->t("your settings page") ])
      ], AbstractView::ALERT_SEVERITY_INFO);
      return $this;
    }
    $tab = ucfirst($_SERVER["TAB"]);
    $this->view = new UserSettingsView($this, $tab);
    if ($_SERVER["REQUEST_METHOD"] === "POST" && ($errors = $this->{"validate{$tab}Settings"}())) {
      return $errors;
    }
  }

  /**
   * Render the currently logged in user's profile.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructShow() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      http_response_code(401);
      $this->__constructLogin();
      $this->view->setAlert([
        "title" => $i18n->t("Authentication Required"),
        "message" => $i18n->t("You have to log in before you can access {0}.", [ $i18n->t("your profile page") ])
      ], AbstractView::ALERT_SEVERITY_INFO);
      return;
    }
    $this->setPresentation("User\\UserShow");
  }

  /**
   * Helper method to render the user settings page where the user can set his password after registration or a reset
   * password request.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @param string $expiredTokenRoute
   *   The route to the page where the user should go if the token has expired.
   * @param string $expiredTokenRouteText
   *   The text that should be linked.
   * @return mixed
   *   The view to render.
   */
  private function activateOrResetPassword($expiredTokenRoute, $expiredTokenRouteText) {
    global $i18n, $user;
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/settings/password");
    if (empty($_SERVER["TOKEN"])) {
      $this->view = new AlertView($this, $i18n->t("Missing Authentication Token"));
      $this->view->setAlert(
        "<p>{$i18n->t("Your link is missing the authentication token, please go back to the mail we’ve sent you and copy the whole link.")}</p>",
        AbstractView::ALERT_SEVERITY_ERROR
      );
      return;
    }
    $data = $user->selectTmpData($_SERVER["TOKEN"]);
    if (empty($data) || (time() - $data["time"] > 86400)) {
      $this->view = new AlertView($this, $i18n->t("Link Expired"));
      $this->view->setAlert(
        "<p>{$i18n->t("The link you used has already expired.")}</p>" .
        "<p>{$i18n->t("Please go back to the {0} to get a new valid token.", [
          $this->view->a($expiredTokenRoute, $expiredTokenRouteText)
        ])}</p>",
        AbstractView::ALERT_SEVERITY_ERROR
      );
      return;
    }
    $this->view = new UserSettingsView($this, "Password");
    $pass = Crypt::randomUserPassword();
    // We are handling a reset password request if we only have the mail.
    if (empty($data["name"]) && $user->isLoggedIn === false) {
      $user = new UserModel(UserModel::FROM_MAIL, $data["mail"]);
      $user->sessionStart();
    }
    // Something is odd if we aren't dealing with a password reset request and the user is logged in.
    elseif ($user->isLoggedIn === true) {
      $_SERVER["ALERTS"] = [[
        "title" => $i18n->t("Already Activated"),
        "message" => $i18n->t("You already verified your email address and are logged in, if you want to change your password use the form below."),
      ], AbstractView::ALERT_SEVERITY_INFO];
      HTTP::redirect($_SERVER["REQUEST_URI"]);
    }
    // We are handling a new registration if both, name and mail, are present.
    elseif (!empty($data["name"]) && !empty($data["mail"])) {
      try {
        $user->register($data["name"], $data["mail"], $pass);
      } catch (DatabaseException $e) {
        $this->view = new AlertView($this, $i18n->t("Name or Mail already registered"));
        $this->view->setAlert(
          "<p>{$i18n->t("Seems like someone (or you) was faster and already registered an account with your desired data (name or email address).")}</p>" .
          "<p>{$i18n->t("Go to the {0}password reset page{1} if you’ve already registered an account with this email address but forgot your password.", [
            "<a href='{$i18n->r("/user/reset-password")}'>", "</a>"
          ])}</p>" .
          "<p>{$i18n->t("Or go to the {0}registration page{1} to create a new account.", [
            "<a href='{$i18n->r("/user/register")}'>", "</a>"
          ])}</p>",
          AbstractView::ALERT_SEVERITY_ERROR
        );
        return;
      }
    }
    else {
      throw new UserException("Temporary database contained key for password reset or registration without any associated valid data.");
    }
    $this->view->setAlert([
      "title" => $i18n->t("Create a password for your account"),
      "message" =>
        "<p>{$i18n->t("You can now create a password for your account via the following form. Please choose a strong password that is hard to guess.")}</p>" .
        "<p>{$i18n->t("We created the following random password for you:")} <code>{$pass}</code></p>" .
        "<p>{$i18n->t("If you like it, write it down and leave this page without any further changes.")}</p>"
    ], AbstractView::ALERT_SEVERITY_INFO, true);
    $this->view->formDisabled["pass"] = true;
    $this->view->inputValues["pass"] = $pass;
  }

  private function validateAccountSettings() {

  }

  private function validateNotificationSettings() {

  }

  private function validateMailSettings() {
    global $i18n, $user;
    $errors = null;
    // Check if the password equals the hash we have stored for this user.
    if ($user->validatePassword() === false) {
      $errors["pass"] = $i18n->t("The submitted password is not valid.");
    }
    // Check if the current mail is equal to the mail we have stored for this user.
    if (isset($_POST["current-mail"]) && $_POST["current-mail"] !== $user->mail) {
      $errors["current-mail"] = $i18n->t("The submitted current email address is not valid.");
    }
    // Check if the new mail is in a valid format.
    if (($newMail = DelayedMailer::validateInput("new-mail")) === false) {
      $errors["new-mail"] = $i18n->t("The submitted new email address is not valid.");
    }
    // Check if the mail mail differs from the current mail.
    if ($newMail === $user->mail) {
      $errors["new-mail"] = $i18n->t("The submitted new email address is the same as your current email address.");
    }
    // We're done if there are any errors.
    if ($errors) {
      return $errors;
    }
    // @todo Update mail (send verification mail).
  }

  private function validatePasswordSettings() {
    global $i18n, $user;
    $errors = null;
    // Check if the password equals the hash we have stored for this user.
    if ($user->validatePassword() === false) {
      $errors["current-pass"] = $i18n->t("The submitted password is not valid.");
    }
    // Check if we have a new and a confirmation password.
    if ((!isset($_POST["new-pass"]) || empty($_POST["new-pass"])) || (!isset($_POST["confirm-pass"]) || empty($_POST["confirm-pass"]))) {
      $errors["new-pass"] = $i18n->t("You must enter a new and a confirm password.");
      $this->view->formInvalid["confirm-pass"] = true;
    }
    // Check that they are really equal.
    elseif ($_POST["new-pass"] !== $_POST["confirm-pass"]) {
      $errors["confirm-pass"] = $i18n->t("The confirmation password is not equal to your desired new password.");
    }
    // We're done if there are any errors.
    if ($errors) {
      return $errors;
    }
    // @todo Update password.
  }

  private function validateDangzeroneSettings() {

  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Associative array containing the breadcrumb trail for this presenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return array
   */
  public function getBreadcrumb() {
    global $i18n, $user;
    if ($this->getAction() === "Show") {
      return [];
    }
    $breadcrumb = [ $user->isLoggedIn === true
      ? [ $i18n->r("/user"), $i18n->t("Profile"), [ "title" => $i18n->t("Go to your profile page.") ] ]
      : [ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ] ]
    ];
    $additionalBreadcrumbsMethod = __FUNCTION__ . $this->getAction();
    if (method_exists($this, $additionalBreadcrumbsMethod)) {
      return $this->{$additionalBreadcrumbsMethod}($breadcrumb);
    }
    return $breadcrumb;
  }

  /**
   * Get title and points for secondary settings navigation of the user.
   *
   * This must reside in the presenter, because the navigation is shared among views.
   *
   * @see \MovLib\View\HTML\AbstractView::getSecondaryNavigation()
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return array
   *   Associative array with the keys <em>title</em> and <em>points</em>.
   */
  public function getSecondarySettingsNavigation() {
    global $i18n;
    return [
      "title" => $i18n->t("Profile navigation"),
      "points" => [
        [ $i18n->r("/user"), "<i class='icon icon--home'></i> {$i18n->t("Profile")}", [ "class" => "menuitem--separator", "title" => "Go to your profile page." ]],
        [ $i18n->r("/user/account-settings"), "<i class='icon icon--user'></i> {$i18n->t("Account")}", [ "title" => $i18n->t("Manage your basic account settings.") ]],
        [ $i18n->r("/user/notification-settings"), "<i class='icon icon--signal'></i> {$i18n->t("Notifications")}", [ "title" => $i18n->t("Manage your notification settings.") ]],
        [ $i18n->r("/user/mail-settings"), "<i class='icon icon--mail'></i> {$i18n->t("Mail")}", [ "title" => $i18n->t("Change your email address.") ]],
        [ $i18n->r("/user/password-settings"), "<i class='icon icon--lock'></i> {$i18n->t("Password")}", [ "title" => $i18n->t("Change your password.") ]],
        [ $i18n->r("/user/dangerzone-settings"), "<i class='icon icon--alert'></i> {$i18n->t("Dangerzone")}", [ "class" => "menuitem--delete", "title" => $i18n->t("Deactivate or delete your account.") ]],
      ],
    ];
  }

}
