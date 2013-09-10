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

use \MovLib\Model\UserModel;
use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\Utility\String;
use \MovLib\View\HTML\Alert;
use \MovLib\View\HTML\Input\PasswordInput;
use \MovLib\View\HTML\User\UserPasswordSettingsView;

/**
 * Takes care of user password settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserPasswordSettingsPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user password settings presenter.
   *
   * @global \MovLib\Model\UserModel $user
   */
  public function __construct() {
    global $user;
    $this->checkAuthorization();
    $this->profile = new UserModel(UserModel::FROM_ID, $user->id);
    new UserPasswordSettingsView($this, [
      new PasswordInput(),
      new PasswordInput("new"),
      new PasswordInput("confirm"),
    ]);
    // This form only consists of password inputs, disable any autocompletion or save password functionality of the
    // browser via the autocomplete-attribute.
    $this->view->formAttributes["autocomplete"] = "off";
  }

  /**
   * Continue validation after the basic form elements where validated by the view itself.
   *
   * @todo Is it a problem for a user (or could it be a problem for some) that we display their password to them? It can
   *       help user's if they see it again, maybe they have a typo and this allows them to directly identify that. On
   *       the other hand some might think that we aren't storing the password in a secure manner. We could display
   *       another alert (or extend the existing one) explaining to them that we only store a hashed version and that
   *       the current connection is 100% encrypted and secure. (Funny considerations at this point, other services send
   *       their passwords around the world via emails, perfect for our friends of the NSA.)
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   */
  public function validate() {
    global $i18n;
    $message = null;
    if (password_verify($this->view->formElements["pass"]->value, $this->profile->pass) === false) {
      $this->view->formElements["pass"]->invalid();
      $message = $i18n->t("The submitted password is not valid.");
    }
    elseif ($this->view->formElements["pass"]->value == $this->view->formElements["new"]->value) {
      $this->view->formElements["pass"]->invalid();
      $this->view->formElements["new"]->invalid();
      $this->view->formElements["confirm"]->invalid();
      $message = $i18n->t("Your desired new password is equal to your current password.");
    }
    elseif ($this->view->formElements["new"]->value != $this->view->formElements["confirm"]->value) {
      $this->view->formElements["new"]->invalid();
      $this->view->formElements["confirm"]->invalid();
      $message = $i18n->t("The confirmation password is not equal to your desired new password.");
    }
    if ($message) {
      $this->view->addAlert(new Alert($message, [ "severity" => Alert::SEVERITY_ERROR ]));
      return $this;
    }
    $this->profile->updatePassword($this->view->formElements["new"]->value);
    $this->view->addAlert(new Alert($i18n->t(
      "Your password was successfully updated to {0}.", [ String::placeholder($this->view->formElements["new"]->value) ]),
      [
        "block"    => true,
        "severity" => Alert::SEVERITY_SUCCESS,
        "title"    => $i18n->t("Successfully Changed Password"),
      ]
    ));
    return $this;
  }

}
