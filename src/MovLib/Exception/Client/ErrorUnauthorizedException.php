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
namespace MovLib\Exception\Client;

use \MovLib\Presentation\Profile\SignIn;
use \MovLib\Presentation\Partial\Alert;

/**
 * An unauthorized exception might be thrown is a user is not allowed to access some specific content.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ErrorUnauthorizedException extends \MovLib\Exception\Client\AbstractClientException {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * The sign in presentation.
   *
   * @internal
   *   Keep this public and allow altering of the presentation by throwing class.
   * @var \MovLib\Presentation\Users\SignIn
   */
  public $signInPresentation;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new unauthorized exception.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @param string $message [optional]
   *   The alert's translated message, defaults to <code>$i18n->t("Please use the form below to sign in or {0}join
   *   {sitename}{1}."</code>
   * @param string $title [optional]
   *   The alert's translated title, defaults to <code>$i18n->t("You must be signed in to access this content.")</code>.
   * @param string $severity [optional]
   *   The alert's severity level, default to <code>Alert::SEVERITY_ERROR</code>.
   * @param boolean $destroySession [optional]
   *   Flag to determine if the current session should be destroyed (<code>TRUE</code>) or not (<code>FALSE</code>).
   *   Defaults to <code>FALSE</code>.
   */
  public function __construct($message = null, $title = null, $severity = Alert::SEVERITY_ERROR, $destroySession = false) {
    global $i18n, $kernel, $session;
    parent::__construct("User has to authenticate to view this content.");

    // Ensure that the sign in form won't auto-validate any POST data.
    $kernel->requestMethod = "GET";

    // Use translated defaults if nothing else is provided.
    if (!$message) {
      $message = $i18n->t("Please use the form below to sign in or {0}join {sitename}{1}.", [ "<a href='{$i18n->r("/profile/join")}'>", "</a>", "sitename" => $kernel->siteName ]);
    }
    if (!$title) {
      $title = $i18n->t("You must be signed in to access this content.");
    }

    // If the current session should be destroyed, do so.
    if ($destroySession === true) {
      $kernel->requestURI = $kernel->requestPath;
      $session->destroy();
    }

    // Instantiate the sign in page and add the alert message to the presentation.
    $this->signInPresentation          = new SignIn();
    $this->signInPresentation->alerts .= new Alert($message, $title, $severity);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @link http://stackoverflow.com/a/1088127/1251219
   * @global \MovLib\Data\I18n $i18n
   */
  public function getPresentation() {
    global $i18n;
    header("WWW-Authenticate: MovLib location='{$i18n->r("/profile/sign-in")}'", true, 401);
    return $this->signInPresentation->getPresentation();
  }

}
