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
namespace MovLib\Presenter\User;

use \MovLib\Exception\UserException;
use \MovLib\Model\UserModel;
use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\View\HTML\Alert;
use \MovLib\View\HTML\Form;
use \MovLib\View\HTML\Input\MailInput;
use \MovLib\View\HTML\Input\PasswordInput;
use \MovLib\View\HTML\Redirect;
use \MovLib\View\HTML\User\UserLoginView;

/**
 * Takes care of singing in and out of our users.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserLoginPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user login form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   */
  public function __construct() {
    global $i18n, $user;

    // Translate the sign out route, so we can check if the current page is the sign out page.
    $routeLogout = $i18n->r("/user/sign-out", null, [ "absolute" => false ]);

    // If the user is logged in, but didn't request to be signed out, redirect her or him to the personal dashboard.
    if ($user->isLoggedIn === true && $_SERVER["PATH_INFO"] != $routeLogout) {
      $this->view = new Redirect($i18n->r("/my"), 302);
      return;
    }

    // Now we also need to know the translated version of the login route.
    $routeLogin = $i18n->r("/user/login", null, [ "absolute" => false ]);

    // Snatch the current requested URI if a redirect was requested and no redirect is already active. We have to build
    // the complete target URI to ensure that this presenter will receive the submitted form, but at the same time we
    // want to enable ourself to redirect the user after successful sign in to the page she or he requested.
    if ($_SERVER["PATH_INFO"] != $routeLogin && $_SERVER["PATH_INFO"] != $routeLogout) {
      if (empty($_GET["redirect_to"])) {
        $_GET["redirect_to"] = $_SERVER["PATH_INFO"];
      }
      $_GET["redirect_to"] = rawurlencode($_GET["redirect_to"]);
      $routeLogin .= "?redirect_to={$_GET["redirect_to"]}";
    }
    $formAttributes = [ "action" => "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}{$routeLogin}" ];

    // Instantiate the view, pass the user login form along. The user login form will validate itself if we are
    // receiving the form (POST) and call our validate method if basic validation succeded.
    new UserLoginView($this, new Form("login", $this, [ (new MailInput())->required(), new PasswordInput() ], $formAttributes));

    // If the user requested to be signed out, do so.
    if ($user->isLoggedIn === true && $_SERVER["PATH_INFO"] == $routeLogout) {
      $user->destroySession();
      $this->view->addAlert(new Alert($i18n->t("We hope to see you again soon."), [
        "title"    => $i18n->t("You’ve been signed out successfully."),
        "severity" => Alert::SEVERITY_SUCCESS,
      ]));
    }
  }

  /**
   * Continue validation after the basic form elements where validated by the view itself.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   * @param \MovLib\View\HTML\Form $form
   *   The auto-validated form.
   * @return this
   */
  public function validate($form) {
    global $i18n, $user;
    try {
      // Try to load the user from the database and validate the submitted password against this user.
      $userModel = new UserModel(UserModel::FROM_MAIL, $form->elements["mail"]->value);
      if (password_verify($form->elements["pass"]->value, $userModel->pass) === false) {
        // We want to use the same alert message for non-existent user and invalid password, therefor we throw a user
        // exception at this point, so we can catch both errors and set the same alert message.
        throw new UserException("Password is invalid.");
      }

      // If we were able to load the user and the password is valid, allow 'em to enter.
      $user->startSession($userModel);

      // Ensure that the user know's that the log in succeded.
      $_SESSION["ALERTS"][] = new Alert(
        $i18n->t("Log in was successful, welcome back {0}!", [ "<b>{$userModel->name}</b>" ]),
        [ "severity" => Alert::SEVERITY_SUCCESS ]
      );

      // Redirect the user to the requested redirect destination and if none was set to the personalized dashboard.
      $this->view = new Redirect(!empty($_GET["redirect_to"]) ? $_GET["redirect_to"] : $i18n->r("/my"), 302);
    }
    // Never tell the person who's trying to sing in which value was wrong. Both attributes are considered a secret and
    // should never be exposed by our application itself.
    catch (UserException $e) {
      $this->view->addAlert(new Alert($i18n->t("We either don’t know the email address, or the password was wrong."), [ "severity" => Alert::SEVERITY_ERROR ]));
    }
    return $this;
  }

}
