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
use \MovLib\Exception\ImageException;
use \MovLib\Exception\UserException;
use \MovLib\Model\I18nModel;
use \MovLib\Model\UserModel;
use \MovLib\Presenter\AbstractPresenter;
use \MovLib\Utility\Crypt;
use \MovLib\Utility\DelayedMailer;
use \MovLib\Utility\DelayedMethodCalls;
use \MovLib\Utility\Image;
use \MovLib\Utility\String;
use \MovLib\Utility\Validation;
use \MovLib\View\HTML\AbstractView;
use \MovLib\View\HTML\AlertView;
use \MovLib\View\HTML\Redirect;
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
   * @global \MovLib\Model\SessionModel $user
   *   The global user model instance.
   * @param string $redirectTo
   *   [Optional] Set to <code>TRUE</code> if the user should be redirected to the currently requested URI. Defaults to
   *   <code>FALSE</code> and the user will be redirected to the profile page.
   * @return null|array
   *   Associative array containing error messages, if there are any. Otherwise <tt>NULL</tt>.
   */
  private function __constructLogin($redirect = false) {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      $this->view = new Redirect($i18n->r("/user"), 302);
      return;
    }
    if ($redirect === true) {
      $_GET["redirect_to"] = $_SERVER["REQUEST_URI"];
    }
    // Ensure we are using the correct route (this method is called from other constructors in this presenter as well).
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/login");
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      $this->setPresentation("User\\UserLogin");
      return;
    }
    if (($mail = Validation::inputMail("mail")) === false) {
      $errors["mail"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("email address") ]);
    }
    // Try to create a user from the given mail.
    try {
      $userModel = new UserModel(UserModel::FROM_MAIL, $mail);
      $userModel->validatePassword();
    } catch (UserException $e) {
      $errors["mail"] = $i18n->t("We either don’t know the submitted email address, or the password was wrong.");
    }
    if (isset($errors)) {
      return $errors;
    }
    $user->startSession($userModel);
    $_SESSION["ALERTS"][] = [
      $i18n->t("Log in was successful, welcome back {0}!", [ "<b>{$userModel->name}</b>" ]),
      AbstractView::ALERT_SEVERITY_SUCCESS
    ];
    $this->view = new Redirect(isset($_GET["redirect_to"]) ? $_GET["redirect_to"] : $i18n->r("/user"), 302);
  }

  /**
   * Log the user out from the current session.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   */
  private function __constructLogout() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      $this->view = new Redirect($i18n->r("/user/login"), 302);
      return;
    }
    $user->destroySession();
    $this->__constructLogin();
    $this->view->setAlert([
      "title"   => $i18n->t("You’ve been logged out successfully."),
      "message" => $i18n->t("We hope to see you again soon."),
    ], AbstractView::ALERT_SEVERITY_SUCCESS, true);
  }

  /**
   * Render the public profile page of the user identified by ID or name.
   */
  private function __constructProfile() {
    global $i18n;
    try {
      $this->profile = new UserModel(UserModel::FROM_NAME, Validation::inputString("USER_NAME", INPUT_SERVER));
    } catch (UserException $e) {
      $this->setPresentation("Error\\NotFound");
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
      $this->view = new Redirect($profileRoute);
      return;
    }
    $this->setPresentation("User\\UserProfile");
  }

  /**
   * Render the sign up form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructRegister() {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      $this->view = new Redirect($i18n->r("/my"), 302);
      return;
    }
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      // If a token is present, validate it, otherwise render the simple form.
      isset($_SERVER["TOKEN"])
        ? $this->activateOrResetPassword($i18n->r("/user/register"), $i18n->t("registration page"))
        : $this->setPresentation("User\\UserRegister")
      ;
      return;
    }
    $this->view = new UserRegisterView($this);
    if (($mail = Validation::inputMail("mail")) === false) {
      $errors["mail"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("email address") ]);
    }
    if (($name = Validation::inputString("name")) === false) {
      $errors["name"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("username") ]);
    }
    if (($error = Validation::username($name))) {
      $errors["name"] = $error;
    }
    else {
      // So far so good, we need a user model instance, because now we have to validate against the database.
      $userModel = new UserModel();
      if (($userModel->existsName($name)) === true) {
        $errors["name"] = $i18n->t("The {0} {1} is already in use.", [ $i18n->t("username"), String::placeholder($name) ]);
      }
    }
    if (isset($errors)) {
      return $errors;
    }
    // Do not tell the user that we already have this mail, otherwise it would be possible for an attacker to find out
    // which mails we have in our system. Instead we send a message to the user this mail belongs to.
    if ($userModel->existsMail($mail) === true) {
      DelayedMailer::stackMethod("stackActivationMailExists", [ $mail ]);
    }
    // If this is a valid new registration generate a unique activation link and insert the user's data into our
    // temporary database table (which will be deleted after 24 hours). Also send the user a mail explaining what to do.
    else {
      $params = [ Crypt::randomHash(), $name, $mail ];
      DelayedMethodCalls::stack($userModel, "preRegister", $params);
      DelayedMailer::stackMethod("stackActivationMail", $params);
    }
    // Tell the user that we've sent a mail with instructions, nothing more to do here, move along.
    $this->view = new AlertView($this, $this->view->title);
    $this->view->setAlert(
      "<p>{$i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($mail) ])}</p>",
      AbstractView::ALERT_SEVERITY_SUCCESS
    );
    $this->view->content =
      "<div class='container'><small>{$i18n->t(
        "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
        [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
      )}</small></div>"
    ;
  }

  /**
   * Render the reset password page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return $this
   */
  private function __constructResetPassword() {
    global $i18n, $user;
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      isset($_SERVER["TOKEN"])
        ? $this->activateOrResetPassword($i18n->r("/user/reset-password"), $i18n->t("reset password page"))
        : $this->setPresentation("User\\UserResetPassword")
      ;
    }
    else {
      $this->view = new UserResetPasswordView($this);
      if (($mail = Validation::inputMail("mail")) === false) {
        return [ "mail" => $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("email address") ]) ];
      }
      try {
        $userModel = new UserModel(UserModel::FROM_MAIL, $mail);
        $params = [ Crypt::randomHash(), $mail ];
        DelayedMethodCalls::stack($userModel, "preResetPassword", $params);
        DelayedMailer::stackMethod("stackPasswordReset", $params);
      } catch (UserException $e) {
        // Only tell a logged in user that the email is wrong!
        if ($user->isLoggedIn === true) {
          $this->view->setAlert($i18n->t("The email address you entered is not correct, please try again."), AbstractView::ALERT_SEVERITY_ERROR);
          return;
        }
      }
      $this->view = new AlertView($this, $this->view->title);
      $this->view->setAlert(
        $i18n->t("An email with further instructions has been sent to {0}.", [ String::placeholder($mail) ]),
        AbstractView::ALERT_SEVERITY_SUCCESS
      );
      $this->view->content =
        "<div class='container'><small>{$i18n->t(
          "Mistyped something? No problem, simply {0}go back{1} and fill out the form again.",
          [ "<a href='{$_SERVER["REQUEST_URI"]}'>" , "</a>" ]
        )}</small></div>"
      ;
    }
  }

  /**
   * Render the settings page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return $this
   */
  private function __constructSettings() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      // Ensure that the request method is set to GET, otherwise the login view will validate the submitted stuff.
      $_SERVER["REQUEST_METHOD"] = "GET";
      http_response_code(401);
      $this->__constructLogin(true);
      $this->view->setAlert([
        "title"   => $i18n->t("Authentication Required"),
        "message" => $i18n->t("You have to log in before you can access {0}.", [ $i18n->t("your settings page") ]),
      ], AbstractView::ALERT_SEVERITY_INFO);
      return;
    }
    $tab = ucfirst($_SERVER["TAB"]);
    try {
      $this->profile = new UserModel(UserModel::FROM_ID, $user->id);
    } catch (UserException $e) {
      $user->destroySession();
      $this->view = new Redirect($i18n->r("/user/login"), 302);
      return;
    }
    $this->view = new UserSettingsView($this, $tab);
    $this->view->inputValues = [
      "real_name" => $this->profile->realName,
      "gender"    => $this->profile->gender,
      "country"   => $i18n->getCountries()[$this->profile->countryId]["name"],
      "timezone"  => $this->profile->timezone,
      "profile"   => $this->profile->profile,
      "birthday"  => $this->profile->birthday,
      "website"   => $this->profile->website,
      "private"   => $this->profile->private,
    ];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      if ($_POST["csrf"] !== $user->csrfToken) {
        return $this->__constructLogout();
      }
      return $this->{"validate{$tab}Settings"}();
    }
  }

  /**
   * Render the currently logged in user's profile.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The global user model instance.
   * @return $this
   */
  private function __constructShow() {
    global $i18n, $user;
    if ($user->isLoggedIn === false) {
      http_response_code(401);
      $this->__constructLogin();
      $this->view->setAlert([
        "title"   => $i18n->t("Authentication Required"),
        "message" => $i18n->t("You have to log in before you can access {0}.", [ $i18n->t("your profile page") ]),
      ], AbstractView::ALERT_SEVERITY_INFO);
      return;
    }
    try {
      $this->profile = new UserModel(UserModel::FROM_ID, $user->id);
      $this->setPresentation("User\\UserShow");
    } catch (UserException $e) {
      $user->destroySession();
      $this->view = new Redirect($i18n->r("/user/login"), 302);
    }
  }

  /**
   * Helper method to render the user settings page where the user can set his password after registration or a reset
   * password request.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
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
    // Ensure that we are internally working with the correct route.
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/password-settings");
    if (($token = Validation::inputString("TOKEN", INPUT_SERVER)) === false) {
      $this->view = new AlertView($this, $i18n->t("Missing Authentication Token"));
      $this->view->setAlert(
        "<p>{$i18n->t("Your link is missing the authentication token, please go back to the mail we’ve sent you and copy the whole link.")}</p>",
        AbstractView::ALERT_SEVERITY_ERROR
      );
      return;
    }
    // Create new empty user model, time to query the database for more info on this token.
    $userModel = new UserModel();
    $data = $userModel->selectAndDeleteTemporaryData($token);
    // Check if we have any data at all and if we have check if it has already expired.
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
    // The token is valid and we have data stored along with it. Generate the password settings page and a random user
    // friendly password.
    $this->view = new UserSettingsView($this, "Password");
    $pass = Crypt::randomUserPassword();
    // We are handling a reset password request if we only have the mail. It's absolutely okay if the user is logged in
    // and followed a reset password link. A still logged in user might have forgotten his password and needs a new one.
    // We don't change the currently stored hash, if the user decides not to update his password we want the old one
    // and not the one we generated just before (that should simply act as help at this point).
    if (empty($data["name"]) && !empty($data["mail"])) {
      $userModel = new UserModel(UserModel::FROM_MAIL, $data["mail"]);
      $this->view->setAlert([
        "title"   => $i18n->t("Update your account password"),
        "message" =>
          "<p>{$i18n->t("You can now update your account password via the following form.")}</p>" .
          "<p>{$i18n->t("Choose a strong password, which is easy to remember but still hard to crack. To help you, " .
            "we generated a password from the most frequent words in American English:")}&nbsp;<code>{$pass}</code></p>" .
          "<p>{$i18n->t("If you’ve changed your mind and want to keep your old password, simply leave this page without any further changes")}</p>"
      ], AbstractView::ALERT_SEVERITY_INFO, true);
    }
    // We are not dealing with a password reset request, but the user is logged in. We have to assume that this is a
    // registration activation link. Tell the user that this account was already activated.
    elseif ($user->isLoggedIn === true) {
      $this->view->setAlert([
        "title"   => $i18n->t("Already Activated"),
        "message" => $i18n->t("You already verified your email address and are logged in, if you want to change your password use the form below."),
      ], AbstractView::ALERT_SEVERITY_INFO);
      return;
    }
    // We are handling a new registration if both, name and mail, are present.
    elseif (!empty($data["name"]) && !empty($data["mail"])) {
      try {
        $userModel->register($data["name"], $data["mail"], $pass);
        $this->view->setAlert([
          "title"   => $i18n->t("Create a password for your account"),
          "message" =>
            "<p>{$i18n->t("You can now create a password for your account via the following form. Please choose a strong password that is hard to guess.")}</p>" .
            "<p>{$i18n->t("We created the following random password for you:")} <code>{$pass}</code></p>" .
            "<p>{$i18n->t("If you like it, write it down and leave this page without any further changes.")}</p>"
        ], AbstractView::ALERT_SEVERITY_INFO, true);
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
    $user->startSession($userModel);
    $this->view->formDisabled["pass"] = true;
    $this->view->inputValues["pass"] = $pass;
  }

  /**
   * Validate the user submitted data for the account tab in the user settings.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The global session model instance.
   * @return null|array
   *   Returns <code>NULL</code> if no error occurred. Otherwise an array containing the alert messages is returned.
   */
  private function validateAccountSettings() {
    global $i18n, $user;
    $errors = null;
    try {
      if (($avatar = Image::upload("avatar", "user/avatar-{$user->id}"))) {
        $this->profile->avatarExt = $avatar["ext"];
      }
    } catch (ImageException $e) {
      $errors["avatar"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("Avatar") ]);
    }
    if (($realName = Validation::inputString("real_name")) !== false) {
      $this->profile->realName = $realName;
    }
    if (($gender = Validation::inputString("gender")) !== false) {
      settype($gender, "int");
      if ($gender === -1) {
        unset($this->profile->gender);
      }
      else {
        if ($gender === 0 || $gender === 1) {
          $this->profile->gender = $gender;
        }
        else {
          $errors["gender"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("Gender") ]);
        }
      }
    }
    if (($country = Validation::inputString("country")) !== false) {
      $countries = $i18n->getCountries(I18nModel::COUNTRY_KEY_NAME);
      if (in_array($country, $countries)) {
        $this->profile->countryId = $countries[$country]["id"];
      }
      else {
        $errors["country"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("Country") ]);
      }
    }
    if (($timezone = Validation::inputString("timezone")) !== false) {
      if (in_array($timezone, timezone_identifiers_list())) {
        $this->profile->timezone = $timezone;
      }
      else {
        $errors["timezone"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("Time Zone") ]);
      }
    }
    // @todo Validate profile text
    if (($birthday = Validation::inputString("birthday")) !== false) {
      $birthdayTimestamp = strtotime($birthday);
      if (checkdate(date("m", $birthdayTimestamp), date("d", $birthdayTimestamp), date("Y", $birthdayTimestamp))) {
        $this->profile->birthday = $birthday;
      }
      else {
        $errors["birthday"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("Date of Birth") ]);
      }
    }
    if (isset($_POST["website"]) && !empty($_POST["website"])) {
      if (($website = Validation::inputUrl("website")) !== false) {
        $this->profile->website = $website;
      }
      else {
        $errors["website"] = $i18n->t("The submitted {0} is not valid or empty.", [ $i18n->t("Website") ]);
      }
    }
    if (isset($_POST["private"])) {
      $this->profile->private = true;
    }
    if ($errors) {
      return $errors;
    }
