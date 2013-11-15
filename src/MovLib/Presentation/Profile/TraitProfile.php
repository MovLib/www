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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User\Full
   */
  protected $user;

  /**
   * The translated text "Profile".
   *
   * @var string
   */
  protected $profileText;

  /**
   * The translated profile route.
   *
   * @var string
   */
  protected $routeProfile;

  /**
   * The translated account settings route.
   *
   * @var string
   */
  protected $routeAccountSettings;

  /**
   * The translated notification settings route.
   *
   * @var string
   */
  protected $routeNotificationSettings;

  /**
   * The translated email settings route.
   *
   * @var string
   */
  protected $routeEmailSettings;

  /**
   * The translated password settings route.
   *
   * @var string
   */
  protected $routePasswordSettings;

  /**
   * The translated danger zone settings route.
   *
   * @var string
   */
  protected $routeDangerZoneSettings;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function init($title = null) {
    global $i18n;
    $this->routeProfile = $i18n->r("/profile");
    $this->profileText  = $i18n->t("Profile");
    if (!$title) {
      $title = $this->profileText;
    }
    $this->routeAccountSettings      = $i18n->r("/profile/account-settings");
    $this->routeNotificationSettings = $i18n->r("/profile/notification-settings");
    $this->routeEmailSettings        = $i18n->r("/profile/email-settings");
    $this->routePasswordSettings     = $i18n->r("/profile/password-settings");
    $this->routeDangerZoneSettings   = $i18n->r("/profile/danger-zone-settings");
    return parent::init($title);
  }

  /**
   * @inhertidoc
   */
  protected function getBreadcrumbs() {
    return [[ $this->routeProfile, $this->profileText ]];
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  protected function getSecondaryNavigationMenuitems() {
    global $i18n, $session;
    if ($session->isAuthenticated === true) {
      return [
        [
          $this->routeProfile,
          "<i class='icon icon--info-circled'></i> {$this->profileText}",
          [ "class" => "separator", "title" => $i18n->t("Check out our account summary.") ]
        ],
        [
          $this->routeAccountSettings,
          "<i class='icon icon--user'></i> {$i18n->t("Account")}",
          [ "title" => $i18n->t("Manage your basic account settings.") ]
        ],
        [
          $this->routeNotificationSettings,
          "<i class='icon icon--bell'></i> {$i18n->t("Notifications")}",
          [ "title" => $i18n->t("Manage your notification settings.") ]
        ],
        [
          $this->routeEmailSettings,
          "<i class='icon icon--mail'></i> {$i18n->t("Email")}",
          [ "title" => $i18n->t("Change your email address.") ]
        ],
        [
          $this->routePasswordSettings,
          "<i class='icon icon--lock'></i> {$i18n->t("Password")}",
          [ "title" => $i18n->t("Change your password.") ]
        ],
        [
          $this->routeDangerZoneSettings,
          "<i class='icon icon--alert'></i> {$i18n->t("Danger Zone")}",
          [ "class" => "delete", "title" => $i18n->t("Manage your sessions and/or deactivate your account.") ]
        ],
      ];
    }
    // A user might visit the password settings page after successfully requesting a reset password email. Only display
    // the actual secondary navigation point the user is able to access at this point and omit everything else.
    else {
      return [
        [
          $this->routePasswordSettings,
          "<i class='icon icon--lock'></i> {$i18n->t("Password")}",
          [ "title" => $i18n->t("Change your password.") ]
        ]
      ];
    }
  }

}
