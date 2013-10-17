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
namespace MovLib\Presentation\Profile;

use \IntlDateFormatter;
use \MovLib\Data\Delayed\Mailer;
use \MovLib\Data\User\Full as User;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Presentation\Email\User\Deactivation;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\Button;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\Help;

/**
 * Allows a user to terminate sessions and deactivate the account.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class DangerZoneSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\Profile\TraitProfile;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form for the sessions listing.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  private $formSessions;

  /**
   * The form to deactivate an account.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  private $formDeactivate;

  /**
   * String buffer used to construct the table with the session listing.
   *
   * @var string
   */
  private $sessionsTable;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user danger zone settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $session;

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session
      ->checkAuthorization($i18n->t("You need to sign in to access the danger zone."))
      ->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."))
    ;

    // Start rendering the page.
    $this->init($i18n->t("Danger Zone Settings"));
    $this->user = new User(User::FROM_ID, $session->userId);

    // We must instantiate the form before we create the sessions table, otherwise deletions would happen after the
    // table containing the sessions listing was built. Deleted sessions would still be displayed!
    $this->formSessions = new Form($this, [], "sessions", "deleteSession");
    $buttonText = $i18n->t("Terminate");
    $buttonTitle = $i18n->t("Terminate this session, the associated user agent will be signed out immediately.");
    $sessions = $session->getActiveSessions();
    $c = count($sessions);
    for ($i = 0; $i < $c; ++$i) {
      $sessions[$i]["authentication"] = $i18n->formatDate($sessions[$i]["authentication"], $this->user->timeZoneId, IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
      $sessions[$i]["ip_address"] = inet_ntop($sessions[$i]["ip_address"]);
      $active = null;
      $button = new Button("session_id", $buttonText, [
        "class" => "button--danger",
        "type"  => "submit",
        "value" => $sessions[$i]["session_id"],
        "title" => $buttonTitle,
      ]);
      unset($button->attributes["id"]);
      if ($sessions[$i]["session_id"] == $session->id) {
        $active = " class='warning'";
        $button->attributes["title"] = $i18n->t("If you click this button your active session is terminated and you’ll be signed out!");
        $button->content = $i18n->t("Sign Out");
      }
      $this->sessionsTable .=
        "<tr{$active}>" .
          "<td>{$sessions[$i]["authentication"]}</td>" .
          "<td class='small'><code>{$this->checkPlain($sessions[$i]["user_agent"])}</code></td>" .
          "<td><code>{$sessions[$i]["ip_address"]}</code></td>" .
          "<td class='form-actions'>{$button}</td>" .
        "</tr>"
      ;
    }

    $this->formDeactivate = new Form($this, [], "deactivate", "validateDeactivation");
    $this->formDeactivate->actionElements[] = new InputSubmit([
      "class" => "button--danger",
      "value" => $i18n->t("Request Deactivation"),
    ]);

    if (isset($_GET["token"])) {
      $this->validateToken();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;

    $help = new Help($i18n->t(
      "You agreed to release all your contributions to the {0} database along with an open and free license, therefor " .
      "each of your edits is tightly bound to your account. To draw an analogy, you cannot withdraw your copyright " .
      "if you create something in the European juristiction. The deactivation of your account would be just like that. " .
      "Your {1}personal data{2} is of course deleted and only your username will remain visible to the public.",
      [ "MovLib", "<a href='{$i18n->r("/user/account-settings")}'>", "</a>" ]
    ));

    $deactivate = new Alert("{$help}<p>{$i18n->t(
      "If you want to deactivate your account, for whatever reason, click the button below. All your {0}personal data{1} " .
      "will be purged from our system and you are signed out. To reactivate your account, simply sign in again with " .
      "your email address and secret password (of course you’ll have to re-enter your personal data).",
      [ "<a href='{$i18n->r("/user/account-settings")}'>", "</a>" ]
    )}</p>{$this->formDeactivate}");
    $deactivate->severity = Alert::SEVERITY_ERROR;

    return
      "<h2>{$i18n->t("Active Sessions")}</h2>" .
      "{$this->formSessions->open()}<table class='table-hover' id='profile-sessions'>" .
        "<caption>{$i18n->t(
          "The following table contains a listing of all your active sessions. You can terminate any session by " .
          "clicking on the button to the right. Your current session is highlighted with a yellow background color " .
          "and the button reads “sign out” instead of “terminate”. If you have the feeling that some sessions that are " .
          "listed weren’t initiated by you, terminate them and consider to {0}set a new password{1} to ensure that " .
          "nobody else has access to your account.",
          [ "<a href='{$i18n->r("/user/password-settings")}'>", "</a>" ]
        )}</caption>" .
        "<thead><tr><th>{$i18n->t("Sign In Time")}</th><th>{$i18n->t("User Agent")}</th><th>{$i18n->t("IP address")}</th><th></th></tr></thead>" .
        "<tbody>{$this->sessionsTable}</tbody>" .
      "</table>{$this->formSessions->close()}" .
      "<h2>{$i18n->t("Deactivate Account")}</h2>{$deactivate}"
    ;
  }

  /**
   * Attempt to delete the session identified by the submitted session ID from Memcached and our persistent storage.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   * @throws \MovLib\Exception\Client\RedirectSeeOtherException
   */
  public function deleteSession() {
    global $i18n, $session;
    if ($_POST["session_id"] == $session->id) {
      throw new RedirectSeeOtherException($i18n->r("/profile/sign-out"));
    }
    else {
      try {
        $session->delete($_POST["session_id"]);
      }
      catch (DatabaseException $e) {
        $this->checkErrors([ $i18n->t("The submitted session ID was invalid.") ]);
      }
    }
    return $this;
  }

  /**
   * Delete all personal data and sign out the user.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   */
  public function validateDeactivation() {
    global $i18n;
    Mailer::stack(new Deactivation($this->user));
    http_response_code(202);
    $success = new Alert($i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]));
    $success->title = $i18n->t("Successfully Requested Deactivation");
    $success->severity = Alert::SEVERITY_SUCCESS;
    $info = new Alert($i18n->t("You have to follow the link that we just sent to you via email to complete this action."));
    $info->title = $i18n->t("Important!");
    $info->severity = Alert::SEVERITY_INFO;
    $this->alerts .= "{$success}{$info}";
    return $this;
  }

  /**
   * Validate the submitted authentication token and deactivate the user's account.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @return this
   * @throws \MovLib\Exception\Client\RedirectSeeOtherException
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  private function validateToken() {
    global $i18n, $session;
    $data = $this->user->validateAuthenticationToken($errors, $this->id);
    if ($data && $data["id"] !== $session->userId) {
      throw new UnauthorizedException($i18n->t("The authentication token is invalid, please sign in again and request a new token to deactivate your account."));
    }
    if ($this->checkErrors($errors) === false) {
      $this->user->deactivate();
      $success = new Alert(
        "<p>{$i18n->t("Your account has been successfully deactivated and all your personal data has been purged from our system.")}</p>" .
        "<p>{$i18n->t("To reactivate your account, simply sign in with your email address and secret password.")}</p>"
      );
      $success->title = $i18n->t("Deactivation Successful");
      $success->severity = Alert::SEVERITY_SUCCESS;
      $session->alerts .= $success;
      throw new RedirectSeeOtherException($i18n->r("/profile/sign-out"));
    }
    return $this;
  }

}
