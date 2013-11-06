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

use \MovLib\Presentation\Users\Login;
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
class UnauthorizedException extends \MovLib\Exception\Client\AbstractClientException {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * The login presentation.
   *
   * @internal
   *   Keep this public and allow altering of the presentation by throwing class.
   * @var \MovLib\Presentation\Users\Login
   */
  public $loginPresentation;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new unauthorized exception.
   *
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param string $message [optional]
   *   The alert's translated message, defaults to <code>$i18n->t("Please use the form below to sign in or go to the
   *   {0}registration page to sign up{1}."</code>
   * @param string $title [optional]
   *   The alert's translated title, defaults to <code>$i18n->t("You must be signed in to access this content.")</code>.
   * @param string $severity [optional]
   *   The alert's severity level, default to <code>Alert::SEVERITY_ERROR</code>.
   */
  public function __construct($message = null, $title = null, $severity = Alert::SEVERITY_ERROR) {
    global $kernel, $i18n, $session;
    parent::__construct("User has to authenticate to view this content.");

    // Ensure that the login form won't auto-validate any POST data.
    $kernel->requestMethod = "GET";

    // Use translated defaults if nothing else is provided.
    if (!$message) {
      $message = $i18n->t("Please use the form below to sign in or go to the {0}registration page to sign up{1}.", [ "<a href='{$i18n->r("/users/registration")}'>", "</a>" ]);
    }
    if (!$title) {
      $title = $i18n->t("You must be signed in to access this content.");
    }

    // Ensure any active session is destroyed.
    $session->destroy();

    // Instantiate the login page and add the alert message to the presentation.
    $this->loginPresentation          = new Login();
    $this->loginPresentation->alerts .= new Alert($message, $title, $severity);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function getPresentation() {
    global $i18n;

    // Read the following: http://stackoverflow.com/a/1088127/1251219
    header("WWW-Authenticate: MovLib loation='{$i18n->r("/users/login")}'", true, 401);

    return $this->loginPresentation->getPresentation();
  }

}
