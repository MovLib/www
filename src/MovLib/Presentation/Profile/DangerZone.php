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

use \MovLib\Data\Temporary;
use \MovLib\Data\User\Full as UserFull;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Exception\DatabaseException;
use \MovLib\Presentation\Email\User\Deletion;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\Button;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * Allows a user to terminate sessions and deactivate the account.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DangerZone extends \MovLib\Presentation\Profile\Show {
  use \MovLib\Presentation\TraitFormPage;


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
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function __construct() {
    global $i18n, $kernel, $session;

    // Disallow caching of danger zone settings.
    session_cache_limiter("nocache");

    // We call both auth-methods the session has to ensure that the error message we display is as accurate as possible.
    $session->checkAuthorization($i18n->t("You need to sign in to access the danger zone."));
    $session->checkAuthorizationTimestamp($i18n->t("Please sign in again to verify the legitimacy of this request."));

    // Start rendering the page.
    $this->user = new UserFull(UserFull::FROM_ID, $session->userId);

    if (!empty($_GET["token"])) {
      $this->validateToken();
    }

    $this->init($i18n->t("Danger Zone Settings"));

    // We must instantiate the form before we create the sessions table, otherwise deletions would happen after the
    // table containing the sessions listing was built. Deleted sessions would still be displayed!
    $this->formSessions = new Form($this, [], "{$this->id}-sessions", "deleteSession");

    $buttonText     = $i18n->t("Terminate");
    $buttonTitle    = $i18n->t("Terminate this session, the associated user agent will be signed out immediately.");
    $activeSessions = $session->getActiveSessions();
    while ($activeSession = $activeSessions->fetch_assoc()) {
      $activeSession["authentication"] = $i18n->formatDate($activeSession["authentication"], $this->user->timeZoneIdentifier, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
      $activeSession["ip_address"]     = inet_ntop($activeSession["ip_address"]);
      $active                          = null;
      $button                          = new Button("session_id", $buttonText, [
        "class" => "btn btn-danger",
        "type"  => "submit",
        "value" => $activeSession["id"],
        "title" => $buttonTitle,
      ]);
      unset($button->attributes["id"]);

      if ($activeSession["id"] == $session->id) {
        $active                      = " class='warning'";
        $button->attributes["title"] = $i18n->t("If you click this button your active session is terminated and you’ll be signed out!");
        $button->content             = $i18n->t("Sign Out");
      }

      $this->sessionsTable .=
        "<tr{$active}>" .
        "<td>{$activeSession["authentication"]}</td>" .
        "<td class='small'><code>{$kernel->htmlEncode($activeSession["user_agent"])}</code></td>" .
        "<td><code>{$activeSession["ip_address"]}</code></td>" .
        "<td class='form-actions'>{$button}</td>" .
        "</tr>"
      ;
    }

    $this->form                   = new Form($this);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Delete"), [ "class" => "btn btn-large btn-danger" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;
    return

      // Session Form
      "<h2>{$i18n->t("Active Sessions")}</h2>{$this->formSessions->open()}<table class='table-hover' id='profile-sessions'>" .
        "<caption>{$i18n->t(
          "The following table contains a listing of all your active sessions. You can terminate any session by " .
          "clicking on the button to the right. Your current session is highlighted with a yellow background color " .
          "and the button reads “sign out” instead of “terminate”. If you have the feeling that some sessions that are " .
          "listed weren’t initiated by you, terminate them and consider to {0}set a new password{1} to ensure that " .
          "nobody else has access to your account.",
          [ "<a href='{$i18n->r("/profile/password-settings")}'>", "</a>" ]
        )}</caption><thead><tr><th>{$i18n->t(
          "Sign In Time"
        )}</th><th>{$i18n->t(
          "User Agent"
        )}</th><th>{$i18n->t(
          "IP address"
        )}</th><th></th></tr></thead><tbody>{$this->sessionsTable}</tbody></table>{$this->formSessions->close()}" .

      // Deletion Form
      "<h2>{$i18n->t("Delete Account")}</h2><p>{$i18n->t(
        "If you want to delete your account—for whatever reason—click the button below. All your personal data is " .
        "purged from our system and this action is final. Please note that all your contributions and your username " .
        "will stay in our system. You agreed to release all your contributions to the {0} database along with an " .
        "open and free license, therefor each of your contributions don’t belong to you anymore. Attribution to you " .
        "stays with the username you’ve initially chosen. This doesn’t include any reviews of yours which have no " .
        "open license, they are deleted as well and lost forever. Again, this action is final and there’s no way for " .
        "you to reclaim your account after deletion!"
      , [ $kernel->siteName ])}</p>{$this->form}"
    ;
  }

  /**
   * Attempt to delete the session identified by the submitted session ID from Memcached and our persistent storage.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
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
      throw new RedirectSeeOtherException($i18n->r("/profile/sign-in"));
    }

    // Delete the session from Memcached and our persistent storage.
    try {
      $session->delete($_POST["session_id"]);
    }
    catch (DatabaseException $e) {
      $this->checkErrors($i18n->t("The submitted session ID was invalid."));
    }

    return $this;
  }

  /**
   * No validation at this point, just send the email and display more information to the user.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param array $errors [optional]
   *   {@inheritdoc}
   * @return $this
   */
  public function validate(array $errors = null) {
    global $i18n, $kernel;

    // Send the email for verification of this action.
    $kernel->sendEmail(new Deletion($this->user));

    // The request was accepted but needs further action.
    http_response_code(202);

    // Let the user know where to find the instructions to complete the request.
    $this->alerts .= new Alert(
      $i18n->t("An email with further instructions has been sent to {0}.", [ $this->placeholder($this->user->email) ]),
      $i18n->t("Successfully Requested Deletion"),
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
   * Validate the submitted authentication token and delete the user's account.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @return this
   */
  protected function validateToken() {
    global $i18n, $kernel, $session;
    $tmp = new Temporary();

    if (($data = $tmp->get($_GET["token"])) === false || empty($data["user_id"]) || empty($data["deletion"])) {
      $kernel->alerts .= new Alert(
        $i18n->t("Your confirmation token is invalid or expired, please fill out the form again."),
        $i18n->t("Token Invalid"),
        Alert::SEVERITY_ERROR
      );
      throw new RedirectSeeOtherException($kernel->requestPath);
    }

    if ($data["user_id"] !== $session->userId) {
      throw new UnauthorizedException($i18n->t("The confirmation token is invalid, please sign in again and request a new token to change your password."));
    }

    $this->user->deleteAccount();
    $kernel->delayMethodCall([ $tmp, "delete" ], [ $_GET["token"] ]);

    $session->destroy();

    $kernel->alerts .= new Alert(
      $i18n->t("Your account has been purged from our system. We’re very sorry to see you leave."),
      $i18n->t("Account Deletion Successfull"),
      Alert::SEVERITY_SUCCESS
    );

    throw new RedirectSeeOtherException("/");
  }

}
