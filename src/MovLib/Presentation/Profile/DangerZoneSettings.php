<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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

use \MovLib\Data\User\Full as UserFull;
use \MovLib\Exception\DatabaseException;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Presentation\Email\User\Deactivation;
use \MovLib\Presentation\Email\User\Deletion;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\Button;
use \MovLib\Presentation\Partial\Help;

/**
 * Allows a user to terminate sessions and deactivate the account.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DangerZoneSettings extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\Profile\TraitProfile;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The deactivate button.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Button
   */
  protected $buttonDeactivate;

  /**
   * The delete button.
   *
   * @var \MovLib\Presentation\Partial\FormEelement\Button
   */
  protected $buttonDelete;

  /**
   * The form for the sessions listing.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  protected $formSessions;

  /**
   * String buffer used to construct the table with the session listing.
   *
   * @var string
   */
  protected $sessionsTable;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user danger zone settings presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session->checkAuthorization($i18n->t("You need to sign in to access the danger zone."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    // Start rendering the page.
    $this->init($i18n->t("Danger Zone Settings"));
    $this->user         = new UserFull(UserFull::FROM_ID, $session->userId);

    // We must instantiate the form before we create the sessions table, otherwise deletions would happen after the
    // table containing the sessions listing was built. Deleted sessions would still be displayed!
    $this->formSessions = new Form($this, [], "sessions", "deleteSession");
    $buttonText         = $i18n->t("Terminate");
    $buttonTitle        = $i18n->t("Terminate this session, the associated user agent will be signed out immediately.");

    $sessions           = $session->getActiveSessions();
    $c                  = count($sessions);
    for ($i = 0; $i < $c; ++$i) {
      $sessions[$i]["authentication"] = $i18n->formatDate($sessions[$i]["authentication"], $this->user->timeZoneId, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
      $sessions[$i]["ip_address"]     = inet_ntop($sessions[$i]["ip_address"]);
      $active                         = null;
      $button                         = new Button("session_id", $buttonText, [
        "class" => "button--danger",
        "type"  => "submit",
        "value" => $sessions[$i]["session_id"],
        "title" => $buttonTitle,
      ]);
      unset($button->attributes["id"]);

      if ($sessions[$i]["session_id"] == $session->id) {
        $active                      = " class='warning'";
        $button->attributes["title"] = $i18n->t("If you click this button your active session is terminated and you’ll be signed out!");
        $button->content             = $i18n->t("Sign Out");
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

    $this->form             = new Form($this);
    $this->buttonDeactivate = new Button("deactivate", $i18n->t("Deactivate"), [ "class" => "button--large button--warning" ]);
    $this->buttonDelete     = new Button("delete", $i18n->t("Delete"), [ "class" => "button--large button--danger" ]);

    if ($kernel->requestMethod == "GET" && !empty($_GET["token"])) {
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
      [ "<a href='{$this->routeAccountSettings}'>", "</a>" ]
    )}</p>{$this->form}");
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
          [ "<a href='{$this->routePasswordSettings}'>", "</a>" ]
        )}</caption>" .
        "<thead><tr><th>{$i18n->t("Sign In Time")}</th><th>{$i18n->t("User Agent")}</th><th>{$i18n->t("IP address")}</th><th></th></tr></thead>" .
        "<tbody>{$this->sessionsTable}</tbody>" .
      "</table>{$this->formSessions->close()}" .
      "<h2>{$i18n->t("Deactivate / Delete Account")}</h2>" .
      $this->form->open() .
        "<p>{$i18n->t("If you want to deactivate or delete your account—for whatever reason—click one of the buttons below.")}</p>" .
        "<p>{$i18n->t(
          "We keep all your personal data and make your account inaccessible if you choose to deactivate it. You can " .
          "sign in with your email address and password later on to reactivate your account."
        )}</p>" .
        "<p>{$this->buttonDeactivate}</p>" .
        "<p>{$i18n->t(
          "We purge all your personal data from our system if you choose to delete your account. Please note that all " .
          "your contributions and your username will stay in our system and you’ll never be able to reclaim your " .
          "account. This is because your email address will be purged as well and afterwards there’s no possibility to " .
          "reactivate your account."
        )}</p>" .
        "<p>{$this->buttonDelete}</p>" .
      $this->form->close()
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

    // Nothing to do if we have no session ID.
    if (empty($_POST["session_id"])) {
      return $this;
    }

    // Delete own session means sign out.
    if ($_POST["session_id"] == $session->id) {
      $session->destroy();
      throw new RedirectSeeOtherException($i18n->r("/users/login"));
    }

    // Delete the session from Memcached and our persistent storage.
    try {
      $session->delete($_POST["session_id"]);
    }
    catch (DatabaseException $e) {
      $this->checkErrors([ $i18n->t("The submitted session ID was invalid.") ]);
    }

    return $this;
  }

  /**
   * Delete all personal data and sign out the user.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return $this
   */
  public function validate(array $errors = null) {
    global $i18n, $kernel;

    // Nothing to do!
    if (!isset($_POST["deactivate"]) && !isset($_POST["delete"])) {
      return $this;
    }

    if (isset($_POST["deactivate"])) {
      $kernel->sendEmail(new Deactivation($this->user));
      $title = $i18n->t("Successfully Requested Deactivation");
    }
    else {
      $kernel->sendEmail(new Deletion($this->user));
      $title = $i18n->t("Successfully Requested Deletion");
    }

    // The request was accepted but needs further action.
    http_response_code(202);

    // Let the user know where to find the instructions to complete the request.
    $this->alerts .= new Alert(
      $i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]),
      $title,
      Alert::SEVERITY_SUCCESS
    );

    // Make sure the user really understand what to do.
    $this->alerts .= new Alert(
      $i18n->t("You have to follow the link that we just sent to you via email to complete this action."),
      $i18n->t("Important!"),
      Alert::SEVERITY_INFO
    );

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
  protected function validateToken() {
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
