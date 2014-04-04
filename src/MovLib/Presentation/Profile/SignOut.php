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

use \MovLib\Partial\Alert;
use \MovLib\Exception\RedirectException\SeeOtherException;

/**
 * Defines the sign out presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SignOut {

  /**
   * Instantiate new sign out presenter.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @throws \MovLib\Exception\RedirectException\SeeOtherException
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    if ($diContainerHTTP->session->isAuthenticated) {
      $diContainerHTTP->session->destroy(true);
      $diContainerHTTP->response->createCookie("alerts", (string) new Alert(
        $diContainerHTTP->intl->t("We hope to see you again soon."),
        $diContainerHTTP->intl->t("Sign Out Successfull"),
        Alert::SEVERITY_SUCCESS
      ));
    }
    throw new SeeOtherException($diContainerHTTP->intl->r("/profile/sign-in"));
  }

}
