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

use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractFormView;
use \MovLib\View\HTML\Alert;

/**
 * User mail settings form template.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserMailSettingsView extends AbstractFormView {
  use \MovLib\View\HTML\TraitSecondaryNavigationView;

  /**
   * Instantiate new user mail settings view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param \MovLib\Presenter\User\UserMailSettingsPresenter $presenter
   *   The presenting presenter.
   * @param array $elements
   *   Numeric array of form elements that should be attached to this view.
   */
  public function __construct($presenter, $elements) {
    global $i18n;
    $this->initForm($presenter, $i18n->t("Mail Settings"), $elements);
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryContent() {
    global $i18n;
    $currentMail = new Alert($i18n->t("Your current email address is {0}.", [ String::placeholder($this->presenter->profile->mail) ]), [ "severity" => Alert::SEVERITY_INFO ]);
    return
      "<p>{$i18n->t(
        "MovLib takes your privacy seriously. That’s why your email address will never show up in public. In fact, it " .
        "stays top secret like your password. If you’d like to manage when to receive messages from MovLib go to the " .
        "{0}notification settings{1}.",
        [ "<a href='{$i18n->r("/user/notification-settings")}'>", "</a>" ]
      )}</p>{$currentMail}{$this->formOpen()}<p>{$this->renderInput("mail", [
        "class" => "input--block-level",
        "attributes" => [
          "autofocus",
          "placeholder" => $i18n->t("Please enter your new email address"),
        ],
        "label" => $i18n->t("New Email Address"),
      ])}</p><p>{$this->renderInput("pass", [
        "class" => "input--block-level",
        "attributes" => [
          "autocomplete" => "off",
          "placeholder" => $i18n->t("Please enter your current password to verify this action."),
        ],
      ])}</p>{$this->formClose([ "class" => "button--large", "attributes" => [ "value" => $i18n->t("Change Mail") ] ])}"
    ;
  }

}
