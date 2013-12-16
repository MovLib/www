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

use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Presentation\Partial\Alert;

/**
 * Sign the current user out and redirect to sign in presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SignOut {

  /**
   * Instantiate new sign out presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Exception\Client\RedirectSeeOtherException
   */
  public function __construct() {
    global $i18n, $kernel, $session;
    if ($session->isAuthenticated === true) {
      $session->destroy(true);
      $kernel->alerts .= new Alert(
        $i18n->t("We hope to see you again soon."),
        $i18n->t("Sign Out Successfull"),
        Alert::SEVERITY_SUCCESS
      );
    }
    throw new RedirectSeeOtherException($i18n->r("/profile/sign-in"));
  }

}
