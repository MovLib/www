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
namespace MovLib\View\HTML\User;

use \MovLib\Utility\Crypt;
use \MovLib\View\HTML\AbstractFormView;

/**
 * User password settings form template.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserPasswordSettingsView extends AbstractFormView {
  use \MovLib\View\HTML\TraitSecondaryNavigationView;

  /**
   * Instantiate new user password settings view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\User\UserPasswordSettingsPresenter $presenter
   *   The presenting presenter.
   * @param array $elements
   *   Numeric array of form elements that should be attached to this view.
   */
  public function __construct($presenter, $elements) {
    global $i18n;
    $this->initForm($presenter, $i18n->t("Password Settings"), $elements);
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryContent() {
    global $i18n;
    // We are handling a new registration if the current password element is disabled. The notice should only be
    // displayed if this is not the case.
    $notice = "";
    if ($this->formElements["pass"]->disabled === false) {
      $pass = Crypt::randomUserPassword();
      $notice = "<p>{$i18n->t(
        "Choose a strong password, which is easy to remember but still hard to crack. To help you, we generated a " .
        "password from the most frequent words in American English:"
      )}&nbsp;<code>{$pass}</code></p><hr>";
    }
    return
      "{$notice}{$this->formOpen()}<small class='form-help'>{$this->a(
        $i18n->r("/user/reset-password"),
        $i18n->t("Forgot your current password?"),
        [ "title" => $i18n->t("Follow this link if you forgot your password.") ]
      )}</small><p>{$this->renderInput("pass", [
        "attributes" => [
          "autofocus",
          "placeholder" => $i18n->t("Enter your current password"),
          "title" => $i18n->t("Please enter your current password to confirm the password change."),
        ],
        "class" => "input--block-level",
        "label" => $i18n->t("Current Password"),
      ])}</p><p>{$this->renderInput("new", [
        "attributes" => [
          "placeholder" => $i18n->t("Enter your new password"),
          "title" => $i18n->t("Please enter your desired new password in this field."),
        ],
        "class" => "input--block-level",
        "label" => $i18n->t("New Password"),
      ])}</p><p>{$this->renderInput("confirm", [
        "attributes" => [
          "placeholder" => $i18n->t("Enter your new password again"),
          "title" => $i18n->t("Please enter your desired new password again, we want to make sure that you don’t mistype this."),
        ],
        "class" => "input--block-level",
        "label" => $i18n->t("Confirm Password"),
      ])}</p>{$this->formClose([
        "attributes" => [ "value" => $i18n->t("Change Password") ],
        "class" => "button--large",
      ])}"
    ;
  }

}
