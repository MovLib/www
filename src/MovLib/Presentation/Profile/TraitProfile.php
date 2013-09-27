<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitProfile {

  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User
   */
  protected $user;

  /**
   * The translated profile route.
   *
   * @var string
   */
  protected $profileRoute;

  /**
   * The translated text "Profile".
   *
   * @var string
   */
  protected $profileText;

  /**
   * @inheritdoc
   */
  protected function init($title) {
    global $i18n;
    $this->profileRoute = $i18n->r("/profile");
    $this->profileText  = $i18n->t("Profile");
    return parent::init($title ?: $this->profileText);
  }

  /**
   * @inhertidoc
   */
  protected function getBreadcrumbs() {
    return [[ $this->profileRoute, $this->profileText ]];
  }

  /**
   * @inheritdoc
   */
  protected function getSecondaryNavigationMenuitems() {
    global $i18n;
    return [
      [
        $this->profileRoute,
        "<i class='icon icon--info-circled'></i> {$this->profileText}",
        [ "class" => "separator", "title" => $i18n->t("Check out our account summary.") ]
      ],
      [
        $i18n->r("/profile/account-settings"),
        "<i class='icon icon--user'></i> {$i18n->t("Account")}",
        [ "title" => $i18n->t("Manage your basic account settings.") ]
      ],
      [
        $i18n->r("/profile/notification-settings"),
        "<i class='icon icon--bell'></i> {$i18n->t("Notifications")}",
        [ "title" => $i18n->t("Manage your notification settings.") ]
      ],
      [
        $i18n->r("/profile/email-settings"),
        "<i class='icon icon--mail'></i> {$i18n->t("Email")}",
        [ "title" => $i18n->t("Change your email address.") ]
      ],
      [
        $i18n->r("/profile/password-settings"),
        "<i class='icon icon--lock'></i> {$i18n->t("Password")}",
        [ "title" => $i18n->t("Change your password.") ]
      ],
      [
        $i18n->r("/profile/danger-zone-settings"),
        "<i class='icon icon--alert'></i> {$i18n->t("Danger Zone")}",
        [ "class" => "delete", "title" => $i18n->t("Manage your sessions and/or deactivate your account.") ]
      ],
    ];
  }

}
