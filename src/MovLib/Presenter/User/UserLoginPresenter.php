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

use \MovLib\Exception\ErrorException;
use \MovLib\Exception\ValidatorException;
use \MovLib\Model\UserModel;
use \MovLib\Presenter\AbstractPresenter;
use \MovLib\View\HTML\FormElement\Action\SubmitAction;
use \MovLib\View\HTML\FormElement\Input\MailInput;
use \MovLib\View\HTML\FormElement\Input\PasswordInput;
use \MovLib\View\HTML\Redirect;
use \MovLib\View\HTML\User\UserLoginView;

/**
 * Handles user log ins.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserLoginPresenter extends AbstractPresenter {

  /**
   * The user login view.
   *
   * @var \MovLib\View\HTML\User\UserLoginView
   */
  public $view;

  /**
   * Instantiate new user login form.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   * @param boolean $redirect [optional]
   *   The user will be redirected to the current <var>$_SERVER["REQUEST_URI"]</var> if set to <code>TRUE</code>.
   *   Otherwise the default redirect after successful login will be executed.
   */
  public function __construct($redirect = false) {
    global $i18n, $user;
    if ($user->isLoggedIn === true) {
      $this->presentation = (new Redirect($i18n->r("/my"), 302))->getRenderedView();
      return;
    }
    $this->init($redirect);
  }

  /**
   * We need to keep the logic in a separate method in order to ease extension of this class.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   * @param boolean $redirect [optional]
   *   The user will be redirected to the current <var>$_SERVER["REQUEST_URI"]</var> if set to <code>TRUE</code>.
   *   Otherwise the default redirect after successful login will be executed.
   */
  protected function init($redirect = false) {
    global $i18n, $user;

    // Snatch the current requested URI if a redirect was requested and no redirect is already active.
    if ($redirect === true && empty($_GET["rediret_to"])) {
      $_GET["redirect_to"] = $_SERVER["REQUEST_URI"];
    }

    // Ensure we are using the correct route internally, this presenter is instantiated by other code blocks if
    // something goes wrong and the user has to re-login.
    $_SERVER["REQUEST_URI"] = $i18n->r("/user/login");

    // We always need the view, no matter which request method. If the user requested the page via GET we need it to
    // display the form and if an error occurred after submission of the form via POST we need it to set alert messages
    // and render the form.
    $this->view = (new UserLoginView($this, [
      (new MailInput([ "autofocus", "class" => "input--block-level" ]))->required(),
      (new PasswordInput([ "class" => "input--block-level" ]))->required(),
      new SubmitAction([ "class" => "button--large", "value" => $i18n->t("Sign In") ]),
    ]));

    // Continue validation if this form was just submitted and all form elements are valid.
    if ($_SERVER["REQUEST_METHOD"] === "POST" && $this->view->valid) {
      try {
        // Try to load the user from the database and validate the submitted password against this user.
        $userModel = new UserModel(UserModel::FROM_MAIL, $this->view->formElements["mail"]->value);
        if (password_verify($this->view->formElements["pass"]->value, $userModel->pass) === false) {
          throw new ValidatorException("Password is invalid.");
        }

        // If we were able to load the user and the password is valid, allow 'em to enter.
        $user->startSession($userModel);

        // Ensure that the user know's that the log in succeded.
        $_SESSION["ALERTS"][] = [
          $i18n->t("Log in was successful, welcome back {0}!", [ "<b>{$userModel->name}</b>" ]),
          UserLoginView::ALERT_SEVERITY_SUCCESS
        ];

        // Redirect the user to the requested redirect destination and if none was set to the personalized dashboard.
        $this->view = new Redirect(!empty($_GET["redirect_to"]) ? $_GET["redirect_to"] : $i18n->r("/my"), 302);
      } catch (ErrorException $e) {
        $this->view->setAlert("We either don’t know the email address, or the password was wrong.", UserLoginView::ALERT_SEVERITY_ERROR);
      }
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getBreadcrumb() {
    global $i18n;
    return [[ $i18n->r("/users"), $i18n->t("Users"), [ "title" => $i18n->t("Have a look at our user statistics.") ] ]];
  }

}
