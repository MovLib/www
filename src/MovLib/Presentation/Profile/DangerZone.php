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

use \MovLib\Data\TemporaryStorage;
use \MovLib\Exception\ClientException\UnauthorizedException;
use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Mail\Mailer;
use \MovLib\Mail\Profile\AccountDeletionEmail;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;

/**
 * Defines the profile danger zone presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class DangerZone extends \MovLib\Presentation\Profile\AbstractProfilePresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initProfilePresentation(
      $this->intl->t("You need to sign in to access the danger zone."),
      $this->intl->t("Danger Zone"),
      "/profile/danger-zone",
      true,
      $this->intl->t("Please sign in again to verify the legitimacy of this request.")
    );
    $this->stylesheets[] = "danger-zone";
    if ($this->request->methodGET && ($token = $this->request->filterInputString(INPUT_GET, $this->intl->r("token")))) {
      $this->validateToken($token);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // Make sure that really all sessions are present in the database.
    $this->session->insert();

    // We must initialize the form before we create the sessions table, otherwise deletions would happen after the table
    // containing the sessions listing was built. Deleted sessions would still be displayed!
    $sessions       = (new Form($this->diContainerHTTP, "sessions"))->init([ $this, "deleteSession" ]);
    $sessionsTable  = "";
    $buttonText     = $this->intl->t("Terminate");
    $buttonTitle    = $this->intl->t("Terminate this session, the associated user agent will be signed out immediately.");
    /* @var $activeSession \MovLib\Stub\Core\HTTP\ActiveSessionSet */
    foreach ($this->session->getActiveSessions() as $activeSession) {
      if ($activeSession->ssid == $this->session->ssid) {
        $active = " class='warning'";
        $title  = $this->intl->t("If you use this button all your active session will be terminated and you’ll be signe out!");
        $text   = $this->intl->t("Sign Out");
      }
      else {
        $active = null;
        $title  = $buttonTitle;
        $text   = $buttonText;
      }

      $sessionsTable .=
        "<tr{$active}>" .
        "<td>{$activeSession->authentication->formatIntl($this->intl->locale, $this->user->timezone)}</td>" .
        "<td class='small'><code>{$this->htmlEncode($activeSession->userAgent)}</code></td>" .
        "<td><code>{$activeSession->remoteAddress}</code></td>" .
        "<td class='form-actions'><button class='btn btn-danger' name='ssid' title='{$title}' type='submit' value='{$activeSession->ssid}'>{$text}</button></td>" .
        "</tr>"
      ;
    }
    $sessionsTable =
      "<h2>{$this->intl->t("Active Sessions")}</h2>{$sessions->open()}<table class='table-hover' id='profile-sessions'>" .
      "<caption>{$this->intl->t(
        "The following table contains a listing of all your active sessions. You can terminate any session by " .
        "clicking on the button to the right. Your current session is highlighted with a yellow background color " .
        "and the button reads “sign out” instead of “terminate”. If you have the feeling that some sessions that are " .
        "listed weren’t initiated by you, terminate them and consider to {0}set a new password{1} to ensure that " .
        "nobody else has access to your account.",
        [ "<a href='{$this->intl->r("/profile/password-settings")}'>", "</a>" ]
      )}</caption><thead><tr><th>{$this->intl->t(
        "Sign In Time"
      )}</th><th>{$this->intl->t(
        "User Agent"
      )}</th><th>{$this->intl->t(
        "IP address"
      )}</th><th></th></tr></thead><tbody>{$sessionsTable}</tbody></table>{$sessions->close()}"
    ;

    $deletion = (new Form($this->diContainerHTTP))
      ->addAction($this->intl->t("Delete"), [ "class" => "btn btn-large btn-danger" ])
      ->init([ $this, "deleteAccount" ])
    ;
    return
      "{$sessionsTable}<h2>{$this->intl->t("Delete Account")}</h2><p>{$this->intl->t(
        "If you want to delete your account—for whatever reason—click the button below. All your personal data is " .
        "purged from our system and this action is final. Please note that all your contributions and your username " .
        "will stay in our system. You agreed to release all your contributions to the {sitename} database along with " .
        "an open and free license, therefor each of your contributions don’t belong to you anymore. Attribution to " .
        "you stays with the username you’ve initially chosen. This doesn’t include any reviews of yours which have no " .
        "open license, they are deleted as well and lost forever. Again, this action is final and there’s no way for " .
        "you to reclaim your account after deletion!",
        [ "sitename" => $this->config->sitename ]
      )}</p>{$deletion}"
    ;
  }

  /**
   * Send account deletion email.
   *
   * @return this
   */
  public function deleteAccount() {
    // The request was accepted but needs further action.
    http_response_code(202);
    (new Mailer())->send($this->diContainerHTTP, new AccountDeletionEmail($this->user));

    // Let the user know where to find the instructions to complete the request.
    $this->alerts .= new Alert(
      $this->intl->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]),
      $this->intl->t("Successfully Requested Deletion"),
      Alert::SEVERITY_SUCCESS
    );

    // Make sure the user really understand what to do.
    $this->alerts .= new Alert(
      $this->intl->t("You have to follow the link that we just sent to you via email to complete this action."),
      $this->intl->t("Important!"),
      Alert::SEVERITY_INFO
    );

    return $this;
  }

  /**
   * Attempt to delete the session identified by the submitted session identifier from Memcached and our persistent
   * storage.
   *
   * @return this
   */
  public function deleteSession() {
    $ssid = $this->request->filterInputString(INPUT_POST, "ssid");

    if (!$ssid) {
      $this->alerts .= new Alert(
        $this->intl->t("The submitted session identifier was invalid."),
        $this->intl->t("Validation Error"),
        Alert::SEVERITY_ERROR
      );
    }
    elseif ($ssid == $this->session->ssid) {
      $this->alerts .= new Alert(
        $this->intl->t("You’ve been signed out from your current session and all your active sessions have been deleted."),
        $this->intl->t("Successfully Signed Out"),
        Alert::SEVERITY_SUCCESS
      );
      $this->session->destroy(true);
      throw new SeeOtherException($this->intl->r("/profile/sign-in"));
    }
    else {
      $this->session->delete($ssid);
    }

    return $this;
  }

  /**
   * Validate the submitted authentication token and delete the user's account.
   *
   * @return this
   */
  protected function validateToken($token) {
    $tmp = new TemporaryStorage($this->diContainerHTTP);
    $userId = $tmp->get($token);

    if ($userId === false || empty($userId)) {
      $this->alerts .= new Alert(
        $this->intl->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new SeeOtherException($this->request->path);
    }

    $this->kernel->delayMethodCall([ $tmp, "delete" ], [ $token ]);

    if ($userId !== $this->session->userId) {
      throw new UnauthorizedException(new Alert(
        $this->intl->t("The confirmation token is invalid, please sign in again and request a new token."),
        $this->intl->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      ));
    }

    $this->user->deleteAccount();
    $this->session->destroy(true);

    $this->alerts .= new Alert(
      $this->intl->t("Your account has been purged from our system. We’re very sorry to see you leave."),
      $this->intl->t("Account Deletion Successfull"),
      Alert::SEVERITY_SUCCESS
    );

    throw new SeeOtherException("/");
  }

}