//    $this->profile->commit();
    $this->view->setAlert(
      $i18n->t("Your {0} has been updated.", [ $i18n->t("Account Settings") ]),
      AbstractView::ALERT_SEVERITY_SUCCESS
    );
    $this->view->setAlert("<pre>" . print_r($_POST, true) . "</pre>");
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

  /**
   * Validate the submitted settings of the dangerzone tab.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return null|array
   *   <code>NULL</code> if everything went well, otherwise an array containing the error alert messages.
   */
  private function validateDangerzoneSettings() {
    global $i18n, $user;
    if (($action = Validation::inputString("action")) === false) {
      $this->setPresentation("Error\\BadRequest");
      return;
    }
    switch ($action) {
      case "user-deactivate":
        $this->__constructLogout();
        break;

      case "user-delete":
        $this->__constructLogout();
        break;

      case $user->sessionId:
        $this->__constructLogout();
        break;

      default:
        // Not only delete the requested session, also generate a new one for the current user tighten security.
        $user->deleteSession($action);
        $this->view->setAlert($i18n->t("The session was successfully terminated."), AbstractView::ALERT_SEVERITY_SUCCESS);
        break;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Associative array containing the breadcrumb trail for this presenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
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
        [ $i18n->r("/user"), "<i class='icon icon--info-circled'></i> {$i18n->t("Profile")}", [ "class" => "menuitem--separator", "title" => "Go to your profile page." ]],
        [ $i18n->r("/user/account-settings"), "<i class='icon icon--user'></i> {$i18n->t("Account")}", [ "title" => $i18n->t("Manage your basic account settings.") ]],
        [ $i18n->r("/user/notification-settings"), "<i class='icon icon--bell'></i> {$i18n->t("Notifications")}", [ "title" => $i18n->t("Manage your notification settings.") ]],
        [ $i18n->r("/user/mail-settings"), "<i class='icon icon--mail'></i> {$i18n->t("Mail")}", [ "title" => $i18n->t("Change your email address.") ]],
        [ $i18n->r("/user/password-settings"), "<i class='icon icon--lock'></i> {$i18n->t("Password")}", [ "title" => $i18n->t("Change your password.") ]],
        [ $i18n->r("/user/dangerzone-settings"), "<i class='icon icon--alert'></i> {$i18n->t("Dangerzone")}", [ "class" => "menuitem--delete", "title" => $i18n->t("Deactivate or delete your account.") ]],
      ],
    ];
  }

}
