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
   * @param \MovLib\Presenter\UserPresenter $presenter
   *   The presenter controlling this view.
   * @param string $tab
   *   The name of the tab that should be rendered. Have a look at the <code>get*Tab</code> methods in this class. If
   *   no value is passed along the value submitted by the server will be used.
   */
  public function __construct($presenter, $tab = null) {
    global $i18n;
    parent::__construct($presenter, "{$i18n->t($tab)} {$i18n->t("Settings")}", [ "/assets/css/modules/user.css" ]);
    $this->tab = $tab || $_SERVER["TAB"];
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * {@inheritdoc}
   */
  public function getFormContent() {
    global $i18n;
    return
      "<div class='row'>" .
        "<aside class='span span--3'>{$this->getSecondaryNavigation($i18n->t("Settings navigation"), [
          [ $i18n->r("/user/settings"), $i18n->t("Account"), [ "title" => $i18n->t("Manage your basic account settings.") ]],
          [ $i18n->r("/user/settings/password"), $i18n->t("Password"), [ "title" => $i18n->t("Change your password.") ]],
        ])}</aside>" .
        "<div class='span span--9'>{$this->{"get{$this->tab}Tab"}()}</div>" .
      "</div>"
    ;
  }

  private function getAccountTab() {
    global $i18n;
    return "";
  }

  private function getPasswordTab() {
    global $i18n;
    return
      "<p><small>{$this->a($i18n->r("/user/reset-password"), $i18n->t("Forgotten your current password?"), [
        "class" => "pull-right",
        "title" => $i18n->t("Click this link if you forgot your password."),
      ])}</small><label for='current-password'>{$i18n->t("Current password")}</label>{$this->getInputElement("current-password", [
        "autofocus",
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your current password"),
        "required",
        "tabindex"    => $this->getTabindex(),
        "title"       => $i18n->t("Please enter your current password in this field."),
        "type"        => "password",
      ])}</p>" .
      "<p><label for='new-password'>{$i18n->t("New password")}</label>{$this->getInputElement("new-password", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your new password"),
        "required",
        "tabindex"    => $this->getTabindex(),
        "title"       => $i18n->t("Please enter your new password in this field."),
        "type"        => "password",
      ])}</p>" .
      "<p><label for='confirm-password'>{$i18n->t("Confirm password")}</label>{$this->getInputElement("confirm-password", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your new password again"),
        "required",
        "tabindex"    => $this->getTabindex(),
        "title"       => $i18n->t("Please enter your new password again in this field, we want to make sure that you don’t mistype this."),
        "type"        => "password",
      ])}</p>" .
      "<p><button class='button button--success button--large' name='submitted' tabindex='{$this->getTabindex()}' title='{$i18n->t(
        "Click here after you’ve filled out all fields."
      )}' type='submit'>{$i18n->t("Change password")}</button></p>"
    ;
  }

}
