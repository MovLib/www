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

use \MovLib\Model\UserModel;
use \MovLib\Model\I18nModel;
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
    parent::__construct($userPresenter, "{$i18n->t(($this->tab = $tab))} {$i18n->t("Settings")}");
    $this->stylesheets[] = "modules/user.css";
    // We have to disable this feature on the complete form, otherwise Webkit will ignore it.
    $this->attributes["autocomplete"] = "off";
    switch ($tab) {
      case "Account":
        $this->attributes["enctype"] = AbstractFormView::ENCTYPE_BINARY;
        break;
    }
    $this->scripts["modules"][] = "user";
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
    // This form has a file upload!
    $this->attributes["enctype"] = self::ENCTYPE_BINARY;
    return

      // --------------------------------------------------------------------------------------------------------------- Avatar

      "<div class='row'>" .
        "<div class='span span--3'>{$this->getImage($this->presenter->profile, UserModel::IMAGESTYLE_BIG)}</div>" .
        "<div class='span span--6'>" .
          "<p><label for='avatar'>{$i18n->t("Avatar")}{$this->help($i18n->t(
            "Allowed image extensions: {0}<br>Maximum file size: {1,number}&thinsp;MB",
            [ implode(", ", array_values($this->presenter->profile->imageSupported)), ini_get("upload_max_filesize") ]
          ))}</label>{$this->input("avatar", [
            "accept" => implode(",", array_keys($this->presenter->profile->imageSupported)),
            "type"   => "file",
          ])}</p>" .
        "</div>" .
      "</div>" .

      // --------------------------------------------------------------------------------------------------------------- Real Name

      "<p><label for='real_name'>{$i18n->t("Real Name")}{$this->help($i18n->t(
        "Your {0} will be displayed on your profile page.", [ $i18n->t("Real Name") ]
      ))}</label>{$this->input("real_name", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("Real Name"),
        "title"       => $i18n->t("Please enter your real name in this field."),
      ])}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Sex

      "<p><label>{$i18n->t("Sex")}{$this->help($i18n->t(
        "Your {0} will be displayed on your profile page and is used to create demographic evaluations.", [ $i18n->t("Sex") ]
      ))}</label><span title='{$i18n->t("Please select your sex.")}'>{$this->radioGroup("sex", [
        $i18n->t("Unknown"),
        $i18n->t("Male"),
        $i18n->t("Female"),
      ])}</span></p>" .

      // --------------------------------------------------------------------------------------------------------------- Country

      "<p><label for='country'>{$i18n->t("Country")}{$this->help($i18n->t(
        "Your {0} will be displayed on your profile page and is used to create demographic evaluations.", [ $i18n->t("Country") ]
      ))}</label>{$this->select("country", $this->selectGetCountries(), [ "class" => "input--block-level" ])}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Timezone

      "<p><label for='timezone'>{$i18n->t("Time Zone")}{$this->help($i18n->t(
        "The {0} is used to display the correct dates and times.", [ $i18n->t("Time Zone") ]
      ))}</label>{$this->select("timezone", timezone_identifiers_list(), [ "class" => "input--block-level" ])}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Language

      "<p><label for='language'>{$i18n->t("Language")}{$this->help($i18n->t(
        "Enter your {0}, simply click in the field and start typing.", [ $i18n->t("Language") ]
      ))}</label>{$this->select("language", $this->selectGetSystemLanguages(), [ "class" => "input--block-level" ])}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Profile
      // @todo Language selector for profile and content editable!

      "<p><label for='profile'>{$i18n->t("About You")}{$this->help($i18n->t(
        "You can enter a short profile text to tell other people who you are."
      ))}</label>{$this->input("profile", [
        "class"       => "input--block-level",
        "placeholder" => $i18n->t("This text will appear on your profile page."),
      ], "textarea", $this->presenter->profile->profile)}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Birthday

      "<p><label for='birthday'>{$i18n->t("Date of Birth")}{$this->help(
        $i18n->t("Your {0} will be displayed on your profile page and is used to create demographic evaluations.", [ $i18n->t("birthday") ]) .
        " " .
        $i18n->t(
          "The format for this field is <em>yyyy-mm-dd</em> where <em>yyyy</em> is the year, <em>mm</em> the month, " .
          "and <em>dd</em> the day. Your browser might display a datepicker and automatically enter the correct format. " .
          "The minimum age is 6 and the maximum age is 120 years."
        )
      )}</label>{$this->input("birthday", [
        "class"       => "input--block-level",
        "placeholder" => "yyyy-mm-dd",
        "type"        => "date",
        "max"         => date("Y-m-d", (time() - 1.893e+8)),
        "min"         => date("Y-m-d", (time() - 3.78683e9)),
      ])}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Website

      "<p><label for='website'>{$i18n->t("Website")}{$this->help($i18n->t(
        "Your website will be displayed on your profile page."
      ))}</label>{$this->input("website", [
        "class"       => "input--block-level",
        "placeholder" => "http://example.com/",
        "type"        => "url",
      ])}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Private Flag

      "<p><label class='checkbox inline'>{$i18n->t("Keep my data private!")}{$this->input("private", [
        "type"  => "checkbox"
      ])}</label>{$this->help($i18n->t(
        "Check the following box if you’d like to hide your private data on your profile page. Your data will only be " .
        "used by MovLib for anonymous demographical evaluation of usage statistics and ratings. By providing basic data " .
        "like sex and country, scientists around the world are enabled to research the human interests in movies more " .
        "closely. Of course your real name won’t be used for anything!"
      ))}</p>" .

      // --------------------------------------------------------------------------------------------------------------- Submit

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
        "MovLib takes your privacy seriously. That’s why your email address will never show up in public. In fact, it " .
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
      "<p><small class='form-help'>{$this->a($i18n->r("/user/reset-password"), $i18n->t("Forgot your current password?"), [
        "title" => $i18n->t("Click this link if you forgot your password."),
      ])}</small><label for='current-password'>{$i18n->t("Current password")}</label>{$this->input("pass", [
        "autofocus",
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your current password"),
        "required",
        "title"        => $i18n->t("Please enter your current password in this field."),
        "type"         => "password",
      ])}</p>" .
      "<p><label for='new-password'>{$i18n->t("New password")}</label>{$this->input("new-pass", [
        "class"        => "input--block-level",
        "placeholder"  => $i18n->t("Enter your new password"),
        "required",
        "title"        => $i18n->t("Please enter your new password in this field."),
        "type"         => "password",
      ])}</p>" .
      "<p><label for='confirm-password'>{$i18n->t("Confirm password")}</label>{$this->input("confirm-pass", [
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

  /**
   * The dangerzone tab of the user settings contains all stuff related to sessions and the account itself. The user is
   * able to deactivate or delete the account here.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance.
   * @global \MovLib\Model\SessionModel $user
   *   The currently logged in user.
   * @return string
   *   The mark-up for the dangerzone tab ready for print.
   */
  private function getDangerzoneTab() {
    global $i18n, $user;
    $buttonTitle = $i18n->t("Terminate this session by clicking on this button.");
    $buttonText = $i18n->t("Terminate");
    $sessions = $user->getActiveSessions();
    $c = count($sessions);
    $sessionsTable = "";
    for ($i = 0; $i < $c; ++$i) {
      if ($sessions[$i]["session_id"] === $user->sessionId) {
        $active = " class='warning'";
        $button = "title='{$i18n->t("If you click here, you’ll be logged out from your current session.")}'>{$i18n->t("Logout")}";
      }
      else {
        $active = "";
        $button = "title='{$buttonTitle}'>{$buttonText}";
      }
      $sessionsTable .=
        "<tr{$active}>" .
          "<td class='small'><tt>{$sessions[$i]["session_id"]}</tt></td>" .
          "<td class='small'><tt>{$sessions[$i]["user_agent"]}</tt></td>" .
          "<td><tt>{$sessions[$i]["ip_address"]}</tt></td>" .
          "<td><button class='button button--danger' name='action' type='submit' tabindex='{$this->getTabindex()}' value='{$sessions[$i]["session_id"]}' {$button}</button></td>" .
        "</tr>"
      ;
    }
    return
      "<h2>{$i18n->t("Active Sessions")}</h2>" .
      "<table id='user-sessions' class='table-hover'>" .
        "<caption>{$i18n->t(
          "The following table contains a listing of all your active sessions. You can terminate any session by clicking " .
          "on the button to the right. Your current session is highlighted with a yellow background color and the button " .
          "reads “logout” instead of “terminate”. If you have the feeling that some sessions that are listed weren’t " .
          "initiated by you, terminate them and consider to {0}set a new password{1} to ensure that nobody else has " .
          "access to your account.",
          [ "<a href='{$i18n->r("/user/password-settings")}'>", "</a>" ]
        )}</caption>" .
        "<colgroup><col><col><col><col></colgroup>" .
        "<thead><tr><th>{$i18n->t("Session ID")}</th><th>{$i18n->t("User Agent")}</th><th>{$i18n->t("IP address")}</th><th></th></tr></thead>" .
        "<tbody>{$sessionsTable}</tbody>" .
      "</table>" .
      "<h2>{$i18n->t("Deacitvate Account")}</h2>" .
      "<div class='alert alert--error alert--block'>" .
        $this->help(
          "<b>{$i18n->t("Why can’t I delete my account entirely?")}</b><br>" .
          $i18n->t(
            "You agreed to release all your contributions to the MovLib database along with an open and free " .
            "license, therefore each of your edits is tightly bound to your account. To draw an analogy, you cannot " .
            "withdraw your copyright if you create something in the European jurisdiction. The delection of your " .
            "account would be just like that. Your personal data can of course be deleted, only your user name will " .
            "remain visible to the public."
          )
        ) .
        "<p>{$i18n->t(
          "If you want to deactivate your account, for whatever reason, use one of the two options below. " .
          "You can either simply deactivate, or delete all your {0}personal data{1} and deactivate.", [
            "<a href='{$i18n->r("/user/account-settings")}'>", "</a>"
          ]
        )}</p>" .
        "<p class='form-actions'>" .
          "<button class='button button--primary' name='action' type='submit' value='user-deactivate'>{$i18n->t("Deactivate")}</button>&nbsp;" .
          "<button class='button button--danger' name='action' type='submit' value='user-delete'>{$i18n->t("Delete and Deactivate")}</button>" .
        "</p>" .
      "</div>"
    ;
  }

}
