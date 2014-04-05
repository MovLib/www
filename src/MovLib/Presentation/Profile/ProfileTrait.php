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

/**
 * Defines methods and properties shared by all profile presenters.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait ProfileTrait {
  use \MovLib\Partial\SidebarTrait;

  /**
   * Initialize a profile presentation.
   *
   * @param string $unauthorizedMessage
   *   The message to display along the unauthorized exception if a client tries to access this route without being
   *   signed in.
   * @param string $title
   *   The presentation's translated title.
   * @param string $routeKey
   *   The presentations' untranslated route key for the language links.
   * @return this
   */
  final protected function initProfilePresentation($unauthorizedMessage, $title, $routeKey) {
    // All profile presentations' require at least an active and valid session.
    $this->session->checkAuthorization($unauthorizedMessage);

    $this->initPage($title);

    // Only add the profile route to the breadcrumb if we aren't currently viewing it.
    $profileRoute = $this->intl->r("/profile");
    if ($this->request->path == $profileRoute) {
      $this->initBreadcrumb();
    }
    else {
      $this->initBreadcrumb([[ $profileRoute, $this->intl->t("Profile") ]]);
    }

    $this->initLanguageLinks($routeKey);

    $this->sidebarInit([
      [ $profileRoute, $this->intl->t("Profile"), [ "class" => "ico ico-user" ] ],
      [ $this->intl->r("/profile/messages"), $this->intl->t("Messages"), [ "class" => "ico ico-email" ] ],
      [ $this->intl->r("/profile/collection"), $this->intl->t("Collection"), [ "class" => "ico ico-release" ] ],
      [ $this->intl->r("/profile/wantlist"), $this->intl->t("Wantlist"), [ "class" => "ico ico-heart" ] ],
      [ $this->intl->r("/profile/lists"), $this->intl->t("Lists"), [ "class" => "ico ico-ul" ] ],
      [ $this->intl->r("/profile/watchlist"), $this->intl->t("Watchlist"), [ "class" => "separator ico ico-view" ] ],
      [ $this->intl->r("/profile/account-settings"), $this->intl->t("Account"), [ "class" => "ico ico-settings" ] ],
      [ $this->intl->r("/profile/notification-settings"), $this->intl->t("Notifications"), [ "class" => "ico ico-notification" ] ],
      [ $this->intl->r("/profile/email-settings"), $this->intl->t("Email"), [ "class" => "ico ico-email" ] ],
      [ $this->intl->r("/profile/password-settings"), $this->intl->t("Password"), [ "class" => "ico ico-lock" ] ],
      [ $this->intl->r("/profile/danger-zone"), $this->intl->t("Danger Zone"), [ "class" => "ico ico-alert" ] ],
    ]);

    return $this;
  }

}
