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
 * Render the user's control center where she/he can change all account settings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserSettingsView extends AbstractFormView {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user presenter controlling this view.
   *
   * @var \MovLib\Presenter\UserPresenter
   */
  protected $presenter;

  /**
   * The name of the settings tab that should be rendered.
   *
   * @var string
   */
  public $tab;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user settings view.
   *
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The presenter controlling this view.
   * @param string $tab
   *   The name of the tab that should be rendered. Have a look at the <code>get*Tab</code> methods in this class. If
   *   no value is passed along the value submitted by the server will be used.
   */
  public function __construct($userPresenter, $tab) {
    global $i18n;
    parent::__construct($userPresenter, "{$i18n->t(($this->tab = $tab))} {$i18n->t("Settings")}", [
      "/assets/css/modules/user.css"
    ]);
    $this->activeHeaderUserNavigationPoint = 0;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * {@inheritdoc}
   */
  public function getFormContent() {
    $nav = $this->presenter->getSecondarySettingsNavigation();
    return
      "<div class='row'>" .
        "<aside class='span span--3'>{$this->getSecondaryNavigation($nav["title"], $nav["points"])}</aside>" .
        "<div class='span span--9'>{$this->{"get{$this->tab}Tab"}()}</div>" .
      "</div>"
    ;
  }

  private function getAccountTab() {
    global $i18n;
    return "";
  }

  private function getNotificationTab() {
    global $i18n;
    return "";
  }

  private function getMailTab() {
    global $i18n;
    return
      "<p>{$i18n->t("MovLib takes your privacy seriously, that’s why your email address will never show up in public. In fact, it stays top secret like your password. If you’d like to manage when to receive messages from MovLib go to the {0}notification settings{1}.", [
        "<a href='{$i18n->r("/user/notification-settings")}'>", "</a>"
      ])}</p>" .
      "<hr>" .
      "<p><label for='current-mail'>{$i18n->t("Current email address")}</label>{$this->getInputElement("current-mail", [
        "autofocus",
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your current email address"),
        "required",
        "tabindex"    => $this->getTabindex(),
        "title"       => $i18n->t("Please enter your current email address in this field."),
        "type"        => "email",
      ])}</p>" .
      "<p><label for='new-mail'>{$i18n->t("New email address")}</label>{$this->getInputElement("new-mail", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your new email address"),
        "required",
        "tabindex"    => $this->getTabindex(),
        "title"       => $i18n->t("Please enter your new email address in this field."),
        "type"        => "email",
      ])}</p>" .
      "<p><label for='pass'>{$i18n->t("Password")}</label>{$this->getInputElement("pass", [
        "autocomplete" => "off",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your password"),
        "required",
        "tabindex"     => $this->getTabindex(),
        "title"        => $i18n->t("Please enter your current password to verify this action."),
        "type"         => "password",
      ])}</p>" .
      "<p><button class='button button--success button--large' tabindex='{$this->getTabindex()}' title='{$i18n->t(
        "Click here after you’ve filled out all fields."
      )}' type='submit'>{$i18n->t("Change email address")}</button></p>"
    ;
  }

  private function getPasswordTab() {
    global $i18n;
    // Only display the notice and the generated password if we aren't displaying this form directly after the
    // registration or a reset password request.
    $notice = "";
    if (!isset($this->formDisabled["pass"])) {
      $notice =
        "<p>{$i18n->t("Choose a strong password which you can easily remember. To help you, we generated a password for you from the most frequent words in American English for you:")}</p>" .
        "<p class='text-center'><code>" . Crypt::randomUserPassword() . "</code></p>" .
        "<hr>"
      ;
    }
    return
      $notice .
      "<p><small>{$this->a($i18n->r("/user/reset-password"), $i18n->t("Forgotten your current password?"), [
        "class" => "pull-right",
        "title" => $i18n->t("Click this link if you forgot your password."),
      ])}</small><label for='current-password'>{$i18n->t("Current password")}</label>{$this->getInputElement("pass", [
        "autocomplete" => "off",
        "autofocus",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your current password"),
        "required",
        "tabindex"     => $this->getTabindex(),
        "title"        => $i18n->t("Please enter your current password in this field."),
        "type"         => "password",
      ])}</p>" .
      "<p><label for='new-password'>{$i18n->t("New password")}</label>{$this->getInputElement("new-pass", [
        "autocomplete" => "off",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your new password"),
        "required",
        "tabindex"     => $this->getTabindex(),
        "title"        => $i18n->t("Please enter your new password in this field."),
        "type"         => "password",
      ])}</p>" .
      "<p><label for='confirm-password'>{$i18n->t("Confirm password")}</label>{$this->getInputElement("confirm-pass", [
        "autocomplete" => "off",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your new password again"),
        "required",
        "tabindex"     => $this->getTabindex(),
        "title"        => $i18n->t("Please enter your new password again in this field, we want to make sure that you don’t mistype this."),
        "type"         => "password",
      ])}</p>" .
      "<p><button class='button button--success button--large' tabindex='{$this->getTabindex()}' title='{$i18n->t(
        "Click here after you’ve filled out all fields."
      )}' type='submit'>{$i18n->t("Change password")}</button></p>"
    ;
  }

  private function getDangerzoneTab() {
    global $i18n;
    return "";
  }

}
