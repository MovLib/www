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
use \MovLib\Utility\String;
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
    parent::__construct($userPresenter, "{$i18n->t(($this->tab = $tab))} {$i18n->t("Settings")}");
    $this->stylesheets[] = "modules/user.css";
    $this->attributes["action"] = "patch";
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

  /**
   * Get the HTML for the account settings page.
   *
   * @see \MovLib\View\HTML\User\UserSettingsView::getFormContent()
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @return string
   *   The account settings content ready for print.
   */
  private function getAccountTab() {
    global $i18n;
    $this->attributes["enctype"] = self::ENCTYPE_BINARY;
    if (($avatar = $this->presenter->profile->getAvatarRoute()) === false) {
      $avatar = "<i class='icon icon--user avatar--150'></i>";
    }
    else {
      $avatar = "<img class='avatar avatar--150' alt='{$i18n->t("{0}’s avatar.", [ String::checkPlain($this->presenter->profile->name) ])}' height='150' src='{$avatar}' width='150'>";
    }
    return
      "<div class='row'>" .
        "<div class='span span--6'>" .
          "<p><label for='avatar'>{$i18n->t("Avatar")}</label>{$this->input("avatar", [
            "accept"   => "image/jpeg,image/png,image/svg+xml",
            "type"     => "file",
          ])}</p>" .
        "</div>" .
        "<div class='span span--3'>{$avatar}</div>" .
      "</div>" .
      "<p><label for='real_name'>{$i18n->t("Real Name")}</label>{$this->input("real_name", [
        "class" => "input--block-level",
        "title" => $i18n->t("Please enter your real name in this field."),
      ])}</p>" .
      "<p><span class='label'>{$i18n->t("Gender")}</span><span class='radio' title='{$i18n->t("Please select your gender.")}'>" .
        "<label>{$i18n->t("Male")}{$this->input("gender", [ "type" => "radio", "value" => "0" ])}</label>" .
        "<label>{$i18n->t("Female")}{$this->input("gender", [ "type" => "radio", "value" => "1" ])}</label>" .
        "<label>{$i18n->t("Unknown")}{$this->input("gender", [ "type" => "radio", "value" => "" ])}</label>" .
      "</span></p>" .
      "<p><label for='country'>{$i18n->t("Country")}</label>{$this->inputDatalist(
        [ "country", [ "class" => "input--block-level" ]],
        [ "countries", array_column($i18n->getCountries()["id"], "name") ]
      )}</p>" .
      "<p><label for='timezone'>{$i18n->t("Time Zone")}</label>{$this->inputDatalist(
        [ "timezone", [ "class" => "input--block-level", "placeholder" => "UTC", "value" => $this->presenter->profile->timezone ]],
        [ "timezones", timezone_identifiers_list() ]
      )}</p>" .
      "<p><label for='profile'>{$i18n->t("About You")}</label>{$this->input("profile", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("This text will appear on your profile page."),
      ], "textarea")}</p>" .
      // @todo Language selector for profile and content editable!
      "<p><label for='birthday'>{$i18n->t("Date of Birth")}</label>{$this->input("birthday", [
        "class" => "input--block-level",
        "type"  => "date",
      ])}</p>" .
      // @todo Display help for formatting, type date works in nearly no browser! Or use multiple selects?
      "<p><label for='website'>{$i18n->t("Website")}</label>{$this->input("website", [
        "class"       => "input--block-level",
        "placeholder" => "http://example.com/",
        "type"        => "url",
      ])}</p>" .
      "<p><label>{$i18n->t("Keep my data private!")}{$this->help($i18n->t(
        "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
        "used by MovLib for anonymous demographical evaluation of usage statistics and ratings. By providing basic data " .
        "like gender and country scientiest around the world are enabled to research the human interests in movies more " .
        "closely. Of course your real name won’t be used for anything!"
      ))}{$this->input("private", [ "type" => "checkbox" ])}</label></p>" .
      "<p>{$this->submit($i18n->t("Update account settings"))}</p>"
    ;
  }

  private function getNotificationTab() {
    global $i18n;
    return
      "<div class='alert alert--info' role='alert'>" .
        "<h4 class='alert__title'>{$i18n->t("Check back later")}</h4>" .
        "{$i18n->t("The notification system isn’t implemented yet.")}" .
      "</div>"
    ;
  }

  private function getMailTab() {
    global $i18n;
    return
      "<p>{$i18n->t(
        "MovLib takes your privacy seriously, that’s why your email address will never show up in public. In fact, it " .
        "stays top secret like your password. If you’d like to manage when to receive messages from MovLib go to the " .
        "{0}notification settings{1}.",
        [ "<a href='{$i18n->r("/user/notification-settings")}'>", "</a>" ]
      )}</p>" .
      "<hr>" .
      "<p><label for='current-mail'>{$i18n->t("Current email address")}</label>{$this->input("current-mail", [
        "autofocus",
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your current email address"),
        "required",
        "title"       => $i18n->t("Please enter your current email address in this field."),
        "type"        => "email",
      ])}</p>" .
      "<p><label for='new-mail'>{$i18n->t("New email address")}</label>{$this->input("new-mail", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Enter your new email address"),
        "required",
        "title"       => $i18n->t("Please enter your new email address in this field."),
        "type"        => "email",
      ])}</p>" .
      "<p><label for='pass'>{$i18n->t("Password")}</label>{$this->input("pass", [
        "autocomplete" => "off",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your password"),
        "required",
        "title"        => $i18n->t("Please enter your current password to verify this action."),
        "type"         => "password",
      ])}</p>" .
      "<p>{$this->submit(
        $i18n->t("Change email address"),
        $i18n->t("Click here after you’ve filled out all fields.")
      )}</p>"
    ;
  }

  private function getPasswordTab() {
    global $i18n;
    // Only display the notice and the generated password if we aren't displaying this form directly after the
    // registration or a reset password request.
    $notice = "";
    if (!isset($this->formDisabled["pass"])) {
      $notice =
        "<p>{$i18n->t("Choose a strong password, which is easy to remember but still hard to crack. To help you, we " .
          "generated a password from the most frequent words in American English:")}&nbsp;<code>" .
          Crypt::randomUserPassword() . "</code></p>" .
        "<hr>"
      ;
    }
    return
      $notice .
      "<p><small class='form-help'>{$this->a($i18n->r("/user/reset-password"), $i18n->t("Forgotten your current password?"), [
        "title" => $i18n->t("Click this link if you forgot your password."),
      ])}</small><label for='current-password'>{$i18n->t("Current password")}</label>{$this->input("pass", [
        "autocomplete" => "off",
        "autofocus",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your current password"),
        "required",
        "title"        => $i18n->t("Please enter your current password in this field."),
        "type"         => "password",
      ])}</p>" .
      "<p><label for='new-password'>{$i18n->t("New password")}</label>{$this->input("new-pass", [
        "autocomplete" => "off",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your new password"),
        "required",
        "title"        => $i18n->t("Please enter your new password in this field."),
        "type"         => "password",
      ])}</p>" .
      "<p><label for='confirm-password'>{$i18n->t("Confirm password")}</label>{$this->input("confirm-pass", [
        "autocomplete" => "off",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your new password again"),
        "required",
        "title"        => $i18n->t("Please enter your new password again in this field, we want to make sure that you don’t mistype this."),
        "type"         => "password",
      ])}</p>" .
      "<p>{$this->submit(
        $i18n->t("Change password"),
        $i18n->t("Click here after you’ve filled out all fields.")
      )}</p>"
    ;
  }

  private function getDangerzoneTab() {
    global $i18n;
    return
      "<div class='alert alert--info' role='alert'>" .
        "<h4 class='alert__title'>{$i18n->t("Check back later")}</h4>" .
        "{$i18n->t("The dangerzone system isn’t implemented yet.")}" .
      "</div>"
    ;
  }

}
