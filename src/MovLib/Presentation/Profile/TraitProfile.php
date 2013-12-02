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
 * Shared methods for pages in the Profile namespace.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitProfile {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User\Full
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function init($title = null) {
    global $i18n, $session;
    parent::init($title ?: $i18n->t("Profile"));

    if ($session->isAuthenticated === true) {
      $sidebar = [
        [
          $i18n->r("/profile"),
          "<i class='ico-info'></i> {$i18n->t("Profile")}",
          [ "class" => "separator", "title" => $i18n->t("Check out our account summary.") ]
        ],
        [
          $i18n->r("/profile/account-settings"),
          "<i class='ico-user'></i> {$i18n->t("Account")}",
          [ "title" => $i18n->t("Manage your basic account settings.") ]
        ],
        [
          $i18n->r("/profile/notification-settings"),
          "<i class='ico-notification'></i> {$i18n->t("Notifications")}",
          [ "title" => $i18n->t("Manage your notification settings.") ]
        ],
        [
          $i18n->r("/profile/email-settings"),
          "<i class='ico-email'></i> {$i18n->t("Email")}",
          [ "title" => $i18n->t("Change your email address.") ]
        ],
        [
          $i18n->r("/profile/password-settings"),
          "<i class='ico-lock'></i> {$i18n->t("Password")}",
          [ "title" => $i18n->t("Change your password.") ]
        ],
        [
          $i18n->r("/profile/danger-zone"),
          "<i class='ico-alert'></i> {$i18n->t("Danger Zone")}",
          [ "class" => "delete", "title" => $i18n->t("Manage your sessions and/or deactivate your account.") ]
        ],
      ];
    }
    // A user might visit the password settings page after successfully requesting a reset password email. Only display
    // the actual secondary navigation point the user is able to access at this point and omit everything else.
    else {
      $sidebar = [
        [
          $i18n->r("/profile/password-settings"),
          "<i class='ico-lock'></i> {$i18n->t("Password")}",
          [ "title" => $i18n->t("Change your password.") ]
        ]
      ];
    }
    $this->initSidebar($sidebar);

    return $this;
  }

  /**
   * @inhertidoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->r("/profile"), $i18n->t("Profile") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->form;
  }

}
